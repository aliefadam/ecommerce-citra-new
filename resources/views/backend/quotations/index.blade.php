@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Quotations</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Penawaran harga untuk customer B2B.</p>
            </div>
            <a href="{{ route('quotations.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/20 hover:bg-blue-700 transition-colors shrink-0">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Buat Quotation
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('quotations.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            <input type="text" name="q" value="{{ $filterKeyword }}" placeholder="Cari nomor quotation atau customer..."
                class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-200">
            <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-200">
                <option value="">Semua Status</option>
                @foreach (['draft', 'sent', 'accepted', 'partially_converted', 'rejected', 'expired', 'closed'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ $filterStatus === $statusOption ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-600">Cari</button>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 text-slate-500">Nomor</th>
                            <th class="text-left px-4 py-3 text-slate-500">Customer</th>
                            <th class="text-left px-4 py-3 text-slate-500">Berlaku Hingga</th>
                            <th class="text-left px-4 py-3 text-slate-500">Grand Total</th>
                            <th class="text-left px-4 py-3 text-slate-500">Status</th>
                            <th class="text-left px-4 py-3 text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($quotations as $quotation)
                            @php
                                $statusColor = match ($quotation->status) {
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    'sent' => 'bg-blue-100 text-blue-700',
                                    'accepted', 'partially_converted' => 'bg-emerald-100 text-emerald-700',
                                    'rejected', 'expired' => 'bg-red-100 text-red-700',
                                    'closed' => 'bg-violet-100 text-violet-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('quotations.show', $quotation) }}" class="hover:text-blue-600">{{ $quotation->quotation_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $quotation->customerName() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $quotation->valid_until->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $quotation->status)) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @include('backend.partials.row-actions-dropdown', ['actions' => [
                                        ['label' => 'Buka Detail', 'url' => route('quotations.show', $quotation), 'icon' => 'eye'],
                                        ['label' => 'Cetak', 'url' => route('quotations.print', $quotation), 'icon' => 'printer', 'target' => '_blank'],
                                    ]])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada quotation.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($quotations->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                    {{ $quotations->links() }}
                </div>
            @endif
        </div>
    </main>
@endsection
