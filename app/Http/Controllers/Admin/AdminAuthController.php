<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin?->is_admin || $admin?->is_staff) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login', [
            'adminRegistrationOpen' => ! User::where('is_admin', true)->exists(),
        ]);
    }

    public function showRegister(): View|RedirectResponse
    {
        if (User::where('is_admin', true)->exists()) {
            return redirect()
                ->route('admin.login')
                ->with('status', 'Admin registration is closed. Please sign in.');
        }

        return view('admin.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = DB::transaction(function () use ($data): ?User {
            if (User::where('is_admin', true)->exists()) {
                return null;
            }

            $admin = new User;
            $admin->name = $data['name'];
            $admin->email = $data['email'];
            $admin->password = Hash::make($data['password']);
            $admin->is_admin = true;
            $admin->save();

            return $admin;
        });

        if (! $admin) {
            return redirect()
                ->route('admin.login')
                ->with('status', 'Admin registration is closed. Please sign in.');
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where(function ($query) {
                $query->where('is_admin', true)
                    ->orWhere('is_staff', true);
            })
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::guard('admin')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match an admin account.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
