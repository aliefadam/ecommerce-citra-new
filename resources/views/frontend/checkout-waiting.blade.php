@extends('layouts.user')

@section('title', 'Menunggu Pembayaran - ' . ($appStoreName ?? 'Ecommerce Citra'))
@section('body_class', 'bg-slate-50 text-slate-800 overflow-x-hidden')
@section('style')
    <style>
        .modal-enter {
            animation: modalIn 0.3s ease;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .confetti {
            animation: fall 1s ease-out forwards;
        }

        @keyframes fall {
            from {
                transform: translateY(-20px) rotate(0deg);
                opacity: 1;
            }

            to {
                transform: translateY(80px) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.9);
                opacity: 0.6;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.3;
            }

            100% {
                transform: scale(0.9);
                opacity: 0.6;
            }
        }

        .waiting-pulse {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .timer-glow {
            text-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .step-line::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translateX(-50%);
            width: 2px;
            height: 24px;
            background: linear-gradient(to bottom, #e2e8f0, transparent);
        }
    </style>
@endsection

@section('content')
    @include('partials.navbar-user')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10">

        {{-- Header Card: Status & Timer (full width) --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-5">
            <div class="bg-linear-to-br from-indigo-50 to-slate-50 px-6 py-7 sm:py-8">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="flex items-center gap-5">
                        <div class="relative inline-flex items-center justify-center shrink-0">
                            <div class="absolute w-16 h-16 rounded-full bg-indigo-100 waiting-pulse"></div>
                            <div
                                class="relative w-12 h-12 rounded-full bg-white border-2 border-indigo-200 flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-slate-800">Menunggu Pembayaran</h1>
                            <p class="text-sm text-slate-500">Selesaikan pembayaran sebelum waktu habis</p>
                        </div>
                    </div>
                    <div
                        class="sm:ml-auto flex flex-col items-center bg-white rounded-2xl px-6 py-3 shadow-sm border border-slate-100">
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-widest mb-0.5">Sisa Waktu</p>
                        <span id="countdownTimer"
                            class="text-3xl font-extrabold text-indigo-600 tabular-nums timer-glow">30:00</span>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3.5 flex items-center justify-between flex-wrap gap-3 border-t border-slate-100">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Order ID</p>
                    <p class="text-sm font-mono font-semibold text-slate-700">{{ $payment['order_id'] }}</p>
                </div>
                <div id="txStatusWrap"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 inline-block"></span>
                    <span id="txStatus"
                        class="font-medium ml-1">{{ strtoupper($payment['transaction_status'] ?? 'PENDING') }}</span>
                </div>
            </div>
        </div>

        {{-- Two-column layout: left = payment info, right = order summary + actions --}}
        <div class="flex flex-col lg:flex-row gap-5 items-start">

            {{-- LEFT: Payment Info --}}
            <div class="w-full lg:w-[55%] space-y-5">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 pt-5 pb-2 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Informasi Pembayaran</h2>
                        <button id="openSimulateModalBtn" type="button"
                            class="text-xs px-3 py-1.5 rounded-full border border-indigo-200 text-indigo-600 font-semibold hover:bg-indigo-50 transition-colors">
                            Simulasi
                        </button>
                    </div>
                    <p id="simulateInlineMsg" class="hidden"></p>

                    <div class="px-6 pb-6 space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl">
                            <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Metode Pembayaran</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $payment['method_label'] }}</p>
                            </div>
                        </div>

                        @if (($payment['payment_type'] ?? '') === 'bank_transfer' && !empty($payment['va_number']))
                            <div class="p-4 rounded-2xl border border-indigo-100 bg-linear-to-r from-indigo-50 to-blue-50">
                                <p class="text-xs text-slate-500 mb-2 font-medium">Nomor Virtual Account —
                                    <span class="font-bold text-slate-700 uppercase">{{ $payment['va_bank'] ?? '' }}</span>
                                </p>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <p id="vaNumberText"
                                        class="text-2xl font-extrabold text-indigo-700 tracking-widest font-mono leading-none">
                                        {{ $payment['va_number'] }}</p>
                                    <button id="copyVaBtn" type="button"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                                        Salin
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if (($payment['payment_type'] ?? '') === 'qris')
                            <div class="p-5 rounded-2xl border border-indigo-100 bg-indigo-50 text-center">
                                <p class="text-xs text-slate-500 font-medium mb-3 uppercase tracking-wide">Scan QRIS untuk
                                    Membayar</p>
                                @if (!empty($payment['qr_url']))
                                    <div class="inline-block bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                                        <img src="{{ $payment['qr_url'] }}" alt="QRIS"
                                            class="w-52 h-52 object-contain" />
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500">QR belum tersedia, silakan tunggu sebentar.</p>
                                @endif
                            </div>
                        @endif

                        @if (($payment['payment_type'] ?? '') === 'manual_transfer')
                            <div class="p-4 rounded-2xl border border-blue-100 bg-blue-50">
                                <p class="text-sm font-semibold text-slate-800">Transfer Manual</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $manualPaymentSettings['instruction'] ?? 'Transfer sesuai nominal, lalu upload bukti pembayaran di bawah ini.' }}</p>
                                <div class="mt-3 rounded-xl bg-white p-3 text-sm text-slate-600">
                                    <p>Bank: <span class="font-semibold">{{ $manualPaymentSettings['bank_name'] ?? 'BCA' }} / {{ $manualPaymentSettings['account_number'] ?? '1234567890' }}</span></p>
                                    <p>Atas nama: <span class="font-semibold">{{ $manualPaymentSettings['account_name'] ?? 'Ecommerce Citra' }}</span></p>
                                </div>
                            </div>

                            @if (!empty($payment['payment_proof_path']))
                                <div class="p-4 rounded-2xl border border-emerald-100 bg-emerald-50 text-sm text-emerald-700">
                                    Bukti transfer sudah diupload. Status akan berubah setelah admin memverifikasi.
                                    <a href="{{ asset(ltrim($payment['payment_proof_path'], '/')) }}" target="_blank" class="font-semibold underline ml-1">Lihat bukti</a>
                                    @if (!empty($payment['payment_admin_note']))
                                        <p class="mt-2 text-emerald-800">Catatan admin: {{ $payment['payment_admin_note'] }}</p>
                                    @endif
                                </div>
                            @else
                                <form method="POST" action="{{ route('manual-payment.proof', ['transaction' => $payment['transaction_db_id'] ?? 0]) }}" enctype="multipart/form-data"
                                    class="p-4 rounded-2xl border border-slate-200 bg-white space-y-3">
                                    @csrf
                                    <label class="text-sm font-semibold text-slate-700 block">Upload Bukti Transfer</label>
                                    <input type="file" name="payment_proof" accept="image/*" required
                                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                                    <button class="w-full rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Kirim Bukti Pembayaran</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT: Order Summary + Actions --}}
            <div class="w-full lg:flex-1 space-y-5">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 pt-5 pb-2">
                        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Ringkasan Pesanan</h2>
                    </div>
                    <div class="px-6 pb-6 space-y-4">
                        @foreach ($payment['items'] ?? [] as $item)
                            <div class="flex items-start gap-3">
                                <img src="{{ !empty($item['image']) ? $item['image'] : 'https://via.placeholder.com/80x80?text=No+Image' }}"
                                    alt="{{ $item['name'] }}"
                                    class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-100" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 leading-snug truncate">
                                        {{ $item['name'] }}</p>
                                    @if (!empty($item['variant']))
                                        <p class="text-xs text-slate-400">{{ $item['variant'] }}</p>
                                    @endif
                                    @if (!empty($item['note']))
                                        <p class="text-xs text-slate-400">Catatan: {{ $item['note'] }}</p>
                                    @endif
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-slate-400">x{{ $item['qty'] }}</span>
                                        <span class="text-sm font-semibold text-slate-700">Rp
                                            {{ number_format((int) $item['price'] * (int) $item['qty'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="border-t border-dashed border-slate-100 pt-4 space-y-2">
                            @foreach ($payment['items'] ?? [] as $item)
                                <div class="flex justify-between text-sm text-slate-500">
                                    <span class="truncate max-w-[60%]">{{ $item['name'] }} x{{ $item['qty'] }}</span>
                                    <span>Rp
                                        {{ number_format((int) $item['price'] * (int) $item['qty'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between text-sm text-slate-500">
                                <span>Ongkos Kirim</span>
                                <span>Rp {{ number_format((int) ($payment['shipping_cost'] ?? 0), 0, ',', '.') }}</span>
                            </div>
                            @if ((int) ($payment['discount_amount'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-emerald-600">
                                    <span>Voucher {{ $payment['coupon_code'] ?? '' }}</span>
                                    <span>- Rp {{ number_format((int) ($payment['discount_amount'] ?? 0), 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                <span class="font-bold text-slate-800">Grand Total</span>
                                <span class="text-xl font-extrabold text-indigo-600">Rp
                                    {{ number_format((int) ($payment['gross_amount'] ?? 0), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('frontend.index') }}"
                            class="flex items-center justify-center gap-2 border border-slate-200 text-slate-600 font-semibold py-3.5 rounded-2xl hover:bg-slate-50 transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali Belanja
                        </a>
                        <a href="{{ route('frontend.profil', ['tab' => 'pesanan']) }}"
                            class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-2xl transition-colors text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Lihat Pesanan
                        </a>
                    </div>
                    <button type="button" onclick="openCancelModal()"
                        class="w-full flex items-center justify-center gap-2 text-sm text-red-400 hover:text-red-500 font-medium py-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batalkan Transaksi
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Batalkan Transaksi -->
    <div id="cancelModal"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 border border-slate-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">Batalkan Transaksi</p>
                    <p class="text-xs text-slate-500">Pilih alasan pembatalan</p>
                </div>
            </div>
            <div class="space-y-2 mb-4">
                @php
                    $cancelReasons = [
                        'Berubah pikiran / tidak jadi membeli',
                        'Salah memilih produk atau varian',
                        'Ingin menggunakan metode pembayaran lain',
                        'Harga terlalu mahal',
                        'Menemukan produk lebih murah di tempat lain',
                        'Alasan lainnya',
                    ];
                @endphp
                @foreach ($cancelReasons as $reason)
                    <label
                        class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer has-[:checked]:border-red-400 has-[:checked]:bg-red-50 transition-colors">
                        <input type="radio" name="cancelReason" value="{{ $reason }}"
                            class="text-red-500 focus:ring-red-400">
                        <span class="text-sm text-slate-700">{{ $reason }}</span>
                    </label>
                @endforeach
                <input type="text" id="cancelReasonOther" placeholder="Tulis alasan lainnya..."
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm hidden focus:outline-none focus:border-red-400">
            </div>
            <p id="cancelModalError" class="text-xs text-red-500 mb-2 hidden">Pilih alasan pembatalan terlebih dahulu.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeCancelModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Kembali</button>
                <button type="button" id="confirmCancelBtn"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-colors">Ya,
                    Batalkan</button>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-8 text-center modal-enter relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 flex justify-around">
                <div class="w-2 h-6 bg-yellow-400 rounded opacity-70"
                    style="animation: fall 1.2s 0.1s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-blue-400 rounded opacity-70" style="animation: fall 1.2s 0.3s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-blue-400 rounded opacity-70" style="animation: fall 1.2s 0.2s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-pink-400 rounded opacity-70" style="animation: fall 1.2s 0.4s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-orange-400 rounded opacity-70"
                    style="animation: fall 1.2s 0.15s ease-out forwards;">
                </div>
            </div>

            <div
                class="w-24 h-24 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-blue-200">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Pembelian Berhasil! 🎉</h2>
            <p class="text-slate-600 mb-5">Terima kasih sudah berbelanja di {{ $appStoreName ?? 'Ecommerce Citra' }}. Pesananmu sedang diproses!</p>

            <div class="bg-slate-50 rounded-2xl p-4 mb-5 text-left">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Nomor Pesanan</span>
                        <span class="font-bold text-slate-800 font-mono" id="orderNum">{{ $payment['order_id'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Metode Bayar</span>
                        <span class="font-medium text-slate-700" id="payMethod">{{ $payment['method_label'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total Dibayar</span>
                        <span class="font-bold text-blue-600" id="totalPaid">Rp
                            {{ number_format((int) ($payment['gross_amount'] ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-3 mb-6 text-sm text-blue-700 flex gap-2">
                <span>📱</span>
                <span>Notifikasi status pesanan akan dikirim ke email kamu.</span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('frontend.profil', ['tab' => 'pesanan']) }}"
                    class="flex-1 border-2 border-blue-400 text-blue-600 font-semibold py-3 rounded-xl hover:bg-blue-50 transition-colors text-sm">Lihat
                    Pesanan</a>
                <a href="{{ route('frontend.index') }}"
                    class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold py-3 rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all text-sm">Belanja
                    Lagi</a>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const statusUrl = @json(route('frontend.checkout.midtrans.status', ['orderId' => $payment['order_id']]));
        const simulateUrl = @json(route('frontend.checkout.midtrans.simulate'));
        const cancelUrl = @json(route('frontend.checkout.midtrans.cancel', ['orderId' => $payment['order_id']]));
        const completeCheckoutUrl = @json(route('frontend.checkout.complete'));
        const csrfToken = @json(csrf_token());
        const orderId = @json($payment['order_id']);
        const expiresAtIso = @json($payment['expires_at'] ?? now()->addMinutes(30)->toIso8601String());
        let cancelledByTimeout = false;
        let checkoutCompleted = false;
        let successModalShown = false;
        let countdownInterval = null;
        let currentTxStatus = @json(strtolower((string) ($payment['transaction_status'] ?? 'pending')));
        const paymentType = @json((string) ($payment['payment_type'] ?? ''));
        const vaNumber = @json((string) ($payment['va_number'] ?? ''));
        const vaBank = @json(strtolower((string) ($payment['va_bank'] ?? '')));
        const qrUrl = @json((string) ($payment['qr_url'] ?? ''));

        function showSuccessModal() {
            if (successModalShown) return;
            successModalShown = true;
            const modal = document.getElementById('successModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function switchToMyTransactionButton() {
            const btn = document.getElementById('refreshStatusBtn');
            if (!btn) return;
            btn.textContent = 'Transaksi Saya';
            btn.onclick = () => {
                window.location.href = @json(route('frontend.profil', ['tab' => 'pesanan']));
            };
        }

        async function refreshStatus() {
            const res = await fetch(statusUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;
            const json = await res.json();
            const status = String(json?.transaction_status || 'pending');

            // Stop polling on any terminal status
            if (TERMINAL_STATUSES.includes(status)) stopPolling();

            const el = document.getElementById('txStatus');
            if (['cancel', 'expire', 'deny', 'failure', 'dibatalkan'].includes(status)) {
                if (el) el.textContent = 'DIBATALKAN';
                paintStatus('cancel');
                currentTxStatus = 'cancel';
                return;
            }

            if (el) el.textContent = status.toUpperCase();
            paintStatus(status);
            currentTxStatus = status;

            if (['settlement', 'capture', 'paid'].includes(status)) {
                stopPolling();
                stopCountdown();
                cancelledByTimeout = true;
                switchToMyTransactionButton();
                showSuccessModal();
                if (!checkoutCompleted) {
                    checkoutCompleted = true;
                    await fetch(completeCheckoutUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        }),
                    });
                }
            }
        }

        async function cancelPaymentByTimeout() {
            if (cancelledByTimeout) return;
            if (['settlement', 'capture', 'paid', 'process', 'kirim', 'selesai', 'completed'].includes(String(
                    currentTxStatus || '').toLowerCase())) {
                return;
            }
            cancelledByTimeout = true;
            stopPolling();
            stopCountdown();
            await fetch(cancelUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    cancel_reason: 'Transaksi kadaluarsa (tidak dibayar tepat waktu)'
                }),
            });
            const el = document.getElementById('txStatus');
            if (el) el.textContent = 'DIBATALKAN';
            paintStatus('cancel');
        }

        function paintStatus(statusRaw) {
            const status = String(statusRaw || '').toLowerCase();
            const wrap = document.getElementById('txStatusWrap');
            if (!wrap) return;

            wrap.className = 'p-3 rounded-xl text-sm border';
            if (['settlement', 'capture'].includes(status)) {
                wrap.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                return;
            }
            if (['cancel', 'expire', 'deny', 'failure'].includes(status)) {
                wrap.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                return;
            }
            wrap.classList.add('bg-yellow-50', 'text-yellow-700', 'border-yellow-200');
        }

        function startCountdown() {
            const timerEl = document.getElementById('countdownTimer');
            if (!timerEl) return;

            const tick = async () => {
                const now = new Date().getTime();
                const target = new Date(expiresAtIso).getTime();
                const diff = Math.max(0, target - now);
                const totalSec = Math.floor(diff / 1000);
                const mm = String(Math.floor(totalSec / 60)).padStart(2, '0');
                const ss = String(totalSec % 60).padStart(2, '0');
                timerEl.textContent = `${mm}:${ss}`;

                if (totalSec <= 0) {
                    await cancelPaymentByTimeout();
                    stopCountdown();
                }
            };

            tick();
            countdownInterval = setInterval(tick, 1000);
        }

        function stopCountdown() {
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }

        function bindCopyVa() {
            const copyBtn = document.getElementById('copyVaBtn');
            const vaText = document.getElementById('vaNumberText')?.textContent?.trim();
            if (!copyBtn || !vaText) return;
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(vaText);
                    copyBtn.textContent = 'Tersalin';
                    setTimeout(() => {
                        copyBtn.textContent = 'Salin';
                    }, 1400);
                } catch (e) {
                    copyBtn.textContent = 'Gagal';
                    setTimeout(() => {
                        copyBtn.textContent = 'Salin';
                    }, 1400);
                }
            });
        }

        async function runSimulatePayment() {
            const msg = document.getElementById('simulateInlineMsg');
            const btn = document.getElementById('openSimulateModalBtn');
            if (!btn) return;

            const bankSimulatorMap = {
                bca: 'https://simulator.sandbox.midtrans.com/bca/va/index',
                bri: 'https://simulator.sandbox.midtrans.com/openapi/va/index?bank=bri',
                bni: 'https://simulator.sandbox.midtrans.com/bni/va/index',
                cimb: 'https://simulator.sandbox.midtrans.com/openapi/va/index?bank=cimb',
                mandiri: 'https://simulator.sandbox.midtrans.com/openapi/va/index?bank=mandiri',
            };
            const normalizedType = String(paymentType || '').toLowerCase();
            let targetUrl = 'https://simulator.sandbox.midtrans.com/v2/qris/index';

            if (normalizedType === 'bank_transfer') {
                targetUrl = bankSimulatorMap[String(vaBank || '').toLowerCase()] || bankSimulatorMap.bca;
            }

            window.open(targetUrl, '_blank', 'noopener,noreferrer');
        }

        document.getElementById('refreshStatusBtn')?.addEventListener('click', refreshStatus);
        document.getElementById('openSimulateModalBtn')?.addEventListener('click', runSimulatePayment);
        bindCopyVa();
        if (paymentType !== 'manual_transfer') startCountdown();
        paintStatus(@json(strtolower((string) ($payment['transaction_status'] ?? 'pending'))));
        if (['settlement', 'capture', 'paid'].includes(@json(strtolower((string) ($payment['transaction_status'] ?? 'pending'))))) {
            switchToMyTransactionButton();
            showSuccessModal();
        }
        const TERMINAL_STATUSES = ['settlement', 'capture', 'paid', 'cancel', 'expire', 'deny', 'failure', 'dibatalkan'];
        let pollInterval = setInterval(refreshStatus, 12000);

        function stopPolling() {
            clearInterval(pollInterval);
        }

        // Stop polling immediately if already in terminal state
        const initStatus = @json(strtolower((string) ($payment['transaction_status'] ?? 'pending')));
        if (TERMINAL_STATUSES.includes(initStatus)) stopPolling();

        // Cancel modal

        function openCancelModal() {
            document.getElementById('cancelModal').classList.remove('hidden');
            document.getElementById('cancelModal').classList.add('flex');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
            document.getElementById('cancelModal').classList.remove('flex');
        }

        // Toggle free-text input when "Alasan lainnya" is selected
        document.querySelectorAll('input[name="cancelReason"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const other = document.getElementById('cancelReasonOther');
                if (this.value === 'Alasan lainnya') {
                    other.classList.remove('hidden');
                    other.focus();
                } else {
                    other.classList.add('hidden');
                }
            });
        });

        document.getElementById('confirmCancelBtn')?.addEventListener('click', async function() {
            const selected = document.querySelector('input[name="cancelReason"]:checked');
            const errEl = document.getElementById('cancelModalError');
            if (!selected) {
                errEl.classList.remove('hidden');
                return;
            }
            errEl.classList.add('hidden');

            let reason = selected.value;
            if (reason === 'Alasan lainnya') {
                const other = document.getElementById('cancelReasonOther').value.trim();
                reason = other || 'Alasan lainnya';
            }

            this.disabled = true;
            this.textContent = 'Membatalkan...';
            try {
                const res = await fetch(cancelUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        cancel_reason: reason
                    }),
                });
                const json = await res.json();
                if (json.ok || json.transaction_status === 'cancel') {
                    stopPolling();
                    closeCancelModal();
                    paintStatus('cancel');
                    const el = document.getElementById('txStatus');
                    if (el) el.textContent = 'DIBATALKAN';
                    // Hide cancel button after successful cancel
                    const cancelBtn = document.querySelector('button[onclick="openCancelModal()"]');
                    if (cancelBtn) cancelBtn.style.display = 'none';
                }
            } catch (e) {}
            this.disabled = false;
            this.textContent = 'Ya, Batalkan';
        });
    </script>
@endsection
