@props(['id', 'title'])
<div id="{{ $id }}" class="ec-overlay" data-ec-dialog hidden>
    <section class="ec-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" tabindex="-1">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <h2 id="{{ $id }}-title" class="ec-display text-xl">{{ $title }}</h2>
            <button type="button" class="grid size-11 place-items-center rounded-lg hover:bg-slate-100" data-ec-dialog-close aria-label="Tutup dialog"><i class="fi fi-rr-cross" aria-hidden="true"></i></button>
        </header>
        <div class="p-5">{{ $slot }}</div>
    </section>
</div>
