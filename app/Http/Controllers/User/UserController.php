<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MarketplaceListing;
use App\Services\NotificationService;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Test notifications removed - no longer creating default notifications for new users

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
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zipCode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path && file_exists(public_path($user->profile_photo_path))) {
                unlink(public_path($user->profile_photo_path));
            }

            // Create directory if it doesn't exist
            $uploadPath = public_path('storage/profile-photos');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Store new photo
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($uploadPath, $filename);
            $validated['profile_photo_path'] = 'storage/profile-photos/' . $filename;
        }

        // Remove profile_photo from validated data as it's handled separately
        unset($validated['profile_photo']);

        // Update user
        $user->update($validated);

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

        // Ensure user is loaded with all necessary data
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access your profile.');
        }

        return view('user.content.profile-edit', compact('user'));
    }
}
