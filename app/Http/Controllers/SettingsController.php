<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:1024'],
            'password' => ['required', 'string', 'min:8', 'max:1024', 'confirmed'],
        ]);
        $user = User::query()->find($request->session()->get('dashboard_user_id'));

        if (! $user) {
            abort(403, 'Akun pengguna tidak ditemukan. Silakan masuk kembali.');
        }

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->password = $data['password'];
        $user->save();

        $request->session()->regenerate();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
