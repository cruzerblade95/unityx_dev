<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerAdvertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerAdvertisementController extends Controller
{
    /**
     * Display a listing of banner advertisements.
     */
    public function index()
    {
        $banners = BannerAdvertisement::ordered()->paginate(10);
        return view('admin.content.banner-advertisements.index', compact('banners'));
    }

    /**
     * Show the form for creating a new banner advertisement.
     */
    public function create()
    {
        return view('admin.content.banner-advertisements.create');
    }

    /**
     * Store a newly created banner advertisement in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_url' => 'nullable|url',
            'button_text' => 'required|string|max:50',
            'background_color' => 'required|string|max:7',
            'gradient_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'show_title' => 'boolean',
            'show_description' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $data = $request->except(['image', 'mobile_image']);

        // Handle boolean checkboxes (they don't send value when unchecked)
        $data['show_title'] = $request->has('show_title') ? true : false;
        $data['show_description'] = $request->has('show_description') ? true : false;
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Handle desktop image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_desktop_' . Str::random(10) . '.' . $image->extension();
            $imagePath = $image->storeAs('banners', $imageName, 'public');
            $data['image_path'] = $imagePath;
        }

        // Handle mobile image upload
        if ($request->hasFile('mobile_image')) {
            $mobileImage = $request->file('mobile_image');
            $mobileImageName = time() . '_mobile_' . Str::random(10) . '.' . $mobileImage->extension();
            $mobileImagePath = $mobileImage->storeAs('banners', $mobileImageName, 'public');
            $data['mobile_image_path'] = $mobileImagePath;
        }

        // Set default sort order if not provided
        if (!$request->sort_order) {
            $maxOrder = BannerAdvertisement::max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        BannerAdvertisement::create($data);

        return redirect()->route('admin.banner-advertisements.index')
                        ->with('success', 'Banner advertisement created successfully!');
    }

    /**
     * Display the specified banner advertisement.
     */
    public function show(BannerAdvertisement $bannerAdvertisement)
    {
        return view('admin.content.banner-advertisements.show', compact('bannerAdvertisement'));
    }

    /**
     * Show the form for editing the specified banner advertisement.
     */
    public function edit(BannerAdvertisement $bannerAdvertisement)
    {
        return view('admin.content.banner-advertisements.edit', compact('bannerAdvertisement'));
    }

    /**
     * Update the specified banner advertisement in storage.
     */
    public function update(Request $request, BannerAdvertisement $bannerAdvertisement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_url' => 'nullable|url',
            'button_text' => 'required|string|max:50',
            'background_color' => 'required|string|max:7',
            'gradient_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'show_title' => 'boolean',
            'show_description' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $data = $request->except(['image', 'mobile_image']);

        // Handle boolean checkboxes (they don't send value when unchecked)
        $data['show_title'] = $request->has('show_title') ? true : false;
        $data['show_description'] = $request->has('show_description') ? true : false;
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Handle desktop image upload
        if ($request->hasFile('image')) {
            // Delete old desktop image if exists
            if ($bannerAdvertisement->image_path) {
                Storage::disk('public')->delete($bannerAdvertisement->image_path);
            }

            $image = $request->file('image');
            $imageName = time() . '_desktop_' . Str::random(10) . '.' . $image->extension();
            $imagePath = $image->storeAs('banners', $imageName, 'public');
            $data['image_path'] = $imagePath;
        }

        // Handle mobile image upload
        if ($request->hasFile('mobile_image')) {
            // Delete old mobile image if exists
            if ($bannerAdvertisement->mobile_image_path) {
                Storage::disk('public')->delete($bannerAdvertisement->mobile_image_path);
            }

            $mobileImage = $request->file('mobile_image');
            $mobileImageName = time() . '_mobile_' . Str::random(10) . '.' . $mobileImage->extension();
            $mobileImagePath = $mobileImage->storeAs('banners', $mobileImageName, 'public');
            $data['mobile_image_path'] = $mobileImagePath;
        }

        $bannerAdvertisement->update($data);

        return redirect()->route('admin.banner-advertisements.index')
                        ->with('success', 'Banner advertisement updated successfully!');
    }

    /**
     * Remove the specified banner advertisement from storage.
     */
    public function destroy(BannerAdvertisement $bannerAdvertisement)
    {
        // Delete desktop image file
        if ($bannerAdvertisement->image_path) {
            Storage::disk('public')->delete($bannerAdvertisement->image_path);
        }

        // Delete mobile image file
        if ($bannerAdvertisement->mobile_image_path) {
            Storage::disk('public')->delete($bannerAdvertisement->mobile_image_path);
        }

        $bannerAdvertisement->delete();

        return redirect()->route('admin.banner-advertisements.index')
                        ->with('success', 'Banner advertisement deleted successfully!');
    }

    /**
     * Toggle the active status of a banner advertisement.
     */
    public function toggleStatus(BannerAdvertisement $bannerAdvertisement)
    {
        $oldStatus = $bannerAdvertisement->is_active;
        $newStatus = !$bannerAdvertisement->is_active;

        $bannerAdvertisement->update([
            'is_active' => $newStatus
        ]);

        // Refresh the model to ensure we have the latest data
        $bannerAdvertisement->refresh();

        return redirect()->back()
                        ->with('success', "Banner status updated from " . ($oldStatus ? 'Active' : 'Inactive') . " to " . ($newStatus ? 'Active' : 'Inactive'));
    }

    /**
     * Update sort order of banner advertisements.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banner_advertisements,id',
            'banners.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->banners as $banner) {
            BannerAdvertisement::where('id', $banner['id'])
                              ->update(['sort_order' => $banner['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Banner order updated successfully!']);
    }
}
