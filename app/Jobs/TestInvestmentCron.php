<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class TestInvestmentCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $count = Cache::get('investment_cron_count', 0);
        if ($count < 5) {
            $count++;
            Cache::put('investment_cron_count', $count, 10); // 10 minutes expiry
            info("Investment cronjob executed: $count");
        } else {
            info('Investment cronjob reached max executions (5).');
        }
    }
}
