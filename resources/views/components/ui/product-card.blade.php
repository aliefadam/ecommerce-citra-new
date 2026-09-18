@props(['name', 'url', 'image' => null, 'price' => null, 'sku' => null, 'unit' => null, 'stock' => null, 'company' => null])
<article {{ $attributes->class('ec-product-card') }}>
    <a href="{{ $url }}" class="group block focus-visible:outline-offset-[-3px]">
        <div class="aspect-square overflow-hidden bg-slate-100">
            @if($image)<img src="{{ $image }}" alt="{{ $name }}" width="480" height="480" loading="lazy" class="size-full object-cover transition-transform duration-200 group-hover:scale-[1.02]" />
            @else<div class="grid size-full place-items-center text-slate-400"><i class="fi fi-rr-box text-3xl" aria-hidden="true"></i><span class="sr-only">Gambar belum tersedia</span></div>@endif
        </div>
        <div class="grid gap-2 p-4">
            @if($company)<x-ui.store-label :name="$company" />@endif
            <h3 class="line-clamp-2 min-h-10 text-sm font-bold leading-5 text-slate-900">{{ $name }}</h3>
            @if($sku)<p class="text-xs font-medium text-slate-500">SKU {{ $sku }}</p>@endif
            @if($price !== null)<x-ui.price :amount="$price" />@endif
            <div class="flex items-center justify-between gap-2 text-xs text-slate-600">@if($stock !== null)<span>{{ $stock > 0 ? 'Stok '.$stock : 'Stok habis' }}</span>@endif @if($unit)<span>/ {{ $unit }}</span>@endif</div>
        </div>
    </a>
</article>
