<?php

namespace App\Services;

use App\Models\User;
use App\Models\TelegramVerification;
use App\Services\TelegramNotificationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class CustomPasswordResetService
{
    /**
     * Send password reset notification via email and telegram
     */
    public static function sendResetLinkNotification($email)
    {
        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found with this email address.'
                ];
            }

            // Generate password reset token using Laravel's built-in system
            $token = app('auth.password.broker')->createToken($user);

            // Create telegram verification token
            $telegramToken = Str::random(32);
            $expiresAt = Carbon::now()->addHours(1); // Token expires in 1 hour

            // Store telegram verification
            $existingVerification = TelegramVerification::where('user_id', $user->id)->first();
            TelegramVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'token' => $telegramToken,
                    'telegram_username' => $existingVerification ? $existingVerification->telegram_username : null,
                    'chat_id' => $existingVerification ? $existingVerification->chat_id : null,
                    'expires_at' => $expiresAt,
                ]
            );

            // Create reset URLs
            $emailResetUrl = URL::temporarySignedRoute(
                'user.password.reset.email',
                now()->addHours(1),
                ['token' => $token, 'email' => $user->email]
            );

            $telegramResetUrl = URL::temporarySignedRoute(
                'user.password.reset.telegram',
                now()->addHours(1),
                ['token' => $telegramToken, 'email' => $user->email]
            );

            // Send email notification
            $emailSent = self::sendEmailNotification($user, $token, $emailResetUrl);

            // Send telegram notification if user has telegram verification
            $telegramSent = false;
            if ($user->telegramVerification && $user->telegramVerification->telegram_username) {
                $telegramSent = self::sendTelegramNotification($user, $telegramResetUrl);
            }

            $message = 'Password reset instructions have been sent to your email.';
            if ($telegramSent) {
                $message .= ' You will also receive a notification on Telegram.';
            } elseif ($user->telegramVerification && $user->telegramVerification->telegram_username) {
                $message .= ' We attempted to send a Telegram notification but it may have failed.';
            }

            return [
                'success' => true,
                'message' => $message,
                'email_sent' => $emailSent,
                'telegram_sent' => $telegramSent
            ];

        } catch (\Exception $e) {
            Log::error('Password reset service error: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.'
            ];
        }
    }

    /**
     * Send email notification
     */
    private static function sendEmailNotification($user, $token, $resetUrl)
    {
        try {
            $user->sendPasswordResetNotification($token);
            return true;
        } catch (\Exception $e) {
            Log::error('Email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send telegram notification
     */
    private static function sendTelegramNotification($user, $resetUrl)
    {
        try {
            // Try to get telegram username from existing telegram verification or use user's username
            $telegramVerification = TelegramVerification::where('user_id', $user->id)->first();
            $telegramUsername = $telegramVerification && $telegramVerification->telegram_username
                ? '@' . $telegramVerification->telegram_username
                : '@' . $user->username;

            $title = "🔒 Password Reset Request";
            $message = "Hello {$telegramUsername}!\n\n";
            $message .= "We received a request to reset your password for your UnityX account.\n\n";
            $message .= "🔗 Click here to reset your password:\n";
            $message .= $resetUrl . "\n\n";
            $message .= "⏰ This link will expire in 1 hour.\n\n";
            $message .= "If you didn't request this password reset, please ignore this message or contact support if you have concerns.\n\n";
            $message .= "Best regards,\nUnityX Team";

            return TelegramNotificationService::sendNotificationToTelegram(
                $user->id,
                $title,
                $message
            );
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify telegram reset token
     */
    public static function verifyTelegramToken($token, $email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }

        $telegramVerification = TelegramVerification::where('user_id', $user->id)
            ->where('token', $token)
            ->first();

        if (!$telegramVerification || $telegramVerification->isExpired()) {
            return false;
        }

        return $user;
    }

    /**
     * Reset password using custom token (telegram or email)
     */
    public static function resetPassword($token, $email, $password, $isFromTelegram = false, $ipAddress = null, $userAgent = null)
    {
        try {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found with this email address.'
                ];
            }

            if ($isFromTelegram) {
                // Verify telegram token
                $telegramVerification = TelegramVerification::where('user_id', $user->id)
                    ->where('token', $token)
                    ->first();

                if (!$telegramVerification || $telegramVerification->isExpired()) {
                    return [
                        'success' => false,
                        'message' => 'Invalid or expired reset token.'
                    ];
                }

                // Update password
                $user->password = bcrypt($password);
                $user->save();

                // Delete the used telegram verification token
                $telegramVerification->delete();

                // Send password changed notification
                NotificationService::createPasswordChangedNotification($user->id, $ipAddress, $userAgent);

                return [
                    'success' => true,
                    'message' => 'Your password has been reset successfully! You can now log in with your new password.'
                ];
            } else {
                // Use Laravel's built-in password reset broker for email tokens
                $passwordResetSuccess = false;
                $status = app('auth.password.broker')->reset(
                    compact('email', 'password', 'token'),
                    function ($user, $password) use (&$passwordResetSuccess, $ipAddress, $userAgent) {
                        $user->password = bcrypt($password);
                        $user->save();
                        $passwordResetSuccess = true;

                        // Send password changed notification
                        NotificationService::createPasswordChangedNotification($user->id, $ipAddress, $userAgent);
                    }
                );

                if ($status == Password::PASSWORD_RESET && $passwordResetSuccess) {
                    return [
                        'success' => true,
                        'message' => 'Your password has been reset successfully! You can now log in with your new password.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Invalid or expired reset token.'
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while resetting your password. Please try again.'
            ];
        }
    }
}
