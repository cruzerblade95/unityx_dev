<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

class TelegramController extends Controller
{
    // Telegram webhook handler
    public function webhook(Request $request)
    {
        $data = $request->all();
        if (isset($data['message']) && isset($data['message']['chat']['id'])) {
            $chatId = $data['message']['chat']['id'];
            $text = $data['message']['text'] ?? '';
            if ($text === '/start') {
                // Find verification token for this user (by email or session, or let user enter email)
                // For demo, assume user enters their email after /start
                $reply = "Please enter your email to link your account.";
                $this->sendTelegramMessage($chatId, $reply);
            } elseif (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $text)->first();
                if ($user) {
                    // Generate or get token
                    $token = Str::random(40);
                    DB::table('telegram_verifications')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'token' => $token,
                            'expires_at' => Carbon::now()->addMinutes(30),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                    $link = URL::to('/telegram/verify?token=' . $token);
                    $this->sendTelegramMessage($chatId, "Click to verify your profile: $link");
                } else {
                    $this->sendTelegramMessage($chatId, "No user found for this email.");
                }
            }
        }
        return response('ok', 200);
    }

    // Verification link handler
    public function verify(Request $request)
    {
        $token = $request->query('token');
        $row = DB::table('telegram_verifications')->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
        if ($row) {
            $user = User::find($row->user_id);
            if ($user) {
                $user->profile_complete = 1;
                $user->telegram_id = $request->query('chat_id', null); // fallback if needed
                $user->save();
                // Optionally, delete the verification row
                DB::table('telegram_verifications')->where('id', $row->id)->delete();
                return Redirect::to('/profile')->with('success', 'Profile verified and Telegram linked!');
            }
        }
        return response('Invalid or expired token.', 400);
    }

    // Helper to send message via Telegram API
    private function sendTelegramMessage($chatId, $text)
    {
        $token = Config::get('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $client->post($url, ['form_params' => $params]);
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage());
        }
    }
}
