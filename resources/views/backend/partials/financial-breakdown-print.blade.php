{{-- Versi cetak (tabel <tr><td>) dari breakdown diskon/PPN/biaya lain, lihat financial-breakdown.blade.php untuk versi halaman detail. --}}
@if (($document->discount_amount ?? 0) > 0)
    <tr>
        <td>Diskon</td>
        <td class="right">- Rp {{ number_format($document->discount_amount, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->ppn_amount > 0)
    <tr>
        <td>PPN ({{ rtrim(rtrim(number_format($document->ppn_rate, 2, ',', '.'), '0'), ',') }}%)</td>
        <td class="right">Rp {{ number_format($document->ppn_amount, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->shipping_cost > 0)
    <tr>
        <td>Ongkir</td>
        <td class="right">Rp {{ number_format($document->shipping_cost, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->admin_fee > 0)
    <tr>
        <td>Biaya Admin</td>
        <td class="right">Rp {{ number_format($document->admin_fee, 0, ',', '.') }}</td>
    </tr>
@endif
@if ($document->other_cost > 0)
    <tr>
        <td>Lain-lain{{ $document->other_cost_note ? ' ('.$document->other_cost_note.')' : '' }}</td>
        <td class="right">Rp {{ number_format($document->other_cost, 0, ',', '.') }}</td>
    </tr>
@endif
