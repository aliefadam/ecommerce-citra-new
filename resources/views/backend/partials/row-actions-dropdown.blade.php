{{--
    Dropdown aksi per baris (tombol titik tiga), konsisten dengan pola yang sudah
    dipakai di halaman Transaksi. Dipakai via:
    @include('backend.partials.row-actions-dropdown', ['actions' => [
        ['label' => 'Buka Detail', 'url' => route(...), 'icon' => 'eye'],
        ['label' => 'Cetak', 'url' => route(...), 'icon' => 'printer', 'target' => '_blank'],
    ]])
--}}
<div class="relative inline-block text-left" x-data="{ open: false }">
    <button type="button" @click="open = !open" @click.outside="open = false"
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Aksi lainnya">
        <i data-lucide="more-vertical" class="h-4 w-4"></i>
    </button>
    <div x-show="open" x-transition @click="open = false"
        class="absolute right-0 z-20 mt-1 w-44 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg py-1" style="display: none;">
        @foreach ($actions ?? [] as $action)
            <a href="{{ $action['url'] }}" @if(!empty($action['target'])) target="{{ $action['target'] }}" @endif
                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">
                <i data-lucide="{{ $action['icon'] }}" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                {{ $action['label'] }}
            </a>
        @endforeach
    </div>
</div>
