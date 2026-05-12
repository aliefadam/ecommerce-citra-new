@extends('layouts.app')

@section('title', 'Detail Transaction')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('transactions.index') }}" class="text-sm text-blue-600 font-semibold hover:underline">← Kembali ke transaksi</a>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white mt-2">{{ $transaction->invoice_no }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $transaction->order_id }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoice.show', $transaction) }}" target="_blank" class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">Print Invoice</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-bold text-slate-800 dark:text-white mb-4">Produk</h2>
                    <div class="space-y-3">
                        @foreach ($transaction->details as $detail)
                            <div class="flex items-start gap-3">
                                <img src="{{ $detail->image ? ((str_starts_with($detail->image, 'http://') || str_starts_with($detail->image, 'https://') || str_starts_with($detail->image, '//') || str_starts_with($detail->image, 'data:')) ? $detail->image : asset('storage/' . ltrim(str_starts_with($detail->image, 'storage/') ? \Illuminate\Support\Str::after($detail->image, 'storage/') : $detail->image, '/'))) : 'https://via.placeholder.com/80x80?text=No+Image' }}"
                                    class="w-14 h-14 rounded-xl object-cover border border-slate-100" alt="{{ $detail->product_name }}">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $detail->variant_name ?: '-' }}</p>
                                    @if ($detail->item_note)
                                        <p class="text-xs text-slate-400 mt-1">Catatan: {{ $detail->item_note }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-slate-500">{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</p>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-bold text-slate-800 dark:text-white mb-4">Riwayat Status</h2>
                    <div class="space-y-3">
                        @forelse ($transaction->statusHistories as $history)
                            <div class="flex gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5"></div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $history->to_status }}</p>
                                    <p class="text-xs text-slate-500">{{ $history->created_at->format('d M Y H:i') }} oleh {{ $history->user?->name ?? 'System' }}</p>
                                    @if ($history->note)
                                        <p class="text-xs text-slate-400 mt-1">{{ $history->note }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada riwayat status.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-bold text-slate-800 dark:text-white mb-4">Informasi</h2>
                    <div class="space-y-3 text-sm">
                        <div><p class="text-xs text-slate-400">Customer</p><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->user?->name ?? '-' }}</p></div>
                        <div><p class="text-xs text-slate-400">Status</p><p class="font-semibold text-blue-600">{{ $transaction->status }}</p></div>
                        <div><p class="text-xs text-slate-400">Pembayaran</p><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->payment_method ?: '-' }}</p></div>
                        <div><p class="text-xs text-slate-400">Alamat</p><p class="text-slate-600 dark:text-slate-300">{{ $transaction->shipping_recipient_name }}<br>{{ $transaction->shipping_phone }}<br>{{ $transaction->shipping_address_line }}, {{ $transaction->shipping_city }}</p></div>
                        <div><p class="text-xs text-slate-400">Pengiriman</p><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->shipping_label ?: '-' }} / {{ $transaction->tracking_number ?: 'Belum ada resi' }}</p>@if($transaction->shipping_note)<p class="text-xs text-slate-500 mt-1">{{ $transaction->shipping_note }}</p>@endif</div>
                    </div>
                </div>

                @if ($transaction->payment_type === 'manual_transfer')
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <h2 class="font-bold text-slate-800 dark:text-white mb-4">Verifikasi Pembayaran</h2>
                        @if ($transaction->payment_proof_path)
                            <a href="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" target="_blank" class="block mb-3 rounded-xl overflow-hidden border border-slate-200">
                                <img src="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" class="w-full max-h-56 object-cover" alt="Bukti transfer">
                            </a>
                        @else
                            <p class="text-sm text-slate-400 mb-3">Customer belum upload bukti transfer.</p>
                        @endif
                        <form method="POST" action="{{ route('transactions.verify-payment', $transaction) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <textarea name="payment_admin_note" rows="3" placeholder="Catatan admin" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-3 text-sm dark:text-slate-200">{{ $transaction->payment_admin_note }}</textarea>
                            <div class="grid grid-cols-2 gap-2">
                                <button name="action" value="reject" class="rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Tolak</button>
                                <button name="action" value="approve" class="rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Setujui</button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-bold text-slate-800 dark:text-white mb-4">Total</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span>Ongkir</span><span>Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</span></div>
                        @if ($transaction->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600"><span>Voucher {{ $transaction->coupon_code }}</span><span>- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold text-blue-600"><span>Grand Total</span><span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
@endsection
