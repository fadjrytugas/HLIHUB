<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('dashboard_authenticated') === true) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);
        $user = User::query()->where('username', $credentials['username'])->first();

        if (! $user) {
            $user = $this->createLegacyDashboardUser($credentials['username'], $credentials['password']);
        }

        if (! $user || ! hash_equals($user->username, $credentials['username']) || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['username' => 'Username atau password tidak sesuai.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('dashboard_authenticated', true);
        $request->session()->put('dashboard_user_id', $user->getKey());

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function createLegacyDashboardUser(string $username, string $password): ?User
    {
        $configuredUsername = config('services.dashboard_login.username');
        $passwordHash = DB::table('dashboard_settings')
            ->where('key', 'dashboard_password_hash')
            ->value('value') ?: config('services.dashboard_login.password_hash');

        if (
            ! is_string($configuredUsername)
            || $configuredUsername === ''
            || ! hash_equals($configuredUsername, $username)
            || ! is_string($passwordHash)
            || $passwordHash === ''
            || ! Hash::check($password, $passwordHash)
        ) {
            return null;
        }

        return User::query()->create([
            'name' => $username,
            'username' => $username,
            'password' => $password,
        ]);
    }
}
