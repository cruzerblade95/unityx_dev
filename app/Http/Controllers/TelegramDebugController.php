<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TelegramDebugController extends Controller
{
    public function debug(Request $request)
    {
        // Log everything for debugging
        $debugData = [
            'timestamp' => now(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'query_params' => $request->query(),
            'body_data' => $request->all(),
            'raw_content' => $request->getContent(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'is_secure' => $request->secure(),
            'server_vars' => [
                'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'N/A',
                'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'N/A',
                'HTTPS' => $_SERVER['HTTPS'] ?? 'N/A',
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            ]
        ];

        // Log to Laravel log
        Log::info('TELEGRAM DEBUG WEBHOOK', $debugData);

        // Also save to file for easier access
        $logFile = storage_path('logs/telegram_debug_' . date('Y-m-d') . '.log');
        file_put_contents($logFile,
            "[" . date('Y-m-d H:i:s') . "] " . json_encode($debugData, JSON_PRETTY_PRINT) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        // Test database connectivity
        try {
            $dbTest = DB::select('SELECT 1 as test');
            $debugData['database_status'] = 'connected';
        } catch (\Exception $e) {
            $debugData['database_status'] = 'error: ' . $e->getMessage();
        }

        // Test environment variables
        $debugData['env_check'] = [
            'APP_ENV' => config('app.env'),
            'APP_DEBUG' => config('app.debug'),
            'APP_URL' => config('app.url'),
            'TELEGRAM_BOT_TOKEN' => env('TELEGRAM_BOT_TOKEN') ? 'SET' : 'NOT_SET',
        ];

        return response()->json([
            'status' => 'debug_webhook_received',
            'message' => 'Debug data logged successfully',
            'debug_data' => $debugData
        ], 200);
    }

    public function testMessage(Request $request)
    {
        $chatId = $request->input('chat_id', '123456789');
        $message = $request->input('message', 'Test message from production server');

        try {
            $token = config('services.telegram.bot_token');

            if (!$token) {
                return response()->json([
                    'error' => 'TELEGRAM_BOT_TOKEN not configured',
                    'env_token' => env('TELEGRAM_BOT_TOKEN') ? 'SET' : 'NOT_SET'
                ], 400);
            }

            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $params = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ];

            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, ['form_params' => $params]);
            $result = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'status' => 'message_sent',
                'telegram_response' => $result,
                'used_token' => substr($token, 0, 10) . '...'
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send message',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
