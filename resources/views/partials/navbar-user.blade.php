<nav class="bg-white sticky top-0 z-50 shadow-sm border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('frontend.index') }}" class="flex items-center gap-2 flex-shrink-0">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                        <path d="M16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                    </svg>
                </div>
                <span
                    class="text-lg sm:text-xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Ecommerce
                    Citra</span>
            </a>

            <div class="hidden md:flex flex-1 max-w-xl mx-6 relative items-center gap-2">
                <button id="ecCategoryTrigger" type="button"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
                    Kategori
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div class="relative flex-1">
                    <form action="{{ route('frontend.search') }}" method="GET"
                        class="flex border border-slate-200 rounded-xl overflow-hidden focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                        <input type="text" id="ecNavSearchDesktop" name="q"
                            value="{{ trim(request('q', $query ?? '')) }}" placeholder="Cari produk, merek, kategori..."
                            class="flex-1 px-4 py-2.5 text-sm outline-none bg-white" autocomplete="off" />
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>
                    <div id="ecNavSearchDropdownDesktop"
                        class="hidden absolute top-full left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                    </div>
                </div>

                <div id="ecCategoryDropdown"
                    class="hidden absolute top-full left-0 mt-2 w-[820px] max-w-[88vw] bg-white rounded-2xl shadow-xl border border-slate-100 z-50">
                    <div class="grid grid-cols-5 min-h-[360px]">
                        <div class="col-span-1 border-r border-slate-100 p-4 overflow-y-auto">
                            <h4 class="text-sm font-semibold text-slate-800 mb-3">Semua Kategori</h4>
                            <div id="ecMegaCategoryMenu" class="space-y-1"></div>
                            <a href="{{ route('frontend.kategori') }}"
                                class="mt-4 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium px-3 py-2">
                                Lihat Semua
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                        <div id="ecMegaCategoryContent" class="col-span-4 p-6 overflow-y-auto"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                <button id="ecMobileSearchToggle" class="md:hidden p-2 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button class="hidden sm:flex p-2 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
                <a href="{{ route('frontend.checkout') }}" class="p-2 rounded-lg hover:bg-slate-100 relative">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span
                        class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">3</span>
                </a>
                <a href="{{ route('frontend.profil') }}"
                    class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100">
                    <div
                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                        A</div>
                    <span class="hidden sm:block text-sm font-medium text-slate-700">Andi</span>
                </a>
            </div>
        </div>
    </div>

    <div id="ecMobileSearch" class="hidden md:hidden px-4 pb-3 border-t border-slate-100 pt-3">
        <form action="{{ route('frontend.search') }}" method="GET"
            class="flex border border-slate-200 rounded-xl overflow-hidden focus-within:border-blue-400">
            <input type="text" id="ecNavSearchMobile" name="q" value="{{ trim(request('q', $query ?? '')) }}"
                placeholder="Cari produk..." class="flex-1 px-4 py-2.5 text-sm outline-none" autocomplete="off" />
            <button type="submit" class="bg-blue-500 text-white px-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </form>
        <div id="ecNavSearchDropdownMobile"
            class="hidden mt-1 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden"></div>
    </div>
</nav>

<script>
    (() => {
        if (window.__ecNavbarReady) return;
        window.__ecNavbarReady = true;

        const detailUrl = @json(route('frontend.detail-produk'));
        const products = [{
                name: 'Kemeja Oxford Slim Fit',
                meta: 'Fashion Pria',
                image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=120&h=120&fit=crop'
            },
            {
                name: 'Sneakers Urban Street',
                meta: 'Sepatu',
                image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&h=120&fit=crop'
            },
            {
                name: 'Wireless Earbuds Pro',
                meta: 'Elektronik',
                image: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=120&h=120&fit=crop'
            },
            {
                name: 'Skincare Serum Vitamin C',
                meta: 'Kecantikan',
                image: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=120&h=120&fit=crop'
            },
            {
                name: 'Kamera Mirrorless Entry',
                meta: 'Elektronik',
                image: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=120&h=120&fit=crop'
            },
            {
                name: 'Blender Portable Mini',
                meta: 'Rumah & Dapur',
                image: 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=120&h=120&fit=crop'
            },
            {
                name: 'Hoodie Oversized Fleece',
                meta: 'Fashion Pria',
                image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=120&h=120&fit=crop'
            },
            {
                name: 'Yoga Mat Premium',
                meta: 'Olahraga',
                image: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=120&h=120&fit=crop'
            }
        ];

        const megaCategories = [{
                key: 'rumah-tangga',
                name: 'Rumah Tangga',
                columns: [{
                        title: 'Dekorasi',
                        items: ['Hiasan Dinding', 'Jam Dinding', 'Lilin Aroma', 'Karpet Ruang']
                    },
                    {
                        title: 'Kamar Mandi',
                        items: ['Cermin Kamar Mandi', 'Dispenser Sabun', 'Rak Toilet', 'Handuk Mandi']
                    },
                    {
                        title: 'Kebutuhan Rumah',
                        items: ['Baterai', 'Humidifier', 'Payung', 'Termometer']
                    },
                    {
                        title: 'Tempat Penyimpanan',
                        items: ['Box Plastik', 'Keranjang', 'Rak Serbaguna', 'Lemari Kecil']
                    }
                ]
            },
            {
                key: 'fashion-pria',
                name: 'Fashion Pria',
                columns: [{
                        title: 'Atasan',
                        items: ['Kemeja', 'Kaos', 'Polo Shirt', 'Hoodie']
                    },
                    {
                        title: 'Bawahan',
                        items: ['Celana Chino', 'Jeans', 'Jogger', 'Celana Pendek']
                    },
                    {
                        title: 'Aksesoris',
                        items: ['Topi', 'Ikat Pinggang', 'Dompet', 'Jam Tangan']
                    },
                    {
                        title: 'Sepatu',
                        items: ['Sneakers', 'Pantofel', 'Boots', 'Sandal']
                    }
                ]
            },
            {
                key: 'fashion-wanita',
                name: 'Fashion Wanita',
                columns: [{
                        title: 'Atasan',
                        items: ['Blouse', 'Kemeja Wanita', 'Tunik', 'Crop Top']
                    },
                    {
                        title: 'Bawahan',
                        items: ['Rok', 'Jeans Wanita', 'Kulot', 'Legging']
                    },
                    {
                        title: 'Dress',
                        items: ['Dress Kasual', 'Dress Formal', 'Maxi Dress', 'Midi Dress']
                    },
                    {
                        title: 'Aksesoris',
                        items: ['Tas Wanita', 'Perhiasan', 'Hijab', 'Sepatu Wanita']
                    }
                ]
            },
            {
                key: 'elektronik',
                name: 'Elektronik',
                columns: [{
                        title: 'Komputer',
                        items: ['Laptop', 'PC Desktop', 'Monitor', 'Keyboard']
                    },
                    {
                        title: 'Gadget',
                        items: ['Smartphone', 'Tablet', 'Smartwatch', 'Earbuds']
                    },
                    {
                        title: 'Gaming',
                        items: ['Konsol', 'Gamepad', 'Mouse Gaming', 'Headset Gaming']
                    },
                    {
                        title: 'Aksesoris',
                        items: ['Power Bank', 'Charger', 'Kabel Data', 'Storage']
                    }
                ]
            }
        ];

        const skeletonHtml = () => Array.from({
            length: 4
        }, () => `
            <div class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 animate-pulse">
                <div class="w-12 h-12 rounded-lg bg-slate-200"></div>
                <div class="flex-1">
                    <div class="h-3 rounded bg-slate-200 w-40 mb-2"></div>
                    <div class="h-3 rounded bg-slate-200 w-24"></div>
                </div>
                <div class="w-5 h-5 rounded bg-slate-200"></div>
            </div>
        `).join('');

        function renderSearchResults(container, keyword) {
            const val = keyword.trim().toLowerCase();
            if (!val) {
                container.classList.add('hidden');
                container.innerHTML = '';
                return;
            }

            container.classList.remove('hidden');
            container.innerHTML = skeletonHtml();

            setTimeout(() => {
                const filtered = products.filter((p) =>
                    p.name.toLowerCase().includes(val) || p.meta.toLowerCase().includes(val)
                ).slice(0, 4);

                if (!filtered.length) {
                    container.innerHTML = `
                        <div class="px-4 py-5 text-center text-sm text-slate-500">
                            Produk tidak ditemukan
                        </div>`;
                    return;
                }

                container.innerHTML = filtered.map((p) => `
                    <a href="${detailUrl}" class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
                        <img src="${p.image}" alt="${p.name}" class="w-12 h-12 rounded-lg object-cover border border-slate-100" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">${p.name}</p>
                            <p class="text-xs text-slate-500 truncate">${p.meta}</p>
                        </div>
                        <i class="ri-arrow-right-up-line text-slate-400 text-base"></i>
                    </a>
                `).join('');
            }, 320);
        }

        function bindLiveSearch(inputId, dropdownId) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            if (!input || !dropdown) return;
            let timer = null;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => renderSearchResults(dropdown, input.value || ''), 80);
            });
            input.addEventListener('focus', () => {
                if ((input.value || '').trim()) renderSearchResults(dropdown, input.value || '');
            });
        }

        bindLiveSearch('ecNavSearchDesktop', 'ecNavSearchDropdownDesktop');
        bindLiveSearch('ecNavSearchMobile', 'ecNavSearchDropdownMobile');

        const mobileSearch = document.getElementById('ecMobileSearch');
        const mobileSearchToggle = document.getElementById('ecMobileSearchToggle');
        if (mobileSearch && mobileSearchToggle) {
            mobileSearchToggle.addEventListener('click', () => {
                mobileSearch.classList.toggle('hidden');
            });
        }

        const categoryTrigger = document.getElementById('ecCategoryTrigger');
        const categoryDropdown = document.getElementById('ecCategoryDropdown');
        const categoryMenu = document.getElementById('ecMegaCategoryMenu');
        const categoryContent = document.getElementById('ecMegaCategoryContent');
        if (categoryTrigger && categoryDropdown && categoryMenu && categoryContent) {
            let active = megaCategories[0].key;

            const renderMenu = () => {
                categoryMenu.innerHTML = megaCategories.map((cat) => `
                    <button type="button" data-key="${cat.key}" class="w-full text-left px-3 py-2 rounded-lg text-sm ${cat.key === active ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'}">
                        ${cat.name}
                    </button>
                `).join('');
            };

            const renderColumns = () => {
                const found = megaCategories.find((x) => x.key === active) || megaCategories[0];
                categoryContent.innerHTML = `<div class="grid grid-cols-4 gap-6">${found.columns.map((col) => `
                    <div>
                        <h5 class="text-sm font-semibold text-slate-800 mb-3">${col.title}</h5>
                        <ul class="space-y-2">${col.items.map((item) => `<li><a href="#" class="text-sm text-slate-600 hover:text-blue-600">${item}</a></li>`).join('')}</ul>
                    </div>
                `).join('')}</div>`;
            };

            renderMenu();
            renderColumns();

            categoryMenu.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = e.target.closest('[data-key]');
                if (!target) return;
                active = target.getAttribute('data-key') || megaCategories[0].key;
                renderMenu();
                renderColumns();
            });

            categoryTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                categoryDropdown.classList.toggle('hidden');
            });
        }

        document.addEventListener('click', (e) => {
            const categoryDropdownEl = document.getElementById('ecCategoryDropdown');
            const categoryTriggerEl = document.getElementById('ecCategoryTrigger');
            if (categoryDropdownEl && categoryTriggerEl && !categoryDropdownEl.contains(e.target) && !
                categoryTriggerEl
                .contains(e.target)) {
                categoryDropdownEl.classList.add('hidden');
            }

            ['ecNavSearchDropdownDesktop', 'ecNavSearchDropdownMobile'].forEach((id) => {
                const box = document.getElementById(id);
                if (!box) return;
                const inputId = id.includes('Desktop') ? 'ecNavSearchDesktop' : 'ecNavSearchMobile';
                const input = document.getElementById(inputId);
                if (!input) return;
                if (!box.contains(e.target) && e.target !== input) {
                    box.classList.add('hidden');
                }
            });
        });
    })();
</script>
