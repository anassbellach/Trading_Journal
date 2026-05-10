<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ─── Login ────────────────────────────────────────────────────────────────

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'De opgegeven inloggegevens zijn onjuist.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    // ─── Register ─────────────────────────────────────────────────────────────

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a default demo account
        $account = Account::create([
            'user_id'          => $user->id,
            'name'             => 'Mijn Account',
            'broker'           => 'Demo',
            'type'             => 'demo',
            'currency'         => 'USD',
            'starting_balance' => 50000,
            'current_balance'  => 50000,
            'is_default'       => true,
        ]);

        $user->update(['active_account_id' => $account->id]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard'));
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ─── Google OAuth ─────────────────────────────────────────────────────────

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect(route('login'))->withErrors(['email' => 'Google authenticatie mislukt.']);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'              => $googleUser->getName(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password'          => Hash::make(str()->random(32)),
            ]
        );

        if (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        if (! $user->activeAccount()) {
            $account = Account::create([
                'user_id'          => $user->id,
                'name'             => 'Mijn Account',
                'type'             => 'demo',
                'starting_balance' => 50000,
                'current_balance'  => 50000,
                'is_default'       => true,
            ]);
            $user->update(['active_account_id' => $account->id]);
        }

        Auth::login($user, true);
        return redirect(route('dashboard'));
    }
}
