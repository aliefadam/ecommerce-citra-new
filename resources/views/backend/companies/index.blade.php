@extends('layouts.app')

@section('title', 'Companies')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Perusahaan</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola perusahaan (PT) yang beroperasi dalam satu sistem ini.</p>
            </div>
            @if (auth()->user()->hasAdminPermission('companies.create'))
                <a href="{{ route('companies.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/40">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Perusahaan
                </a>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Perusahaan</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Prefix Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Produk</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Transaksi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-500 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($companies as $company)
                            <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if ($company->logo_path)
                                            <img src="{{ asset('storage/' . $company->logo_path) }}" class="h-8 w-8 rounded-lg object-contain border border-slate-200 dark:border-slate-600 bg-white">
                                        @endif
                                        <div>
                                            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $company->name }}</div>
                                            <div class="mt-0.5 text-xs text-slate-400">{{ $company->legal_name ?: '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $company->invoice_prefix }}</td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $company->products_count }}</td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $company->transactions_count }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $company->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                                        {{ $company->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()->hasAdminPermission('companies.edit'))
                                            <a href="{{ route('companies.edit', $company) }}"
                                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-blue-500/50 dark:hover:text-blue-300">
                                                Edit
                                            </a>
                                        @endif
                                        @if ($company->is_active && auth()->user()->hasAdminPermission('companies.delete'))
                                            <form action="{{ route('companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Nonaktifkan perusahaan ini? Data historis tetap tersimpan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada perusahaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection
