<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SidebarItem;
use App\Models\User;
use Illuminate\Http\Request;

class SidebarAccessController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $users = User::query()
            ->with(['role', 'sidebarItems'])
            ->orderBy('name')
            ->get();

        $roles = Role::query()->orderBy('name')->get();

        $sidebarItems = SidebarItem::query()
            ->with([
                'children' => fn ($query) => $query->ordered()->with('children'),
            ])
            ->ordered()
            ->get()
            ->whereNull('parent_id')
            ->values();

        return view('settings.roles', compact('users', 'roles', 'sidebarItems'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'sidebar_item_ids' => 'nullable|array',
            'sidebar_item_ids.*' => 'integer|exists:sidebar_items,id',
        ]);

        $user->update([
            'role_id' => $validated['role_id'] ?? null,
        ]);

        $user->sidebarItems()->sync($validated['sidebar_item_ids'] ?? []);

        return back()->with('success', 'Sidebar access updated for ' . ($user->username ?: $user->name) . '.');
    }
}
