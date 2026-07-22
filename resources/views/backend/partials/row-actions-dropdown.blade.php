{{--
    Dropdown aksi per baris (tombol titik tiga), konsisten dengan pola yang sudah
    dipakai di halaman Transaksi. Dipakai via:
    @include('backend.partials.row-actions-dropdown', ['actions' => [
        ['label' => 'Buka Detail', 'url' => route(...), 'icon' => 'eye'],
        ['label' => 'Cetak', 'url' => route(...), 'icon' => 'printer', 'target' => '_blank'],
    ]])
--}}
<div class="inline-block text-left" x-data="{ open: false, top: 0, left: 0 }">
    <button type="button" x-ref="rowActionBtn"
        @click="
            open = !open;
            if (open) {
                const rect = $refs.rowActionBtn.getBoundingClientRect();
                const menuWidth = 176;
                left = Math.max(8, rect.right - menuWidth);
                top = rect.bottom + 4;
                if (top + 120 > window.innerHeight) top = rect.top - 4 - 120;
                $nextTick(() => window.lucide?.createIcons?.());
            }
        "
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Aksi lainnya">
        <i data-lucide="more-vertical" class="h-4 w-4"></i>
    </button>

    {{-- Teleported to <body> so the table card's `overflow-hidden` can't clip the menu. --}}
    <template x-teleport="body">
        <div x-show="open" @click.outside="open = false" x-transition
            :style="`top: ${top}px; left: ${left}px;`"
            class="fixed z-[9999] w-44 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg py-1"
            style="display: none;">
            @foreach ($actions ?? [] as $action)
                <a href="{{ $action['url'] }}" @if(!empty($action['target'])) target="{{ $action['target'] }}" @endif
                    @click="open = false"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">
                    <i data-lucide="{{ $action['icon'] }}" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </template>
</div>
