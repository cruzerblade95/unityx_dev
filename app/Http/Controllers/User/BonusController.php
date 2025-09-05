<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DepositPromoBonusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramNotificationService;

class BonusController extends Controller
{
    /**
     * Display user's available and claimed bonuses
     */
    public function index()
    {
        $user = Auth::user();

        $availableBonuses = DepositPromoBonusHistory::forUser($user->id)
            ->claimable()
            ->with('campaign:id,name,description')
            ->orderBy('created_at', 'desc')
            ->get();

        $claimedBonuses = DepositPromoBonusHistory::forUser($user->id)
            ->where('status', DepositPromoBonusHistory::STATUS_CLAIMED)
            ->with('campaign:id,name,description')
            ->orderBy('claimed_at', 'desc')
            ->take(10)
            ->get();

        $expiredBonuses = DepositPromoBonusHistory::forUser($user->id)
            ->where('status', DepositPromoBonusHistory::STATUS_EXPIRED)
            ->with('campaign:id,name,description')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $totalClaimedAmount = DepositPromoBonusHistory::forUser($user->id)
            ->where('status', DepositPromoBonusHistory::STATUS_CLAIMED)
            ->sum('user_bonus_amount');

        return view('user.bonuses.index', compact(
            'availableBonuses',
            'claimedBonuses',
            'expiredBonuses',
            'totalClaimedAmount'
        ));
    }

    /**
     * Claim a specific bonus
     */
    public function claim(Request $request, $bonusId)
    {
        try {
            $user = Auth::user();

            $bonus = DepositPromoBonusHistory::forUser($user->id)
                ->where('id', $bonusId)
                ->with('campaign')
                ->first();

            if (!$bonus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bonus not found'
                ], 404);
            }

            if (!$bonus->isClaimable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bonus is not claimable (already claimed or expired)'
                ], 422);
            }

            DB::transaction(function () use ($bonus, $user) {
                // Mark bonus as claimed
                $bonus->markAsClaimed();

                // Add bonus amount to user's deposit wallet
                if ($user->wallet) {
                    $user->wallet->deposit += $bonus->user_bonus_amount;
                    $user->wallet->save();
                }


            });

            // Send success notification to user
            try {
                $title = '✅ Bonus Claimed Successfully!';
                $message = "Your deposit promo bonus has been claimed!\n\n";
                $message .= "🏆 Campaign: {$bonus->campaign->name}\n";
                $message .= "💰 Bonus Amount: $" . number_format($bonus->user_bonus_amount, 2) . "\n";
                $message .= "📅 Claimed: " . now()->format('M j, Y g:i A') . "\n\n";
                $message .= "The bonus has been added to your deposit wallet.";

                TelegramNotificationService::sendNotificationToTelegram(
                    $user->id,
                    $title,
                    $message
                );
            } catch (\Exception $e) {
                Log::error('Failed to send bonus claim success notification: ' . $e->getMessage());
            }

            Log::info('Deposit promo bonus claimed', [
                'user_id' => $user->id,
                'bonus_id' => $bonus->id,
                'campaign_id' => $bonus->campaign_id,
                'bonus_amount' => $bonus->user_bonus_amount,
                'upline_bonus_amount' => $bonus->upline_bonus_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bonus claimed successfully!',
                'bonus_amount' => $bonus->user_bonus_amount,
                'new_wallet_balance' => $user->wallet ? $user->wallet->deposit : 0
            ]);

        } catch (\Exception $e) {
            Log::error('Error claiming deposit promo bonus: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'bonus_id' => $bonusId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error claiming bonus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bonus details
     */
    public function show($bonusId)
    {
        $user = Auth::user();

        $bonus = DepositPromoBonusHistory::forUser($user->id)
            ->where('id', $bonusId)
            ->with('campaign')
            ->first();

        if (!$bonus) {
            return response()->json([
                'success' => false,
                'message' => 'Bonus not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'bonus' => $bonus
        ]);
    }

    /**
     * Mark expired bonuses (can be run via cron job)
     */
    public function markExpiredBonuses()
    {
        $expiredBonuses = DepositPromoBonusHistory::expired()->get();

        foreach ($expiredBonuses as $bonus) {
            $bonus->markAsExpired();
        }

        Log::info('Marked expired deposit promo bonuses', [
            'count' => $expiredBonuses->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expired bonuses marked',
            'count' => $expiredBonuses->count()
        ]);
    }
}
