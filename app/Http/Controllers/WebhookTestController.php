<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookTestController extends Controller
{
    /**
     * Test endpoint for Telegram webhook
     */
    public function testTelegram(Request $request)
    {
        Log::info('Telegram webhook test', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_content' => $request->getContent()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Telegram webhook is working!',
            'timestamp' => now(),
            'received_data' => $request->all()
        ]);
    }

    /**
     * Test endpoint for NOWPayments webhook
     */
    public function testNowPayments(Request $request)
    {
        Log::info('NOWPayments webhook test', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_content' => $request->getContent()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'NOWPayments webhook is working!',
            'timestamp' => now(),
            'received_data' => $request->all()
        ]);
    }

    /**
     * General webhook test endpoint
     */
    public function test(Request $request)
    {
        Log::info('General webhook test', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_content' => $request->getContent(),
            'url' => $request->fullUrl()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook endpoint is working!',
            'timestamp' => now(),
            'method' => $request->method(),
            'received_data' => $request->all()
        ]);
    }
}
