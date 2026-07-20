@extends('layouts.user')

@section('title', 'Pesanan Dibuat - ' . ($appStoreName ?? 'Ecommerce Citra'))
@section('body_class', 'bg-slate-50 text-slate-800 overflow-x-hidden')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-2">
                {{ $orders->count() > 1 ? $orders->count() . ' Pesanan Berhasil Dibuat' : 'Pesanan Berhasil Dibuat' }}
            </h1>
            <p class="text-slate-500 text-sm">
                @if ($orders->count() > 1)
                    Keranjangmu berisi produk dari {{ $orders->count() }} toko berbeda, jadi dibuat sebagai {{ $orders->count() }} pesanan terpisah. Kamu bisa bayar sekarang atau nanti, satu per satu.
                @else
                    Silakan lanjutkan pembayaran untuk pesananmu.
                @endif
            </p>
        </div>

        <div class="space-y-4">
            @forelse ($orders as $order)
                @php
                    $statusMap = [
                        'pending' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-100 text-amber-700'],
                        'menunggu' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-100 text-amber-700'],
                        'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-amber-100 text-amber-700'],
                        'paid' => ['label' => 'Sudah Dibayar', 'class' => 'bg-emerald-100 text-emerald-700'],
                        'settlement' => ['label' => 'Sudah Dibayar', 'class' => 'bg-emerald-100 text-emerald-700'],
                        'capture' => ['label' => 'Sudah Dibayar', 'class' => 'bg-emerald-100 text-emerald-700'],
                    ];
                    $statusInfo = $statusMap[strtolower((string) $order->status)] ?? ['label' => ucfirst((string) $order->status), 'class' => 'bg-slate-100 text-slate-600'];
                @endphp
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1" />
                            </svg>
                            <span class="font-semibold text-slate-800 text-sm truncate">{{ $order->company?->name ?? 'Toko' }}</span>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                    </div>
                    <div class="px-5 py-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs text-slate-400">{{ $order->invoice_no }}</p>
                            <p class="font-bold text-slate-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                        </div>
                        <a href="{{ route('frontend.checkout.waiting', ['orderId' => $order->order_id]) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shrink-0">
                            Lihat Pembayaran
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center text-sm text-slate-500">
                    Pesanan tidak ditemukan.
                </div>
            @endforelse
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('frontend.profil') }}?tab=pesanan"
                class="flex-1 border-2 border-blue-400 text-blue-600 font-semibold py-3 rounded-xl hover:bg-blue-50 transition-colors text-sm text-center">
                Lihat Semua Pesanan Saya
            </a>
            <a href="{{ route('frontend.index') }}"
                class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold py-3 rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all text-sm text-center">
                Belanja Lagi
            </a>
        </div>
    </div>
@endsection
