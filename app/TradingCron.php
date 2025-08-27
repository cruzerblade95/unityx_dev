<?php

namespace App;

use Illuminate\Support\Facades\DB;
use App\Helpers\Helpers;
use App\Models\AiTrade;
use App\Models\AiTradeHistory;
use App\Models\Wallet;
use App\Services\TelegramNotificationService;

class TradingCron
{
    /**
     * Create a new class instance.
     */
    public function __invoke()
    {
        logger()->info("Trading Cron Running Function");
        try {
            // Configurable min/max percent (as decimals, e.g., 0.0003 for 0.03%)
            $minPercent = 0.003; // 0.3%
            $maxPercent = 0.004; // 0.4%

            // Only run for users with ai_trade_activates.status = 1
            $activeUserIds = DB::table('ai_trade_activates')->where('status', 1)->pluck('user_id');
            $trades = AiTrade::where('trade_status', 1)->whereIn('user_id', $activeUserIds)->get();
            foreach ($trades as $trade) {
                $percent = mt_rand($minPercent * 1000000, $maxPercent * 1000000) / 1000000;
                $profit = $trade->amount_invest * $percent;

                // Insert into ai_trade_histories
                $history = new AiTradeHistory([
                    'amount' => $profit,
                    'pnl' => $profit,
                    'description' => 'Ai Trade successful please check your profit',
                ]);

                $trade->histories()->save($history);
                // Distribute ROI Matching Bonus after history insert
                Helpers::roiMatchingBonus($trade->user_id, $profit);

                // Add profit to wallet profit column
                $wallet = Wallet::where('user_id', $trade->user_id)->first();
                if ($wallet) {
                    $wallet->profit += $profit;
                    $wallet->save();
                }

                // Sum all pnl for this trade
                $totalPnl = $trade->histories()->sum('pnl');

                // If current_amount would exceed ending_amount, cap it
                $newCurrentAmount = $totalPnl;
                $status = 1;
                if ($newCurrentAmount >= $trade->ending_amount) {
                    $newCurrentAmount = $trade->ending_amount;
                    $status = 2;
                }

                // Update ai_trades
                $trade->current_amount = $newCurrentAmount;
                $trade->trade_status = $status;
                $trade->updated_at = now();
                $trade->save();

                // If trade just ended, subtract amount_invest from wallet deposit
                if ($status === 2) {
                    $wallet = Wallet::where('user_id', $trade->user_id)->first();
                    if ($wallet && $wallet->deposit >= $trade->amount_invest) {
                        $wallet->deposit -= $trade->amount_invest;
                        $wallet->save();
                    }
                }

                $title = '🤖 AI Trade has been credited';
                $message = "Your AI Trade has been successfully credited into Profit!\n\n";
                $message .= "💰 Investment Amount: $" . number_format($trade->amount_invest, 8) . "\n";
                $message .= "🎯 Your Pnl(Profit and Lost): $" . number_format($profit, 8) . " (5x potential return)\n";
                $message .= "📊 Trade ID: #{$trade->id}\n";
                $message .= "⏰ Status: Running\n\n";
                $message .= "Your AI Trade is now active. You can monitor its progress in your dashboard.";

                TelegramNotificationService::sendNotificationToTelegram(
                  $trade->user_id,
                  $title,
                  $message
                );

            }
        } catch (\Exception $e) {
            // Log the error and optionally display it (for debugging)
            logger()->error('TestInvestmentCron error: ' . $e->getMessage(), ['exception' => $e]);
            echo 'TestInvestmentCron error: ' . $e->getMessage();
        }
    }
}
