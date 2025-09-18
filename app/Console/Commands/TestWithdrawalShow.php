<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use Illuminate\Http\Request;

class TestWithdrawalShow extends Command
{
    protected $signature = 'test:withdrawal-show {id}';
    protected $description = 'Test withdrawal show method';

    public function handle()
    {
        $id = $this->argument('id');

        try {
            $controller = new WithdrawalManagementController();
            $response = $controller->show($id);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);
                $this->info('Response: ' . json_encode($data, JSON_PRETTY_PRINT));
            } else {
                $this->info('Unexpected response type: ' . get_class($response));
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }

        return 0;
    }
}
