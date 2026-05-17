@extends('layouts.app')

@section('title', 'Promo Pages')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Promo Pages</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Landing page promo umum untuk campaign berkala selain flash sale.</p>
        </div>
        <a href="{{ route('promo-pages.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">Tambah Promo</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Title</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Slug</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Periode</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Status</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($promos as $promo)
                        <tr>
                            <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">{{ $promo->title }}</td>
                            <td class="px-4 py-3.5 text-slate-500">/promo/{{ $promo->slug }}</td>
                            <td class="px-4 py-3.5 text-slate-500">{{ optional($promo->starts_at)->format('d M Y') ?: '-' }} — {{ optional($promo->ends_at)->format('d M Y') ?: '-' }}</td>
                            <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $promo->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $promo->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('frontend.promo', $promo->slug) }}" target="_blank" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat</a>
                                    <a href="{{ route('promo-pages.edit', $promo) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-100">Edit</a>
                                    <form action="{{ route('promo-pages.destroy', $promo) }}" method="POST" onsubmit="return confirm('Hapus promo ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada promo page.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
