@extends('layouts.user')

@section('title', 'Checkout - Ecommerce Citra')
@section('body_class', 'bg-slate-50 text-slate-800 overflow-x-hidden')

@section('style')
    <style>
        * {
            font-family: 'Poppins', sans-serif;
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
    </style>
@endsection

@section('content')
    <!-- NAVBAR -->
    @include('partials.navbar-user')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- LEFT COL -->
            <div class="lg:col-span-2 space-y-6">

                <!-- CART ITEMS -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Keranjang Belanja
                        </h2>
                        <span class="text-sm text-slate-500" id="itemCountText">3 item</span>
                    </div>
                    <div id="cartItems" class="divide-y divide-slate-100 p-4 space-y-0"></div>
                    <div
                        class="px-6 py-3 bg-slate-50 flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="accent-blue-500" checked /> <span
                                class="text-sm text-slate-600">Pilih semua</span>
                        </label>
                        <button class="text-red-400 hover:text-red-500 text-sm font-medium">Hapus dipilih</button>
                    </div>
                </div>

                <!-- ALAMAT PENGIRIMAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Alamat Pengiriman
                        </h2>
                        <button onclick="showAddressModal()" class="text-blue-600 text-sm font-medium hover:text-blue-700">+
                            Tambah Alamat</button>
                    </div>
                    <div class="p-6 space-y-3" id="addressList">
                        <label
                            class="flex items-start gap-3 p-4 border-2 border-blue-400 bg-blue-50 rounded-xl cursor-pointer">
                            <input type="radio" name="address" class="mt-1 accent-blue-500" checked />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-semibold text-slate-800">Andi Pratama</p>
                                    <span
                                        class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">Utama</span>
                                </div>
                                <p class="text-sm text-slate-600">0812-3456-7890</p>
                                <p class="text-sm text-slate-600">Jl. Sudirman No. 123, Kel. Karet Semanggi, Kec. Setiabudi,
                                    Jakarta Selatan, DKI Jakarta, 12920</p>
                            </div>
                            <button class="text-xs text-blue-600 hover:underline flex-shrink-0">Ubah</button>
                        </label>
                        <label
                            class="flex items-start gap-3 p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-slate-300 transition-colors">
                            <input type="radio" name="address" class="mt-1 accent-blue-500" />
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800 mb-1">Kantor</p>
                                <p class="text-sm text-slate-600">0812-3456-7890</p>
                                <p class="text-sm text-slate-600">Gedung Menara BRI Lt. 5, Jl. Gatot Subroto, Jakarta
                                    Selatan</p>
                            </div>
                            <button class="text-xs text-blue-600 hover:underline flex-shrink-0">Ubah</button>
                        </label>
                    </div>
                </div>

                <!-- METODE PENGIRIMAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                            Metode Pengiriman
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <label
                            class="shipping-card active flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                            onclick="setShipping(15000, 'Reguler')">
                            <input type="radio" name="shipping" class="accent-blue-500" checked />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="font-semibold text-slate-800">JNE Reguler</p>
                                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">2-3 Hari</span>
                                </div>
                                <p class="text-sm text-slate-500">Estimasi tiba 20-22 Jan 2025</p>
                            </div>
                            <span class="font-semibold text-slate-800 text-sm">Rp 15.000</span>
                        </label>
                        <label
                            class="shipping-card flex items-center gap-4 p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-slate-300 transition-all"
                            onclick="setShipping(25000, 'Ekspres')">
                            <input type="radio" name="shipping" class="accent-blue-500" />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="font-semibold text-slate-800">JNE Ekspres</p>
                                    <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded">1 Hari</span>
                                </div>
                                <p class="text-sm text-slate-500">Estimasi tiba 19 Jan 2025</p>
                            </div>
                            <span class="font-semibold text-slate-800 text-sm">Rp 25.000</span>
                        </label>
                        <label
                            class="shipping-card flex items-center gap-4 p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-slate-300 transition-all"
                            onclick="setShipping(0, 'Gratis')">
                            <input type="radio" name="shipping" class="accent-blue-500" />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="font-semibold text-slate-800">Gratis Ongkir</p>
                                    <span
                                        class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded font-semibold">GRATIS</span>
                                </div>
                                <p class="text-sm text-slate-500">2-5 Hari • Syarat & Ketentuan berlaku</p>
                            </div>
                            <span class="font-semibold text-blue-600 text-sm">Rp 0</span>
                        </label>
                        <label
                            class="shipping-card flex items-center gap-4 p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-slate-300 transition-all"
                            onclick="setShipping(35000, 'Same Day')">
                            <input type="radio" name="shipping" class="accent-blue-500" />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="font-semibold text-slate-800">GoSend Same Day</p>
                                    <span class="text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded font-semibold">⚡
                                        Instan</span>
                                </div>
                                <p class="text-sm text-slate-500">Tiba hari ini, pesan sebelum 14.00 WIB</p>
                            </div>
                            <span class="font-semibold text-slate-800 text-sm">Rp 35.000</span>
                        </label>
                    </div>
                </div>

                <!-- METODE PEMBAYARAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Metode Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Tabs -->
                        <div class="flex gap-2 mb-5 overflow-x-auto">
                            <button onclick="setPaymentTab('ewallet')" id="tab-ewallet"
                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-500 text-white whitespace-nowrap transition-all">E-Wallet</button>
                            <button onclick="setPaymentTab('bank')" id="tab-bank"
                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all">Transfer
                                Bank</button>
                            <button onclick="setPaymentTab('card')" id="tab-card"
                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all">Kartu
                                Kredit/Debit</button>
                            <button onclick="setPaymentTab('cod')" id="tab-cod"
                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all">COD</button>
                        </div>

                        <!-- E-Wallet -->
                        <div id="panel-ewallet" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label onclick="setPayment('GoPay')"
                                class="payment-card active flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" checked />
                                <div
                                    class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-sm font-bold text-blue-700">
                                    G</div>
                                <span class="text-sm font-semibold text-slate-700">GoPay</span>
                            </label>
                            <label onclick="setPayment('OVO')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <div
                                    class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-sm font-bold text-purple-700">
                                    O</div>
                                <span class="text-sm font-semibold text-slate-700">OVO</span>
                            </label>
                            <label onclick="setPayment('DANA')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <div
                                    class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-sm font-bold text-blue-700">
                                    D</div>
                                <span class="text-sm font-semibold text-slate-700">DANA</span>
                            </label>
                            <label onclick="setPayment('ShopeePay')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <div
                                    class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center text-sm font-bold text-orange-700">
                                    S</div>
                                <span class="text-sm font-semibold text-slate-700">ShopeePay</span>
                            </label>
                            <label onclick="setPayment('LinkAja')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <div
                                    class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-sm font-bold text-red-700">
                                    L</div>
                                <span class="text-sm font-semibold text-slate-700">LinkAja</span>
                            </label>
                        </div>

                        <!-- Bank Transfer -->
                        <div id="panel-bank" class="hidden grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label onclick="setPayment('BCA')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <span class="font-bold text-blue-700">BCA</span>
                            </label>
                            <label onclick="setPayment('Mandiri')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <span class="font-bold text-yellow-700">Mandiri</span>
                            </label>
                            <label onclick="setPayment('BNI')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <span class="font-bold text-orange-700">BNI</span>
                            </label>
                            <label onclick="setPayment('BRI')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <span class="font-bold text-blue-600">BRI</span>
                            </label>
                            <label onclick="setPayment('CIMB')"
                                class="payment-card flex items-center gap-3 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all">
                                <input type="radio" name="payment" class="accent-blue-500" />
                                <span class="font-bold text-red-700">CIMB</span>
                            </label>
                        </div>

                        <!-- Kartu -->
                        <div id="panel-card" class="hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-medium text-slate-600 mb-1 block">Nomor Kartu</label>
                                    <input type="text" placeholder="1234 5678 9012 3456" maxlength="19"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                        oninput="formatCard(this)" />
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-slate-600 mb-1 block">Nama Pemegang
                                        Kartu</label>
                                    <input type="text" placeholder="Nama sesuai kartu"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-slate-600 mb-1 block">Berlaku Hingga</label>
                                    <input type="text" placeholder="MM/YY" maxlength="5"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-slate-600 mb-1 block">CVV</label>
                                    <input type="text" placeholder="123" maxlength="3"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                                </div>
                            </div>
                        </div>

                        <!-- COD -->
                        <div id="panel-cod" class="hidden">
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                                <span class="text-2xl">💵</span>
                                <div>
                                    <p class="font-semibold text-amber-800 mb-1">Bayar di Tempat (COD)</p>
                                    <p class="text-sm text-amber-700">Siapkan uang pas saat kurir tiba. Tersedia untuk area
                                        Jabodetabek dan kota-kota besar.</p>
                                    <p class="text-sm text-amber-600 mt-1 font-medium">Biaya COD: Rp 5.000</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VOUCHER -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Voucher & Promo
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-3 mb-4">
                            <input type="text" id="voucherInput" placeholder="Masukkan kode voucher..."
                                class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <button onclick="applyVoucher()"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Pakai</button>
                        </div>
                        <div id="voucherMsg" class="hidden"></div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button onclick="useVoucher('HEMAT50')"
                                class="flex items-start gap-3 p-3 border border-dashed border-blue-300 bg-blue-50 rounded-xl text-left hover:border-blue-400 transition-colors">
                                <div
                                    class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-lg flex-shrink-0">
                                    🎁</div>
                                <div>
                                    <p class="text-xs font-bold text-blue-700">HEMAT50</p>
                                    <p class="text-xs text-slate-600">Diskon Rp 50.000 min. belanja Rp 200.000</p>
                                    <p class="text-xs text-red-400 mt-0.5">Berakhir 31 Jan 2025</p>
                                </div>
                            </button>
                            <button onclick="useVoucher('ONGKIR0')"
                                class="flex items-start gap-3 p-3 border border-dashed border-blue-300 bg-blue-50 rounded-xl text-left hover:border-blue-400 transition-colors">
                                <div
                                    class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-lg flex-shrink-0">
                                    🚚</div>
                                <div>
                                    <p class="text-xs font-bold text-blue-700">ONGKIR0</p>
                                    <p class="text-xs text-slate-600">Gratis ongkir ke seluruh Indonesia</p>
                                    <p class="text-xs text-red-400 mt-0.5">Berakhir 28 Feb 2025</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CATATAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Catatan untuk Penjual
                    </h2>
                    <textarea placeholder="Contoh: Tolong dibungkus rapi sebagai hadiah..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-20"></textarea>
                </div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 sticky top-24">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-800">Ringkasan Pesanan</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Subtotal (<span id="sumItems">3 item</span>)</span>
                            <span class="font-medium text-slate-800" id="subtotalAmt">Rp 947.000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Ongkos Kirim</span>
                            <span class="font-medium text-slate-800" id="shippingAmt">Rp 15.000</span>
                        </div>
                        <div class="flex justify-between text-sm" id="discRow">
                            <span class="text-slate-600">Diskon Produk</span>
                            <span class="font-medium text-red-500">-Rp 74.000</span>
                        </div>
                        <div class="flex justify-between text-sm hidden" id="voucherRow">
                            <span class="text-slate-600">Voucher</span>
                            <span class="font-medium text-red-500" id="voucherAmt">-Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Asuransi Pengiriman</span>
                            <span class="font-medium text-blue-600">Gratis</span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="font-bold text-slate-800">Grand Total</span>
                                <span class="font-extrabold text-blue-600 text-xl" id="grandTotal">Rp 888.000</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Termasuk pajak dan biaya lainnya</p>
                        </div>

                        <!-- Info -->
                        <div class="bg-blue-50 rounded-xl p-3 flex gap-2 items-start">
                            <span class="text-blue-500 mt-0.5">ℹ️</span>
                            <p class="text-xs text-blue-700">Transaksi dilindungi sistem keamanan Ecommerce Citra. Uang
                                dikembalikan jika barang tidak sampai.</p>
                        </div>

                        <!-- Bayar -->
                        <button onclick="processPayment()" id="payBtn"
                            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-blue-200 hover:shadow-blue-300 flex items-center justify-center gap-2 mt-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Bayar Sekarang
                        </button>

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
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 modal-enter max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-slate-800 text-lg">Tambah Alamat Baru</h3>
                <button onclick="closeAddressModal()" class="text-slate-400 hover:text-slate-600 text-xl">✕</button>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Nama Penerima *</label>
                        <input type="text" placeholder="Nama lengkap"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">No. Telepon *</label>
                        <input type="text" placeholder="08xx-xxxx-xxxx"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Label Alamat</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="px-4 py-1.5 rounded-full border-2 border-blue-400 text-blue-600 text-xs font-semibold">Rumah</button>
                        <button
                            class="px-4 py-1.5 rounded-full border border-slate-200 text-slate-600 text-xs font-semibold hover:border-slate-300">Kantor</button>
                        <button
                            class="px-4 py-1.5 rounded-full border border-slate-200 text-slate-600 text-xs font-semibold hover:border-slate-300">Lainnya</button>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Provinsi *</label>
                    <select
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400">
                        <option>Pilih Provinsi</option>
                        <option>DKI Jakarta</option>
                        <option>Jawa Barat</option>
                        <option>Jawa Tengah</option>
                        <option>Jawa Timur</option>
                        <option>Banten</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kota/Kabupaten *</label>
                        <input type="text" placeholder="Kota"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kode Pos *</label>
                        <input type="text" placeholder="12345"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400" />
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Alamat Lengkap *</label>
                    <textarea placeholder="Nama jalan, nomor, RT/RW, kelurahan, kecamatan..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-20"></textarea>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="accent-blue-500" />
                    <span class="text-sm text-slate-600">Jadikan sebagai alamat utama</span>
                </label>
                <div class="flex gap-3 pt-2">
                    <button onclick="closeAddressModal()"
                        class="flex-1 border border-slate-200 text-slate-600 font-semibold py-3 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button onclick="saveAddress()"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition-colors">Simpan
                        Alamat</button>
                </div>
            </div>
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
            <p class="text-slate-600 mb-5">Terima kasih sudah berbelanja di Ecommerce Citra. Pesananmu sedang diproses!</p>

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
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Estimasi Tiba</span>
                        <span class="font-medium text-slate-700">20 - 22 Jan 2025</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-3 mb-6 text-sm text-blue-700 flex gap-2">
                <span>📱</span>
                <span>Notifikasi status pesanan akan dikirim ke WhatsApp dan email kamu.</span>
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
        const cartItems = [{
                id: 1,
                name: "Kemeja Oxford Slim Fit Premium",
                variant: "Biru Navy • M",
                price: 189000,
                origPrice: 270000,
                qty: 1,
                image: "https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=100&h=100&fit=crop"
            },
            {
                id: 2,
                name: "Sneakers Urban Street",
                variant: "Hitam • Size 42",
                price: 459000,
                origPrice: 650000,
                qty: 1,
                image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=100&h=100&fit=crop"
            },
            {
                id: 3,
                name: "Skincare Serum Vitamin C",
                variant: "30ml • 1 Box",
                price: 189000,
                origPrice: 250000,
                qty: 2,
                image: "https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=100&h=100&fit=crop"
            },
        ];

        let shippingCost = 15000;
        let shippingLabel = 'Reguler';
        let voucherDiscount = 0;
        let selectedPayment = 'GoPay';

        function renderCart() {
            const container = document.getElementById('cartItems');
            container.innerHTML = cartItems.map((item, i) => `
        <div class="relative flex gap-3 py-4 pr-7 ${i > 0 ? 'border-t border-slate-100' : ''}">
          <input type="checkbox" class="mt-10 sm:mt-1 accent-blue-500 flex-shrink-0" checked />
          <img src="${item.image}" alt="${item.name}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-slate-800 text-sm line-clamp-2 mb-0.5">${item.name}</p>
            <p class="text-xs text-slate-500 mb-2">${item.variant}</p>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
              <div>
                <span class="font-bold text-slate-900 text-sm">Rp ${item.price.toLocaleString('id-ID')}</span>
                <span class="text-xs text-slate-400 line-through ml-1">Rp ${item.origPrice.toLocaleString('id-ID')}</span>
              </div>
              <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden self-start sm:self-auto">
                <button class="qty-btn px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm" onclick="changeQty(${i}, -1)">−</button>
                <span class="px-3 py-1 text-sm font-semibold border-x border-slate-200">${item.qty}</span>
                <button class="qty-btn px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm" onclick="changeQty(${i}, 1)">+</button>
              </div>
            </div>
          </div>
          <button onclick="removeItem(${i})" class="absolute top-4 right-0 sm:static text-slate-300 hover:text-red-400 transition-colors flex-shrink-0 self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>`).join('');
            updateSummary();
        }

        function changeQty(idx, d) {
            cartItems[idx].qty = Math.max(1, cartItems[idx].qty + d);
            renderCart();
        }

        function removeItem(idx) {
            cartItems.splice(idx, 1);
            renderCart();
        }

        function updateSummary() {
            const subtotal = cartItems.reduce((s, i) => s + i.price * i.qty, 0);
            const productDiscount = cartItems.reduce((s, i) => s + (i.origPrice - i.price) * i.qty, 0);
            const grandTotal = subtotal + shippingCost - voucherDiscount;
            const totalItems = cartItems.reduce((s, i) => s + i.qty, 0);
            document.getElementById('itemCountText').textContent = totalItems + ' item';
            document.getElementById('sumItems').textContent = totalItems + ' item';
            document.getElementById('subtotalAmt').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('shippingAmt').textContent = shippingCost === 0 ? 'Gratis' : 'Rp ' + shippingCost
                .toLocaleString('id-ID');
            document.getElementById('grandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
            document.getElementById('totalPaid').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        function setShipping(cost, label) {
            shippingCost = cost;
            shippingLabel = label;
            document.querySelectorAll('.shipping-card').forEach(c => {
                c.classList.remove('active', 'border-blue-400');
                c.classList.add('border-slate-200');
            });
            event.currentTarget.classList.add('active', 'border-blue-400');
            event.currentTarget.classList.remove('border-slate-200');
            updateSummary();
        }

        function setPaymentTab(tab) {
            ['ewallet', 'bank', 'card', 'cod'].forEach(t => {
                document.getElementById('panel-' + t).classList.add('hidden');
                document.getElementById('tab-' + t).className =
                    'px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 whitespace-nowrap transition-all';
            });
            document.getElementById('panel-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).className =
                'px-4 py-2 rounded-xl text-sm font-semibold bg-blue-500 text-white whitespace-nowrap transition-all';
        }

        function setPayment(method) {
            selectedPayment = method;
            document.getElementById('payMethod').textContent = method;
            document.querySelectorAll('.payment-card').forEach(c => {
                c.classList.remove('active');
                c.style.borderColor = '';
            });
        }

        function applyVoucher() {
            const code = document.getElementById('voucherInput').value.trim().toUpperCase();
            applyVoucherCode(code);
        }

        function useVoucher(code) {
            document.getElementById('voucherInput').value = code;
            applyVoucherCode(code);
        }

        function applyVoucherCode(code) {
            const msg = document.getElementById('voucherMsg');
            msg.classList.remove('hidden');
            if (code === 'HEMAT50') {
                voucherDiscount = 50000;
                msg.innerHTML =
                    '<div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-700 flex gap-2 mb-3"><span>✅</span> Voucher berhasil! Hemat Rp 50.000</div>';
                document.getElementById('voucherRow').classList.remove('hidden');
                document.getElementById('voucherAmt').textContent = '-Rp 50.000';
            } else if (code === 'ONGKIR0') {
                shippingCost = 0;
                msg.innerHTML =
                    '<div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-700 flex gap-2 mb-3"><span>✅</span> Voucher berhasil! Gratis ongkir diterapkan.</div>';
            } else {
                voucherDiscount = 0;
                msg.innerHTML =
                    '<div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-600 flex gap-2 mb-3"><span>❌</span> Kode voucher tidak valid atau sudah kadaluarsa.</div>';
            }
            updateSummary();
        }

        function showAddressModal() {
            const m = document.getElementById('addressModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeAddressModal() {
            const m = document.getElementById('addressModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        function saveAddress() {
            closeAddressModal();
            alert('✅ Alamat berhasil disimpan!');
        }

        function generateOrderNum() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) result += chars[Math.floor(Math.random() * chars.length)];
            return '#TK-2025-' + result;
        }

        function processPayment() {
            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = `
        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Memproses Pembayaran...`;

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML =
                    `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Bayar Sekarang`;
                const orderNum = generateOrderNum();
                document.getElementById('orderNum').textContent = orderNum;
                document.getElementById('payMethod').textContent = selectedPayment;
                const modal = document.getElementById('successModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }, 2500);
        }

        function formatCard(input) {
            let val = input.value.replace(/\D/g, '').substring(0, 16);
            input.value = val.replace(/(.{4})/g, '$1 ').trim();
        }

        renderCart();
    </script>
@endsection
