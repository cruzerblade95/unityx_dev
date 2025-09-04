<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ProcessTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chatId;
    public $text;
    public $telegramUsername;
    public $messageData;

    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $text, $telegramUsername, $messageData = [])
    {
        $this->chatId = $chatId;
        $this->text = $text;
        $this->telegramUsername = $telegramUsername;
        $this->messageData = $messageData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing queued Telegram message', [
                'chat_id' => $this->chatId,
                'text' => $this->text,
                'username' => $this->telegramUsername
            ]);

            if ($this->text === '/start') {
                $reply = "Welcome! Please type /verify to link your account.";
                Log::info('Sending start message', ['chat_id' => $this->chatId]);
                $this->sendTelegramMessage($this->chatId, $reply);
            } elseif ($this->text === '/verify') {
                $reply = "Please enter your email to link your account.";
                Log::info('Sending verify message', ['chat_id' => $this->chatId]);
                $this->sendTelegramMessage($this->chatId, $reply);
            } elseif (filter_var($this->text, FILTER_VALIDATE_EMAIL)) {
                Log::info('Processing email verification', ['email' => $this->text, 'chat_id' => $this->chatId]);
                $user = User::where('email', $this->text)->first();
                if ($user) {
                    $token = Str::random(40);
                    DB::table('telegram_verifications')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'token' => $token,
                            'expires_at' => Carbon::now()->addMinutes(30),
                            'telegram_username' => $this->telegramUsername,
                            'chat_id' => $this->chatId,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                    // Include token for verification
                    $link = URL::to('/telegram/verify?token=' . $token);
                    $this->sendTelegramMessage($this->chatId, "Click to verify your profile: $link");
                    Log::info('Verification link sent', ['user_id' => $user->id, 'chat_id' => $this->chatId]);
                } else {
                    $this->sendTelegramMessage($this->chatId, "No user found for this email.");
                    Log::warning('User not found for email', ['email' => $this->text, 'chat_id' => $this->chatId]);
                }
            } else {
                // For any other message that's not /start, /verify, or email
                $reply = "Please type /start to begin.";
                Log::info('Sending default message', ['chat_id' => $this->chatId, 'text' => $this->text]);
                $this->sendTelegramMessage($this->chatId, $reply);
            }
        } catch (\Exception $e) {
            Log::error('Error processing queued Telegram message', [
                'error' => $e->getMessage(),
                'chat_id' => $this->chatId,
                'text' => $this->text
            ]);
        }
    }

    private function sendTelegramMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        if (!$token) {
            Log::error('Telegram bot token not configured');
            return false;
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        try {
            Log::info('Attempting to send Telegram message from job', [
                'chat_id' => $chatId,
                'text' => $text,
                'url' => $url
            ]);

            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'form_params' => $params,
                'timeout' => 15,
                'connect_timeout' => 10,
                'verify' => false,
                'http_errors' => false
            ]);

            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody, true);

            Log::info('Telegram API response from job', [
                'status_code' => $response->getStatusCode(),
                'response_body' => $responseBody,
                'parsed_result' => $result
            ]);

            if ($response->getStatusCode() == 200 && isset($result['ok']) && $result['ok']) {
                Log::info('Telegram message sent successfully from job', ['chat_id' => $chatId]);
                return true;
            } else {
                Log::error('Telegram API returned error from job', [
                    'status_code' => $response->getStatusCode(),
                    'error_code' => $result['error_code'] ?? 'unknown',
                    'description' => $result['description'] ?? 'unknown'
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Telegram send error from job', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'text' => $text
            ]);
            return false;
        }
    }
}
