@extends('layouts.user')

@section('title', 'Checkout - ' . ($appStoreName ?? 'Ecommerce Citra'))
@section('body_class', 'bg-slate-50 text-slate-800 overflow-x-hidden')

@section('style')
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        html,
        body {
            overflow-x: hidden;
            max-width: 100vw;
        }

        .step-active {
            background: #2563eb;
            color: white;
        }

        .step-done {
            background: #2563eb;
            color: white;
        }

        .step-inactive {
            background: #e2e8f0;
            color: #94a3b8;
        }

        .payment-card.active {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .payment-logo-box {
            width: 72px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .payment-logo-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            object-position: center;
        }

        .shipping-card.active {
            border-color: #2563eb;
            background: #eff6ff;
        }

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

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        .qty-btn:hover {
            background: #f1f5f9;
        }

        .checkout-page,
        .checkout-card,
        .checkout-card-body,
        .checkout-page .grid,
        .checkout-page .grid > *,
        .address-card > div,
        .payment-tabs,
        .payment-card,
        .shipping-card,
        .shipping-card > div,
        .summary-row,
        .summary-value {
            min-width: 0;
        }

        .payment-tabs {
            max-width: 100%;
        }

        .summary-value {
            overflow-wrap: anywhere;
            text-align: right;
        }

        @media (max-width: 430px) {
            .checkout-page {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .checkout-card-header,
            .checkout-card-body {
                padding: 1rem;
            }

            .payment-tabs {
                display: grid;
                grid-template-columns: 1fr;
                overflow: visible;
            }

            .payment-tab {
                width: 100%;
                justify-content: center;
            }

            .payment-panel {
                grid-template-columns: 1fr !important;
            }

            .payment-card {
                gap: 0.625rem;
            }

            .shipping-card {
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .shipping-card > div {
                flex-basis: calc(100% - 2rem);
            }

            .shipping-card > span:last-child {
                margin-left: 1.75rem;
            }

            .payment-logo-box {
                width: 58px;
            }

            .summary-row {
                align-items: flex-start;
                gap: 0.5rem;
            }

            .summary-row.stack-mobile {
                flex-direction: column;
            }

            .summary-row.stack-mobile .summary-value {
                width: 100%;
                text-align: left;
            }

            .voucher-actions {
                flex-direction: column;
            }

            .voucher-actions button {
                width: 100%;
            }

            #grandTotal {
                font-size: 1.125rem;
                line-height: 1.5rem;
            }
        }
    </style>
@endsection
@section('content')
    @php
        $isRedeemCheckout = ($checkoutSource ?? 'cart_all') === 'redeem_point';
        $checkoutTaxProfilesPayload = ($taxProfiles ?? collect())
            ->map(fn($profile) => [
                'id' => (int) $profile->id,
                'taxpayer_name' => (string) $profile->taxpayer_name,
                'taxpayer_number' => (string) $profile->taxpayer_number,
                'taxpayer_address' => (string) $profile->taxpayer_address,
                'taxpayer_email' => (string) $profile->taxpayer_email,
            ])
            ->values();
    @endphp
    <!-- NAVBAR -->
    @include('partials.navbar-user')

    <div class="checkout-page max-w-7xl mx-auto px-4 sm:px-6 py-5">
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- LEFT COL -->
            <div class="lg:col-span-2 space-y-6">

                <!-- CART ITEMS (dikelompokkan per perusahaan) -->
                <div class="flex items-center justify-between px-1">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Keranjang Belanja
                    </h2>
                    <span class="text-sm text-slate-500" id="itemCountText">0 item</span>
                </div>
                <div id="orderGroups" class="space-y-6"></div>

                <!-- ALAMAT PENGIRIMAN -->
                <div class="checkout-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div
                        class="checkout-card-header px-6 py-4 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Alamat Pengiriman
                        </h2>
                        <button type="button" onclick="showAddressModal()"
                            class="text-blue-600 text-sm font-medium hover:text-blue-700">
                            Tambah Alamat
                        </button>
                    </div>
                    <div class="checkout-card-body p-6 space-y-3" id="addressList">
                        @forelse(($addresses ?? collect()) as $address)
                            <label
                                class="address-card flex items-start gap-3 p-4 border-2 {{ $address->is_primary ? 'border-blue-400 bg-blue-50' : 'border-slate-200' }} rounded-xl cursor-pointer hover:border-slate-300 transition-colors">
                                <input type="radio" name="address" class="mt-1 accent-blue-500"
                                    data-address-id="{{ $address->id }}"
                                    data-destination-id="{{ $address->destination_id }}"
                                    {{ $address->is_primary ? 'checked' : '' }} />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-semibold text-slate-800">{{ $address->recipient_name }}</p>
                                        <span
                                            class="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $address->label }}</span>
                                        @if ($address->is_primary)
                                            <span
                                                class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">Utama</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-slate-600">{{ $address->phone_country_code }}
                                        {{ $address->phone_number }}</p>
                                    <p class="text-sm text-slate-600">{{ $address->address_line }}, {{ $address->city }},
                                        {{ $address->province }}{{ $address->postal_code ? ', ' . $address->postal_code : '' }}
                                    </p>
                                </div>
                            </label>
                        @empty
                            <div class="text-sm text-slate-500">
                                Belum ada alamat tersimpan. Silakan tambahkan alamat di halaman profil.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- METODE PEMBAYARAN -->
                <div class="checkout-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="checkout-card-header px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            {{ $isRedeemCheckout ? 'Pembayaran Ongkir' : 'Metode Pembayaran' }}
                        </h2>
                    </div>
                    <div class="checkout-card-body p-6">
                        @if ($isRedeemCheckout)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 mb-5">
                                <p class="text-sm font-semibold text-amber-800">Produk tetap ditukar dengan point, tetapi ongkir dibayar customer.</p>
                                <p class="mt-1 text-xs text-amber-700">Point baru akan dipotong setelah pembayaran ongkir berhasil dikonfirmasi.</p>
                            </div>
                        @endif
                        <!-- Tabs -->
                        <div class="payment-tabs flex gap-2 mb-5 overflow-x-auto">
                            <button onclick="setPaymentTab('qris')" id="tab-qris"
                                class="payment-tab inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-blue-500 text-white whitespace-nowrap transition-all">QRIS</button>
                            <button onclick="setPaymentTab('bank')" id="tab-bank"
                                class="payment-tab inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all">Transfer
                                Bank</button>
                            <button onclick="setPaymentTab('manual')" id="tab-manual"
                                class="payment-tab inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all">Transfer Manual</button>
                        </div>

                            <!-- QRIS -->
                            <div id="panel-qris" class="payment-panel grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <label onclick="setPayment('qris', 'QRIS')"
                                    class="payment-card active flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                    <input type="radio" name="payment" value="qris" class="accent-blue-500" checked />
                                    <span class="payment-logo-box">
                                        <img class="payment-logo-img" alt="QRIS" src="{{ asset('imgs/qris.png') }}" />
                                    </span>
                                    <span class="text-sm font-semibold text-slate-700">QRIS</span>
                                </label>
                            </div>

                        <!-- Bank Transfer -->
                        <div id="panel-bank" class="payment-panel hidden grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label onclick="setPayment('bca', 'BCA Virtual Account')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" value="bca" class="accent-blue-500" />
                                <span class="payment-logo-box">
                                    <img class="payment-logo-img" alt="BCA" src="{{ asset('imgs/bca.png') }}" />
                                </span>
                                <span class="text-sm font-semibold text-slate-700">BCA</span>
                            </label>
                            <label onclick="setPayment('mandiri', 'Mandiri Virtual Account')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" value="mandiri" class="accent-blue-500" />
                                <span class="payment-logo-box">
                                    <img class="payment-logo-img" alt="Mandiri"
                                        src="{{ asset('imgs/mandiri.png') }}" />
                                </span>
                                <span class="text-sm font-semibold text-slate-700">Mandiri</span>
                            </label>
                            <label onclick="setPayment('bni', 'BNI Virtual Account')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" value="bni" class="accent-blue-500" />
                                <span class="payment-logo-box">
                                    <img class="payment-logo-img" alt="BNI" src="{{ asset('imgs/bni.png') }}" />
                                </span>
                                <span class="text-sm font-semibold text-slate-700">BNI</span>
                            </label>
                            <label onclick="setPayment('bri', 'BRI Virtual Account')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" value="bri" class="accent-blue-500" />
                                <span class="payment-logo-box">
                                    <img class="payment-logo-img" alt="BRI" src="{{ asset('imgs/bri.png') }}" />
                                </span>
                                <span class="text-sm font-semibold text-slate-700">BRI</span>
                            </label>
                            <label onclick="setPayment('cimb', 'CIMB Virtual Account')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" value="cimb" class="accent-blue-500" />
                                <span class="payment-logo-box">
                                    <img class="payment-logo-img" alt="CIMB Niaga" src="{{ asset('imgs/cimb.png') }}" />
                                </span>
                                <span class="text-sm font-semibold text-slate-700">CIMB</span>
                            </label>
                        </div>

                            <div id="panel-manual" class="payment-panel hidden">
                                <label onclick="setPayment('manual_transfer', 'Transfer Manual')"
                                    class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                    <input type="radio" name="payment" value="manual_transfer" class="accent-blue-500" />
                                    <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <i class="ri-bank-card-line text-xl"></i>
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-700">Transfer Manual</span>
                                        <span class="block text-xs text-slate-400">Upload bukti transfer setelah checkout</span>
                                    </span>
                                </label>
                            </div>
                    </div>
                </div>

                <!-- FAKTUR PAJAK -->
                <div class="checkout-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="checkout-card-header px-6 py-4 border-b border-slate-100">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input id="taxInvoiceRequested" type="checkbox" class="mt-1 accent-blue-500"
                                onchange="toggleTaxInvoiceForm()" />
                            <span class="min-w-0">
                                <span class="block font-bold text-slate-800">Saya membutuhkan faktur pajak</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Data NPWP disimpan sebagai snapshot transaksi dan diproses admin setelah checkout.</span>
                            </span>
                        </label>
                    </div>
                    <div id="taxInvoiceForm" class="checkout-card-body hidden p-6 space-y-4">
                        @if (($taxProfiles ?? collect())->isNotEmpty())
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-600">Pakai profil wajib pajak</label>
                                <select id="taxInvoiceProfileId" onchange="applyTaxInvoiceProfile()"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-blue-400 focus:outline-none">
                                    <option value="">Isi manual</option>
                                    @foreach ($taxProfiles as $profile)
                                        <option value="{{ $profile->id }}">{{ $profile->taxpayer_name }} - {{ $profile->masked_taxpayer_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-600">Nama NPWP</label>
                                <input id="taxpayerName" type="text" placeholder="Nama sesuai NPWP"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-600">Nomor NPWP</label>
                                <input id="taxpayerNumber" type="text" placeholder="15/16 digit atau format NPWP"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-xs font-semibold text-slate-600">Alamat NPWP</label>
                                <textarea id="taxpayerAddress" rows="3" placeholder="Alamat lengkap sesuai NPWP"
                                    class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none"></textarea>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-600">Email Penerima</label>
                                <input id="taxpayerEmail" type="email" placeholder="finance@example.com"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-600">Catatan</label>
                                <input id="taxInvoiceNote" type="text" placeholder="Opsional"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none">
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input id="saveTaxProfile" type="checkbox" class="accent-blue-500">
                                Simpan sebagai profil wajib pajak
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input id="setDefaultTaxProfile" type="checkbox" class="accent-blue-500">
                                Jadikan default
                            </label>
                        </div>
                    </div>
                </div>

                {{-- <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Catatan untuk Penjual
                    </h2>
                    <textarea placeholder="Contoh: Tolong dibungkus rapi sebagai hadiah..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-20"></textarea>
                </div> --}}
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="lg:col-span-1 lg:self-start">
                <div class="checkout-card bg-white rounded-2xl shadow-sm border border-slate-100 sticky top-24">
                    <div class="checkout-card-header px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-800">Ringkasan Pesanan</h2>
                    </div>
                    <div class="checkout-card-body p-6 space-y-3">
                        <p class="text-xs text-slate-400" id="sumItems">0 item</p>
                        <div id="summaryBreakdown" class="space-y-4"></div>
                        <div class="border-t border-slate-100 pt-3 mt-3">
                            <div class="summary-row flex justify-between">
                                <span class="font-bold text-slate-800">{{ $isRedeemCheckout ? 'Total Bayar Ongkir' : 'Grand Total' }}</span>
                                <span class="summary-value font-extrabold text-blue-600 text-xl" id="grandTotal">Rp 888.000</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ $isRedeemCheckout ? 'Point digunakan untuk produk, sedangkan ongkir dibayar terpisah oleh customer.' : 'PPN dihitung dari subtotal produk setelah diskon. Ongkir tidak dikenakan PPN.' }}</p>
                        </div>

                        <!-- Info -->
                        <div class="bg-blue-50 rounded-xl p-3 flex gap-2 items-start">
                            <span class="text-blue-500 mt-0.5">ℹ️</span>
                            <p class="text-xs text-blue-700">Transaksi dilindungi sistem keamanan {{ $appStoreName ?? 'Ecommerce Citra' }}. Uang
                                dikembalikan jika barang tidak sampai.</p>
                        </div>

                        <!-- Bayar -->
                        <button onclick="processPayment()" id="payBtn"
                            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-blue-200 hover:shadow-blue-300 flex items-center justify-center gap-2 mt-4 disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            {{ $isRedeemCheckout ? 'Bayar Ongkir & Tukar Point' : 'Bayar Sekarang' }}
                        </button>
                        <p id="checkoutHintText" class="text-xs text-slate-500 mt-2 text-center"></p>

                        <div class="flex flex-wrap items-center justify-center gap-4 mt-3 text-slate-400">
                            <div class="flex items-center gap-1 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                SSL Secure
                            </div>
                            <div class="flex items-center gap-1 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Pembayaran Aman
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ADD ADDRESS MODAL -->
    <div id="addressModal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-6 modal-enter max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-slate-800 text-lg">Tambah Alamat Baru</h3>
                <button type="button" onclick="closeAddressModal()"
                    class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>

            <form id="checkoutAddressForm" class="space-y-4">
                <input type="hidden" id="checkoutAddressLabel" value="Rumah" />
                <input type="hidden" id="checkoutPhoneCountryCode" value="+62" />
                <input type="hidden" id="checkoutIsPrimary" value="0" />
                <input type="hidden" id="checkoutProvinceId" />
                <input type="hidden" id="checkoutCityId" />
                <input type="hidden" id="checkoutDistrictId" />
                <input type="hidden" id="checkoutSubdistrictId" />
                <input type="hidden" id="checkoutDestinationId" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Nama Penerima *</label>
                        <input id="checkoutRecipientName" type="text" placeholder="Nama lengkap"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">No. Telepon *</label>
                        <input id="checkoutPhoneNumber" type="text" placeholder="08xx-xxxx-xxxx"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Provinsi *</label>
                        <div class="relative">
                            <input id="checkoutProvinceInput" type="text" placeholder="Cari provinsi"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                            <span id="checkoutProvinceChevron"
                                class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span id="checkoutProvinceSpinner"
                                class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                            </span>
                            <div id="checkoutProvinceDropdown"
                                class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kota/Kabupaten *</label>
                        <div class="relative">
                            <input id="checkoutCityInput" type="text" disabled placeholder="Pilih provinsi dulu"
                                data-placeholder-enabled="Cari kota/kabupaten" autocomplete="new-password"
                                autocorrect="off" autocapitalize="off" spellcheck="false"
                                class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                            <span id="checkoutCityChevron"
                                class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span id="checkoutCitySpinner"
                                class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                            </span>
                            <div id="checkoutCityDropdown"
                                class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kecamatan *</label>
                        <div class="relative">
                            <input id="checkoutDistrictInput" type="text" disabled placeholder="Pilih kota dulu"
                                data-placeholder-enabled="Cari kecamatan" autocomplete="new-password" autocorrect="off"
                                autocapitalize="off" spellcheck="false"
                                class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                            <span id="checkoutDistrictChevron"
                                class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span id="checkoutDistrictSpinner"
                                class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                            </span>
                            <div id="checkoutDistrictDropdown"
                                class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kelurahan *</label>
                        <div class="relative">
                            <input id="checkoutSubdistrictInput" type="text" disabled
                                placeholder="Pilih kecamatan dulu" data-placeholder-enabled="Cari kelurahan"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                            <span id="checkoutSubdistrictChevron"
                                class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span id="checkoutSubdistrictSpinner"
                                class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                            </span>
                            <div id="checkoutSubdistrictDropdown"
                                class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Kode Pos</label>
                    <input id="checkoutPostalCode" type="text" placeholder="Kode Pos"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Alamat Lengkap *</label>
                    <textarea id="checkoutAddressLine" placeholder="Nama jalan, nomor, RT/RW, kelurahan..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-20"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeAddressModal()"
                        class="flex-1 border border-slate-200 text-slate-600 font-semibold py-3 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="button" onclick="saveAddress()"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition-colors">Simpan
                        Alamat</button>
                </div>
            </form>
        </div>
    </div>
    <!-- SUCCESS MODAL -->
    <div id="successModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-8 text-center modal-enter relative overflow-hidden">
            <!-- Confetti decoration -->
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
            <p class="text-slate-600 mb-5">Terima kasih sudah berbelanja di {{ $appStoreName ?? 'Ecommerce Citra' }}. Pesananmu sedang diproses!</p>

            <div class="bg-slate-50 rounded-2xl p-4 mb-5 text-left">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Nomor Pesanan</span>
                        <span class="font-bold text-slate-800 font-mono" id="orderNum">#TK-2025-XXXXX</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Metode Bayar</span>
                        <span class="font-medium text-slate-700" id="payMethod">GoPay</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total Dibayar</span>
                        <span class="font-bold text-blue-600" id="totalPaid">Rp 888.000</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-3 mb-6 text-sm text-blue-700 flex gap-2">
                <span>📱</span>
                <span>Notifikasi status pesanan akan dikirim ke email kamu.</span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('frontend.profil') }}"
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
        let cartItems = @json($checkoutItems ?? []);
        const checkoutSource = @json($checkoutSource ?? 'cart_all');
        const isRedeemCheckout = checkoutSource === 'redeem_point';
        const taxSettingsByCompany = @json($taxSettingsByCompany ?? []);
        function taxSettingsFor(companyId) {
            return taxSettingsByCompany?.[companyId] ?? { enabled: true, name: 'PPN', rate: 11 };
        }
        let selectedPayment = 'qris';
        let selectedPaymentLabel = 'QRIS';
        const csrfToken = @json(csrf_token());
        const completeCheckoutUrl = @json(route('frontend.checkout.complete'));
        const checkoutOrdersUrl = @json(route('frontend.checkout.orders'));
        const shippingOptionsUrl = @json(route('frontend.rajaongkir.shipping-options'));
        const midtransChargeUrl = @json(route('frontend.checkout.midtrans.charge'));
        const manualPaymentUrl = @json(route('frontend.checkout.manual-payment'));
        const couponApplyUrl = @json(route('frontend.checkout.coupon.apply'));
        const couponRemoveUrl = @json(route('frontend.checkout.coupon.remove'));
        const taxProfiles = @json($checkoutTaxProfilesPayload);
        const roProvincesUrl = @json(route('frontend.rajaongkir.provinces'));
        const roCitiesUrl = @json(route('frontend.rajaongkir.cities'));
        const roDistrictsUrl = @json(route('frontend.rajaongkir.districts'));
        const roSubdistrictsUrl = @json(route('frontend.rajaongkir.subdistricts'));
        const storeAddressUrl = @json(route('frontend.profil.addresses.store'));
        const FALLBACK_IMAGE = 'https://via.placeholder.com/100x100?text=No+Image';
        let roProvinces = [];
        let roCities = [];
        let roDistricts = [];
        let roSubdistricts = [];

        function normalizeItem(item, index = 0) {
            const price = Number(item?.price || 0);
            const defaultWeightPerItem = Number(@json((int) env('CHECKOUT_DEFAULT_ITEM_WEIGHT', 1000)));
            return {
                key: item?.key || `${item?.product_id || item?.id || 'item'}::${item?.variant || '-'}`,
                cartId: item?.cartId || item?.cart_id || null,
                id: item?.product_id || item?.id || index + 1,
                companyId: Number(item?.companyId || item?.company_id || 0),
                companyName: String(item?.companyName || item?.company_name || 'Toko'),
                productVariantId: item?.productVariantId || item?.product_variant_id || null,
                slug: item?.slug || '',
                name: item?.name || 'Produk',
                variant: item?.variant || '-',
                price,
                origPrice: Number(item?.origPrice || item?.orig_price || price),
                qty: Math.max(1, Number(item?.qty || 1)),
                image: item?.image || FALLBACK_IMAGE,
                note: String(item?.note || ''),
                redeemPoints: Math.max(0, Number(item?.redeemPoints || item?.redeem_points || 0)),
                isRedeemProduct: Boolean(item?.isRedeemProduct || item?.is_redeem_product || false),
                weightGrams: Math.max(1, Number(item?.weightGrams || item?.weight_grams || defaultWeightPerItem || 1)),
            };
        }
        cartItems = (Array.isArray(cartItems) ? cartItems : []).map((item, idx) => normalizeItem(item, idx));

        // Menyimpan state ongkir/kupon per perusahaan (checkout marketplace: N transaksi sekaligus,
        // satu per perusahaan -- lihat docs/prd-multi-company-foundation.md §4b).
        const groupState = {};

        function getGroupState(companyId) {
            if (!groupState[companyId]) {
                groupState[companyId] = { shippingCost: null, shippingLabel: 'Reguler', couponCode: '', discountAmount: 0 };
            }
            return groupState[companyId];
        }

        function buildGroups() {
            const map = new Map();
            cartItems.forEach((item, idx) => {
                const key = item.companyId || 0;
                if (!map.has(key)) {
                    map.set(key, { companyId: key, companyName: item.companyName || 'Toko', items: [] });
                }
                map.get(key).items.push({ ...item, _idx: idx });
            });
            return Array.from(map.values());
        }

        function groupWeight(group) {
            const totalWeight = group.items.reduce((sum, item) => {
                return sum + Math.max(1, Number(item.weightGrams || 0)) * Math.max(1, Number(item.qty || 0));
            }, 0);
            return Math.max(1, totalWeight);
        }

        function renderCart() {
            const container = document.getElementById('orderGroups');
            if (!cartItems.length) {
                container.innerHTML = `
                    <div class="checkout-card bg-white rounded-2xl shadow-sm border border-slate-100 py-10 text-center">
                        <p class="text-slate-500 text-sm mb-3">Keranjang masih kosong.</p>
                        <a href="{{ route('frontend.index') }}" class="inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:text-blue-700">
                            Belanja sekarang
                        </a>
                    </div>`;
                updateSummary();
                return;
            }

            const groups = buildGroups();
            container.innerHTML = groups.map((group) => {
                const itemsHtml = group.items.map((item, i) => `
        <div class="relative flex gap-3 py-4 pr-7 ${i > 0 ? 'border-t border-slate-100' : ''}">
          <input type="checkbox" class="mt-10 sm:mt-1 accent-blue-500 flex-shrink-0" checked />
          <img src="${item.image}" alt="${item.name}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-slate-800 text-sm line-clamp-2 mb-0.5">${item.name}</p>
            <p class="text-xs text-slate-500 mb-2">${item.variant}</p>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
              <div>
                ${isRedeemCheckout
                    ? `<span class="font-bold text-amber-700 text-sm">${item.redeemPoints.toLocaleString('id-ID')} point</span>`
                    : `<span class="font-bold text-slate-900 text-sm">Rp ${item.price.toLocaleString('id-ID')}</span>
                       ${item.origPrice > item.price ? `<span class="text-xs text-slate-400 line-through ml-1">Rp ${item.origPrice.toLocaleString('id-ID')}</span>` : ''}`}
              </div>
              <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden self-start sm:self-auto">
                <button class="qty-btn px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm" onclick="changeQty(${item._idx}, -1)">−</button>
                <input
                  type="number"
                  min="1"
                  value="${item.qty}"
                  inputmode="numeric"
                  oninput="handleCheckoutQtyInput(${item._idx}, this)"
                  onblur="commitCheckoutQtyInput(${item._idx}, this)"
                  class="w-12 px-2 py-1 text-sm font-semibold text-center border-x border-slate-200 focus:outline-none focus:bg-blue-50"
                />
                <button class="qty-btn px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm" onclick="changeQty(${item._idx}, 1)">+</button>
              </div>
            </div>
            <div class="mt-2">
              <label class="text-xs text-slate-500 block mb-1">Catatan Item</label>
              <input
                type="text"
                value="${String(item.note || '').replace(/"/g, '&quot;')}"
                oninput="updateItemNote(${item._idx}, this.value)"
                placeholder="Tulis catatan untuk produk ini"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-blue-400"
              />
            </div>
          </div>
          <button onclick="removeItem(${item._idx})" class="absolute top-4 right-0 sm:static text-slate-300 hover:text-red-400 transition-colors flex-shrink-0 self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>`).join('');

                return `
        <div class="checkout-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" data-company-id="${group.companyId}">
            <div class="checkout-card-header px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1" />
                    </svg>
                    ${group.companyName}
                </h3>
                <span class="text-xs text-slate-400">${group.items.length} produk</span>
            </div>
            <div class="p-4">${itemsHtml}</div>
            <div class="border-t border-slate-100 p-4 space-y-2">
                <p class="text-xs font-semibold text-slate-600">Pengiriman dari ${group.companyName}</p>
                <div id="shippingOptions-${group.companyId}" class="space-y-2">
                    <div class="text-sm text-slate-500">Menghitung ongkos kirim...</div>
                </div>
            </div>
            ${!isRedeemCheckout ? `
            <div class="border-t border-slate-100 p-4 space-y-2 bg-slate-50">
                <p class="text-xs font-semibold text-slate-600">Voucher untuk ${group.companyName}</p>
                <div class="flex gap-2">
                    <input id="couponInput-${group.companyId}" type="text" placeholder="Kode voucher"
                        class="min-w-0 flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 uppercase bg-white">
                    <button type="button" onclick="applyGroupCoupon(${group.companyId})"
                        class="px-3 py-2 rounded-lg bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600">Pakai</button>
                </div>
                <div id="couponInfo-${group.companyId}" class="hidden text-xs"></div>
            </div>` : ''}
        </div>`;
            }).join('');

            updateSummary();
            loadShippingOptions();
        }

        function changeQty(idx, d) {
            cartItems[idx].qty = Math.max(1, cartItems[idx].qty + d);
            renderCart();
        }

        function normalizeCheckoutQty(value) {
            const parsed = parseInt(value, 10);
            if (!Number.isFinite(parsed)) return 1;
            return Math.max(1, parsed);
        }

        function handleCheckoutQtyInput(idx, input) {
            if (!cartItems[idx] || !input.value) return;
            cartItems[idx].qty = normalizeCheckoutQty(input.value);
            input.value = String(cartItems[idx].qty);
            updateSummary();
        }

        function commitCheckoutQtyInput(idx, input) {
            if (!cartItems[idx]) return;
            cartItems[idx].qty = normalizeCheckoutQty(input.value);
            input.value = String(cartItems[idx].qty);
            renderCart();
        }

        function removeItem(idx) {
            cartItems.splice(idx, 1);
            renderCart();
        }

        function updateItemNote(idx, value) {
            if (!cartItems[idx]) return;
            cartItems[idx].note = String(value || '');
        }

        function updateSummary() {
            const groups = buildGroups();
            const totalItems = cartItems.reduce((s, i) => s + i.qty, 0);
            const hasSelectedAddress = !!document.querySelector('input[name="address"]:checked');
            document.getElementById('itemCountText').textContent = totalItems + ' item';
            document.getElementById('sumItems').textContent = totalItems + ' item dari ' + groups.length + ' toko';

            let grandTotal = 0;
            let allShippingReady = groups.length > 0;

            const breakdownHtml = groups.map((group) => {
                const state = getGroupState(group.companyId);
                const subtotal = group.items.reduce((s, i) => s + i.price * i.qty, 0);
                const totalRedeemPoints = group.items.reduce((s, i) => s + (Number(i.redeemPoints || 0) * Number(i.qty || 0)), 0);
                const shippingValue = typeof state.shippingCost === 'number' ? state.shippingCost : 0;
                if (typeof state.shippingCost !== 'number') allShippingReady = false;
                if (state.discountAmount > subtotal) state.discountAmount = subtotal;
                const groupTaxSettings = taxSettingsFor(group.companyId);
                const taxableAmount = isRedeemCheckout ? 0 : Math.max(0, subtotal - state.discountAmount);
                const taxEnabled = !isRedeemCheckout && Boolean(groupTaxSettings?.enabled);
                const taxRate = taxEnabled ? Math.max(0, Math.min(100, Number(groupTaxSettings?.rate || 0))) : 0;
                const taxAmount = taxEnabled ? Math.round(taxableAmount * taxRate / 100) : 0;
                const groupTotal = isRedeemCheckout ? shippingValue : (taxableAmount + taxAmount + shippingValue);
                grandTotal += groupTotal;

                const shippingText = !hasSelectedAddress
                    ? 'Pilih alamat dulu'
                    : (typeof state.shippingCost !== 'number'
                        ? 'Menghitung...'
                        : (state.shippingCost === 0 ? 'Gratis' : 'Rp ' + state.shippingCost.toLocaleString('id-ID')));

                return `
                    <div class="rounded-xl border border-slate-200 p-3">
                        <p class="text-xs font-semibold text-slate-700 mb-2">${group.companyName}</p>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between text-slate-500">
                                <span>${isRedeemCheckout ? 'Point' : 'Subtotal'}</span>
                                <span>${isRedeemCheckout ? totalRedeemPoints.toLocaleString('id-ID') + ' point' : 'Rp ' + subtotal.toLocaleString('id-ID')}</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Ongkos Kirim</span>
                                <span>${shippingText}</span>
                            </div>
                            ${state.discountAmount > 0 && !isRedeemCheckout ? `
                            <div class="flex justify-between text-emerald-600">
                                <span>Diskon (${state.couponCode})</span>
                                <span>- Rp ${state.discountAmount.toLocaleString('id-ID')}</span>
                            </div>` : ''}
                            ${taxAmount > 0 ? `
                            <div class="flex justify-between text-slate-500">
                                <span>${groupTaxSettings?.name || 'PPN'} ${taxRate.toLocaleString('id-ID')}%</span>
                                <span>Rp ${taxAmount.toLocaleString('id-ID')}</span>
                            </div>` : ''}
                            <div class="flex justify-between font-semibold text-slate-800 pt-1 mt-1 border-t border-slate-100">
                                <span>Subtotal Toko</span>
                                <span>Rp ${groupTotal.toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    </div>`;
            }).join('');

            document.getElementById('summaryBreakdown').innerHTML = breakdownHtml || '<p class="text-xs text-slate-400">Keranjang kosong.</p>';
            document.getElementById('grandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
            document.getElementById('totalPaid').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

            const payBtn = document.getElementById('payBtn');
            const hintEl = document.getElementById('checkoutHintText');
            if (payBtn) {
                const shouldDisable = totalItems <= 0 || !hasSelectedAddress || !allShippingReady;
                payBtn.disabled = shouldDisable;
                payBtn.classList.toggle('opacity-60', shouldDisable);
                payBtn.classList.toggle('cursor-not-allowed', shouldDisable);
                payBtn.classList.toggle('pointer-events-none', shouldDisable);
            }
            if (hintEl) {
                if (totalItems <= 0) {
                    hintEl.textContent = isRedeemCheckout ? 'Pilih produk redeem terlebih dahulu untuk melanjutkan penukaran.' : 'Pilih produk terlebih dahulu untuk melanjutkan pembayaran.';
                } else if (!hasSelectedAddress) {
                    hintEl.textContent = 'Tambahkan atau pilih alamat pengiriman agar ongkos kirim bisa dihitung.';
                } else if (!allShippingReady) {
                    hintEl.textContent = 'Ongkos kirim belum tersedia untuk salah satu toko. Pilih alamat lain atau coba lagi.';
                } else {
                    hintEl.textContent = '';
                }
            }
        }

        async function applyGroupCoupon(companyId) {
            if (isRedeemCheckout) return;
            const input = document.getElementById(`couponInput-${companyId}`);
            const info = document.getElementById(`couponInfo-${companyId}`);
            const code = String(input?.value || '').trim().toUpperCase();
            if (!code) return;
            const state = getGroupState(companyId);
            const group = buildGroups().find((g) => g.companyId === companyId);
            const subtotal = group ? group.items.reduce((s, i) => s + i.price * i.qty, 0) : 0;

            info.className = 'text-xs text-slate-500';
            info.textContent = 'Memeriksa voucher...';
            info.classList.remove('hidden');

            try {
                const res = await fetch(couponApplyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ code, subtotal, company_id: companyId }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(json?.message || 'Voucher tidak valid.');
                state.couponCode = json.code || code;
                state.discountAmount = Number(json.discount_amount || 0);
                input.value = state.couponCode;
                info.className = 'text-xs text-emerald-600';
                info.textContent = `${json.message || 'Voucher digunakan'} (-Rp ${state.discountAmount.toLocaleString('id-ID')})`;
                updateSummary();
            } catch (e) {
                state.couponCode = '';
                state.discountAmount = 0;
                await fetch(`${couponRemoveUrl}?company_id=${companyId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                }).catch(() => {});
                info.className = 'text-xs text-red-500';
                info.textContent = e.message || 'Voucher tidak valid.';
                updateSummary();
            }
        }

        function setGroupShipping(companyId, el, cost, label) {
            const state = getGroupState(companyId);
            state.shippingCost = cost;
            state.shippingLabel = label;
            const container = document.getElementById(`shippingOptions-${companyId}`);
            container?.querySelectorAll('.shipping-card').forEach(c => {
                c.classList.remove('active', 'border-blue-400');
                c.classList.add('border-slate-200');
            });
            if (el) {
                el.classList.add('active', 'border-blue-400');
                el.classList.remove('border-slate-200');
                const radio = el.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            }
            updateSummary();
        }

        function renderGroupShippingOptions(companyId, list) {
            const container = document.getElementById(`shippingOptions-${companyId}`);
            const state = getGroupState(companyId);
            if (!container) return;
            if (!Array.isArray(list) || !list.length) {
                container.innerHTML =
                    `<div class="text-sm text-slate-500">Opsi pengiriman belum tersedia untuk alamat ini.</div>`;
                state.shippingCost = null;
                state.shippingLabel = '-';
                updateSummary();
                return;
            }

            container.innerHTML = list.map((item, idx) => {
                const label = `${(item.name || item.code || '').toUpperCase()} ${item.service || ''}`.trim();
                const etd = String(item.etd || '-');
                const cost = Number(item.cost || 0);
                return `
                    <label class="shipping-card ${idx === 0 ? 'active border-blue-400' : 'border-slate-200'} flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer hover:border-slate-300 transition-all"
                        onclick="setGroupShipping(${companyId}, this, ${cost}, '${label.replace(/'/g, "\\'")}')">
                        <input type="radio" name="shipping-${companyId}" class="accent-blue-500" ${idx === 0 ? 'checked' : ''} />
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-800 text-sm">${label}</p>
                                <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">${etd}</span>
                            </div>
                        </div>
                        <span class="font-semibold text-slate-800 text-xs">Rp ${cost.toLocaleString('id-ID')}</span>
                    </label>
                `;
            }).join('');

            const first = list[0];
            state.shippingCost = Number(first.cost || 0);
            state.shippingLabel = `${(first.name || first.code || '').toUpperCase()} ${first.service || ''}`.trim();
            updateSummary();
        }

        async function loadShippingOptions() {
            const checkedAddress = document.querySelector('input[name="address"]:checked');
            const destinationId = Number(checkedAddress?.dataset?.destinationId || 0);
            const groups = buildGroups();

            for (const group of groups) {
                const container = document.getElementById(`shippingOptions-${group.companyId}`);
                const state = getGroupState(group.companyId);
                if (!container) continue;

                if (!destinationId) {
                    container.innerHTML = `<div class="text-sm text-slate-500">Anda belum mengatur alamat pengiriman, Silahkan atur dibagian profil</div>`;
                    state.shippingCost = null;
                    state.shippingLabel = '-';
                    continue;
                }

                if (!group.companyId) {
                    container.innerHTML = `<div class="text-sm text-red-500">Produk ini belum terhubung ke perusahaan mana pun.</div>`;
                    state.shippingCost = null;
                    state.shippingLabel = '-';
                    continue;
                }

                container.innerHTML = `<div class="text-sm text-slate-500">Memuat opsi pengiriman...</div>`;
                const query = new URLSearchParams({
                    destination_id: String(destinationId),
                    weight: String(groupWeight(group)),
                    company_id: String(group.companyId),
                });

                try {
                    const res = await fetch(`${shippingOptionsUrl}?${query.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error('gagal');
                    const json = await res.json();
                    renderGroupShippingOptions(group.companyId, Array.isArray(json?.data) ? json.data : []);
                } catch (e) {
                    container.innerHTML = `<div class="text-sm text-red-500">Gagal memuat ongkir RajaOngkir.</div>`;
                    state.shippingCost = null;
                    state.shippingLabel = '-';
                }
            }
            updateSummary();
        }

        function setPaymentTab(tab) {
            ['qris', 'bank', 'manual'].forEach(t => {
                document.getElementById('panel-' + t).classList.add('hidden');
                document.getElementById('tab-' + t).className =
                    'payment-tab inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all';
            });
            document.getElementById('panel-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).className =
                'payment-tab inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-blue-500 text-white whitespace-nowrap transition-all';
        }

        function setPayment(method, label) {
            selectedPayment = method;
            selectedPaymentLabel = label || method.toUpperCase();
            document.getElementById('payMethod').textContent = selectedPaymentLabel;
            document.querySelectorAll('.payment-card').forEach(c => {
                c.classList.remove('active');
                c.style.borderColor = '';
            });
            const selectedRadio = document.querySelector(`input[name="payment"][value="${method}"]`);
            if (selectedRadio) {
                selectedRadio.checked = true;
                selectedRadio.closest('.payment-card')?.classList.add('active');
            }
        }

        function byCheckoutField(id) {
            return document.getElementById(id);
        }

        const _coMap = {
            'checkoutProvinceList': {
                dropdownId: 'checkoutProvinceDropdown',
                inputId: 'checkoutProvinceInput'
            },
            'checkoutCityList': {
                dropdownId: 'checkoutCityDropdown',
                inputId: 'checkoutCityInput'
            },
            'checkoutDistrictList': {
                dropdownId: 'checkoutDistrictDropdown',
                inputId: 'checkoutDistrictInput'
            },
            'checkoutSubdistrictList': {
                dropdownId: 'checkoutSubdistrictDropdown',
                inputId: 'checkoutSubdistrictInput'
            },
        };

        function _coShowDropdown(dropdownId, query) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown || !dropdown._items) return;
            const q = (query || '').toLowerCase().trim();
            const filtered = q ?
                dropdown._items.filter(item => (dropdown._toLabel(item) || '').toLowerCase().includes(q)) :
                dropdown._items;
            if (!filtered.length) {
                dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-slate-400 text-center">Tidak ada data</div>';
            } else {
                const toLabel = dropdown._toLabel;
                dropdown.innerHTML = filtered.map((item, i) =>
                    `<div class="co-opt px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-idx="${i}">${toLabel(item)}</div>`
                ).join('');
                dropdown.querySelectorAll('.co-opt').forEach((el, i) => {
                    el.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        const input = document.getElementById(dropdown._inputId);
                        if (input) {
                            input.value = toLabel(filtered[i]);
                            input.dispatchEvent(new Event('change'));
                        }
                        dropdown.classList.add('hidden');
                    });
                });
            }
            dropdown.classList.remove('hidden');
        }

        function _coSetup(inputId, dropdownId) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            if (!input || !dropdown) return;
            input.addEventListener('focus', () => {
                if (!input.disabled) _coShowDropdown(dropdownId, input.value);
            });
            input.addEventListener('input', () => {
                if (!input.disabled) _coShowDropdown(dropdownId, input.value);
            });
            input.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('hidden'), 200));
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') dropdown.classList.add('hidden');
            });
        }

        function _coSetLoading(inputId, isLoading) {
            const input = document.getElementById(inputId);
            const chevron = document.getElementById(inputId.replace('Input', 'Chevron'));
            const spinner = document.getElementById(inputId.replace('Input', 'Spinner'));
            if (input) input.disabled = isLoading;
            if (chevron) chevron.classList.toggle('hidden', isLoading);
            if (spinner) spinner.classList.toggle('hidden', !isLoading);
            if (isLoading) {
                const dd = document.getElementById(inputId.replace('Input', 'Dropdown'));
                if (dd) dd.classList.add('hidden');
            }
        }

        function _coSetEnabled(inputId, enabled) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.disabled = !enabled;
            if (enabled && input.dataset.placeholderEnabled) {
                input.placeholder = input.dataset.placeholderEnabled;
            }
            if (!enabled) {
                const dd = document.getElementById(inputId.replace('Input', 'Dropdown'));
                if (dd) dd.classList.add('hidden');
            }
        }

        function fillCheckoutDatalist(listId, items, type) {
            const info = _coMap[listId];
            if (!info) return;
            const dropdown = document.getElementById(info.dropdownId);
            if (!dropdown) return;
            const toLabel = (item) => String(item.label || item.name || '').trim();
            dropdown._items = items;
            dropdown._inputId = info.inputId;
            dropdown._toLabel = toLabel;
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
        }


        function resetCheckoutLocationFields(level) {
            const postalCodeInput = byCheckoutField('checkoutPostalCode');
            if (level === 'province') {
                ['checkoutCityInput', 'checkoutDistrictInput', 'checkoutSubdistrictInput'].forEach(id => {
                    const el = byCheckoutField(id);
                    if (el) el.value = '';
                });
                byCheckoutField('checkoutCityId').value = '';
                byCheckoutField('checkoutDistrictId').value = '';
                byCheckoutField('checkoutSubdistrictId').value = '';
                byCheckoutField('checkoutDestinationId').value = '';
                roCities = [];
                roDistricts = [];
                roSubdistricts = [];
                fillCheckoutDatalist('checkoutCityList', [], 'city');
                fillCheckoutDatalist('checkoutDistrictList', [], 'district');
                fillCheckoutDatalist('checkoutSubdistrictList', [], 'subdistrict');
                _coSetLoading('checkoutCityInput', false);
                _coSetEnabled('checkoutCityInput', false);
                _coSetLoading('checkoutDistrictInput', false);
                _coSetEnabled('checkoutDistrictInput', false);
                _coSetLoading('checkoutSubdistrictInput', false);
                _coSetEnabled('checkoutSubdistrictInput', false);
                if (postalCodeInput) postalCodeInput.value = '';
            }
            if (level === 'city') {
                ['checkoutDistrictInput', 'checkoutSubdistrictInput'].forEach(id => {
                    const el = byCheckoutField(id);
                    if (el) el.value = '';
                });
                byCheckoutField('checkoutDistrictId').value = '';
                byCheckoutField('checkoutSubdistrictId').value = '';
                byCheckoutField('checkoutDestinationId').value = '';
                roDistricts = [];
                roSubdistricts = [];
                fillCheckoutDatalist('checkoutDistrictList', [], 'district');
                fillCheckoutDatalist('checkoutSubdistrictList', [], 'subdistrict');
                _coSetLoading('checkoutDistrictInput', false);
                _coSetEnabled('checkoutDistrictInput', false);
                _coSetLoading('checkoutSubdistrictInput', false);
                _coSetEnabled('checkoutSubdistrictInput', false);
                if (postalCodeInput) postalCodeInput.value = '';
            }
            if (level === 'district') {
                const el = byCheckoutField('checkoutSubdistrictInput');
                if (el) el.value = '';
                byCheckoutField('checkoutSubdistrictId').value = '';
                byCheckoutField('checkoutDestinationId').value = '';
                roSubdistricts = [];
                fillCheckoutDatalist('checkoutSubdistrictList', [], 'subdistrict');
                _coSetLoading('checkoutSubdistrictInput', false);
                _coSetEnabled('checkoutSubdistrictInput', false);
                if (postalCodeInput) postalCodeInput.value = '';
            }
        }

        async function loadCheckoutProvinces() {
            if (roProvinces.length) {
                _coSetEnabled('checkoutProvinceInput', true);
                return;
            }
            _coSetLoading('checkoutProvinceInput', true);
            try {
                const res = await fetch(roProvincesUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                roProvinces = Array.isArray(json?.data) ? json.data : [];
                fillCheckoutDatalist('checkoutProvinceList', roProvinces, 'province');
            } finally {
                _coSetLoading('checkoutProvinceInput', false);
            }
        }

        async function onCheckoutProvinceChange() {
            const input = byCheckoutField('checkoutProvinceInput');
            const selected = roProvinces.find((item) => String(item.label || item.name || '') === String(input?.value ||
                '').trim());
            byCheckoutField('checkoutProvinceId').value = selected?.id ? String(selected.id) : '';
            resetCheckoutLocationFields('province');
            if (!selected?.id) return;
            _coSetLoading('checkoutCityInput', true);
            try {
                const res = await fetch(`${roCitiesUrl}?province_id=${encodeURIComponent(selected.id)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                roCities = Array.isArray(json?.data) ? json.data : [];
                fillCheckoutDatalist('checkoutCityList', roCities, 'city');
            } finally {
                _coSetLoading('checkoutCityInput', false);
                _coSetEnabled('checkoutCityInput', true);
            }
        }

        async function onCheckoutCityChange() {
            const input = byCheckoutField('checkoutCityInput');
            const selected = roCities.find((item) => String(item.label || item.name || '') === String(input?.value ||
                '').trim());
            byCheckoutField('checkoutCityId').value = selected?.id ? String(selected.id) : '';
            resetCheckoutLocationFields('city');
            if (!selected?.id) return;
            _coSetLoading('checkoutDistrictInput', true);
            try {
                const res = await fetch(`${roDistrictsUrl}?city_id=${encodeURIComponent(selected.id)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                roDistricts = Array.isArray(json?.data) ? json.data : [];
                fillCheckoutDatalist('checkoutDistrictList', roDistricts, 'district');
            } finally {
                _coSetLoading('checkoutDistrictInput', false);
                _coSetEnabled('checkoutDistrictInput', true);
            }
        }

        async function onCheckoutDistrictChange() {
            const input = byCheckoutField('checkoutDistrictInput');
            const selected = roDistricts.find((item) => String(item.label || item.name || '') === String(input?.value ||
                '').trim());
            byCheckoutField('checkoutDistrictId').value = selected?.id ? String(selected.id) : '';
            resetCheckoutLocationFields('district');
            if (!selected?.id) return;
            _coSetLoading('checkoutSubdistrictInput', true);
            try {
                const res = await fetch(`${roSubdistrictsUrl}?district_id=${encodeURIComponent(selected.id)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                roSubdistricts = Array.isArray(json?.data) ? json.data : [];
                fillCheckoutDatalist('checkoutSubdistrictList', roSubdistricts, 'subdistrict');
            } finally {
                _coSetLoading('checkoutSubdistrictInput', false);
                _coSetEnabled('checkoutSubdistrictInput', true);
            }
        }

        function onCheckoutSubdistrictChange() {
            const input = byCheckoutField('checkoutSubdistrictInput');
            const selected = roSubdistricts.find((item) => String(item.label || item.name || '') === String(input?.value ||
                '').replace(/\s*\([^)]*\)\s*$/, '').trim());
            byCheckoutField('checkoutSubdistrictId').value = selected?.id ? String(selected.id) : '';
            byCheckoutField('checkoutDestinationId').value = selected?.destination_id ? String(selected.destination_id) : (
                selected?.id ? String(selected.id) : '');
            const detectedPostal = String(selected?.zip_code || selected?.postal_code || '');
            if (detectedPostal) {
                byCheckoutField('checkoutPostalCode').value = detectedPostal;
            }
        }

        function showAddressModal() {
            const m = document.getElementById('addressModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
            loadCheckoutProvinces().catch(() => {});
        }

        function closeAddressModal() {
            const m = document.getElementById('addressModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        async function postForm(url, payload) {
            const body = new URLSearchParams();
            Object.entries(payload).forEach(([k, v]) => body.append(k, v ?? ''));
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body.toString(),
            });
            return res;
        }

        async function saveAddress() {
            const payload = {
                label: byCheckoutField('checkoutAddressLabel')?.value || 'Rumah',
                recipient_name: byCheckoutField('checkoutRecipientName')?.value || '',
                phone_country_code: byCheckoutField('checkoutPhoneCountryCode')?.value || '+62',
                phone_number: byCheckoutField('checkoutPhoneNumber')?.value || '',
                province_id: byCheckoutField('checkoutProvinceId')?.value || '',
                city_id: byCheckoutField('checkoutCityId')?.value || '',
                district_id: byCheckoutField('checkoutDistrictId')?.value || '',
                subdistrict_id: byCheckoutField('checkoutSubdistrictId')?.value || '',
                destination_id: byCheckoutField('checkoutDestinationId')?.value || '',
                province: byCheckoutField('checkoutProvinceInput')?.value || '',
                city: byCheckoutField('checkoutCityInput')?.value || '',
                district: byCheckoutField('checkoutDistrictInput')?.value || '',
                subdistrict: byCheckoutField('checkoutSubdistrictInput')?.value || '',
                postal_code: byCheckoutField('checkoutPostalCode')?.value || '',
                address_line: byCheckoutField('checkoutAddressLine')?.value || '',
                is_primary: byCheckoutField('checkoutIsPrimary')?.value || '0',
            };

            try {
                const response = await postForm(storeAddressUrl, payload);
                const json = await response.json().catch(() => null);
                if (!response.ok) {
                    const msg = json?.message || 'Gagal menambahkan alamat.';
                    throw new Error(msg);
                }
                window.location.reload();
            } catch (error) {
                alert(error?.message || 'Gagal menambahkan alamat.');
            }
        }

        function generateOrderNum() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) result += chars[Math.floor(Math.random() * chars.length)];
            return '#TK-2025-' + result;
        }

        function toggleTaxInvoiceForm() {
            const checked = Boolean(document.getElementById('taxInvoiceRequested')?.checked);
            document.getElementById('taxInvoiceForm')?.classList.toggle('hidden', !checked);
        }

        function applyTaxInvoiceProfile() {
            const profileId = Number(document.getElementById('taxInvoiceProfileId')?.value || 0);
            const profile = taxProfiles.find((item) => Number(item.id) === profileId);
            if (!profile) return;
            document.getElementById('taxpayerName').value = profile.taxpayer_name || '';
            document.getElementById('taxpayerNumber').value = profile.taxpayer_number || '';
            document.getElementById('taxpayerAddress').value = profile.taxpayer_address || '';
            document.getElementById('taxpayerEmail').value = profile.taxpayer_email || '';
        }

        function buildTaxInvoicePayload() {
            const requested = Boolean(document.getElementById('taxInvoiceRequested')?.checked);
            if (!requested) return { requested: false };

            const payload = {
                requested: true,
                profile_id: Number(document.getElementById('taxInvoiceProfileId')?.value || 0) || null,
                taxpayer_name: document.getElementById('taxpayerName')?.value.trim() || '',
                taxpayer_number: document.getElementById('taxpayerNumber')?.value.trim() || '',
                taxpayer_address: document.getElementById('taxpayerAddress')?.value.trim() || '',
                taxpayer_email: document.getElementById('taxpayerEmail')?.value.trim() || '',
                customer_note: document.getElementById('taxInvoiceNote')?.value.trim() || '',
                save_profile: Boolean(document.getElementById('saveTaxProfile')?.checked),
                set_default_profile: Boolean(document.getElementById('setDefaultTaxProfile')?.checked),
            };

            if (!payload.taxpayer_name || !payload.taxpayer_number || !payload.taxpayer_address || !payload.taxpayer_email) {
                throw new Error('Lengkapi nama NPWP, nomor NPWP, alamat NPWP, dan email penerima faktur pajak.');
            }

            return payload;
        }

        function payBtnIdleHtml() {
            return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>${isRedeemCheckout ? 'Bayar Ongkir & Tukar Point' : 'Bayar Sekarang'}`;
        }

        /**
         * Pola marketplace: klik "Bayar Sekarang" sekali membuat N Transaction sekaligus (satu per
         * perusahaan pemilik item di keranjang), lalu mengarahkan ke daftar pesanan -- customer bisa
         * membayar tiap pesanan independen. Endpoint per-company (Midtrans/manual) dipanggil
         * berurutan dari sini karena keduanya sudah company-aware sejak checkpoint sebelumnya; gagal
         * di satu perusahaan tidak membatalkan yang lain (lihat docs/prd-multi-company-foundation.md §4b).
         */
        async function processPayment() {
            if (!cartItems.length) {
                alert('Keranjang masih kosong. Silakan pilih produk terlebih dahulu.');
                return;
            }
            const groups = buildGroups();
            if (!isRedeemCheckout && groups.some((g) => typeof getGroupState(g.companyId).shippingCost !== 'number')) {
                alert('Ongkos kirim masih dihitung untuk salah satu toko. Silakan tunggu sebentar.');
                return;
            }
            let taxInvoicePayload = { requested: false };
            try {
                taxInvoicePayload = buildTaxInvoicePayload();
            } catch (error) {
                alert(error?.message || 'Data faktur pajak belum lengkap.');
                return;
            }

            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = `
        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        ${isRedeemCheckout ? 'Memproses Pembayaran Ongkir...' : `Memproses ${groups.length > 1 ? groups.length + ' Pesanan' : 'Pembayaran'}...`}`;

            const checkedAddress = document.querySelector('input[name="address"]:checked');
            const selectedAddressId = checkedAddress ? Number(checkedAddress.dataset.addressId || 0) : null;
            const checkoutUrl = selectedPayment === 'manual_transfer' ? manualPaymentUrl : midtransChargeUrl;

            const successes = [];
            const failures = [];

            for (const group of groups) {
                const state = getGroupState(group.companyId);
                const payload = {
                    items: group.items.map((i) => ({
                        id: i.id,
                        productVariantId: i.productVariantId || null,
                        companyId: i.companyId || null,
                        name: i.name,
                        variant: i.variant || '',
                        image: i.image || '',
                        note: i.note || '',
                        price: i.price,
                        qty: i.qty,
                        redeemPoints: Number(i.redeemPoints || 0),
                    })),
                    company_id: group.companyId,
                    shipping_cost: Number(state.shippingCost || 0),
                    shipping_label: String(state.shippingLabel || 'Reguler'),
                    address_id: selectedAddressId || null,
                    payment_method: selectedPayment,
                    tax_invoice: taxInvoicePayload,
                };

                try {
                    const res = await fetch(checkoutUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json?.message || `Gagal membuat pesanan untuk ${group.companyName}.`);
                    successes.push({ companyName: group.companyName, orderId: json?.order_id || '' });
                } catch (e) {
                    failures.push({ companyName: group.companyName, message: e?.message || 'Terjadi kesalahan.' });
                }
            }

            if (successes.length > 0) {
                await fetch(completeCheckoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                }).catch(() => {});

                if (failures.length > 0) {
                    alert(`${successes.length} pesanan berhasil dibuat. ${failures.length} gagal:\n` +
                        failures.map((f) => `- ${f.companyName}: ${f.message}`).join('\n'));
                }

                const ids = successes.map((s) => s.orderId).filter(Boolean).join(',');
                window.location.href = `${checkoutOrdersUrl}?ids=${encodeURIComponent(ids)}`;
                return;
            }

            btn.disabled = false;
            btn.innerHTML = payBtnIdleHtml();
            alert(`Gagal membuat pesanan:\n` + failures.map((f) => `- ${f.companyName}: ${f.message}`).join('\n'));
        }

        function formatCard(input) {
            let val = input.value.replace(/\D/g, '').substring(0, 16);
            input.value = val.replace(/(.{4})/g, '$1 ').trim();
        }

        function syncSelectedAddressCard() {
            const addressRadios = document.querySelectorAll('input[name="address"]');
            addressRadios.forEach((radio) => {
                const card = radio.closest('.address-card');
                if (!card) return;
                if (radio.checked) {
                    card.classList.add('border-blue-400', 'bg-blue-50');
                    card.classList.remove('border-slate-200');
                } else {
                    card.classList.remove('border-blue-400', 'bg-blue-50');
                    card.classList.add('border-slate-200');
                }
            });
        }

        renderCart();
        setPaymentTab('qris');
        setPayment('qris', 'QRIS');
        _coSetup('checkoutProvinceInput', 'checkoutProvinceDropdown');
        _coSetup('checkoutCityInput', 'checkoutCityDropdown');
        _coSetup('checkoutDistrictInput', 'checkoutDistrictDropdown');
        _coSetup('checkoutSubdistrictInput', 'checkoutSubdistrictDropdown');
        document.getElementById('checkoutProvinceInput')?.addEventListener('change', onCheckoutProvinceChange);
        document.getElementById('checkoutCityInput')?.addEventListener('change', onCheckoutCityChange);
        document.getElementById('checkoutDistrictInput')?.addEventListener('change', onCheckoutDistrictChange);
        document.getElementById('checkoutSubdistrictInput')?.addEventListener('change', onCheckoutSubdistrictChange);
        document.querySelectorAll('input[name="address"]').forEach((el) => {
            el.addEventListener('change', () => {
                syncSelectedAddressCard();
                loadShippingOptions();
            });
        });
        syncSelectedAddressCard();
        loadShippingOptions();
    </script>
@endsection

