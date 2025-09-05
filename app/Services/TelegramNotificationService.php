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

            // Check if user has telegram verification record
            if (!$user) {
                Log::info('User not found', ['user_id' => $userId]);
                return false;
            }

            $telegramVerification = DB::table('telegram_verifications')
                ->where('user_id', $user->id)
                ->whereNotNull('chat_id')
                ->first();

            if (!$telegramVerification) {
                Log::info('User has no telegram verification or chat_id', ['user_id' => $userId]);
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

            // Use chat_id from telegram verification table
            $chatId = $telegramVerification->chat_id;

            if (!$chatId || !is_numeric($chatId)) {
                Log::warning('No valid numeric chat_id found for user', [
                    'user_id' => $userId,
                    'chat_id' => $chatId
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
     * Send OTP code to user's Telegram account
     */
    public static function sendOtpToTelegram($user, $otp, $withdrawalType = 'crypto')
    {
        // Handle both User object and user ID
        $userId = is_object($user) ? $user->id : $user;

        // Handle both OTP object and OTP code string
        $otpCode = is_object($otp) ? $otp->otp_code : $otp;

        Log::info('Sending OTP to Telegram', [
            'user_id' => $userId,
            'otp_code' => $otpCode,
            'withdrawal_type' => $withdrawalType
        ]);

        $title = "🔐 Withdrawal OTP Verification";
        $message = "Your 6-digit OTP code for {$withdrawalType} withdrawal is:\n\n";
        $message .= "<code>{$otpCode}</code>\n\n";
        $message .= "⏰ This code will expire in 5 minutes.\n";
        $message .= "🔒 Do not share this code with anyone.";

        $result = self::sendNotificationToTelegram($userId, $title, $message);

        Log::info('OTP Telegram send result', [
            'user_id' => $userId,
            'success' => $result
        ]);

        return $result;
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
