@props(['name' => 'quantity', 'value' => 1, 'min' => 1, 'max' => null])
<div class="inline-grid grid-cols-[44px_4rem_44px] overflow-hidden rounded-lg border border-slate-300 bg-white" data-quantity>
    <button type="button" class="grid min-h-11 place-items-center hover:bg-slate-100" data-quantity-minus aria-label="Kurangi jumlah">−</button>
    <input class="w-full border-x border-slate-300 text-center font-bold tabular-nums outline-none" type="number" name="{{ $name }}"
        value="{{ $value }}" min="{{ $min }}" @if($max !== null) max="{{ $max }}" @endif aria-label="Jumlah" />
    <button type="button" class="grid min-h-11 place-items-center hover:bg-slate-100" data-quantity-plus aria-label="Tambah jumlah">+</button>
</div>
