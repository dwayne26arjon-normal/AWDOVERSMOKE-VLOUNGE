<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staffMembers' => User::query()
                ->where('is_staff', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $staff = new User;
        $staff->name = $data['name'];
        $staff->email = $data['email'];
        $staff->password = Hash::make($data['password']);
        $staff->is_admin = false;
        $staff->is_staff = true;
        $staff->save();

        return redirect()->route('admin.staff.index')->with('status', 'Staff account created.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        abort_unless($staff->is_staff, 404);

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('status', 'Staff account removed.');
    }
}
