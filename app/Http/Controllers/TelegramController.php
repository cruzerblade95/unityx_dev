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
        // Return success immediately to prevent timeout
        $response = response('ok', 200);

        // Use fastcgi_finish_request if available to send response immediately
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        try {
            $this->processWebhookAsync($request);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
        }

        return $response;
    }

    private function processWebhookAsync(Request $request)
    {
        // Enhanced logging for debugging
        $rawContent = $request->getContent();
        $data = $request->all();

        // Log everything for debugging
        Log::info('TELEGRAM WEBHOOK RECEIVED', [
            'headers' => $request->headers->all(),
            'raw_content' => $rawContent,
            'parsed_data' => $data,
            'method' => $request->method(),
            'ip' => $request->ip(),
            'url' => $request->fullUrl()
        ]);

        // Also log to file for easier debugging
        file_put_contents(
            storage_path('logs/telegram_webhook_' . date('Y-m-d') . '.log'),
            "[" . date('Y-m-d H:i:s') . "] " . json_encode([
                'data' => $data,
                'raw' => $rawContent
            ], JSON_PRETTY_PRINT) . PHP_EOL,
            FILE_APPEND
        );

        if (isset($data['message']) && isset($data['message']['chat']['id'])) {
            $chatId = $data['message']['chat']['id'];
            $text = $data['message']['text'] ?? '';
            $telegramUsername = $data['message']['from']['username'] ?? null;

            Log::info('Processing Telegram message', [
                'chat_id' => $chatId,
                'text' => $text,
                'from' => $data['message']['from'] ?? 'unknown'
            ]);

            // Process synchronously for now to avoid queue issues
            $this->processTelegramMessageSync($chatId, $text, $telegramUsername);
        } else {
            Log::warning('Invalid webhook data received', ['data' => $data]);
        }
    }

    private function processTelegramMessageSync($chatId, $text, $telegramUsername)
    {
        if ($text === '/start') {
            $reply = "Welcome! Please type /verify to link your account.";
            Log::info('Sending start message (sync)', ['chat_id' => $chatId]);
            $this->sendTelegramMessage($chatId, $reply);
        } elseif ($text === '/verify') {
            $reply = "Please enter your email to link your account.";
            Log::info('Sending verify message (sync)', ['chat_id' => $chatId]);
            $this->sendTelegramMessage($chatId, $reply);
        } elseif (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            Log::info('Processing email verification (sync)', ['email' => $text, 'chat_id' => $chatId]);
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
                Log::info('Verification link sent (sync)', ['user_id' => $user->id, 'chat_id' => $chatId]);
            } else {
                $this->sendTelegramMessage($chatId, "No user found for this email.");
                Log::warning('User not found for email (sync)', ['email' => $text, 'chat_id' => $chatId]);
            }
        } else {
            // For any other message that's not /start, /verify, or email
            $reply = "Please type /start to begin.";
            Log::info('Sending default message (sync)', ['chat_id' => $chatId, 'text' => $text]);
            $this->sendTelegramMessage($chatId, $reply);
        }
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
                // Store the telegram_username for marketplace display and profile
                if ($row->telegram_username) {
                    $user->telegram_username = $row->telegram_username;
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
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'form_params' => $params,
                'timeout' => 5, // Very short timeout
                'connect_timeout' => 3, // Very short connect timeout
                'verify' => false,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                if (isset($result['ok']) && $result['ok']) {
                    Log::info('Telegram message sent successfully', ['chat_id' => $chatId]);
                    return true;
                }
            }

            Log::error('Telegram API error', [
                'status_code' => $statusCode,
                'chat_id' => $chatId
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Telegram send error', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return false;
        }
    }

    public function setupWebhook(Request $request)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        // Use the provided webhook URL or construct from current request
        $webhookUrl = $request->input('webhook_url') ?: $request->getSchemeAndHttpHost() . '/api/telegram/webhook';

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
