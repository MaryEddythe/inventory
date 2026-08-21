<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyAnnouncementController extends Controller
{
    protected array $announcementManagerRoleIds = [1, 5, 6, 7, 8, 9, 10];

    public function index()
    {
        $user = Auth::user();
        $canManageAnnouncements = $this->canManageAnnouncements($user?->role_id);

        $announcements = Announcement::query()
            ->with(['creator', 'editor'])
            ->when(! $canManageAnnouncements, fn ($query) => $query->where('is_published', true))
            ->latest()
            ->get();

        return view('announcements.dashboard', compact('announcements', 'canManageAnnouncements'));
    }

    public function store(Request $request)
    {
        $this->ensureCanManageAnnouncements();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'is_published' => 'nullable|boolean',
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published', true),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('company-announcements.index')
            ->with('success', 'Announcement posted successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->ensureCanManageAnnouncements();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'is_published' => 'nullable|boolean',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published', $announcement->is_published),
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('company-announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    protected function canManageAnnouncements(?int $roleId): bool
    {
        return $roleId !== null && in_array($roleId, $this->announcementManagerRoleIds, true);
    }

    protected function ensureCanManageAnnouncements(): void
    {
        abort_unless($this->canManageAnnouncements(Auth::user()?->role_id), 403);
    }
}
