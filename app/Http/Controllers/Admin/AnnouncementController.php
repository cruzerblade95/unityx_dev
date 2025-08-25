<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::ordered()->paginate(10);
        return view('admin.content.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content.announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:primary,success,warning,danger,info',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_dismissible'] = $request->has('is_dismissible');
        $data['sort_order'] = $request->sort_order ?? 0;

        Announcement::create($data);

        return redirect()->route('admin.announcements.index')
                        ->with('success', 'Announcement created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        return view('admin.content.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.content.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:primary,success,warning,danger,info',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_dismissible'] = $request->has('is_dismissible');
        $data['sort_order'] = $request->sort_order ?? 0;

        $announcement->update($data);

        return redirect()->route('admin.announcements.index')
                        ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
                        ->with('success', 'Announcement deleted successfully!');
    }

    /**
     * Toggle the active status of an announcement.
     */
    public function toggleStatus(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        $status = $announcement->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.announcements.index')
                        ->with('success', "Announcement {$status} successfully!");
    }
}
