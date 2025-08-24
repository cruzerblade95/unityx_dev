<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AiTradeActivate;

class AiTradeActivateController extends Controller
{
    public function toggle(Request $request)
    {
        $user = Auth::user();
        $aiTradeActivate = AiTradeActivate::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 0]
        );
        $aiTradeActivate->status = $aiTradeActivate->status ? 0 : 1;
        $aiTradeActivate->save();
        return response()->json([
            'status' => $aiTradeActivate->status,
            'message' => $aiTradeActivate->status ? 'AI Trade Activated' : 'AI Trade Deactivated',
        ]);
    }
}
