@extends('layouts.user')

@section('title', 'Menunggu Pembayaran - Ecommerce Citra')
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
    </style>
@endsection

@section('content')
    @include('partials.navbar-user')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-800">Menunggu Pembayaran</h2>
                        <p class="text-sm text-slate-500 mt-1">Order ID: {{ $payment['order_id'] }}</p>
                    </div>
                    <button id="openSimulateModalBtn" type="button"
                        class="px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 text-xs font-semibold hover:bg-blue-50 transition-colors">
                        Simulasi Pembayaran
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 mb-3">Informasi Produk</h3>
                    <div class="space-y-3">
                        @foreach (($payment['items'] ?? []) as $item)
                            <div class="flex items-start gap-3">
                                <img src="{{ !empty($item['image']) ? $item['image'] : 'https://via.placeholder.com/80x80?text=No+Image' }}"
                                    alt="{{ $item['name'] }}"
                                    class="w-14 h-14 rounded-xl object-cover flex-shrink-0 border border-slate-100" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 leading-5">{{ $item['name'] }}</p>
                                    @if (!empty($item['variant']))
                                        <p class="text-xs text-slate-500">{{ $item['variant'] }}</p>
                                    @endif
                                    @if (!empty($item['note']))
                                        <p class="text-xs text-slate-500">Catatan: {{ $item['note'] }}</p>
                                    @endif
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-slate-500">x{{ $item['qty'] }}</span>
                                        <span class="text-sm font-medium text-slate-800">Rp {{ number_format((int) $item['price'] * (int) $item['qty'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-sm">
                    <span class="text-slate-500">Metode:</span>
                    <span class="font-semibold text-slate-800">{{ $payment['method_label'] }}</span>
                </div>

                @if (($payment['payment_type'] ?? '') === 'bank_transfer' && !empty($payment['va_number']))
                    <div class="p-4 border-2 border-blue-200 bg-blue-50 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">Nomor Virtual Account</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p id="vaNumberText" class="text-xl font-extrabold text-blue-700 tracking-wide">{{ $payment['va_number'] }}</p>
                            <button id="copyVaBtn" type="button"
                                class="px-3 py-1.5 rounded-lg border border-blue-300 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                                Salin
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Bank: {{ strtoupper((string) ($payment['va_bank'] ?? '-')) }}</p>
                    </div>
                @endif

                @if (($payment['payment_type'] ?? '') === 'qris')
                    <div class="p-4 border-2 border-blue-200 bg-blue-50 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-2">QRIS Pembayaran</p>
                        @if (!empty($payment['qr_url']))
                            <img src="{{ $payment['qr_url'] }}" alt="QRIS" class="w-56 h-56 object-contain bg-white p-2 rounded-lg border border-slate-200 mx-auto" />
                        @else
                            <p class="text-sm text-slate-500">QR belum tersedia, silakan refresh status.</p>
                        @endif
                    </div>
                @endif

                <div class="p-3 rounded-xl bg-slate-100 text-sm text-slate-700">
                    Sisa waktu pembayaran:
                    <span id="countdownTimer" class="font-semibold text-blue-700">30:00</span>
                </div>

                <div id="txStatusWrap" class="p-3 rounded-xl bg-yellow-50 text-sm text-yellow-700 border border-yellow-200">
                    Status saat ini: <span id="txStatus" class="font-semibold">{{ strtoupper($payment['transaction_status'] ?? 'PENDING') }}</span>
                </div>

                <div class="border-t border-slate-100 pt-2 mt-2">
                    <h3 class="font-bold text-slate-800 mb-3">Ringkasan Pesanan</h3>
                    <div class="space-y-3">
                        @foreach (($payment['items'] ?? []) as $item)
                            <div class="flex justify-between text-sm gap-2">
                                <span class="text-slate-600">{{ $item['name'] }} x{{ $item['qty'] }}</span>
                                <span class="font-medium text-slate-800">Rp {{ number_format((int) $item['price'] * (int) $item['qty'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Ongkos Kirim</span>
                            <span class="font-medium text-slate-800">Rp {{ number_format((int) ($payment['shipping_cost'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="font-bold text-slate-800">Grand Total</span>
                                <span class="font-extrabold text-blue-600 text-xl">Rp {{ number_format((int) ($payment['gross_amount'] ?? 0), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-1">
                    <button id="refreshStatusBtn" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition-colors">
                        Cek Status Pembayaran
                    </button>
                    <a href="{{ route('frontend.index') }}" class="w-full text-center border border-slate-200 text-slate-600 font-semibold py-3 rounded-xl hover:bg-slate-50 transition-colors">
                        Kembali Belanja
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="simulateModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Simulasi Pembayaran</h3>
                <button type="button" id="closeSimulateModalBtn" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <div class="space-y-3">
                <label class="text-xs font-medium text-slate-600 block">Order ID</label>
                <input id="simulateOrderIdInput" type="text" value="{{ $payment['order_id'] }}"
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                <p id="simulateMsg" class="text-xs hidden"></p>
                <button id="simulatePayBtn" type="button"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition-colors">
                    Bayar Simulasi
                </button>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-8 text-center modal-enter relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 flex justify-around">
                <div class="w-2 h-6 bg-yellow-400 rounded opacity-70"
                    style="animation: fall 1.2s 0.1s ease-out forwards;"></div>
                <div class="w-2 h-6 bg-blue-400 rounded opacity-70" style="animation: fall 1.2s 0.3s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-blue-400 rounded opacity-70" style="animation: fall 1.2s 0.2s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-pink-400 rounded opacity-70" style="animation: fall 1.2s 0.4s ease-out forwards;">
                </div>
                <div class="w-2 h-6 bg-orange-400 rounded opacity-70"
                    style="animation: fall 1.2s 0.15s ease-out forwards;"></div>
            </div>

            <div
                class="w-24 h-24 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-blue-200">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Pembelian Berhasil! 🎉</h2>
            <p class="text-slate-600 mb-5">Terima kasih sudah berbelanja di Ecommerce Citra. Pesananmu sedang diproses!</p>

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
                        <span class="font-bold text-blue-600" id="totalPaid">Rp {{ number_format((int) ($payment['gross_amount'] ?? 0), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Estimasi Tiba</span>
                        <span class="font-medium text-slate-700">2 - 5 Hari</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-3 mb-6 text-sm text-blue-700 flex gap-2">
                <span>📱</span>
                <span>Notifikasi status pesanan akan dikirim ke WhatsApp dan email kamu.</span>
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
            const el = document.getElementById('txStatus');
            if (el) el.textContent = status.toUpperCase();
            paintStatus(status);

            if (['settlement', 'capture', 'paid'].includes(status)) {
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
                            order_id: orderId,
                        }),
                    });
                }
            }
        }

        async function cancelPaymentByTimeout() {
            if (cancelledByTimeout) return;
            cancelledByTimeout = true;
            await fetch(cancelUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const el = document.getElementById('txStatus');
            if (el) el.textContent = 'EXPIRE';
            paintStatus('expire');
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
                    clearInterval(interval);
                }
            };

            tick();
            const interval = setInterval(tick, 1000);
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

        function openSimulateModal() {
            const modal = document.getElementById('simulateModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            const msg = document.getElementById('simulateMsg');
            if (msg) msg.classList.add('hidden');
        }

        function closeSimulateModal() {
            const modal = document.getElementById('simulateModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        async function runSimulatePayment() {
            const orderInput = document.getElementById('simulateOrderIdInput');
            const msg = document.getElementById('simulateMsg');
            const btn = document.getElementById('simulatePayBtn');
            const value = String(orderInput?.value || '').trim();
            if (!value) {
                if (msg) {
                    msg.className = 'text-xs text-red-600';
                    msg.textContent = 'Order ID wajib diisi.';
                }
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Memproses...';
            try {
                const res = await fetch(simulateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        order_id: value,
                    }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(json?.message || 'Order ID tidak valid.');
                }
                if (msg) {
                    msg.className = 'text-xs text-green-600';
                    msg.textContent = 'Simulasi berhasil. Status akan diperbarui.';
                }
                await refreshStatus();
                setTimeout(() => {
                    closeSimulateModal();
                }, 600);
            } catch (e) {
                if (msg) {
                    msg.className = 'text-xs text-red-600';
                    msg.textContent = e?.message || 'Gagal simulasi pembayaran.';
                }
            } finally {
                btn.disabled = false;
                btn.textContent = 'Bayar Simulasi';
            }
        }

        document.getElementById('refreshStatusBtn')?.addEventListener('click', refreshStatus);
        document.getElementById('openSimulateModalBtn')?.addEventListener('click', openSimulateModal);
        document.getElementById('closeSimulateModalBtn')?.addEventListener('click', closeSimulateModal);
        document.getElementById('simulatePayBtn')?.addEventListener('click', runSimulatePayment);
        bindCopyVa();
        startCountdown();
        paintStatus(@json(strtolower((string) ($payment['transaction_status'] ?? 'pending'))));
        if (['settlement', 'capture', 'paid'].includes(@json(strtolower((string) ($payment['transaction_status'] ?? 'pending'))))) {
            switchToMyTransactionButton();
            showSuccessModal();
        }
        setInterval(refreshStatus, 12000);
    </script>
@endsection
