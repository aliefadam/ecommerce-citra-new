<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function showLogin()
    {
        $this->storeIntendedFromRedirectQuery(request());
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak valid.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user()?->role);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $exists = User::query()->where('email', $validated['email'])->exists();
        if (!$exists) {
            return back()
                ->withErrors(['email' => 'Email tidak ditemukan.'])
                ->withInput();
        }

        try {
            $status = Password::sendResetLink(['email' => $validated['email']]);
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withErrors(['email' => 'Gagal mengirim email reset password. Periksa konfigurasi email SMTP Anda.'])
                ->withInput();
        }
        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput();
        }

        return back()->with('status', 'Link reset password berhasil dikirim ke email Anda.');
    }

    public function showResetPassword(string $token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput();
        }

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login.');
    }

    public function redirectToGoogle()
    {
        $this->storeIntendedFromRedirectQuery(request());
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google gagal. Silakan coba lagi.',
            ]);
        }

        $email = (string) ($googleUser->getEmail() ?? '');
        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google tidak memiliki email yang valid.',
            ]);
        }

        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => (string) ($googleUser->getName() ?? 'Google User'),
                'email' => $email,
                'password' => Str::random(32),
                'role' => 'user',
                'google_id' => (string) $googleUser->getId(),
                'avatar' => (string) ($googleUser->getAvatar() ?? ''),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->google_id = (string) $googleUser->getId();
            if (empty($user->avatar)) {
                $user->avatar = (string) ($googleUser->getAvatar() ?? '');
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    private function redirectByRole(?string $role)
    {
        if ($role === 'user') {
            return redirect()->intended(route('frontend.index'));
        }

        return redirect()->intended(route('pages.index'));
    }

    private function storeIntendedFromRedirectQuery(Request $request): void
    {
        $redirect = trim((string) $request->query('redirect', ''));
        if ($redirect === '') {
            return;
        }

        $parsed = parse_url($redirect);
        if ($parsed === false) {
            return;
        }

        $target = null;
        if (!isset($parsed['scheme']) && !isset($parsed['host'])) {
            $target = $redirect;
        } else {
            $currentHost = parse_url(url('/'), PHP_URL_HOST);
            if (($parsed['host'] ?? null) === $currentHost) {
                $target = ($parsed['path'] ?? '/');
                if (!empty($parsed['query'])) {
                    $target .= '?' . $parsed['query'];
                }
            }
        }

        if (!$target || !str_starts_with($target, '/')) {
            return;
        }

        $request->session()->put('url.intended', $target);
    }
}
