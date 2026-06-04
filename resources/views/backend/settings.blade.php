@extends('layouts.app')

@section('title', 'Store Settings')

@section('content')
    @php
        $activeTab = in_array(request('tab', 'store'), ['store', 'location', 'payment', 'tax', 'social'], true) ? request('tab', 'store') : 'store';
        $logoPath = (string) ($storeSettings['store_logo_path'] ?? '');
        $logoUrl = $logoPath !== '' ? asset('storage/' . ltrim($logoPath, '/')) : null;
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
