<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class TelegramNotificationService
{
    /**
     * Send notification to user's Telegram account
     */
    public static function sendNotificationToTelegram($userId, $title, $message)
    {
        try {
            $user = User::find($userId);

            if (!$user || !$user->telegram_id) {
                Log::info('User not found or no telegram_id', ['user_id' => $userId]);
                return false;
            }

            $botToken = Config::get('services.telegram.bot_token');
            if (!$botToken) {
                Log::error('Telegram bot token not configured');
                return false;
            }

            // Format the notification message with username for multi-account support
            $telegramMessage = "🔔 <b>{$title}</b>\n\n";
            $telegramMessage .= "{$message}\n\n";
            $telegramMessage .= "👤 Account: <b>@{$user->username}</b>";

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            // Use telegram_id from user table - should be chat_id (numeric)
            $chatId = $user->telegram_id;

            // If telegram_id is not numeric (old username format), try to get chat_id from verification table
            if (!is_numeric($chatId)) {
                Log::info('telegram_id is not numeric, checking verification table', [
                    'user_id' => $userId,
                    'telegram_id' => $user->telegram_id
                ]);

                $verification = DB::table('telegram_verifications')
                    ->where('user_id', $user->id)
                    ->first();

                if ($verification && $verification->chat_id) {
                    $chatId = $verification->chat_id;
                    Log::info('Found chat_id in verification table', [
                        'user_id' => $userId,
                        'chat_id' => $chatId
                    ]);
                } else {
                    Log::warning('No chat_id found in verification table', [
                        'user_id' => $userId,
                        'telegram_id' => $user->telegram_id
                    ]);
                }
            }

            if (!$chatId || !is_numeric($chatId)) {
                Log::warning('No valid numeric chat_id found for user', [
                    'user_id' => $userId,
                    'telegram_id' => $user->telegram_id,
                    'final_chat_id' => $chatId
                ]);
                return false;
            }

            $params = [
                'chat_id' => $chatId,
                'text' => $telegramMessage,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            $client = new Client(['timeout' => 10, 'verify' => false]);
            $response = $client->post($url, ['form_params' => $params]);

            if ($response->getStatusCode() === 200) {
                Log::info('Telegram notification sent successfully', [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'chat_id' => $chatId,
                    'title' => $title
                ]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public static function sendBulkNotificationToTelegram($userIds, $title, $message)
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($userIds as $userId) {
            if (self::sendNotificationToTelegram($userId, $title, $message)) {
                $successCount++;
            } else {
                $failureCount++;
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }

        Log::info('Bulk Telegram notifications sent', [
            'total_users' => count($userIds),
            'successful' => $successCount,
            'failed' => $failureCount
        ]);

        return [
            'successful' => $successCount,
            'failed' => $failureCount,
            'total' => count($userIds)
        ];
    }

    /**
     * Test if user can receive Telegram notifications
     */
    public static function testTelegramConnection($userId)
    {
        return self::sendNotificationToTelegram(
            $userId,
            '🧪 Test Notification',
            'This is a test message to verify your Telegram connection is working properly.'
        );
    }
}
