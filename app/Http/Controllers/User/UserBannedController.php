<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserBannedController extends Controller
{
    public function index(Request $request)
    {
        $pageConfigs = ['myLayout' => 'blank'];

        $banReason = session('banReason', 'Your account has been suspended for violating our terms of service.');
        $bannedAt = session('bannedAt');

        return view('user.banned', compact('pageConfigs', 'banReason', 'bannedAt'));
    }
}
