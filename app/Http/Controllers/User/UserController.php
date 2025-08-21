<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MarketplaceListing;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        // Get 4 latest marketplace listings for mini display
        $miniMarketplaceListings = MarketplaceListing::with('seller')
            ->active()
            ->where('seller_id', '!=', $user->id) // Don't show user's own listings
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
        
        return view('user.content.dashboard', compact('wallet', 'miniMarketplaceListings'));
    }

    public function profile()
    {
        return view('user.content.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
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
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Remove profile_photo from validated data before updating
        unset($validated['profile_photo']);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path && file_exists(public_path($user->profile_photo_path))) {
                unlink(public_path($user->profile_photo_path));
            }

            // Store new photo
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/profile-photos'), $filename);
            $validated['profile_photo_path'] = 'storage/profile-photos/' . $filename;
        }
        
        // Update user using model query
        User::where('id', $user->id)->update($validated);
        
        return redirect()->route('user.profile.edit')->with('profile_updated', true);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old photo if exists
        if ($user->profile_photo_path && file_exists(public_path($user->profile_photo_path))) {
            unlink(public_path($user->profile_photo_path));
        }

        // Store new photo
        $file = $request->file('profile_photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('storage/profile-photos'), $filename);
        $photoPath = 'storage/profile-photos/' . $filename;

        // Update user profile photo path
        User::where('id', $user->id)->update(['profile_photo_path' => $photoPath]);

        return response()->json([
            'success' => true,
            'photo_url' => asset($photoPath),
            'message' => 'Profile photo updated successfully!'
        ]);
    }

    public function downline()
    {
        $user = Auth::user();
        $downlines = $user->downlines ?? collect();
        $upline = $user->upline_id ? User::find($user->upline_id) : null;
        return view('user.content.downline', compact('downlines', 'upline'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('user.content.profile-edit', compact('user'));
    }
}
