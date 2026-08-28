<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Role;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required'
        ]);

        // Check if the input is an email or username.
        $login = $credentials['email'];
        $password = $credentials['password'];
        $usernameColumn = Schema::hasColumn('users', 'username') ? 'username' : 'name';
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : $usernameColumn;
        $attempts = [
            [$field => $login, 'password' => $password],
        ];

        // Backward compatibility for older users that only had a name value.
        if ($field === 'username') {
            $attempts[] = ['name' => $login, 'password' => $password];
        }

        if (Auth::attempt($attempts[0]) || (isset($attempts[1]) && Auth::attempt($attempts[1]))) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->route($user?->defaultLandingRouteName() ?? 'inventory.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => Role::where('slug', 'employee')->value('id'),
        ]);

        Auth::login($user);

        return redirect()->route($user->defaultLandingRouteName());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showProfile()
    {
        // Eager-load the user's role and the linked employee's division so the
        // profile view can display division/role information without triggering
        // extra (N+1) queries. The `employee.division` nested eager-load mirrors
        // the pattern already used when loading leave applications below.
        $user = Auth::user()->loadMissing(['employee.division', 'role']);

        $leaveApplications = $user?->employee
            ? $user->employee->leaveApplications()
                ->with([
                    'employee.division',
                    'hrSigner',
                    'divisionChiefSigner',
                    'regionalDirectorSigner',
                ])
                ->latest()
                ->get()
            : collect();

        return view('auth.profile', compact('user', 'leaveApplications'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $usernameColumn = Schema::hasColumn('users', 'username') ? 'username' : 'name';

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users,' . $usernameColumn . ',' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_path' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $updates = [
            'name' => $validated['username'],
            'email' => $validated['email'],
        ];

        if ($usernameColumn === 'username') {
            $updates['username'] = $validated['username'];
        }

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $updates['profile_image'] = $imagePath;

            // Sync profile image to linked employee record
            if ($user->employee) {
                $user->employee->profile_image = $imagePath;
                $user->employee->save();
            }
        }

        if ($request->hasFile('signature_path')) {
            $signaturePath = $request->file('signature_path')->store('signatures', 'public');
            $updates['signature_path'] = $signaturePath;
        }

        $user->update($updates);

        Auth::setUser($user->fresh());

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile')->with('success', 'Password changed successfully!');
    }

    public function storeSignature(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'signature_path' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $signaturePath = $request->file('signature_path')->store('signatures', 'public');

        $user->update([
            'signature_path' => $signaturePath,
        ]);

        Auth::setUser($user->fresh());

        return back()->with('success', 'Your signature was saved successfully.');
    }
}
