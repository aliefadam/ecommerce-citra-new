{{--
    Shared breakdown baris diskon/PPN/biaya lain untuk halaman show & cetak Quotation,
    Sales Order, Proforma Invoice, dan Invoice — dipakai dengan @include(..., ['document' => $quotation])
    dsb. karena kolomnya (ppn_rate, ppn_amount, shipping_cost, admin_fee, other_cost,
    other_cost_note) identik namanya di keempat model.
--}}
@if (($document->discount_amount ?? 0) > 0)
    <div class="flex items-center justify-between px-5 py-3">
        <span class="text-slate-500 dark:text-slate-400">Diskon</span>
        <span class="font-semibold text-emerald-600">- Rp {{ number_format($document->discount_amount, 0, ',', '.') }}</span>
    </div>
@endif
@if ($document->ppn_amount > 0)
    <div class="flex items-center justify-between px-5 py-3">
        <span class="text-slate-500 dark:text-slate-400">PPN ({{ rtrim(rtrim(number_format($document->ppn_rate, 2, ',', '.'), '0'), ',') }}%)</span>
        <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($document->ppn_amount, 0, ',', '.') }}</span>
    </div>
@endif
@if ($document->shipping_cost > 0)
    <div class="flex items-center justify-between px-5 py-3">
        <span class="text-slate-500 dark:text-slate-400">Ongkir</span>
        <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($document->shipping_cost, 0, ',', '.') }}</span>
    </div>
@endif
@if ($document->admin_fee > 0)
    <div class="flex items-center justify-between px-5 py-3">
        <span class="text-slate-500 dark:text-slate-400">Biaya Admin</span>
        <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($document->admin_fee, 0, ',', '.') }}</span>
    </div>
@endif
@if ($document->other_cost > 0)
    <div class="flex items-center justify-between px-5 py-3">
        <span class="text-slate-500 dark:text-slate-400">Lain-lain{{ $document->other_cost_note ? ' ('.$document->other_cost_note.')' : '' }}</span>
        <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($document->other_cost, 0, ',', '.') }}</span>
    </div>
@endif
