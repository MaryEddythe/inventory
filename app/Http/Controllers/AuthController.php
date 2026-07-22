<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
        return view('auth.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $usernameColumn = Schema::hasColumn('users', 'username') ? 'username' : 'name';

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users,' . $usernameColumn . ',' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
}
