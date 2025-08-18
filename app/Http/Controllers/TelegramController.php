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
                // Update telegram_id with username if available, otherwise use chat_id
                if ($row->telegram_username) {
                    $user->telegram_id = $row->telegram_username;
                } elseif ($row->chat_id) {
                    $user->telegram_id = $row->chat_id;
                }
                $user->save();
                // Optionally, delete the verification row
                DB::table('telegram_verifications')->where('id', $row->id)->delete();
                return Redirect::to('/user/login')->with('success', 'Profile verified and Telegram linked! Please login to continue.');
            }
        }
        return Redirect::to('/user/login')->with('error', 'Verification link has expired or is invalid. Please try again.');
    }

    // Helper to send message via Telegram API
    private function sendTelegramMessage($chatId, $text)
    {
        $token = Config::get('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $client->post($url, ['form_params' => $params]);
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage());
        }
    }
}
