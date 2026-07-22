{{-- Blok identitas perusahaan (logo + alamat) untuk header dokumen cetak. Dipakai di dalam <thead> agar ikut berulang di tiap halaman saat dicetak. --}}
<div class="print-company">
    @if ($company?->logo_path)
        <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="print-company-logo">
    @else
        <span class="brand-mark">{{ strtoupper(substr($company->name ?? 'C', 0, 1)) }}</span>
    @endif
    <div class="print-company-info">
        <strong>{{ $company->legal_name ?: ($company->name ?? '') }}</strong>
        @if ($company?->address)
            <p>{{ $company->address }}</p>
        @endif
        @if ($company?->phone || $company?->email)
            <p>{{ collect([$company->phone, $company->email])->filter()->implode(' • ') }}</p>
        @endif
        @if ($company?->npwp)
            <p>NPWP: {{ $company->npwp }}</p>
        @endif
    </div>
</div>
