@extends('layouts.app')

@section('title', 'Surat Jalan')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Surat Jalan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Bukti pengiriman barang dari gudang ke customer.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="GET" action="{{ route('delivery-notes.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-200">
                <option value="">Semua Status</option>
                @foreach (['draft', 'shipped', 'delivered', 'cancelled'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ $filterStatus === $statusOption ? 'selected' : '' }}>{{ ucfirst($statusOption) }}</option>
                @endforeach
            </select>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 text-slate-500">Nomor</th>
                            <th class="text-left px-4 py-3 text-slate-500">Sales Order</th>
                            <th class="text-left px-4 py-3 text-slate-500">Penerima</th>
                            <th class="text-left px-4 py-3 text-slate-500">Status</th>
                            <th class="text-left px-4 py-3 text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($deliveryNotes as $dn)
                            @php
                                $statusColor = match ($dn->status) {
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    'shipped' => 'bg-blue-100 text-blue-700',
                                    'delivered' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('delivery-notes.show', $dn) }}" class="hover:text-blue-600">{{ $dn->delivery_note_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <a href="{{ route('sales-orders.show', $dn->sales_order_id) }}" class="hover:text-blue-600 hover:underline">{{ $dn->salesOrder?->sales_order_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $dn->recipient_name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">{{ ucfirst($dn->status) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('delivery-notes.show', $dn) }}" class="text-blue-600 hover:underline text-xs font-semibold">Lihat Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada Surat Jalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($deliveryNotes->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                    {{ $deliveryNotes->links() }}
                </div>
            @endif
        </div>
    </main>
@endsection
