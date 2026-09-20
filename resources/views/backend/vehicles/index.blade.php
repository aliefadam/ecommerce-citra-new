@extends('layouts.app')

@section('title', 'Master Kendaraan')

@section('content')
<main class="mt-6 flex-1 p-4 sm:p-6">
    <section class="relative overflow-hidden rounded-3xl bg-slate-950 px-6 py-7 text-white shadow-xl sm:px-8">
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full border-[38px] border-amber-400/10"></div>
        <div class="absolute bottom-0 right-24 h-px w-64 bg-gradient-to-r from-transparent via-amber-400/70 to-transparent"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-3 py-1 text-xs font-bold uppercase tracking-[.18em] text-amber-300">
                    <i data-lucide="route" class="h-3.5 w-3.5"></i> Logistik Internal
                </div>
                <h1 class="text-2xl font-black tracking-tight sm:text-3xl">Master Kendaraan</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">Kelola armada, tarif berat, dan tarif per blok jarak. Ongkir dihitung otomatis pada transaksi manual.</p>
            </div>
            @if (auth()->user()?->hasAdminPermission('vehicles.create'))
                <a href="{{ route('vehicles.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-3 text-sm font-black text-slate-950 shadow-lg shadow-amber-950/30 transition hover:-translate-y-0.5 hover:bg-amber-300">
                    <i data-lucide="plus" class="h-4 w-4"></i> Tambah Kendaraan
                </a>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="mt-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            <i data-lucide="circle-check" class="h-5 w-5"></i> {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($vehicles as $vehicle)
            @php
                $icons = ['motor' => 'bike', 'mobil' => 'car', 'van' => 'truck', 'pickup' => 'truck', 'truk' => 'container', 'lainnya' => 'package'];
            @endphp
            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-slate-200/60 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-amber-500/40 dark:hover:shadow-black/20">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-amber-300 dark:bg-slate-700">
                            <i data-lucide="{{ $icons[$vehicle->type] ?? 'package' }}" class="h-6 w-6"></i>
                        </span>
                        <div>
                            <h2 class="font-extrabold text-slate-900 dark:text-white">{{ $vehicle->name }}</h2>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $vehicle->typeLabel() }}{{ $vehicle->plate_number ? ' · '.$vehicle->plate_number : '' }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $vehicle->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">{{ $vehicle->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>

                <div class="mt-5 grid grid-cols-3 divide-x divide-slate-100 rounded-xl bg-slate-50 px-3 py-3 dark:divide-slate-700 dark:bg-slate-900/50">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tarif / kg</p>
                        <p class="mt-1 font-black text-slate-900 dark:text-white">Rp {{ number_format($vehicle->rate_per_kg, 0, ',', '.') }}</p>
                    </div>
                    <div class="pl-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tarif jarak</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">Rp {{ number_format($vehicle->rate_per_distance_block, 0, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400">/{{ rtrim(rtrim(number_format($vehicle->distance_block_km, 2, ',', '.'), '0'), ',') }} km</p>
                    </div>
                    <div class="pl-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kapasitas</p>
                        <p class="mt-1 font-black text-slate-900 dark:text-white">{{ $vehicle->capacity_kg ? number_format($vehicle->capacity_kg, 0, ',', '.').' kg' : 'Tanpa batas' }}</p>
                    </div>
                </div>
                <p class="mt-4 min-h-10 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ $vehicle->notes ?: 'Tidak ada catatan operasional.' }}</p>

                <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4 dark:border-slate-700">
                    @if (auth()->user()?->hasAdminPermission('vehicles.edit'))
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-amber-500/10"><i data-lucide="pencil" class="h-3.5 w-3.5"></i> Edit</a>
                    @endif
                    @if (auth()->user()?->hasAdminPermission('vehicles.delete'))
                        <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Hapus kendaraan {{ addslashes($vehicle->name) }}?')">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center justify-center rounded-xl border border-red-100 p-2 text-red-500 hover:bg-red-50 dark:border-red-500/20 dark:hover:bg-red-500/10" title="Hapus"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center dark:border-slate-700 dark:bg-slate-800">
                <i data-lucide="truck" class="mx-auto h-10 w-10 text-slate-300"></i>
                <h2 class="mt-3 font-bold text-slate-700 dark:text-slate-200">Belum ada kendaraan</h2>
                <p class="mt-1 text-sm text-slate-400">Tambahkan kendaraan pertama untuk mulai menghitung ongkir kurir toko.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500"></i>
        <p><strong class="text-slate-700 dark:text-slate-200">Aturan hitung:</strong> ongkir adalah biaya berat + biaya jarak. Keduanya dibulatkan ke blok berikutnya agar perhitungan operasional konsisten.</p>
    </div>
</main>
@endsection
