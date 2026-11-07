<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only).
     */
    public function index(): View
    {
        $users = User::withCount('documents')->orderBy('role')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$statusStr}.");
    }

    /**
     * Reset user password to default 'password'.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $user->password = Hash::make('password');
        $user->save();

        return back()->with('success', "Kata sandi untuk {$user->name} telah direset ke 'password'.");
    }
}

