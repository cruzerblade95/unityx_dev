<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Models\AiTrade;
use App\Models\AiTradeHistory;
use App\Models\Wallet;

class TestInvestmentCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function calculatesss()
    {
        // dd("TestInvestmentCron started");
        try {
            // Configurable min/max percent (as decimals, e.g., 0.0003 for 0.03%)
            $minPercent = 0.0002; // 0.02%
            $maxPercent = 0.0005; // 0.05%

            $trades = AiTrade::where('trade_status', 1)->get();
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
            }
        } catch (\Exception $e) {
            // Log the error and optionally display it (for debugging)
            \Log::error('TestInvestmentCron error: ' . $e->getMessage(), ['exception' => $e]);
            echo 'TestInvestmentCron error: ' . $e->getMessage();
        }
    }
}
