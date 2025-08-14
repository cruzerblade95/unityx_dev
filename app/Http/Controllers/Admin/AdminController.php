<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $admin = Auth::guard('admin')->user();
        return view('content.dashboard.dashboards-analytics', [
            'admin' => $admin,
            'pageTitle' => 'Admin Dashboard',
            'pageConfigs' => ['myLayout' => 'vertical']
        ]);
    }
}
