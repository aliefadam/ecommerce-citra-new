@extends('layouts.user')

@section('title', 'Lacak Pesanan - ' . ($appStoreName ?? config('app.name')))
@section('body_class', 'bg-slate-50 text-slate-800 overflow-x-hidden')

@section('style')
    <style>
        .tracking-grid {
            background-image: linear-gradient(rgba(148, 163, 184, .08) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .tracking-reveal {
            animation: trackingReveal .45s ease-out both;
        }

        @keyframes trackingReveal {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection

@section('content')
    @include('partials.navbar-user')

    @php
        $statusKey = strtolower((string) ($transaction?->status ?? ''));
        $statusInfo = match ($statusKey) {
            'paid', 'settlement', 'capture' => ['label' => 'Pembayaran diterima', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
            'process', 'diproses' => ['label' => 'Sedang diproses', 'class' => 'border-blue-200 bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500'],
            'kirim', 'dikirim' => ['label' => 'Sedang dikirim', 'class' => 'border-violet-200 bg-violet-50 text-violet-700', 'dot' => 'bg-violet-500'],
            'selesai', 'completed' => ['label' => 'Pesanan selesai', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
            'cancel', 'dibatalkan', 'expire', 'deny', 'failure' => ['label' => 'Pesanan dibatalkan', 'class' => 'border-red-200 bg-red-50 text-red-700', 'dot' => 'bg-red-500'],
            'menunggu_verifikasi' => ['label' => 'Menunggu verifikasi', 'class' => 'border-amber-200 bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
            default => ['label' => 'Menunggu pembayaran', 'class' => 'border-amber-200 bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
        };
    @endphp

    <main class="tracking-grid relative min-h-[72vh] overflow-hidden px-4 py-10 sm:px-6 sm:py-14">
        <div class="pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-blue-100/70 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-amber-100/60 blur-3xl"></div>

        <div class="relative mx-auto max-w-5xl">
            <div class="mx-auto mb-8 max-w-2xl text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    Status pesanan real-time
                </span>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Lacak perjalanan pesananmu</h1>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">Masukkan email pemesan dan nomor order yang tercantum pada email konfirmasi.</p>
            </div>

            <section class="mx-auto max-w-2xl rounded-3xl border border-white/80 bg-white/95 p-5 shadow-xl shadow-slate-200/60 backdrop-blur sm:p-7">
                <form method="POST" action="{{ route('frontend.order-tracking.verify') }}" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    @csrf
                    <div>
                        <label for="trackingEmail" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Email pemesan</label>
                        <input id="trackingEmail" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                            placeholder="nama@email.com"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="trackingOrderId" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Nomor order</label>
                        <input id="trackingOrderId" name="order_id" type="text" value="{{ old('order_id', $orderId) }}" required autocomplete="off"
                            placeholder="ORD-... atau MAN-..."
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm uppercase outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <button class="h-[46px] rounded-xl bg-slate-900 px-6 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:-translate-y-0.5 hover:bg-blue-700">
                        Lacak
                    </button>
                </form>

                @if ($errors->has('tracking'))
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first('tracking') }}</div>
                @elseif ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif
            </section>

            @if ($transaction)
                <div class="tracking-reveal mt-8 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Order terverifikasi</p>
                                    <h2 class="mt-1 font-mono text-lg font-bold text-slate-900">{{ $transaction->order_id }}</h2>
                                    <p class="mt-1 text-xs text-slate-500">Dibuat {{ $transaction->created_at?->translatedFormat('d M Y, H:i') }}</p>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $statusInfo['class'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $statusInfo['dot'] }}"></span>
                                    {{ $statusInfo['label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100 px-6 sm:px-7">
                            @foreach ($transaction->details as $item)
                                <div class="flex items-center gap-4 py-5">
                                    <img src="{{ $item->image ?: 'https://via.placeholder.com/80x80?text=No+Image' }}" alt="{{ $item->product_name }}"
                                        class="h-14 w-14 rounded-2xl border border-slate-100 object-cover">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-slate-800">{{ $item->product_name }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $item->variant_name ?: 'Tanpa varian' }} · {{ $item->quantity }} item</p>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Rp {{ number_format((int) $item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <div class="space-y-6">
                        <section class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-xl shadow-slate-300/40">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Ringkasan pembayaran</p>
                            <div class="mt-5 space-y-3 text-sm">
                                <div class="flex justify-between text-slate-300"><span>Metode</span><span class="font-semibold text-white">{{ $transaction->payment_method ?: strtoupper((string) $transaction->payment_type) }}</span></div>
                                <div class="flex justify-between text-slate-300"><span>Subtotal</span><span>Rp {{ number_format((int) $transaction->subtotal_amount, 0, ',', '.') }}</span></div>
                                <div class="flex justify-between text-slate-300"><span>Ongkir</span><span>Rp {{ number_format((int) $transaction->shipping_cost, 0, ',', '.') }}</span></div>
                                @if ((int) $transaction->discount_amount > 0)
                                    <div class="flex justify-between text-emerald-300"><span>Diskon</span><span>- Rp {{ number_format((int) $transaction->discount_amount, 0, ',', '.') }}</span></div>
                                @endif
                            </div>
                            <div class="mt-5 flex items-end justify-between border-t border-slate-700 pt-5">
                                <span class="text-sm text-slate-300">Total</span>
                                <span class="text-2xl font-extrabold">Rp {{ number_format((int) $transaction->grand_total, 0, ',', '.') }}</span>
                            </div>
                        </section>

                        @if ((string) $transaction->payment_type === 'manual_transfer' && in_array($statusKey, ['pending', 'menunggu_verifikasi'], true))
                            <section class="rounded-3xl border border-blue-200 bg-blue-50 p-6">
                                @if ($transaction->payment_proof_path)
                                    <p class="text-sm font-bold text-blue-900">Bukti pembayaran sudah diterima</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-700">Admin sedang memverifikasi bukti transfer kamu.</p>
                                    <a href="{{ $transaction->paymentProofUrl() }}" target="_blank" class="mt-3 inline-flex text-xs font-bold text-blue-700 underline">Lihat bukti transfer</a>
                                @else
                                    <p class="text-sm font-bold text-blue-900">Upload bukti transfer</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-700">Format JPG, PNG, atau WebP dengan ukuran maksimal 4 MB.</p>
                                    <form method="POST" action="{{ route('manual-payment.proof', $transaction) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                                        @csrf
                                        <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" required
                                            class="w-full rounded-xl border border-blue-200 bg-white px-3 py-2.5 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-100 file:px-3 file:py-1.5 file:font-bold file:text-blue-700">
                                        <button class="w-full rounded-xl bg-blue-700 py-3 text-sm font-bold text-white transition hover:bg-blue-800">Kirim Bukti Pembayaran</button>
                                    </form>
                                @endif
                            </section>
                        @endif

                        @guest
                            <section class="relative overflow-hidden rounded-3xl border border-sky-200 bg-sky-50 p-6">
                                <div class="absolute -right-10 -top-12 h-32 w-32 rounded-full bg-blue-200/60 blur-2xl"></div>
                                <div class="relative">
                                    <p class="text-xs font-extrabold uppercase tracking-widest text-blue-600">Satu langkah lagi</p>
                                    <h3 class="mt-2 text-lg font-extrabold text-slate-900">Simpan pesanan ke akunmu</h3>
                                    <p class="mt-2 text-xs leading-5 text-slate-600">Buat akun dengan email pemesan agar status dan riwayat order ini bisa diakses setelah login.</p>
                                    <a href="{{ route('register', ['checkout_order' => $transaction->order_id]) }}"
                                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-700">
                                        Buat Akun Saya
                                    </a>
                                </div>
                            </section>
                        @endguest
                    </div>
                </div>

                @if ($shipmentTracking)
                    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-widest text-blue-600">Tracking resi</p>
                                <h3 class="mt-1 text-lg font-extrabold text-slate-900">
                                    {{ $shipmentTracking['courier_name'] ?: 'Ekspedisi' }}
                                    @if (!empty($shipmentTracking['service']))
                                        <span class="text-slate-400">{{ $shipmentTracking['service'] }}</span>
                                    @endif
                                </h3>
                                <p class="mt-1 font-mono text-sm text-slate-500">{{ $shipmentTracking['awb'] }}</p>
                            </div>
                            @if (!empty($shipmentTracking['status']))
                                <span class="inline-flex w-fit items-center gap-2 rounded-full border {{ !empty($shipmentTracking['delivered']) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-blue-200 bg-blue-50 text-blue-700' }} px-3 py-1.5 text-xs font-bold">
                                    <span class="h-2 w-2 rounded-full {{ !empty($shipmentTracking['delivered']) ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                    {{ $shipmentTracking['status'] }}
                                </span>
                            @endif
                        </div>

                        @if (empty($shipmentTracking['available']))
                            <div class="px-6 py-5 text-sm leading-6 text-amber-700 sm:px-7">
                                {{ $shipmentTracking['message'] }}
                            </div>
                        @else
                            @if (!empty($shipmentTracking['origin']) || !empty($shipmentTracking['destination']))
                                <div class="grid gap-3 border-b border-slate-100 bg-slate-50/70 px-6 py-4 text-xs sm:grid-cols-2 sm:px-7">
                                    <div><span class="font-bold text-slate-400">Dari</span><p class="mt-1 font-semibold text-slate-700">{{ $shipmentTracking['origin'] ?: '-' }}</p></div>
                                    <div><span class="font-bold text-slate-400">Tujuan</span><p class="mt-1 font-semibold text-slate-700">{{ $shipmentTracking['destination'] ?: '-' }}</p></div>
                                </div>
                            @endif

                            @if (!empty($shipmentTracking['events']))
                                <div class="px-6 py-6 sm:px-7">
                                    <div class="space-y-0">
                                        @foreach ($shipmentTracking['events'] as $event)
                                            <div class="relative flex gap-4 pb-6 last:pb-0">
                                                @unless ($loop->last)
                                                    <span class="absolute left-[7px] top-4 h-full w-px bg-slate-200"></span>
                                                @endunless
                                                <span class="relative mt-1.5 h-4 w-4 shrink-0 rounded-full border-4 {{ $loop->first ? 'border-blue-100 bg-blue-600' : 'border-slate-100 bg-slate-400' }}"></span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold leading-6 text-slate-800">{{ $event['description'] }}</p>
                                                    <p class="mt-1 text-xs text-slate-400">
                                                        {{ trim($event['date'].' '.$event['time']) ?: 'Waktu belum tersedia' }}
                                                        @if (!empty($event['city']))
                                                            <span class="mx-1">&middot;</span>{{ $event['city'] }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="px-6 py-5 text-sm text-slate-500 sm:px-7">Belum ada riwayat perjalanan dari ekspedisi.</div>
                            @endif

                            @if (!empty($shipmentTracking['delivered']) && !empty($shipmentTracking['pod_receiver']))
                                <div class="border-t border-emerald-100 bg-emerald-50 px-6 py-4 text-sm text-emerald-800 sm:px-7">
                                    Paket diterima oleh <span class="font-bold">{{ $shipmentTracking['pod_receiver'] }}</span>
                                    {{ trim(($shipmentTracking['pod_date'] ?? '').' '.($shipmentTracking['pod_time'] ?? '')) }}.
                                </div>
                            @endif
                        @endif
                    </section>
                @endif
            @endif
        </div>
    </main>
@endsection
