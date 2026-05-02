<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($user, $validated) {
            if (!empty($validated['is_primary'])) {
                $user->addresses()->update(['is_primary' => false]);
            }

            $address = $user->addresses()->create($validated);

            if (!$user->addresses()->where('is_primary', true)->exists()) {
                $address->update(['is_primary' => true]);
            }
        });

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($request, $address, $validated) {
            if (!empty($validated['is_primary'])) {
                $request->user()->addresses()->update(['is_primary' => false]);
            }

            $address->update($validated);

            if (!$request->user()->addresses()->where('is_primary', true)->exists()) {
                $address->update(['is_primary' => true]);
            }
        });

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', Rule::in(['Rumah', 'Kantor', 'Lainnya'])],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone_country_code' => ['required', 'string', 'max:8'],
            'phone_number' => ['required', 'string', 'max:30'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'address_line' => ['required', 'string', 'max:1000'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }
}
