@props(['id', 'title', 'side' => 'right'])
<div id="{{ $id }}" class="fixed inset-0" style="z-index:var(--ec-z-drawer)" data-ec-dialog hidden>
    <button class="absolute inset-0 bg-slate-950/60" data-ec-dialog-close aria-label="Tutup drawer"></button>
    <section class="absolute inset-y-0 {{ $side === 'left' ? 'left-0' : 'right-0' }} flex w-[min(90vw,24rem)] flex-col bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" tabindex="-1">
        <header class="flex items-center justify-between border-b border-slate-200 p-4"><h2 id="{{ $id }}-title" class="ec-display text-xl">{{ $title }}</h2><button class="grid size-11 place-items-center" data-ec-dialog-close aria-label="Tutup drawer">×</button></header>
        <div class="flex-1 overflow-y-auto p-4">{{ $slot }}</div>
    </section>
</div>
