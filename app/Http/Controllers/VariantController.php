<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantController extends Controller
{
    public function index()
    {
        $variants = Variant::orderBy('name')->orderBy('value')->get();

        return view('backend.variants.index', compact('variants'));
    }

    public function create()
    {
        return view('backend.variants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'value' => [
                'required', 'string', 'max:255',
                Rule::unique('variants')->where(fn ($q) => $q->where('name', $request->name)),
            ],
        ]);

        Variant::create($validated);

        return redirect()
            ->route('variants.index')
            ->with('success', 'Varian berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(Variant $variant)
    {
        return view('backend.variants.edit', compact('variant'));
    }

    public function update(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'value' => [
                'required', 'string', 'max:255',
                Rule::unique('variants')
                    ->where(fn ($q) => $q->where('name', $request->name))
                    ->ignore($variant->id),
            ],
        ]);

        $variant->update($validated);

        return redirect()
            ->route('variants.index')
            ->with('success', 'Varian berhasil diperbarui.');
    }

    public function destroy(Variant $variant)
    {
        $variant->delete();

        return redirect()
            ->route('variants.index')
            ->with('success', 'Varian berhasil dihapus.');
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
        ]);

        $variant = Variant::firstOrCreate(
            ['name' => trim($request->name), 'value' => trim($request->value)]
        );

        return response()->json([
            'id'    => $variant->id,
            'name'  => $variant->name,
            'value' => $variant->value,
        ]);
    }
}
