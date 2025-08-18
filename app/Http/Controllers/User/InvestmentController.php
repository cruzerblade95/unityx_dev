<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Investment;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = Investment::where('user_id', Auth::id())->latest()->get();
        return view('user.content.investment', compact('investments'));
    }
}
