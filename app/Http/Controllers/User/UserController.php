<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        return view('user.content.dashboard', compact('wallet'));
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
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            // 'organization' => 'nullable|string|max:255',
            // 'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zipCode' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            // 'language' => 'nullable|string|max:10',
            // 'timezone' => 'nullable|string|max:64',
            // 'currency' => 'nullable|string|max:16',
        ]);
        $user->update($validated);
        return redirect()->route('user.profile.edit')->with('profile_updated', true);
    }

    public function downline()
    {
        $user = auth()->user();
        $downlines = $user->downlines ?? collect();
        $upline = $user->upline_id ? User::find($user->upline_id) : null;
        return view('user.content.downline', compact('downlines', 'upline'));
    }

    public function editProfile()
    {
        $user = auth()->user();
        return view('user.content.profile-edit', compact('user'));
    }
}
