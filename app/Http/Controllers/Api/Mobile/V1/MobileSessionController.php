<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MobileSessionController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'app' => [
                    'name' => (string) config('app.name'),
                    'version' => (string) config('mobile-spa.version'),
                    'locale' => (string) app()->getLocale(),
                    'pilot' => (bool) config('mobile-spa.pilot'),
                ],
                'user' => $this->userData($request->user()),
            ],
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => $this->userData($request->user()),
            ],
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        abort_unless($request->hasSession(), 419, 'Session same-origin diperlukan.');

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'return_url' => ['nullable', 'string', 'max:2048', 'regex:#^/app(?:/.*)?$#'],
        ]);

        $credentials = [
            'email' => strtolower(trim((string) $validated['email'])),
            'password' => (string) $validated['password'],
        ];

        if (! Auth::guard('web')->attempt($credentials, (bool) ($validated['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'user' => $this->userData($request->user()),
                'return_url' => $validated['return_url'] ?? '/app',
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        abort_unless($request->hasSession(), 419, 'Session same-origin diperlukan.');

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
            'password' => ['required', 'confirmed', Password::min(6)],
            'return_url' => ['nullable', 'string', 'max:2048', 'regex:#^/app(?:/.*)?$#'],
        ]);

        $user = DB::transaction(fn () => User::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'role' => 'user',
        ]));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'user' => $this->userData($user),
                'return_url' => $validated['return_url'] ?? '/app',
            ],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }

    /** @return array{id: int, name: string, email: string}|null */
    private function userData(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
        ];
    }
}
