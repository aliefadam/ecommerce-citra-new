@php
    $quotation = $quotation ?? null;
    $isEdit = $quotation !== null;
    $existingCustomerMode = $isEdit && $quotation->user_id ? 'existing' : 'manual';
    $itemsEditable = ! $isEdit || $quotation->itemsAreEditable();
    $defaultPpnRate = $defaultPpnRate ?? 0;
@endphp

<section class="space-y-6">
    {{-- Customer --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-bold text-slate-800 dark:text-white">Customer</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih customer existing atau input customer manual.</p>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-700/70">
                <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 has-[:checked]:bg-white has-[:checked]:text-blue-700 has-[:checked]:shadow-sm dark:text-slate-300 dark:has-[:checked]:bg-slate-800 dark:has-[:checked]:text-blue-300">
                    <input class="sr-only" type="radio" name="customer_mode" value="existing" {{ $existingCustomerMode === 'existing' ? 'checked' : '' }} onchange="setCustomerMode('existing')">
                    Existing
                </label>
                <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 has-[:checked]:bg-white has-[:checked]:text-blue-700 has-[:checked]:shadow-sm dark:text-slate-300 dark:has-[:checked]:bg-slate-800 dark:has-[:checked]:text-blue-300">
                    <input class="sr-only" type="radio" name="customer_mode" value="manual" {{ $existingCustomerMode === 'manual' ? 'checked' : '' }} onchange="setCustomerMode('manual')">
                    Manual
                </label>
            </div>
        </div>

        <div id="existingCustomerPanel" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem] {{ $existingCustomerMode === 'manual' ? 'hidden' : '' }}">
            <div>
                <label for="customer_id" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Customer</label>
                <select id="customer_id" name="customer_id" class="w-full"></select>
            </div>
            <div id="customerSnapshot" class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-700/40 dark:text-slate-400">
                {{ $isEdit && $quotation->user ? $quotation->user->name . ' — ' . $quotation->user->email : 'Belum ada customer dipilih.' }}
            </div>
        </div>

        <div id="manualCustomerPanel" class="{{ $existingCustomerMode === 'existing' ? 'hidden' : '' }} grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nama</label>
                <input name="manual_customer_name" type="text" value="{{ old('manual_customer_name', $quotation->manual_customer_name ?? '') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nomor HP</label>
                <input name="manual_customer_phone" type="text" value="{{ old('manual_customer_phone', $quotation->manual_customer_phone ?? '') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Email</label>
                <input name="manual_customer_email" type="email" value="{{ old('manual_customer_email', $quotation->manual_customer_email ?? '') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
        </div>
    </div>

    {{-- Produk --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-slate-800 dark:text-white">Produk</h2>
                @if ($itemsEditable)
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan produk, qty, harga hasil nego, dan catatan.</p>
                @else
                    <p class="text-xs text-amber-600 dark:text-amber-400">Item terkunci — sudah ada Sales Order yang ditarik dari quotation ini.</p>
                @endif
            </div>
            @if ($itemsEditable)
                <button type="button" onclick="addQuotationItem()"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-500/20 transition-colors hover:bg-blue-700">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Tambah Item
                </button>
            @endif
        </div>

        @if ($itemsEditable)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-400 dark:bg-slate-700/50 dark:text-slate-500">
                        <tr>
                            <th class="product-col px-3 py-3">Produk</th>
                            <th class="w-20 px-3 py-3">Qty</th>
                            <th class="w-32 px-3 py-3">Harga</th>
                            <th class="px-3 py-3">Catatan</th>
                            <th class="w-32 px-3 py-3 text-right">Subtotal</th>
                            <th class="w-10 px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="quotationItemsBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-400 dark:bg-slate-700/50 dark:text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Produk</th>
                            <th class="w-20 px-3 py-3 text-right">Qty</th>
                            <th class="w-32 px-3 py-3 text-right">Harga</th>
                            <th class="w-32 px-3 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach ($quotation->details as $detail)
                            <tr>
                                <td class="px-3 py-3">{{ $detail->product_name }}{{ $detail->variant_name ? ' - ' . $detail->variant_name : '' }}</td>
                                <td class="px-3 py-3 text-right">{{ $detail->quantity }}</td>
                                <td class="px-3 py-3 text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right font-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Berlaku & Catatan --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Berlaku Hingga <span class="text-red-500">*</span></label>
                <input name="valid_until" type="date" required
                    value="{{ old('valid_until', $isEdit ? $quotation->valid_until->format('Y-m-d') : '') }}"
                    min="{{ now()->addDay()->format('Y-m-d') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
        </div>
        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Catatan / Syarat & Ketentuan</label>
            <textarea name="note" rows="3"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ old('note', $quotation->note ?? '') }}</textarea>
        </div>
    </div>
</section>

{{-- Sidebar total --}}
<aside class="xl:sticky xl:top-24 xl:h-fit">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan</h2>
        </div>

        @if ($itemsEditable)
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="text-slate-500 dark:text-slate-400 shrink-0">Subtotal</span>
                    <span id="summarySubtotal" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                </div>

                <div class="flex items-center gap-3 px-5 py-3">
                    <label for="discount_amount" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Diskon</label>
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input id="discount_amount" name="discount_amount" type="number" min="0" step="1"
                            value="{{ old('discount_amount', $quotation->discount_amount ?? 0) }}" oninput="recalculateQuotation()"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                    <span id="summaryDiscount" class="shrink-0 w-24 text-right font-semibold text-emerald-600">- Rp 0</span>
                </div>

                <div class="flex items-center gap-3 px-5 py-3">
                    <label for="ppn_rate" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">PPN (%)</label>
                    <input id="ppn_rate" name="ppn_rate" type="number" min="0" max="100" step="0.01"
                        value="{{ old('ppn_rate', $quotation->ppn_rate ?? $defaultPpnRate) }}" oninput="recalculateQuotation()"
                        class="w-20 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    <span id="summaryPpn" class="shrink-0 flex-1 text-right font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                </div>

                <div class="flex items-center gap-3 px-5 py-3">
                    <label for="shipping_cost" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Ongkir</label>
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input id="shipping_cost" name="shipping_cost" type="number" min="0" step="1"
                            value="{{ old('shipping_cost', $quotation->shipping_cost ?? 0) }}" oninput="recalculateQuotation()"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                </div>

                <div class="flex items-center gap-3 px-5 py-3">
                    <label for="admin_fee" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Biaya Admin</label>
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input id="admin_fee" name="admin_fee" type="number" min="0" step="1"
                            value="{{ old('admin_fee', $quotation->admin_fee ?? 0) }}" oninput="recalculateQuotation()"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                </div>

                <div class="flex items-center gap-3 px-5 py-3">
                    <label for="other_cost" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Lain-lain</label>
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input id="other_cost" name="other_cost" type="number" min="0" step="1"
                            value="{{ old('other_cost', $quotation->other_cost ?? 0) }}" oninput="recalculateQuotation()"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                </div>
                <div class="px-5 pb-3 -mt-2">
                    <input name="other_cost_note" type="text" placeholder="Keterangan biaya lain-lain (opsional)"
                        value="{{ old('other_cost_note', $quotation->other_cost_note ?? '') }}"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                    <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                    <span id="summaryGrandTotal" class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp 0</span>
                </div>
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="text-slate-500 dark:text-slate-400 shrink-0">Subtotal</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($quotation->subtotal_amount, 0, ',', '.') }}</span>
                </div>
                @include('backend.partials.financial-breakdown', ['document' => $quotation])
                <div class="flex items-center justify-between gap-3 px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                    <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif

        <div class="px-5 py-4">
            <button type="submit"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition-colors hover:bg-blue-700">
                <i data-lucide="save" class="h-4 w-4"></i>
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Quotation' }}
            </button>
        </div>
    </div>
</aside>
