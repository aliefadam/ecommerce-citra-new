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
                <a href="{{ route('transactions.shipping-label', $transaction) }}" target="_blank" class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-50">Print Resi</a>
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
                                    @if (($detail->discount_amount ?? 0) > 0)
                                        <p class="text-xs text-emerald-600">Diskon item: Rp {{ number_format($detail->discount_amount, 0, ',', '.') }}</p>
                                    @endif
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
                        <div>
                            <p class="text-xs text-slate-400">Customer</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->customerDisplayName() }}</p>
                            @if ($transaction->manual_customer_phone || $transaction->manual_customer_email)
                                <p class="text-xs text-slate-500 mt-1">{{ $transaction->manual_customer_phone ?: '-' }}{{ $transaction->manual_customer_email ? ' / ' . $transaction->manual_customer_email : '' }}</p>
                            @endif
                        </div>
                        <div><p class="text-xs text-slate-400">Source</p><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->source_label }}</p></div>
                        @if ($transaction->normalizedSource() === 'manual')
                            <div><p class="text-xs text-slate-400">Dibuat oleh</p><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->createdByAdmin?->name ?? 'Admin tidak tersedia' }}</p></div>
                        @endif
                        <div><p class="text-xs text-slate-400">Status</p><p class="font-semibold text-blue-600">{{ $transaction->status }}</p></div>
                        <div>
                            <p class="text-xs text-slate-400">Pembayaran</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->payment_method ?: '-' }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $transaction->paymentStatusLabel() }}{{ $transaction->payment_amount ? ' / Rp ' . number_format($transaction->payment_amount, 0, ',', '.') : '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Alamat</p>
                            <p class="text-slate-600 dark:text-slate-300">{{ $transaction->shipping_recipient_name ?: '-' }}<br>{{ $transaction->shipping_phone ?: '-' }}<br>{{ $transaction->shipping_address_line ?: '-' }}{{ $transaction->shipping_district ? ', ' . $transaction->shipping_district : '' }}{{ $transaction->shipping_city ? ', ' . $transaction->shipping_city : '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Pengiriman</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->shippingTypeLabel() }} / {{ $transaction->tracking_number ?: 'Belum ada resi' }}</p>
                            @if ($transaction->shipping_label)
                                <p class="text-xs text-slate-500 mt-1">{{ $transaction->shipping_label }}</p>
                            @endif
                            @if($transaction->shipping_note)<p class="text-xs text-slate-500 mt-1">{{ $transaction->shipping_note }}</p>@endif
                        </div>
                    </div>
                </div>

                @if ($transaction->payment_type === 'manual_transfer')
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <h2 class="font-bold text-slate-800 dark:text-white mb-4">Bukti Transfer</h2>
                        @if ($transaction->payment_proof_path)
                            <a href="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" target="_blank" class="block mb-3 rounded-xl overflow-hidden border border-slate-200">
                                <img src="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" class="w-full max-h-56 object-cover" alt="Bukti transfer">
                            </a>
                        @else
                            <p class="text-sm text-slate-400 mb-3">Customer belum upload bukti transfer.</p>
                        @endif
                        @if ($transaction->payment_admin_note)
                            <p class="text-xs text-slate-500 mt-2">Catatan admin: {{ $transaction->payment_admin_note }}</p>
                        @endif
                    </div>
                @endif

                @if ($transaction->normalizedSource() === 'manual')
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <h2 class="font-bold text-slate-800 dark:text-white mb-4">Pembayaran Manual</h2>
                        <form method="POST" action="{{ route('transactions.manual-payment.update', $transaction) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Status Pembayaran</label>
                                <select name="payment_status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    @foreach (['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $value => $label)
                                        <option value="{{ $value }}" @selected(($transaction->payment_status ?: 'unpaid') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Metode Pembayaran</label>
                                <input name="payment_method" type="text" value="{{ old('payment_method', $transaction->payment_method) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200" placeholder="Transfer, Cash, QRIS toko">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Tanggal Bayar</label>
                                    <input name="payment_paid_at" type="datetime-local" value="{{ old('payment_paid_at', $transaction->payment_paid_at ? $transaction->payment_paid_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nominal</label>
                                    <input name="payment_amount" type="number" min="0" step="1" value="{{ old('payment_amount', $transaction->payment_amount ?: $transaction->grand_total) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Bukti Pembayaran</label>
                                <input name="payment_proof" type="file" accept="image/*,.pdf" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:file:bg-slate-600 dark:file:text-slate-200">
                                @if ($transaction->payment_proof_path)
                                    <a href="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" target="_blank" class="mt-1 inline-block text-xs font-semibold text-blue-600 hover:underline">Lihat bukti tersimpan</a>
                                @endif
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Catatan Admin</label>
                                <textarea name="payment_admin_note" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200" placeholder="Catatan pembayaran">{{ old('payment_admin_note', $transaction->payment_admin_note) }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-500/20 hover:bg-blue-700">
                                Simpan Pembayaran
                            </button>
                        </form>
                    </div>

                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <h2 class="font-bold text-slate-800 dark:text-white mb-4">Pengiriman Manual</h2>
                        <form method="POST" action="{{ route('transactions.manual-shipping.update', $transaction) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Jenis Pengiriman</label>
                                <select name="shipping_type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    @foreach ([
                                        'belum_ditentukan' => 'Belum ditentukan',
                                        'dikirim' => 'Dikirim',
                                        'ambil_sendiri' => 'Ambil sendiri',
                                        'kurir_toko' => 'Kurir toko',
                                        'ekspedisi_manual' => 'Ekspedisi manual',
                                        'gratis_ongkir' => 'Gratis ongkir',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(($transaction->shipping_type ?: 'belum_ditentukan') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Penerima</label>
                                    <input name="shipping_recipient_name" type="text" value="{{ old('shipping_recipient_name', $transaction->shipping_recipient_name) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nomor HP</label>
                                    <input name="shipping_phone" type="text" value="{{ old('shipping_phone', $transaction->shipping_phone) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Alamat Lengkap</label>
                                <textarea name="shipping_address_line" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ old('shipping_address_line', $transaction->shipping_address_line) }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Provinsi</label>
                                    <input name="shipping_province" type="text" value="{{ old('shipping_province', $transaction->shipping_province) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kota/Kabupaten</label>
                                    <input name="shipping_city" type="text" value="{{ old('shipping_city', $transaction->shipping_city) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kecamatan</label>
                                    <input name="shipping_district" type="text" value="{{ old('shipping_district', $transaction->shipping_district) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kode Pos</label>
                                    <input name="shipping_postal_code" type="text" value="{{ old('shipping_postal_code', $transaction->shipping_postal_code) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kurir/Ekspedisi</label>
                                    <input name="shipping_courier_name" type="text" value="{{ old('shipping_courier_name', $transaction->shipping_courier_name) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200" placeholder="JNE, Kurir toko">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Service</label>
                                    <input name="shipping_service" type="text" value="{{ old('shipping_service', $transaction->shipping_service) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200" placeholder="REG, Same day">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Ongkir</label>
                                    <input name="shipping_cost" type="number" min="0" step="1" value="{{ old('shipping_cost', $transaction->shipping_cost) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nomor Resi</label>
                                    <input name="tracking_number" type="text" value="{{ old('tracking_number', $transaction->tracking_number) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Catatan Pengiriman</label>
                                <textarea name="shipping_note" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ old('shipping_note', $transaction->shipping_note) }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-500/20 hover:bg-blue-700">
                                Simpan Pengiriman
                            </button>
                        </form>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-bold text-slate-800 dark:text-white mb-4">Total</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal_amount, 0, ',', '.') }}</span></div>
                        @if ($transaction->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600"><span>Voucher {{ $transaction->coupon_code }}</span><span>- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="flex justify-between"><span>Taxable Amount</span><span>Rp {{ number_format($transaction->taxable_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span>{{ $transaction->tax_name ?: 'PPN' }} {{ number_format((float) $transaction->tax_rate, 2, ',', '.') }}%</span><span>Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span>Ongkir</span><span>Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold text-blue-600"><span>Grand Total</span><span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
@endsection
