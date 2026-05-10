<?php

namespace App\Http\Controllers;

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function updateBiodata(Request $request, ImageOptimizer $imageOptimizer)
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
            'avatar_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'avatar_remove' => ['nullable', 'boolean'],
        ]);

        $fullName = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));

        $payload = [
            'name' => $fullName !== '' ? $fullName : $user->name,
            ...collect($validated)->except(['avatar_file', 'avatar_remove'])->all(),
        ];

        $removeAvatar = (bool) ($validated['avatar_remove'] ?? false);

        if ($removeAvatar) {
            $imageOptimizer->deletePublicFile((string) ($user->avatar ?? ''));
            $payload['avatar'] = null;
        }

        if ($request->hasFile('avatar_file')) {
            $imageOptimizer->deletePublicFile((string) ($user->avatar ?? ''));
            $payload['avatar'] = $imageOptimizer->storeWebp($request->file('avatar_file'), 'avatars', 512, 512, 82, true);
        }

        $user->update($payload);

        return back()->with('success', 'Biodata berhasil diperbarui.');
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
