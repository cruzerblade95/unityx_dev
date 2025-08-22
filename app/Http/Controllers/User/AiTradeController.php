<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiTrade;
use Illuminate\Support\Facades\Auth;

class AiTradeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tradesRunning = AiTrade::where('user_id', $user->id)->where('trade_status', 1)->orderByDesc('id')->paginate(10, ['*'], 'running_page');
        $tradesEnded = AiTrade::where('user_id', $user->id)->where('trade_status', 2)->orderByDesc('id')->paginate(10, ['*'], 'ended_page');
        $tradesCancelled = AiTrade::where('user_id', $user->id)->where('trade_status', 0)->orderByDesc('id')->paginate(10, ['*'], 'cancelled_page');
        return view('user.content.ai_trades.index', compact('tradesRunning', 'tradesEnded', 'tradesCancelled'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $trade = AiTrade::where('user_id', $user->id)->findOrFail($id);
        $histories = $trade->histories()->orderByDesc('created_at')->get();
        return view('user.content.ai_trades.show', compact('trade', 'histories'));
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $trade = AiTrade::where('user_id', $user->id)->where('trade_status', 1)->findOrFail($id);
        $trade->trade_status = 0;
        $trade->save();
        return redirect()->route('user.ai_trades.index')->with('success', 'Trade cancelled successfully.');
    }
}
