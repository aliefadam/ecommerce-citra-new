@extends('layouts.app')

@section('title', 'Packing Lists')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Packing Lists</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Rincian isi kemasan per pengiriman.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 text-slate-500">Nomor</th>
                            <th class="text-left px-4 py-3 text-slate-500">Surat Jalan</th>
                            <th class="text-right px-4 py-3 text-slate-500">Total Berat</th>
                            <th class="text-right px-4 py-3 text-slate-500">Koli</th>
                            <th class="text-left px-4 py-3 text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($packingLists as $pl)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $pl->packing_list_no }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <a href="{{ route('delivery-notes.show', $pl->delivery_note_id) }}" class="hover:text-blue-600 hover:underline">{{ $pl->deliveryNote?->delivery_note_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ number_format($pl->total_weight_grams / 1000, 2) }} kg</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $pl->total_packages ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('delivery-notes.show', $pl->delivery_note_id) }}" class="text-blue-600 hover:underline text-xs font-semibold">Lihat Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada Packing List.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($packingLists->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                    {{ $packingLists->links() }}
                </div>
            @endif
        </div>
    </main>
@endsection
