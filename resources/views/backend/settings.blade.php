@extends('layouts.app')

@section('title', 'Store Settings')

@section('content')
    @php
        $activeTab = in_array(request('tab', 'store'), ['store', 'location', 'payment', 'tax', 'whatsapp', 'social'], true) ? request('tab', 'store') : 'store';
        $logoPath = (string) ($storeSettings['store_logo_path'] ?? '');
        $logoUrl = $logoPath !== '' ? asset('storage/' . ltrim($logoPath, '/')) : null;
        $waGatewayRoutes = [
            'update' => route('whatsapp-gateway.update'),
            'prepare' => route('whatsapp-gateway.prepare'),
            'connect' => route('whatsapp-gateway.connect'),
            'disconnect' => route('whatsapp-gateway.disconnect'),
            'status' => route('whatsapp-gateway.status'),
            'qr' => route('whatsapp-gateway.qr'),
            'qrRaw' => route('whatsapp-gateway.qr-raw'),
            'usage' => route('whatsapp-gateway.usage'),
        ];
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Store Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola profil toko, lokasi asal pengiriman, dan rekening transfer manual.</p>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="lg:w-60 flex-shrink-0">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-2">
                    <nav class="space-y-0.5">
                        <button type="button" onclick="showTab('store')" id="nav-store"
                            class="settings-tab {{ $activeTab === 'store' ? 'active' : '' }} w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
                            <i data-lucide="store" class="w-[17px] h-[17px]"></i>
                            Store
                        </button>
                        <button type="button" onclick="showTab('location')" id="nav-location"
                            class="settings-tab {{ $activeTab === 'location' ? 'active' : '' }} w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
                            <i data-lucide="map-pin" class="w-[17px] h-[17px]"></i>
                            Store Location
                        </button>
                        <button type="button" onclick="showTab('payment')" id="nav-payment"
                            class="settings-tab {{ $activeTab === 'payment' ? 'active' : '' }} w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
                            <i data-lucide="credit-card" class="w-[17px] h-[17px]"></i>
                            Manual Payment
                        </button>
                        <button type="button" onclick="showTab('tax')" id="nav-tax"
                            class="settings-tab {{ $activeTab === 'tax' ? 'active' : '' }} w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
                            <i data-lucide="percent" class="w-[17px] h-[17px]"></i>
                            Pajak / PPN
                        </button>
                        <button type="button" onclick="showTab('whatsapp')" id="nav-whatsapp"
                            class="settings-tab {{ $activeTab === 'whatsapp' ? 'active' : '' }} w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
                            <i data-lucide="message-circle" class="w-[17px] h-[17px]"></i>
                            WhatsApp Gateway
                        </button>
                        <button type="button" onclick="showTab('social')" id="nav-social"
                            class="settings-tab {{ $activeTab === 'social' ? 'active' : '' }} w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
                            <i data-lucide="share-2" class="w-[17px] h-[17px]"></i>
                            Social Media
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1 space-y-5">
                <div id="tab-store" class="settings-content {{ $activeTab === 'store' ? '' : 'hidden' }}">
                    <form method="POST" action="{{ route('pages.settings.update') }}" enctype="multipart/form-data"
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        @csrf
                        <input type="hidden" name="section" value="store">

                        <h2 class="font-bold text-slate-800 dark:text-white mb-1">Profil Toko</h2>
                        <p class="text-xs text-slate-400 mb-6">Atur nama toko dan logo yang dipakai sebagai identitas toko.</p>

                        <div class="flex flex-col sm:flex-row gap-5 mb-6 pb-6 border-b border-slate-100 dark:border-slate-700">
                            <div class="w-24 h-24 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 flex items-center justify-center overflow-hidden">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="Logo toko" class="w-full h-full object-contain p-2">
                                @else
                                    <span class="text-xl font-extrabold text-blue-600">{{ strtoupper(substr($storeSettings['store_name'] ?? 'Citra', 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Logo Toko</label>
                                <input type="file" name="store_logo" accept="image/*"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                <p class="text-xs text-slate-400 mt-1.5">Format gambar, maksimal 2 MB. Logo lama tetap dipakai kalau tidak upload file baru.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Nama Toko</label>
                            <input type="text" name="store_name" value="{{ old('store_name', $storeSettings['store_name'] ?? 'Ecommerce Citra') }}" required
                                class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                        </div>

                        <div class="flex justify-end mt-5">
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Profil Toko</button>
                        </div>
                    </form>
                </div>

                <div id="tab-location" class="settings-content {{ $activeTab === 'location' ? '' : 'hidden' }}">
                    <form action="{{ route('store-locations.update') }}" method="POST"
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="settings">
                        <input type="hidden" name="province_id" id="provinceId" value="{{ old('province_id', $location?->province_id) }}">
                        <input type="hidden" name="city_id" id="cityId" value="{{ old('city_id', $location?->city_id) }}">
                        <input type="hidden" name="city_name" id="cityName" value="{{ old('city_name', $location?->city_name) }}">
                        <input type="hidden" name="province_name" id="provinceName" value="{{ old('province_name', $location?->province_name) }}">

                        <h2 class="font-bold text-slate-800 dark:text-white mb-1">Store Location</h2>
                        <p class="text-xs text-slate-400 mb-6">Lokasi ini dipakai sebagai asal pengiriman untuk perhitungan ongkos kirim checkout.</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Label Lokasi</label>
                                <input type="text" name="label" value="{{ old('label', $location?->label ?? 'Lokasi Toko Utama') }}"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Provinsi</label>
                                    <div class="relative">
                                        <input type="text" id="provinceSearchInput" value="{{ old('province_name', $location?->province_name) }}"
                                            placeholder="Ketik atau pilih provinsi..."
                                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                        <div id="provinceSearchDropdown"
                                            class="hidden absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-56 overflow-y-auto">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Asal Kota</label>
                                    <div class="relative">
                                        <input type="text" id="citySearchInput" value="{{ old('city_name', $location?->city_name) }}"
                                            placeholder="Ketik atau pilih kota..."
                                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                        <div id="citySearchDropdown"
                                            class="hidden absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-56 overflow-y-auto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Lokasi</button>
                        </div>
                    </form>
                </div>

                <div id="tab-whatsapp" class="settings-content {{ $activeTab === 'whatsapp' ? '' : 'hidden' }}">
                    <div class="space-y-5">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                                        </span>
                                        <div>
                                            <h2 class="font-bold text-slate-800 dark:text-white">WhatsApp Gateway</h2>
                                            <p class="text-xs text-slate-400">Gateway: <span id="waGatewayLabel">{{ $waGateway['storeId'] ?? 'boq-ecommerce' }}</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span id="waStatusBadge" class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">Memuat status</span>
                                    <button type="button" onclick="refreshWaGateway()" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                        Refresh
                                    </button>
                                </div>
                            </div>

                            @unless ($waGateway['configured'] ?? false)
                                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                    WA_GATEWAY_URL atau WA_GATEWAY_TOKEN belum lengkap di ENV. Isi konfigurasi dulu agar tombol gateway bisa dipakai.
                                </div>
                            @endunless
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
                            <div class="xl:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                                    <button id="waPrepareBtn" type="button" onclick="prepareWaGateway()" class="inline-flex items-center justify-center gap-2 min-h-12 px-4 py-3 text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                                        Siapkan
                                    </button>
                                    <button id="waConnectBtn" type="button" onclick="connectWaGateway()" class="inline-flex items-center justify-center gap-2 min-h-12 px-4 py-3 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
                                        <i data-lucide="plug" class="w-4 h-4"></i>
                                        Hubungkan
                                    </button>
                                    <button id="waDisconnectBtn" type="button" onclick="disconnectWaGateway()" class="inline-flex items-center justify-center gap-2 min-h-12 px-4 py-3 text-sm font-bold rounded-xl border border-red-200 dark:border-red-400/40 text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <i data-lucide="unplug" class="w-4 h-4"></i>
                                        Putuskan
                                    </button>
                                </div>

                                <div id="waQrHeader" class="flex items-center justify-between mb-5">
                                    <div>
                                        <h3 class="font-bold text-slate-800 dark:text-white">QR WhatsApp</h3>
                                        <p id="waQrHint" class="text-xs text-slate-400 mt-1">Siapkan atau hubungkan gateway, lalu scan QR dari WhatsApp.</p>
                                    </div>
                                    <button id="waQrRefreshBtn" type="button" onclick="loadWaQr()" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                        QR
                                    </button>
                                </div>

                                <div id="waQrPanel" class="min-h-[360px] rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 flex items-center justify-center p-5">
                                    <img id="waQrImage" alt="QR WhatsApp Gateway" class="hidden w-full max-w-sm rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <div id="waQrEmpty" class="text-center max-w-sm">
                                        <div class="mx-auto mb-3 w-12 h-12 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400">
                                            <i data-lucide="qr-code" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">QR belum dimuat</p>
                                        <p class="text-xs text-slate-400 mt-1">Klik Hubungkan atau QR untuk mengambil kode scan terbaru.</p>
                                    </div>
                                </div>
                                <div id="waConnectedPanel" class="hidden min-h-[240px] rounded-2xl border border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-900/15 flex items-center justify-center p-6 text-center">
                                    <div class="max-w-sm">
                                        <div class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-300">
                                            <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                                        </div>
                                        <p class="text-base font-bold text-slate-800 dark:text-white">WhatsApp sudah terhubung</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">QR disembunyikan otomatis. Gunakan Putuskan jika ingin mengganti perangkat.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                                <h3 class="font-bold text-slate-800 dark:text-white mb-1">Sisa Kuota Pesan</h3>
                                <p class="text-xs text-slate-400 mb-5">Pemakaian dikirim dari endpoint usage gateway.</p>
                                <div class="space-y-4">
                                    @foreach ([
                                        'minute' => ['label' => 'Menit Ini', 'limit' => $waGateway['limits']['perMinute'] ?? 10],
                                        'day' => ['label' => 'Hari Ini', 'limit' => $waGateway['limits']['perDay'] ?? 200],
                                        'month' => ['label' => 'Bulan Ini', 'limit' => $waGateway['limits']['perMonth'] ?? 3000],
                                    ] as $key => $meta)
                                        <div>
                                            <div class="flex items-center justify-between gap-3 text-sm mb-2">
                                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $meta['label'] }}</span>
                                                <span id="waUsage{{ ucfirst($key) }}Text" class="text-xs font-semibold text-slate-500 dark:text-slate-400">0 / {{ $meta['limit'] }}</span>
                                            </div>
                                            <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                                <div id="waUsage{{ ucfirst($key) }}Bar" class="h-full w-0 rounded-full bg-emerald-500 transition-all"></div>
                                            </div>
                                            <p id="waUsage{{ ucfirst($key) }}Remaining" class="text-xs text-slate-400 mt-1">Sisa {{ $meta['limit'] }} pesan</p>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="waGatewayMessage" class="mt-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                    Belum ada aktivitas gateway.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-social" class="settings-content {{ $activeTab === 'social' ? '' : 'hidden' }}">
                    <form method="POST" action="{{ route('pages.settings.update') }}"
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        @csrf
                        <input type="hidden" name="section" value="social_media">

                        <h2 class="font-bold text-slate-800 dark:text-white mb-1">Social Media</h2>
                        <p class="text-xs text-slate-400 mb-6">Isi URL profil / halaman toko yang ingin ditampilkan. Kosongkan kolom yang tidak dipakai.</p>

                        {{-- Marketplace --}}
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-3">Marketplace</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 pb-6 border-b border-slate-100 dark:border-slate-700">
                            @foreach ([
                                'social_shopee'    => ['Shopee',    'https://shopee.co.id/namatoko'],
                                'social_tokopedia' => ['Tokopedia', 'https://www.tokopedia.com/namatoko'],
                                'social_lazada'    => ['Lazada',    'https://www.lazada.co.id/shop/namatoko'],
                            ] as $key => [$label, $placeholder])
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">{{ $label }}</label>
                                    <input type="url" name="{{ $key }}"
                                        value="{{ old($key, $storeSettings[$key] ?? '') }}"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                </div>
                            @endforeach
                        </div>

                        {{-- Social --}}
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-3">Sosial Media</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ([
                                'social_instagram' => ['Instagram', 'https://www.instagram.com/namatoko'],
                                'social_tiktok'    => ['TikTok',    'https://www.tiktok.com/@namatoko'],
                                'social_facebook'  => ['Facebook',  'https://www.facebook.com/namatoko'],
                                'social_twitter'   => ['X / Twitter', 'https://x.com/namatoko'],
                                'social_youtube'   => ['YouTube',   'https://www.youtube.com/@namatoko'],
                                'social_whatsapp'  => ['WhatsApp',  'https://wa.me/628123456789'],
                            ] as $key => [$label, $placeholder])
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">{{ $label }}</label>
                                    <input type="url" name="{{ $key }}"
                                        value="{{ old($key, $storeSettings[$key] ?? '') }}"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-5">
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Social Media</button>
                        </div>
                    </form>
                </div>

                <div id="tab-payment" class="settings-content {{ $activeTab === 'payment' ? '' : 'hidden' }}">
                    <form method="POST" action="{{ route('pages.settings.update') }}"
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        @csrf
                        <input type="hidden" name="section" value="manual_payment">

                        <h2 class="font-bold text-slate-800 dark:text-white mb-1">Manual Payment</h2>
                        <p class="text-xs text-slate-400 mb-6">Rekening ini ditampilkan ke customer saat memilih transfer manual.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Bank</label>
                                <input type="text" name="manual_payment_bank_name" value="{{ old('manual_payment_bank_name', $storeSettings['manual_payment_bank_name'] ?? 'BCA') }}" required
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Nomor Rekening</label>
                                <input type="text" name="manual_payment_account_number" value="{{ old('manual_payment_account_number', $storeSettings['manual_payment_account_number'] ?? '1234567890') }}" required
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Atas Nama</label>
                                <input type="text" name="manual_payment_account_name" value="{{ old('manual_payment_account_name', $storeSettings['manual_payment_account_name'] ?? 'Ecommerce Citra') }}" required
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Instruksi Transfer</label>
                                <textarea name="manual_payment_instruction" rows="4"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 resize-none">{{ old('manual_payment_instruction', $storeSettings['manual_payment_instruction'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end mt-5">
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Pembayaran</button>
                        </div>
                    </form>
                </div>

                <div id="tab-tax" class="settings-content {{ $activeTab === 'tax' ? '' : 'hidden' }}">
                    <form method="POST" action="{{ route('pages.settings.update') }}"
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        @csrf
                        <input type="hidden" name="section" value="tax">

                        <h2 class="font-bold text-slate-800 dark:text-white mb-1">Pajak / PPN</h2>
                        <p class="text-xs text-slate-400 mb-6">PPN dihitung dari subtotal produk setelah diskon. Ongkir tidak dikenakan PPN.</p>

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-4 py-3">
                                <input type="hidden" name="tax_enabled" value="0">
                                <input type="checkbox" name="tax_enabled" value="1"
                                    class="accent-blue-600"
                                    {{ filter_var(old('tax_enabled', $storeSettings['tax_enabled'] ?? '1'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Aktifkan PPN</span>
                                    <span class="block text-xs text-slate-400">Nonaktifkan jika checkout tidak perlu menambahkan pajak.</span>
                                </span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Nama Pajak</label>
                                    <input type="text" name="tax_name" maxlength="30" value="{{ old('tax_name', $storeSettings['tax_name'] ?? 'PPN') }}"
                                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Persentase</label>
                                    <input type="number" name="tax_rate" min="0" max="100" step="0.01" value="{{ old('tax_rate', $storeSettings['tax_rate'] ?? '11.00') }}"
                                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-5">
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Pajak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        function showTab(tab) {
            document.querySelectorAll('.settings-content').forEach((el) => el.classList.add('hidden'));
            document.querySelectorAll('.settings-tab').forEach((el) => el.classList.remove('active'));
            document.getElementById('tab-' + tab)?.classList.remove('hidden');
            document.getElementById('nav-' + tab)?.classList.add('active');

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());

            if (tab === 'whatsapp') {
                refreshWaGateway();
            }
        }

        const waGatewayRoutes = @json($waGatewayRoutes);
        const waGatewayLimits = @json($waGateway['limits'] ?? ['perMinute' => 10, 'perDay' => 200, 'perMonth' => 3000]);
        const csrfToken = @json(csrf_token());
        let waGatewayState = 'unknown';
        let waStatusPollTimer = null;
        let waStatusPollCount = 0;

        function setWaMessage(message, tone = 'slate') {
            const el = document.getElementById('waGatewayMessage');
            if (!el) return;
            const tones = {
                slate: 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400',
                success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                warning: 'border-amber-200 bg-amber-50 text-amber-700',
                danger: 'border-red-200 bg-red-50 text-red-700',
            };
            el.className = `mt-5 rounded-xl px-4 py-3 text-xs ${tones[tone] || tones.slate}`;
            el.textContent = message || 'Belum ada aktivitas gateway.';
        }

        function setWaStatus(label, tone = 'slate') {
            const el = document.getElementById('waStatusBadge');
            if (!el) return;
            const tones = {
                slate: 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
                success: 'bg-emerald-100 text-emerald-700',
                warning: 'bg-amber-100 text-amber-700',
                danger: 'bg-red-100 text-red-700',
            };
            el.className = `inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ${tones[tone] || tones.slate}`;
            el.textContent = label;
        }

        function setButtonDisabled(id, disabled) {
            const button = document.getElementById(id);
            if (!button) return;
            button.disabled = disabled;
            button.classList.toggle('opacity-50', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
        }

        function updateWaGatewayUiState(state) {
            waGatewayState = state || 'unknown';
            const connected = waGatewayState === 'connected';
            const scanning = waGatewayState === 'scanning';
            const qrHeader = document.getElementById('waQrHeader');
            const qrPanel = document.getElementById('waQrPanel');
            const connectedPanel = document.getElementById('waConnectedPanel');
            const qrImage = document.getElementById('waQrImage');
            const qrEmpty = document.getElementById('waQrEmpty');
            const hint = document.getElementById('waQrHint');

            qrHeader?.classList.toggle('hidden', connected);
            qrPanel?.classList.toggle('hidden', connected);
            qrPanel?.classList.toggle('flex', !connected);
            connectedPanel?.classList.toggle('hidden', !connected);
            connectedPanel?.classList.toggle('flex', connected);

            setButtonDisabled('waConnectBtn', connected);
            setButtonDisabled('waPrepareBtn', connected);
            setButtonDisabled('waQrRefreshBtn', connected);
            setButtonDisabled('waDisconnectBtn', !connected && !scanning);

            if (connected) {
                stopWaStatusPolling();
                if (qrImage) {
                    qrImage.src = '';
                    qrImage.classList.add('hidden');
                }
                qrEmpty?.classList.remove('hidden');
                setWaMessage('WhatsApp sudah terhubung. QR otomatis disembunyikan.', 'success');
            } else if (scanning && hint) {
                hint.textContent = 'Scan QR ini lewat WhatsApp di ponsel admin. Status dicek otomatis.';
            }

            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        }

        function stopWaStatusPolling() {
            if (waStatusPollTimer) {
                clearInterval(waStatusPollTimer);
                waStatusPollTimer = null;
            }
            waStatusPollCount = 0;
        }

        function startWaStatusPolling() {
            stopWaStatusPolling();
            waStatusPollTimer = setInterval(async () => {
                waStatusPollCount += 1;
                await loadWaStatus(false);
                if (waGatewayState === 'connected' || waStatusPollCount >= 40) {
                    stopWaStatusPolling();
                }
            }, 3000);
        }

        async function waFetch(url, options = {}) {
            const res = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.method && options.method !== 'GET' ? {
                        'X-CSRF-TOKEN': csrfToken
                    } : {}),
                    ...(options.headers || {}),
                },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.success === false) {
                throw new Error(json.message || 'WA Gateway gagal memproses permintaan.');
            }
            return json;
        }

        function normalizeWaStatus(data) {
            const raw = String(data?.status || data?.state || data?.connection || data?.data?.status || '').toLowerCase();
            const connected = data?.connected === true || data?.isConnected === true || ['connected', 'open', 'ready', 'authenticated'].includes(raw);
            const qr = ['qr', 'scan', 'pairing', 'connecting'].some((key) => raw.includes(key));
            if (connected) return ['Terhubung', 'success', 'connected'];
            if (qr) return ['Menunggu scan QR', 'warning', 'scanning'];
            if (raw) return [raw.replace(/_/g, ' '), 'slate', 'disconnected'];
            return ['Belum terhubung', 'slate', 'disconnected'];
        }

        function findQrValue(data) {
            const candidates = [
                data?.qr,
                data?.qrcode,
                data?.qrCode,
                data?.image,
                data?.base64,
                data?.data?.qr,
                data?.data?.qrcode,
                data?.data?.qrCode,
                data?.data?.image,
                data?.data?.base64,
            ].filter(Boolean);
            return candidates.length ? String(candidates[0]) : '';
        }

        function setQrImage(src) {
            const img = document.getElementById('waQrImage');
            const empty = document.getElementById('waQrEmpty');
            if (!img || !empty) return;
            updateWaGatewayUiState('scanning');
            img.src = src;
            img.classList.remove('hidden');
            empty.classList.add('hidden');
        }

        async function prepareWaGateway() {
            try {
                setWaMessage('Menyiapkan toko di WA Gateway...', 'slate');
                const json = await waFetch(waGatewayRoutes.prepare, {
                    method: 'POST'
                });
                setWaMessage(json.message, 'success');
                refreshWaGateway();
            } catch (error) {
                setWaMessage(error.message, 'danger');
            }
        }

        async function connectWaGateway() {
            try {
                setWaMessage('Menghubungkan sesi WhatsApp...', 'slate');
                const json = await waFetch(waGatewayRoutes.connect, {
                    method: 'POST'
                });
                setWaMessage(json.message, 'success');
                await loadWaQr();
                await refreshWaGateway();
                if (waGatewayState !== 'connected') {
                    startWaStatusPolling();
                }
            } catch (error) {
                setWaMessage(error.message, 'danger');
            }
        }

        async function disconnectWaGateway() {
            try {
                setWaMessage('Memutuskan sesi WhatsApp...', 'slate');
                const json = await waFetch(waGatewayRoutes.disconnect, {
                    method: 'POST'
                });
                setWaMessage(json.message, 'success');
                stopWaStatusPolling();
                updateWaGatewayUiState('disconnected');
                refreshWaGateway();
            } catch (error) {
                setWaMessage(error.message, 'danger');
            }
        }

        async function loadWaStatus(showError = true) {
            try {
                const json = await waFetch(waGatewayRoutes.status);
                const [label, tone, state] = normalizeWaStatus(json.data || {});
                setWaStatus(label, tone);
                updateWaGatewayUiState(state);
            } catch (error) {
                setWaStatus('Status gagal dimuat', 'danger');
                updateWaGatewayUiState('unknown');
                if (showError) {
                    setWaMessage(error.message, 'danger');
                }
            }
        }

        async function loadWaQr() {
            const hint = document.getElementById('waQrHint');
            try {
                if (hint) hint.textContent = 'Mengambil QR terbaru dari gateway...';
                const json = await waFetch(waGatewayRoutes.qr);
                const qr = findQrValue(json.data || {});
                if (qr) {
                    const src = qr.startsWith('data:image') ? qr : `data:image/png;base64,${qr}`;
                    setQrImage(src);
                } else {
                    setQrImage(`${waGatewayRoutes.qrRaw}?t=${Date.now()}`);
                }
                startWaStatusPolling();
            } catch (error) {
                setWaMessage(error.message, 'warning');
                setQrImage(`${waGatewayRoutes.qrRaw}?t=${Date.now()}`);
                startWaStatusPolling();
            }
        }

        function readUsageBucket(data, key) {
            const bucket = data?.[key] || data?.usage?.[key] || data?.limits?.[key] || {};
            const aliases = {
                minute: ['minute', 'perMinute', 'currentMinute'],
                day: ['day', 'perDay', 'today'],
                month: ['month', 'perMonth', 'currentMonth'],
            };
            const used = Number(bucket.used ?? bucket.count ?? data?.used?.[key] ?? data?.usage?.[aliases[key]?.[1]] ?? 0);
            const fallbackLimit = key === 'minute' ? waGatewayLimits.perMinute : key === 'day' ? waGatewayLimits.perDay : waGatewayLimits.perMonth;
            const limit = Number(bucket.limit ?? data?.limit?.[key] ?? data?.limits?.[aliases[key]?.[1]] ?? fallbackLimit ?? 0);
            const remaining = Number(bucket.remaining ?? Math.max(0, limit - used));
            return {
                used: Number.isFinite(used) ? used : 0,
                limit: Number.isFinite(limit) && limit > 0 ? limit : 0,
                remaining: Number.isFinite(remaining) ? remaining : 0,
            };
        }

        function renderUsage(key, bucket) {
            const name = key.charAt(0).toUpperCase() + key.slice(1);
            const text = document.getElementById(`waUsage${name}Text`);
            const bar = document.getElementById(`waUsage${name}Bar`);
            const remaining = document.getElementById(`waUsage${name}Remaining`);
            const percent = bucket.limit > 0 ? Math.min(100, Math.round((bucket.used / bucket.limit) * 100)) : 0;
            if (text) text.textContent = `${bucket.used} / ${bucket.limit}`;
            if (bar) bar.style.width = `${percent}%`;
            if (remaining) remaining.textContent = `Sisa ${bucket.remaining} pesan`;
        }

        async function loadWaUsage() {
            try {
                const json = await waFetch(waGatewayRoutes.usage);
                ['minute', 'day', 'month'].forEach((key) => renderUsage(key, readUsageBucket(json.data || {}, key)));
            } catch (error) {
                setWaMessage(error.message, 'warning');
            }
        }

        async function refreshWaGateway() {
            await loadWaStatus();
            loadWaUsage();
        }

        if (@json($activeTab) === 'whatsapp') {
            refreshWaGateway();
        }

        const provincesUrl = @json(route('store-locations.provinces'));
        const citiesUrl = @json(route('store-locations.cities'));
        const provinceSearchInput = document.getElementById('provinceSearchInput');
        const provinceSearchDropdown = document.getElementById('provinceSearchDropdown');
        const citySearchInput = document.getElementById('citySearchInput');
        const citySearchDropdown = document.getElementById('citySearchDropdown');
        const provinceIdField = document.getElementById('provinceId');
        const cityIdField = document.getElementById('cityId');
        const cityNameField = document.getElementById('cityName');
        const provinceNameField = document.getElementById('provinceName');
        let provinceSearchTimeout = null;
        let citySearchTimeout = null;
        let allProvinces = [];
        let provinceCities = [];

        function hideDropdown(dropdown) {
            if (!dropdown) return;
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
        }

        function hideAllDropdowns() {
            hideDropdown(provinceSearchDropdown);
            hideDropdown(citySearchDropdown);
        }

        function selectProvince(item) {
            provinceSearchInput.value = item.name || item.province_name || '';
            provinceIdField.value = String(item.id || item.province_id || '');
            provinceNameField.value = String(item.name || item.province_name || '');
            citySearchInput.value = '';
            cityIdField.value = '';
            cityNameField.value = '';
            provinceCities = [];
            hideDropdown(provinceSearchDropdown);
            loadCitiesByProvince().catch(() => {});
        }

        function selectCity(item) {
            citySearchInput.value = item.label || item.city_name || '';
            cityIdField.value = String(item.city_id || '');
            cityNameField.value = String(item.city_name || item.label || '');
            hideDropdown(citySearchDropdown);
        }

        function renderProvinceDropdown(items, keyword) {
            const q = String(keyword || '').trim().toLowerCase();
            const filtered = (Array.isArray(items) ? items : []).filter((item) => {
                const name = String(item.name || item.province_name || '').toLowerCase();
                return q === '' || name.includes(q);
            });

            if (!filtered.length) {
                provinceSearchDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-slate-400">Provinsi tidak ditemukan</div>';
                provinceSearchDropdown.classList.remove('hidden');
                return;
            }

            provinceSearchDropdown.innerHTML = filtered.map((item) => `
                <button type="button" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors">
                    ${item.name || item.province_name || ''}
                </button>
            `).join('');
            provinceSearchDropdown.querySelectorAll('button').forEach((btn, idx) => {
                btn.addEventListener('click', () => selectProvince(filtered[idx]));
            });
            provinceSearchDropdown.classList.remove('hidden');
        }

        function renderCityDropdown(items, keyword) {
            const q = String(keyword || '').trim().toLowerCase();
            const filtered = (Array.isArray(items) ? items : [])
                .filter((item) => {
                    const haystack = `${item.label || ''} ${item.city_name || ''} ${item.province_name || ''}`.toLowerCase();
                    return q === '' || haystack.includes(q);
                })
                .filter((item, index, arr) => arr.findIndex((x) => String(x.city_id) === String(item.city_id)) === index);

            if (!filtered.length) {
                citySearchDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-slate-400">Kota tidak ditemukan</div>';
                citySearchDropdown.classList.remove('hidden');
                return;
            }

            citySearchDropdown.innerHTML = filtered.map((item) => `
                <button type="button" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors">
                    ${item.label || item.city_name || ''}
                </button>
            `).join('');
            citySearchDropdown.querySelectorAll('button').forEach((btn, idx) => {
                btn.addEventListener('click', () => selectCity(filtered[idx]));
            });
            citySearchDropdown.classList.remove('hidden');
        }

        async function loadProvinces() {
            if (allProvinces.length) return;
            const res = await fetch(provincesUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json().catch(() => ({}));
            allProvinces = Array.isArray(json.data) ? json.data : [];
        }

        async function loadCitiesByProvince() {
            const provinceId = Number(provinceIdField?.value || 0);
            if (!provinceId) {
                provinceCities = [];
                return;
            }

            const res = await fetch(`${citiesUrl}?province_id=${encodeURIComponent(provinceId)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json().catch(() => ({}));
            provinceCities = Array.isArray(json.data) ? json.data : [];
        }

        async function searchProvinces(keyword) {
            await loadProvinces();
            renderProvinceDropdown(allProvinces, keyword);
        }

        async function searchCities(keyword) {
            if (!provinceIdField?.value) {
                citySearchDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-slate-400">Pilih provinsi terlebih dahulu</div>';
                citySearchDropdown.classList.remove('hidden');
                return;
            }
            if (!provinceCities.length) {
                await loadCitiesByProvince();
            }
            renderCityDropdown(provinceCities, keyword);
        }

        provinceSearchInput?.addEventListener('input', function() {
            provinceIdField.value = '';
            provinceNameField.value = '';
            citySearchInput.value = '';
            cityIdField.value = '';
            cityNameField.value = '';
            provinceCities = [];
            clearTimeout(provinceSearchTimeout);
            provinceSearchTimeout = setTimeout(() => searchProvinces(this.value), 250);
        });

        provinceSearchInput?.addEventListener('focus', function() {
            searchProvinces(this.value || '');
        });

        citySearchInput?.addEventListener('input', function() {
            cityIdField.value = '';
            cityNameField.value = '';
            clearTimeout(citySearchTimeout);
            citySearchTimeout = setTimeout(() => searchCities(this.value), 250);
        });

        citySearchInput?.addEventListener('focus', function() {
            searchCities(this.value || '');
        });

        document.addEventListener('click', function(e) {
            if (!citySearchInput || !citySearchDropdown || !provinceSearchInput || !provinceSearchDropdown) return;
            const onProvince = provinceSearchInput.contains(e.target) || provinceSearchDropdown.contains(e.target);
            const onCity = citySearchInput.contains(e.target) || citySearchDropdown.contains(e.target);
            if (!onProvince && !onCity) hideAllDropdowns();
        });

        if (provinceIdField?.value) {
            loadCitiesByProvince().catch(() => {});
        }
    </script>
@endsection
