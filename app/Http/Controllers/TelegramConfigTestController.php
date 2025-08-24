<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class TelegramConfigTestController extends Controller
{
    public function testConfig(Request $request)
    {
        return response()->json([
            'env_token' => env('TELEGRAM_BOT_TOKEN') ? 'SET (length: ' . strlen(env('TELEGRAM_BOT_TOKEN')) . ')' : 'NOT_SET',
            'config_token' => Config::get('services.telegram.bot_token') ? 'SET (length: ' . strlen(Config::get('services.telegram.bot_token')) . ')' : 'NOT_SET',
            'config_all_services' => Config::get('services'),
            'app_env' => config('app.env'),
            'config_path' => config_path('services.php'),
            'config_exists' => file_exists(config_path('services.php')),
        ]);
    }
}
