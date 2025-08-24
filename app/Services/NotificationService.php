<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create notification and send to Telegram if user has it connected
     */
    private static function createAndSendNotification($userId, $type, $title, $message, $data = null)
    {
        // Create the web notification
        $notification = Notification::createNotification($userId, $type, $title, $message, $data);

        // Also send to Telegram if user has telegram verification
        TelegramNotificationService::sendNotificationToTelegram($userId, $title, $message);

        return $notification;
    }

    /**
     * Create KYC approved notification
     */
    public static function createKycApprovedNotification($userId)
    {
        return self::createAndSendNotification(
            $userId,
            Notification::TYPE_KYC_APPROVED,
            'KYC Verification Approved ✅',
            'Your KYC verification has been approved! You can now access all features.'
        );
    }

    /**
     * Create KYC rejected notification
     */
    public static function createKycRejectedNotification($userId, $reason = null)
    {
        $message = 'Your KYC verification has been rejected.';
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        return self::createAndSendNotification(
            $userId,
            Notification::TYPE_KYC_REJECTED,
            'KYC Verification Rejected ❌',
            $message
        );
    }

    /**
     * Create withdrawal approved notification
     */
    public static function createWithdrawalApprovedNotification($userId, $amount, $withdrawalId)
    {
        return self::createAndSendNotification(
            $userId,
            Notification::TYPE_WITHDRAWAL_APPROVED,
            'Withdrawal Approved ✅',
            "Your withdrawal request of $" . number_format($amount, 2) . " has been approved and processed.",
            ['withdrawal_id' => $withdrawalId, 'amount' => $amount]
        );
    }

    /**
     * Create withdrawal rejected notification
     */
    public static function createWithdrawalRejectedNotification($userId, $amount, $withdrawalId, $reason = null)
    {
        $message = "Your withdrawal request of $" . number_format($amount, 2) . " has been rejected.";
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        return self::createAndSendNotification(
            $userId,
            Notification::TYPE_WITHDRAWAL_REJECTED,
            'Withdrawal Rejected ❌',
            $message,
            ['withdrawal_id' => $withdrawalId, 'amount' => $amount, 'reason' => $reason]
        );
    }

    /**
     * Create withdrawal success notification (alias for approved)
     */
    public static function createWithdrawalSuccessNotification($userId, $amount, $withdrawalId)
    {
        return self::createWithdrawalApprovedNotification($userId, $amount, $withdrawalId);
    }

    /**
     * Create withdrawal failed notification (alias for rejected)
     */
    public static function createWithdrawalFailedNotification($userId, $amount, $withdrawalId, $reason = null)
    {
        return self::createWithdrawalRejectedNotification($userId, $amount, $withdrawalId, $reason);
    }

    /**
     * Create KYC verified notification (alias for approved)
     */
    public static function createKycVerifiedNotification($userId)
    {
        return self::createKycApprovedNotification($userId);
    }

    /**
     * Create deposit success notification
     */
    public static function createDepositSuccessNotification($userId, $amount, $depositId)
    {
        Log::info('Creating deposit success notification', [
            'user_id' => $userId,
            'amount' => $amount,
            'deposit_id' => $depositId
        ]);

        $notification = self::createAndSendNotification(
            $userId,
            Notification::TYPE_DEPOSIT_SUCCESS,
            'Deposit Successful 💰',
            "Your deposit of $" . number_format($amount, 2) . " has been successfully processed and added to your account.",
            [
                'deposit_id' => $depositId,
                'amount' => $amount
            ]
        );

        Log::info('Deposit success notification created', [
            'notification_id' => $notification ? $notification->id : null,
            'user_id' => $userId,
            'amount' => $amount,
            'deposit_id' => $depositId
        ]);

        return $notification;
    }

    /**
     * Create deposit rejected notification
     */
    public static function createDepositRejectedNotification($userId, $amount, $reason, $depositId)
    {
        Log::info('Creating deposit rejected notification', [
            'user_id' => $userId,
            'amount' => $amount,
            'deposit_id' => $depositId,
            'reason' => $reason
        ]);

        $notification = self::createAndSendNotification(
            $userId,
            Notification::TYPE_DEPOSIT_REJECTED,
            'Deposit Rejected ❌',
            "Your deposit of $" . number_format($amount, 2) . " has been rejected. Reason: " . $reason,
            [
                'deposit_id' => $depositId,
                'amount' => $amount,
                'reason' => $reason
            ]
        );

        Log::info('Deposit rejected notification created', [
            'notification_id' => $notification ? $notification->id : null,
            'user_id' => $userId,
            'amount' => $amount,
            'deposit_id' => $depositId
        ]);

        return $notification;
    }

    /**
     * Create deposit failed notification
     */
    public static function createDepositFailedNotification($userId, $amount, $depositId, $reason = null)
    {
        $message = "Your deposit of $" . number_format($amount, 2) . " has failed to process.";
        if ($reason) {
            $message .= " Reason: " . $reason;
        }

        return Notification::createNotification(
            $userId,
            'deposit_failed', // We might need to add this type to the model
            'Deposit Failed ❌',
            $message,
            ['deposit_id' => $depositId, 'amount' => $amount, 'reason' => $reason]
        );
    }

    /**
     * Create marketplace seller verification pending notification
     */
    public static function createMarketplaceSellerPendingNotification($userId, $listingId)
    {
        return Notification::createNotification(
            $userId,
            Notification::TYPE_MARKETPLACE_SELLER_PENDING,
            'USDT Listing Pending Verification ⏳',
            'Your USDT listing is pending verification. We will review your submission and notify you once approved.',
            ['listing_id' => $listingId]
        );
    }

    /**
     * Create marketplace seller approved notification
     */
    public static function createMarketplaceSellerApprovedNotification($userId, $listingId)
    {
        return Notification::createNotification(
            $userId,
            Notification::TYPE_MARKETPLACE_SELLER_APPROVED,
            'USDT Listing Approved ✅',
            'Your USDT listing has been approved and is now live on the marketplace!',
            ['listing_id' => $listingId]
        );
    }

    /**
     * Create marketplace seller rejected notification
     */
    public static function createMarketplaceSellerRejectedNotification($userId, $listingId, $reason = null)
    {
        $message = 'Your USDT listing has been rejected.';
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        return Notification::createNotification(
            $userId,
            Notification::TYPE_MARKETPLACE_SELLER_REJECTED,
            'USDT Listing Rejected ❌',
            $message,
            ['listing_id' => $listingId, 'reason' => $reason]
        );
    }

    /**
     * Create cash balance topup notification
     */
    public static function createCashTopupNotification($userId, $amount)
    {
        return Notification::createNotification(
            $userId,
            Notification::TYPE_CASH_TOPUP,
            'Cash Balance Topped Up 💳',
            "Your marketplace cash balance has been topped up with $" . number_format($amount, 2) . ".",
            ['amount' => $amount]
        );
    }

    /**
     * Get unread notification count for user
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId, $userId)
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read for user
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Create password changed notification
     */
    public static function createPasswordChangedNotification($userId, $ipAddress = null, $userAgent = null)
    {
        $message = 'Your password has been successfully changed.';

        if ($ipAddress) {
            $message .= ' This change was made from IP address: ' . $ipAddress;
        }

        $message .= ' If you did not make this change, please contact support immediately.';

        $data = [];
        if ($ipAddress) {
            $data['ip_address'] = $ipAddress;
        }
        if ($userAgent) {
            $data['user_agent'] = $userAgent;
        }
        $data['changed_at'] = now()->toISOString();

        return self::createAndSendNotification(
            $userId,
            Notification::TYPE_PASSWORD_CHANGED,
            'Password Changed Successfully 🔒',
            $message,
            $data
        );
    }
}
