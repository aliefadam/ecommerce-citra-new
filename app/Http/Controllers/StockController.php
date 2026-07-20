<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    use ScopesToActiveCompany;

    public function index()
    {
        $companyId = $this->activeCompanyId();

        $movements = StockMovement::query()
            ->whereHas('productVariant.product', fn ($q) => $q->where('company_id', $companyId))
            ->with(['productVariant.product', 'productVariant.variant', 'productVariant.attributeValues.definition', 'adminUser'])
            ->latest()
            ->get();

        $variants = ProductVariant::query()
            ->whereHas('product', fn ($q) => $q->where('company_id', $companyId))
            ->with(['product', 'variant', 'attributeValues.definition'])
            ->orderByDesc('id')
            ->get();

        return view('backend.stocks.index', compact('movements', 'variants'));
    }

    public function updateThreshold(Request $request, ProductVariant $productVariant)
    {
        $this->guardCompanyOwnership($productVariant->product?->company_id);

        $validated = $request->validate([
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
        ]);

        $productVariant->update([
            'low_stock_threshold' => (int) $validated['low_stock_threshold'],
        ]);

        return back()->with('success', 'Batas stok rendah berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'type' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        $companyId = $this->activeCompanyId();

        DB::transaction(function () use ($request, $validated, $companyId) {
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail((int) $validated['product_variant_id']);

            $this->guardCompanyOwnership($variant->product?->company_id);

            $before = (int) $variant->stock;
            $qty = (int) $validated['quantity'];
            $type = (string) $validated['type'];

            if ($type === 'out' && $before < $qty) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak mencukupi untuk pengurangan.',
                ]);
            }

            $after = $type === 'in' ? ($before + $qty) : ($before - $qty);
            $variant->stock = $after;
            $variant->save();

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'admin_user_id' => $request->user()?->id,
                'type' => $type,
                'quantity' => $qty,
                'stock_before' => $before,
                'stock_after' => $after,
                'source' => 'manual',
                'description' => $validated['description'] ?: null,
            ]);
        });

        return redirect()->route('stocks.index')->with('success', 'Data stok berhasil ditambahkan.');
    }
}
