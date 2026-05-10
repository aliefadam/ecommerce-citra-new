<?php

namespace App\Http\Controllers;

use App\Models\MemberTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberTierController extends Controller
{
    public function index()
    {
        $tiers = MemberTier::query()
            ->orderBy('minimum_spending')
            ->orderBy('sort_order')
            ->get();

        return view('backend.member-tiers.index', compact('tiers'));
    }

    public function create()
    {
        return view('backend.member-tiers.create', [
            'tier' => new MemberTier(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTier($request);

        MemberTier::query()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('member-tiers.index')->with('success', 'Tier membership berhasil ditambahkan.');
    }

    public function edit(MemberTier $memberTier)
    {
        return view('backend.member-tiers.edit', [
            'tier' => $memberTier,
        ]);
    }

    public function update(Request $request, MemberTier $memberTier)
    {
        $validated = $this->validateTier($request, $memberTier);

        $memberTier->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('member-tiers.index')->with('success', 'Tier membership berhasil diperbarui.');
    }

    public function destroy(MemberTier $memberTier)
    {
        $memberTier->delete();

        return redirect()->route('member-tiers.index')->with('success', 'Tier membership berhasil dihapus.');
    }

    private function validateTier(Request $request, ?MemberTier $memberTier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('member_tiers', 'name')->ignore($memberTier?->id)],
            'minimum_spending' => ['required', 'integer', 'min:0'],
            'color' => ['required', 'string', 'max:30'],
            'benefits' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
