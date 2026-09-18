@props(['paginator'])
@if($paginator->hasPages())
    <nav {{ $attributes->class('flex items-center justify-between gap-3') }} aria-label="Paginasi">
        @if($paginator->onFirstPage())<span class="ec-btn ec-btn-outline" aria-disabled="true">Sebelumnya</span>@else<a class="ec-btn ec-btn-outline" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
        <span class="text-sm font-bold text-slate-600">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())<a class="ec-btn ec-btn-outline" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span class="ec-btn ec-btn-outline" aria-disabled="true">Berikutnya</span>@endif
    </nav>
@endif
