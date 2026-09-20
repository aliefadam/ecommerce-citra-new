@extends('layouts.app')

@section('title', 'Tambah Kendaraan')

@section('content')
<main class="mt-6 flex-1 p-4 sm:p-6">
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('vehicles.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-amber-600">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Master Kendaraan
        </a>
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-amber-600">Armada Baru</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Tambah kendaraan kurir toko</h1>
            <p class="mt-1 text-sm text-slate-500">Tentukan kapasitas, tarif berat, dan tarif jarak untuk perhitungan ongkir manual.</p>
        </div>

        <form method="POST" action="{{ route('vehicles.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            @csrf
            @include('backend.vehicles.partials.form')
            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-700">
                <a href="{{ route('vehicles.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Batal</a>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-amber-500 hover:text-slate-950 dark:bg-amber-400 dark:text-slate-950">Simpan Kendaraan</button>
            </div>
        </form>
    </div>
</main>
@endsection
