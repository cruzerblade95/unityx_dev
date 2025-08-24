<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

class TelegramController extends Controller
{
    // Telegram webhook handler
    public function webhook(Request $request)
    {
        // Debug: log all incoming requests to a file
        file_put_contents(storage_path('logs/telegram_test.log'), json_encode($request->all()) . PHP_EOL, FILE_APPEND);
        Log::info('TELEGRAM WEBHOOK', $request->all());
        $data = $request->all();
        if (isset($data['message']) && isset($data['message']['chat']['id'])) {
            $chatId = $data['message']['chat']['id'];
            $text = $data['message']['text'] ?? '';

            // Extract telegram username from the webhook data
            $telegramUsername = $data['message']['from']['username'] ?? null;

            if ($text === '/start') {
                $reply = "Welcome! Please type /verify to link your account.";
                $this->sendTelegramMessage($chatId, $reply);
            } elseif ($text === '/verify') {
                $reply = "Please enter your email to link your account.";
                $this->sendTelegramMessage($chatId, $reply);
            } elseif (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $text)->first();
                if ($user) {
                    $token = Str::random(40);
                    DB::table('telegram_verifications')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'token' => $token,
                            'expires_at' => Carbon::now()->addMinutes(30),
                            'telegram_username' => $telegramUsername,
                            'chat_id' => $chatId,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                    // Include token for verification
                    $link = URL::to('/telegram/verify?token=' . $token);
                    $this->sendTelegramMessage($chatId, "Click to verify your profile: $link");
                } else {
                    $this->sendTelegramMessage($chatId, "No user found for this email.");
                }
            } else {
                // For any other message that's not /start, /verify, or email
                $reply = "Please type /start to begin.";
                $this->sendTelegramMessage($chatId, $reply);
            }
        }
        return response('ok', 200);
    }

    // Verification link handler
    public function verify(Request $request)
    {
        $token = $request->query('token');
        $row = DB::table('telegram_verifications')->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
        if ($row) {
            $user = User::find($row->user_id);
            if ($user) {
                $user->profile_complete = 1;
                // Store the chat_id for sending notifications (not the username)
                if ($row->chat_id) {
                    $user->telegram_id = $row->chat_id;
                } elseif ($row->telegram_username) {
                    // Fallback to username if no chat_id (shouldn't happen)
                    $user->telegram_id = $row->telegram_username;
                }
                $user->save();
                // Keep the verification row for potential future use (don't delete)
                // DB::table('telegram_verifications')->where('id', $row->id)->delete();
                return Redirect::to('/user/login')->with('success', 'Profile verified and Telegram linked! Please login to continue.');
            }
        }
        return Redirect::to('/user/login')->with('error', 'Verification link has expired or is invalid. Please try again.');
    }

    // Helper to send message via Telegram API
    private function sendTelegramMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN'); // Changed from Config::get('services.telegram.bot_token')
        if (!$token) {
            Log::error('Telegram bot token not configured');
            return;
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, ['form_params' => $params]);
            Log::info('Telegram message sent successfully', ['chat_id' => $chatId]);
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage());
        }
    }

    public function setupWebhook(Request $request)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $webhookUrl = url('/api/telegram/webhook');

        if (!$botToken) {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN not found in .env file'
            ]);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'form_params' => [
                    'url' => $webhookUrl
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'success' => $result['ok'] ?? false,
                'message' => $result['description'] ?? 'Unknown response',
                'webhook_url' => $webhookUrl,
                'telegram_response' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error setting webhook: ' . $e->getMessage(),
                'webhook_url' => $webhookUrl
            ]);
        }
    }
}
