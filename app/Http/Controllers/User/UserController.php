<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        return view('user.content.dashboard');
    }

    public function profile()
    {
        return view('user.content.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
        ]);
        $user->update($validated);
        return redirect()->route('user.profile')->with('profile_updated', true);
    }

    public function downline()
    {
        $user = auth()->user();
        $downlines = $user->downlines ?? collect();
        return view('user.content.downline', compact('downlines'));
    }
}
