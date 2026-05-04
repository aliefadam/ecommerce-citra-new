<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    public function index()
    {
        $movements = StockMovement::query()
            ->with(['productVariant.product', 'productVariant.variant', 'adminUser'])
            ->latest()
            ->get();

        $variants = ProductVariant::query()
            ->with(['product', 'variant'])
            ->orderByDesc('id')
            ->get();

        return view('backend.stocks.index', compact('movements', 'variants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'type' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail((int) $validated['product_variant_id']);

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
