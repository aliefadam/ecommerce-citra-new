<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::withCount('items')->latest()->get();

        return view('backend.flash-sales.index', compact('flashSales'));
    }

    public function create()
    {
        $productVariants = ProductVariant::with(['product', 'variant'])
            ->orderByDesc('id')
            ->get();

        return view('backend.flash-sales.create', compact('productVariants'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateFlashSale($request);

        DB::transaction(function () use ($validated) {
            $flashSale = FlashSale::create([
                'name' => $validated['name'],
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $flashSale->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'discount_price' => $item['discount_price'],
                    'quota' => $item['quota'],
                    'sold' => 0,
                    'is_active' => array_key_exists('is_active', $item) ? (bool) $item['is_active'] : true,
                ]);
            }
        });

        return redirect()->route('flash-sales.index')->with('success', 'Flash sale berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(FlashSale $flashSale)
    {
        $flashSale->load('items');
        $productVariants = ProductVariant::with(['product', 'variant'])
            ->orderByDesc('id')
            ->get();

        return view('backend.flash-sales.edit', compact('flashSale', 'productVariants'));
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $validated = $this->validateFlashSale($request);

        DB::transaction(function () use ($flashSale, $validated) {
            $flashSale->update([
                'name' => $validated['name'],
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $flashSale->items()->delete();

            foreach ($validated['items'] as $item) {
                $flashSale->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'discount_price' => $item['discount_price'],
                    'quota' => $item['quota'],
                    'sold' => 0,
                    'is_active' => array_key_exists('is_active', $item) ? (bool) $item['is_active'] : true,
                ]);
            }
        });

        return redirect()->route('flash-sales.index')->with('success', 'Flash sale berhasil diperbarui.');
    }

    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();

        return redirect()->route('flash-sales.index')->with('success', 'Flash sale berhasil dihapus.');
    }

    private function validateFlashSale(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id', 'distinct'],
            'items.*.discount_price' => ['required', 'numeric', 'min:0'],
            'items.*.quota' => ['required', 'integer', 'min:1'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ], [
            'items.required' => 'Minimal satu item flash sale harus diisi.',
            'items.*.product_variant_id.distinct' => 'Item varian dalam flash sale tidak boleh duplikat.',
            'end_at.after' => 'Waktu selesai harus lebih besar dari waktu mulai.',
        ]);
    }
}
