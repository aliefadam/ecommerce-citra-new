@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">

    {{-- ── Header ────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('transactions.index') }}"
               class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali ke transaksi
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $transaction->invoice_no }}</h1>
                @php
                    $statusChip = match(strtolower((string) $transaction->status)) {
                        'pending'                => ['bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',  'clock'],
                        'menunggu_verifikasi'    => ['bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', 'hourglass'],
                        'paid','settlement','capture' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'check-circle'],
                        'process','processing'   => ['bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',    'package'],
                        'kirim','shipped'        => ['bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400', 'truck'],
                        'selesai','completed'    => ['bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',   'check-circle-2'],
                        'batal','cancel','cancelled','dibatalkan' => ['bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'x-circle'],
                        default                  => ['bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',  'circle'],
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $statusChip[0] }}">
                    <i data-lucide="{{ $statusChip[1] }}" class="h-3.5 w-3.5"></i>
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                {{ $transaction->order_id }}
                &bull; Dibuat {{ $transaction->created_at->format('d M Y, H:i') }}
                @if ($transaction->normalizedSource() === 'manual')
                    &bull; <span class="text-violet-600 dark:text-violet-400 font-medium">Manual Admin</span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('transactions.shipping-label', $transaction) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-600 px-3.5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                <i data-lucide="printer" class="h-4 w-4"></i> Print Resi
            </a>
            <a href="{{ route('invoice.show', $transaction) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 dark:border-blue-700 px-3.5 py-2 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                <i data-lucide="file-text" class="h-4 w-4"></i> Print Invoice
            </a>
        </div>
    </div>

    {{-- ── Alerts ─────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
            <i data-lucide="check-circle-2" class="h-4 w-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-400">
            <i data-lucide="alert-circle" class="h-4 w-4 shrink-0"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Status Progress ─────────────────────────────────────────────── --}}
    @php
        $statusOrder = ['pending', 'paid', 'process', 'kirim', 'selesai'];
        $statusLabels = ['Pending', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai'];
        $statusIcons  = ['clock', 'credit-card', 'package', 'truck', 'check-circle-2'];
        $normalStatus = strtolower((string) $transaction->status);
        $isCancelled  = in_array($normalStatus, ['batal','cancel','cancelled','dibatalkan'], true);

        $activeIndex = match(true) {
            in_array($normalStatus, ['pending','menunggu_verifikasi'], true) => 0,
            in_array($normalStatus, ['paid','settlement','capture'], true)   => 1,
            in_array($normalStatus, ['process','processing'], true)          => 2,
            in_array($normalStatus, ['kirim','shipped'], true)               => 3,
            in_array($normalStatus, ['selesai','completed'], true)           => 4,
            default => 0,
        };
    @endphp

    <div class="mb-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
        @if ($isCancelled)
            <div class="flex items-center gap-2 text-sm font-semibold text-red-600 dark:text-red-400">
                <i data-lucide="x-circle" class="h-5 w-5"></i>
                Transaksi ini telah dibatalkan
            </div>
        @else
            <div class="relative flex items-start justify-between">
                {{-- connecting line --}}
                <div class="absolute left-0 right-0 top-4 mx-[2.5rem] h-0.5 bg-slate-100 dark:bg-slate-700 -z-0"></div>
                <div class="absolute left-0 top-4 mx-[2.5rem] h-0.5 bg-blue-500 -z-0 transition-all"
                     style="width: calc({{ $activeIndex }} / 4 * (100% - 5rem))"></div>

                @foreach ($statusOrder as $i => $st)
                    @php $done = $i < $activeIndex; $active = $i === $activeIndex; @endphp
                    <div class="relative z-10 flex flex-1 flex-col items-center gap-1.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full
                            {{ $done   ? 'bg-blue-600 text-white'
                            : ($active ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900'
                                       : 'bg-slate-100 dark:bg-slate-700 text-slate-400') }}">
                            <i data-lucide="{{ $statusIcons[$i] }}" class="h-4 w-4"></i>
                        </div>
                        <span class="text-center text-xs font-semibold
                            {{ $done || $active ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}">
                            {{ $statusLabels[$i] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Main Grid ───────────────────────────────────────────────────── --}}
    <div class="grid gap-6 xl:grid-cols-3">

        {{-- LEFT: produk + timeline ──────────────────────────────────── --}}
        <section class="xl:col-span-2 space-y-6">

            {{-- Produk --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Produk</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $transaction->details->count() }} item</p>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach ($transaction->details as $detail)
                        <div class="flex items-start gap-4 p-4 sm:p-5">
                            <img src="{{ $detail->image ? ((str_starts_with($detail->image, 'http://') || str_starts_with($detail->image, 'https://') || str_starts_with($detail->image, '//') || str_starts_with($detail->image, 'data:')) ? $detail->image : asset('storage/' . ltrim(str_starts_with($detail->image, 'storage/') ? \Illuminate\Support\Str::after($detail->image, 'storage/') : $detail->image, '/'))) : 'https://placehold.co/64x64?text=No+Img' }}"
                                 class="h-16 w-16 shrink-0 rounded-xl object-cover border border-slate-100 dark:border-slate-700"
                                 alt="{{ $detail->product_name }}">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 dark:text-slate-200 leading-snug">{{ $detail->product_name }}</p>
                                @if ($detail->variant_name)
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $detail->variant_name }}</p>
                                @endif
                                @if ($detail->sku)
                                    <p class="mt-0.5 text-xs font-mono text-slate-400">SKU: {{ $detail->sku }}</p>
                                @endif
                                @if ($detail->item_note)
                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Catatan: {{ $detail->item_note }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $detail->quantity }} × Rp {{ number_format($detail->price, 0, ',', '.') }}
                                </p>
                                @if (($detail->discount_amount ?? 0) > 0)
                                    <p class="text-xs text-emerald-600">− Rp {{ number_format($detail->discount_amount, 0, ',', '.') }}</p>
                                @endif
                                <p class="mt-0.5 font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- Totals inside product card --}}
                <div class="border-t border-slate-100 dark:border-slate-700 px-5 py-4 space-y-1.5 bg-slate-50 dark:bg-slate-700/30">
                    <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
                        <span>Subtotal Produk</span>
                        <span>Rp {{ number_format($transaction->subtotal_amount, 0, ',', '.') }}</span>
                    </div>
                    @if ($transaction->discount_amount > 0)
                        <div class="flex justify-between text-sm text-emerald-600">
                            <span>Diskon{{ $transaction->coupon_code ? ' (' . $transaction->coupon_code . ')' : '' }}</span>
                            <span>− Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ((float) $transaction->tax_rate > 0)
                        <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
                            <span>{{ $transaction->tax_name ?: 'PPN' }} {{ number_format((float) $transaction->tax_rate, 2, ',', '.') }}%</span>
                            <span>Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
                        <span>Ongkir</span>
                        <span>Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 dark:border-slate-600 pt-2 text-base font-bold text-blue-600">
                        <span>Grand Total</span>
                        <span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Riwayat Status (timeline) --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Riwayat Status</h2>
                </div>
                <div class="p-5">
                    @forelse ($transaction->statusHistories->sortByDesc('created_at') as $history)
                        <div class="relative flex gap-4 pb-5 last:pb-0">
                            {{-- connector line --}}
                            @if (!$loop->last)
                                <div class="absolute left-[11px] top-6 bottom-0 w-0.5 bg-slate-100 dark:bg-slate-700"></div>
                            @endif
                            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
                                <div class="h-2.5 w-2.5 rounded-full bg-blue-500"></div>
                            </div>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <div class="flex flex-wrap items-baseline gap-2">
                                    <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 capitalize">
                                        {{ $history->to_status }}
                                    </p>
                                    <span class="text-xs text-slate-400">
                                        {{ $history->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    oleh {{ $history->user?->name ?? 'System' }}
                                </p>
                                @if ($history->note)
                                    <p class="mt-1 rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $history->note }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada riwayat status.</p>
                    @endforelse
                </div>
            </div>

        </section>

        {{-- RIGHT: sidebar info ─────────────────────────────────────── --}}
        <aside class="space-y-5 xl:sticky xl:top-24 xl:h-fit">

            {{-- Info Customer --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Customer</h3>
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                        <i data-lucide="user" class="h-4 w-4 text-blue-600 dark:text-blue-400"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 dark:text-slate-200 truncate">
                            {{ $transaction->customerDisplayName() }}
                        </p>
                        @if ($transaction->user?->email || $transaction->manual_customer_email)
                            <p class="text-xs text-slate-400 truncate mt-0.5">
                                {{ $transaction->user?->email ?? $transaction->manual_customer_email }}
                            </p>
                        @endif
                        @if ($transaction->manual_customer_phone || $transaction->user?->phone_number)
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $transaction->manual_customer_phone ?: ($transaction->user?->phone_number ?? '-') }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($transaction->normalizedSource() === 'manual')
                    <div class="mt-3 border-t border-slate-100 dark:border-slate-700 pt-3 flex items-center gap-2 text-xs text-slate-500">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5 text-violet-500"></i>
                        Dibuat oleh <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $transaction->createdByAdmin?->name ?? 'Admin' }}</span>
                    </div>
                @endif
            </div>

            {{-- Info Pembayaran --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Pembayaran</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-slate-500 dark:text-slate-400">Metode</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $transaction->payment_method ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-slate-500 dark:text-slate-400">Status</span>
                        @php
                            $pStatusColor = match($transaction->payment_status) {
                                'paid'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'partial' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                default   => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                            };
                        @endphp
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $pStatusColor }}">
                            {{ $transaction->paymentStatusLabel() }}
                        </span>
                    </div>
                    @if ($transaction->payment_amount)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">Nominal</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($transaction->payment_paid_at)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">Tanggal</span>
                            <span class="text-slate-600 dark:text-slate-300">{{ $transaction->payment_paid_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info Pengiriman --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Pengiriman</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="shrink-0 text-slate-500 dark:text-slate-400">Jenis</span>
                        <span class="font-semibold text-right text-slate-700 dark:text-slate-200">{{ $transaction->shippingTypeLabel() }}</span>
                    </div>
                    @if ($transaction->shipping_label)
                        <div class="flex items-start justify-between gap-3">
                            <span class="shrink-0 text-slate-500 dark:text-slate-400">Kurir</span>
                            <span class="font-semibold text-right text-slate-700 dark:text-slate-200">{{ $transaction->shipping_label }}</span>
                        </div>
                    @endif
                    @if ($transaction->tracking_number)
                        <div class="flex items-start justify-between gap-3">
                            <span class="shrink-0 text-slate-500 dark:text-slate-400">Resi</span>
                            <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">{{ $transaction->tracking_number }}</span>
                        </div>
                    @endif
                    @if ($transaction->shipping_recipient_name)
                        <div class="mt-3 rounded-xl bg-slate-50 dark:bg-slate-700/40 p-3 text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $transaction->shipping_recipient_name }}</p>
                            @if ($transaction->shipping_phone)<p>{{ $transaction->shipping_phone }}</p>@endif
                            @if ($transaction->shipping_address_line)
                                <p class="mt-1">{{ $transaction->shipping_address_line }}{{ $transaction->shipping_district ? ', ' . $transaction->shipping_district : '' }}{{ $transaction->shipping_city ? ', ' . $transaction->shipping_city : '' }}{{ $transaction->shipping_province ? ', ' . $transaction->shipping_province : '' }}{{ $transaction->shipping_postal_code ? ' ' . $transaction->shipping_postal_code : '' }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-slate-400 mt-1">Alamat belum diisi.</p>
                    @endif
                    @if ($transaction->shipping_note)
                        <p class="text-xs text-slate-400 italic">{{ $transaction->shipping_note }}</p>
                    @endif
                </div>
            </div>

            {{-- Bukti Transfer --}}
            @if ($transaction->payment_type === 'manual_transfer')
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Bukti Transfer</h3>
                    @if ($transaction->payment_proof_path)
                        <a href="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" target="_blank"
                           class="block mb-3 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-600 hover:opacity-90 transition-opacity">
                            <img src="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}"
                                 class="w-full max-h-48 object-cover" alt="Bukti transfer">
                        </a>
                    @else
                        <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-600 py-6 text-center">
                            <i data-lucide="image-off" class="h-8 w-8 text-slate-300 dark:text-slate-600 mb-2"></i>
                            <p class="text-xs text-slate-400">Belum ada bukti transfer</p>
                        </div>
                    @endif
                    @if ($transaction->payment_admin_note)
                        <p class="mt-2 rounded-lg bg-slate-50 dark:bg-slate-700/40 px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $transaction->payment_admin_note }}
                        </p>
                    @endif
                </div>
            @endif

        </aside>
    </div>

    {{-- ── Action Section ──────────────────────────────────────────────── --}}
    @php
        $canProcess = in_array(strtolower((string) $transaction->status), ['paid','settlement','capture'], true);
        $canShip    = in_array(strtolower((string) $transaction->status), ['process','processing'], true);
        $canVerify  = $transaction->payment_type === 'manual_transfer'
                   && in_array(strtolower((string) $transaction->status), ['menunggu_verifikasi','pending'], true);
        $isManual   = $transaction->normalizedSource() === 'manual';
    @endphp

    @if ($canProcess || $canShip || $canVerify || $isManual)
        <div class="mt-6">
            <div class="mb-4 flex items-center gap-2">
                <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200">Aksi &amp; Update</h2>
                <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">

                {{-- Proses pesanan --}}
                @if ($canProcess)
                    <div class="rounded-2xl border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/50">
                                <i data-lucide="package" class="h-5 w-5 text-blue-600 dark:text-blue-400"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">Proses Pesanan</p>
                                <p class="text-xs text-slate-400">Tandai pesanan sedang disiapkan</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('transactions.process', $transaction) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                onclick="return confirm('Tandai pesanan ini sebagai Diproses?')"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                <i data-lucide="check" class="h-4 w-4"></i> Tandai Diproses
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Kirim pesanan --}}
                @if ($canShip)
                    <div class="rounded-2xl border border-violet-200 dark:border-violet-700 bg-violet-50 dark:bg-violet-900/20 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/50">
                                <i data-lucide="truck" class="h-5 w-5 text-violet-600 dark:text-violet-400"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">Kirim Pesanan</p>
                                <p class="text-xs text-slate-400">Input nomor resi pengiriman</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('transactions.ship', $transaction) }}"
                              id="shipForm" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Nomor Resi <span class="text-red-500">*</span></label>
                                <input name="tracking_number" type="text" required
                                       value="{{ old('tracking_number', $transaction->tracking_number) }}"
                                       placeholder="Contoh: JNE001234567"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Label Kurir</label>
                                <input name="shipping_label" type="text"
                                       value="{{ old('shipping_label', $transaction->shipping_label) }}"
                                       placeholder="JNE REG"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <button type="submit"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                                <i data-lucide="send" class="h-4 w-4"></i> Tandai Dikirim
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Verifikasi pembayaran --}}
                @if ($canVerify)
                    <div class="rounded-2xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/50">
                                <i data-lucide="shield-check" class="h-5 w-5 text-amber-600 dark:text-amber-400"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">Verifikasi Pembayaran</p>
                                <p class="text-xs text-slate-400">Setujui atau tolak bukti transfer</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('transactions.verify-payment', $transaction) }}" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Catatan Admin</label>
                                <textarea name="payment_admin_note" rows="2"
                                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                                          placeholder="Opsional">{{ old('payment_admin_note', $transaction->payment_admin_note) }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" name="action" value="approve"
                                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3 text-sm font-semibold text-white hover:bg-emerald-700">
                                    <i data-lucide="check" class="h-4 w-4"></i> Setujui
                                </button>
                                <button type="submit" name="action" value="reject"
                                    onclick="return confirm('Tolak bukti transfer ini?')"
                                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-red-600 px-3 text-sm font-semibold text-white hover:bg-red-700">
                                    <i data-lucide="x" class="h-4 w-4"></i> Tolak
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Manual: Update Pembayaran --}}
                @if ($isManual)
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700">
                                <i data-lucide="credit-card" class="h-5 w-5 text-slate-500 dark:text-slate-400"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">Update Pembayaran</p>
                                <p class="text-xs text-slate-400">Perbarui status & bukti pembayaran</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('transactions.manual-payment.update', $transaction) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf @method('PATCH')
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
                                    <select name="payment_status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                        @foreach (['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $v => $l)
                                            <option value="{{ $v }}" @selected(($transaction->payment_status ?: 'unpaid') === $v)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nominal</label>
                                    <input name="payment_amount" type="number" min="0" step="1"
                                           value="{{ old('payment_amount', $transaction->payment_amount ?: $transaction->grand_total) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Metode</label>
                                    <input name="payment_method" type="text" value="{{ old('payment_method', $transaction->payment_method) }}"
                                           placeholder="Transfer, Cash, QRIS"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Tanggal Bayar</label>
                                    <input name="payment_paid_at" type="datetime-local"
                                           value="{{ old('payment_paid_at', $transaction->payment_paid_at ? $transaction->payment_paid_at->format('Y-m-d\TH:i') : '') }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Bukti Pembayaran</label>
                                <input name="payment_proof" type="file" accept="image/*,.pdf"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:file:bg-slate-600 dark:file:text-slate-200">
                                @if ($transaction->payment_proof_path)
                                    <a href="{{ asset(ltrim($transaction->payment_proof_path, '/')) }}" target="_blank" class="mt-1 inline-block text-xs font-semibold text-blue-600 hover:underline">Lihat bukti tersimpan</a>
                                @endif
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Catatan Admin</label>
                                <textarea name="payment_admin_note" rows="2"
                                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                                          placeholder="Catatan pembayaran">{{ old('payment_admin_note', $transaction->payment_admin_note) }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                <i data-lucide="save" class="h-4 w-4"></i> Simpan Pembayaran
                            </button>
                        </form>
                    </div>

                    {{-- Manual: Update Pengiriman --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700">
                                <i data-lucide="map-pin" class="h-5 w-5 text-slate-500 dark:text-slate-400"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">Update Pengiriman</p>
                                <p class="text-xs text-slate-400">Isi alamat dan detail pengiriman</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('transactions.manual-shipping.update', $transaction) }}" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Jenis Pengiriman</label>
                                <select name="shipping_type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    @foreach (['belum_ditentukan' => 'Belum ditentukan','dikirim' => 'Dikirim','ambil_sendiri' => 'Ambil sendiri','kurir_toko' => 'Kurir toko','ekspedisi_manual' => 'Ekspedisi manual','gratis_ongkir' => 'Gratis ongkir'] as $v => $l)
                                        <option value="{{ $v }}" @selected(($transaction->shipping_type ?: 'belum_ditentukan') === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Penerima</label>
                                    <input name="shipping_recipient_name" type="text" value="{{ old('shipping_recipient_name', $transaction->shipping_recipient_name) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nomor HP</label>
                                    <input name="shipping_phone" type="text" value="{{ old('shipping_phone', $transaction->shipping_phone) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Alamat Lengkap</label>
                                <textarea name="shipping_address_line" rows="2"
                                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('shipping_address_line', $transaction->shipping_address_line) }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Provinsi</label>
                                    <input name="shipping_province" type="text" value="{{ old('shipping_province', $transaction->shipping_province) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kota/Kabupaten</label>
                                    <input name="shipping_city" type="text" value="{{ old('shipping_city', $transaction->shipping_city) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kecamatan</label>
                                    <input name="shipping_district" type="text" value="{{ old('shipping_district', $transaction->shipping_district) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kode Pos</label>
                                    <input name="shipping_postal_code" type="text" value="{{ old('shipping_postal_code', $transaction->shipping_postal_code) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Kurir/Ekspedisi</label>
                                    <input name="shipping_courier_name" type="text" value="{{ old('shipping_courier_name', $transaction->shipping_courier_name) }}" placeholder="JNE, Kurir toko"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Service</label>
                                    <input name="shipping_service" type="text" value="{{ old('shipping_service', $transaction->shipping_service) }}" placeholder="REG, Same day"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Ongkir</label>
                                    <input name="shipping_cost" type="number" min="0" step="1" value="{{ old('shipping_cost', $transaction->shipping_cost) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nomor Resi</label>
                                    <input name="tracking_number" type="text" value="{{ old('tracking_number', $transaction->tracking_number) }}"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Catatan Pengiriman</label>
                                <textarea name="shipping_note" rows="2"
                                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('shipping_note', $transaction->shipping_note) }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                <i data-lucide="save" class="h-4 w-4"></i> Simpan Pengiriman
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    @endif

</main>

@section('script')
<script>
    // ship form uses JSON endpoint, handle reload on success
    const shipForm = document.getElementById('shipForm');
    if (shipForm) {
        shipForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const fd  = new FormData(shipForm);
            const res = await fetch(shipForm.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan.');
            }
        });
    }
</script>
@endsection
@endsection
