@extends('layouts.app')

@section('title', 'Store Location')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Store Location</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Atur kota asal toko untuk perhitungan ongkos kirim checkout.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('store-locations.update') }}" method="POST"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 max-w-3xl">
            @csrf
            @method('PUT')

            <input type="hidden" name="province_id" id="provinceId" value="{{ old('province_id', $location?->province_id) }}" />
            <input type="hidden" name="city_id" id="cityId" value="{{ old('city_id', $location?->city_id) }}" />
            <input type="hidden" name="city_name" id="cityName" value="{{ old('city_name', $location?->city_name) }}" />
            <input type="hidden" name="province_name" id="provinceName" value="{{ old('province_name', $location?->province_name) }}" />

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Label Lokasi</label>
                    <input type="text" name="label" value="{{ old('label', $location?->label ?? 'Lokasi Toko Utama') }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Provinsi</label>
                    <div class="relative">
                        <input type="text" id="provinceSearchInput"
                            value="{{ old('province_name', $location?->province_name) }}"
                            placeholder="Ketik atau pilih provinsi..."
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                        <div id="provinceSearchDropdown"
                            class="hidden absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-56 overflow-y-auto">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Asal Kota</label>
                    <div class="relative">
                        <input type="text" id="citySearchInput"
                            value="{{ old('city_name', $location?->city_name) }}"
                            placeholder="Ketik atau pilih kota..."
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                        <div id="citySearchDropdown"
                            class="hidden absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-56 overflow-y-auto">
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5">Kota ini akan digunakan sebagai titik asal untuk menghitung ongkos kirim checkout.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                    Simpan Lokasi
                </button>
            </div>
        </form>

        @if (session('success'))
            <div id="toast" class="fixed bottom-6 right-6 z-50">
                <div class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('script')
    <script>
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
            provinceSearchInput.value = item.name;
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
            citySearchInput.value = item.label;
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
                provinceSearchDropdown.innerHTML =
                    '<div class="px-3 py-2 text-sm text-slate-400">Provinsi tidak ditemukan</div>';
                provinceSearchDropdown.classList.remove('hidden');
                return;
            }
            provinceSearchDropdown.innerHTML = filtered.map((item) => `
                <button type="button" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors">
                    ${item.name || item.province_name || ''}
                </button>
            `).join('');
            provinceSearchDropdown.querySelectorAll('button').forEach((btn, idx) => {
                const src = filtered[idx];
                btn.addEventListener('click', () => selectProvince(src));
            });
            provinceSearchDropdown.classList.remove('hidden');
        }

        function renderCityDropdown(items, keyword) {
            const normalizedKeyword = String(keyword || '').trim().toLowerCase();
            const filtered = (Array.isArray(items) ? items : [])
                .filter((item) => {
                    if (!normalizedKeyword) return true;
                    const haystack = `${item.label || ''} ${item.city_name || ''} ${item.province_name || ''}`
                        .toLowerCase();
                    return haystack.includes(normalizedKeyword);
                })
                .filter((item, index, arr) => arr.findIndex((x) => String(x.city_id) === String(item.city_id)) === index);

            if (!filtered.length) {
                citySearchDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-slate-400">Kota tidak ditemukan</div>';
                citySearchDropdown.classList.remove('hidden');
                return;
            }
            citySearchDropdown.innerHTML = filtered.map((item) => `
                <button type="button"
                    class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors">
                    ${item.label}
                </button>
            `).join('');

            citySearchDropdown.querySelectorAll('button').forEach((btn, idx) => {
                const src = filtered[idx];
                btn.addEventListener('click', () => selectCity(src));
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
            const provinceId = Number(provinceIdField.value || 0);
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
            const q = String(keyword || '').trim();
            if (!provinceIdField.value) {
                citySearchDropdown.innerHTML =
                    '<div class="px-3 py-2 text-sm text-slate-400">Pilih provinsi terlebih dahulu</div>';
                citySearchDropdown.classList.remove('hidden');
                return;
            }
            if (!provinceCities.length) {
                await loadCitiesByProvince();
            }
            if (q.length < 1) {
                hideDropdown(citySearchDropdown);
                return;
            }
            renderCityDropdown(provinceCities, q);
        }

        function clearCityFields() {
            cityIdField.value = '';
            cityNameField.value = '';
        }

        function clearProvinceAndCity() {
            provinceIdField.value = '';
            provinceNameField.value = '';
            provinceCities = [];
            clearCityFields();
        }

        function legacyHideDropdown() {
            citySearchDropdown.classList.add('hidden');
            citySearchDropdown.innerHTML = '';
        }

        provinceSearchInput?.addEventListener('input', function() {
            clearProvinceAndCity();
            clearTimeout(provinceSearchTimeout);
            provinceSearchTimeout = setTimeout(() => searchProvinces(this.value), 250);
        });

        provinceSearchInput?.addEventListener('focus', function() {
            searchProvinces(this.value || '');
        });

        citySearchInput?.addEventListener('input', function() {
            clearCityFields();
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
            if (!onProvince && !onCity) {
                hideAllDropdowns();
            }
        });

        if (provinceIdField.value) {
            loadCitiesByProvince().catch(() => {});
        }

        const toast = document.getElementById('toast');
        if (toast) setTimeout(() => toast.remove(), 3000);
    </script>
@endsection
