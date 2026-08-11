<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showRegister(Request $request)
    {
        $checkoutOrderId = strtoupper(trim((string) $request->query('checkout_order', '')));
        $checkoutTransaction = $checkoutOrderId !== ''
            ? $this->guestCheckoutTransaction($request, $checkoutOrderId)
            : null;

        return view('auth.register', compact('checkoutTransaction'));
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
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [strtolower(trim((string) $value))])->exists()) {
                        $fail('Email sudah terdaftar. Silakan masuk ke akun Anda.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'checkout_order' => ['nullable', 'string', 'max:100'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $checkoutOrderId = strtoupper(trim((string) ($validated['checkout_order'] ?? '')));
        $checkoutTransaction = $checkoutOrderId !== ''
            ? $this->guestCheckoutTransaction($request, $checkoutOrderId)
            : null;

        if ($checkoutOrderId !== '' && ! $checkoutTransaction) {
            throw ValidationException::withMessages([
                'checkout_order' => 'Sesi checkout tidak valid. Buka kembali tautan dari halaman pesanan Anda.',
            ]);
        }

        if ($checkoutTransaction && strtolower(trim((string) $checkoutTransaction->manual_customer_email)) !== $email) {
            throw ValidationException::withMessages([
                'email' => 'Gunakan email yang sama dengan email pemesan.',
            ]);
        }

        [$user, $claimedOrders] = DB::transaction(function () use ($request, $validated, $email, $checkoutTransaction): array {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => $validated['password'],
                'role' => 'user',
            ]);

            $claimedOrders = 0;
            if ($checkoutTransaction) {
                $authorizedOrders = $this->authorizedGuestOrders($request);
                $claimedOrders = Transaction::query()
                    ->whereNull('user_id')
                    ->where('source', Transaction::SOURCE_CHECKOUT)
                    ->whereIn('order_id', $authorizedOrders)
                    ->whereRaw('LOWER(manual_customer_email) = ?', [$email])
                    ->update(['user_id' => $user->id]);
            }

            return [$user, $claimedOrders];
        });

        Auth::login($user);
        $request->session()->regenerate();

        if ($claimedOrders > 0) {
            $request->session()->forget(['guest_owned_orders', 'verified_orders']);

            return redirect()
                ->route('frontend.profil', ['tab' => 'pesanan'])
                ->with('success', "Akun berhasil dibuat. {$claimedOrders} pesanan sudah tersimpan di akun Anda.");
        }

        return $this->redirectByRole($user);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak valid.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user());
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
        if (! $exists) {
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
        if (! $user) {
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
            $user->avatar = (string) ($googleUser->getAvatar() ?? '');
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    private function redirectByRole(?User $user)
    {
        if ($user?->canAccessAdminPanel()) {
            return redirect()->intended(route('pages.index'));
        }

        return redirect()->intended(route('frontend.index'));
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
        if (! isset($parsed['scheme']) && ! isset($parsed['host'])) {
            $target = $redirect;
        } else {
            $currentHost = parse_url(url('/'), PHP_URL_HOST);
            if (($parsed['host'] ?? null) === $currentHost) {
                $target = ($parsed['path'] ?? '/');
                if (! empty($parsed['query'])) {
                    $target .= '?'.$parsed['query'];
                }
            }
        }

        if (! $target || ! str_starts_with($target, '/')) {
            return;
        }

        $request->session()->put('url.intended', $target);
    }

    private function guestCheckoutTransaction(Request $request, string $orderId): ?Transaction
    {
        $authorizedOrders = $this->authorizedGuestOrders($request);
        if (! in_array($orderId, $authorizedOrders, true)) {
            return null;
        }

        return Transaction::query()
            ->whereNull('user_id')
            ->where('source', Transaction::SOURCE_CHECKOUT)
            ->where('order_id', $orderId)
            ->first();
    }

    /** @return array<int, string> */
    private function authorizedGuestOrders(Request $request): array
    {
        return collect($request->session()->get('guest_owned_orders', []))
            ->merge($request->session()->get('verified_orders', []))
            ->map(fn ($orderId) => strtoupper(trim((string) $orderId)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
