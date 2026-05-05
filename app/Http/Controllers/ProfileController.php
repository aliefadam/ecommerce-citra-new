<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function updateBiodata(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_country_code' => ['nullable', 'string', 'max:8'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'social_url' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'avatar_remove' => ['nullable', 'boolean'],
        ]);

        $fullName = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));

        $payload = [
            'name' => $fullName !== '' ? $fullName : $user->name,
            ...collect($validated)->except(['avatar_file', 'avatar_remove'])->all(),
        ];

        $removeAvatar = (bool) ($validated['avatar_remove'] ?? false);

        if ($removeAvatar) {
            $this->deleteLocalAvatarIfExists((string) ($user->avatar ?? ''));
            $payload['avatar'] = null;
        }

        if ($request->hasFile('avatar_file')) {
            $this->deleteLocalAvatarIfExists((string) ($user->avatar ?? ''));
            $path = $request->file('avatar_file')->store('avatars', 'public');
            $payload['avatar'] = 'storage/' . ltrim($path, '/');
        }

        $user->update($payload);

        return back()->with('success', 'Biodata berhasil diperbarui.');
    }

    private function deleteLocalAvatarIfExists(string $avatar): void
    {
        $avatar = trim($avatar);
        if ($avatar === '' || str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return;
        }

        $path = ltrim($avatar, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak valid.']);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
