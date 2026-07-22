{{-- Versi cetak (baris <tr> dalam tabel item 4 kolom) dari breakdown diskon/PPN/biaya lain, lihat financial-breakdown.blade.php untuk versi halaman detail. --}}
@if (($document->discount_amount ?? 0) > 0)
    <tr class="summary-row">
        <td colspan="3">Diskon</td>
        <td class="right">- Rp {{ number_format($document->discount_amount, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->ppn_amount > 0)
    <tr class="summary-row">
        <td colspan="3">PPN ({{ rtrim(rtrim(number_format($document->ppn_rate, 2, ',', '.'), '0'), ',') }}%)</td>
        <td class="right">Rp {{ number_format($document->ppn_amount, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->shipping_cost > 0)
    <tr class="summary-row">
        <td colspan="3">Ongkir</td>
        <td class="right">Rp {{ number_format($document->shipping_cost, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->admin_fee > 0)
    <tr class="summary-row">
        <td colspan="3">Biaya Admin</td>
        <td class="right">Rp {{ number_format($document->admin_fee, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->other_cost > 0)
    <tr class="summary-row">
        <td colspan="3">Lain-lain{{ $document->other_cost_note ? ' ('.$document->other_cost_note.')' : '' }}</td>
        <td class="right">Rp {{ number_format($document->other_cost, 0, ',', '.') }}</td>
    </tr>
@endif
