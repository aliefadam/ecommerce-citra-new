<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <script>
            (function() {
                const html = document.documentElement;
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) html.classList.add('dark');
                else html.classList.remove('dark');
            })();
        </script>
        <title>AdminKit - @yield('title', 'Dashboard')</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            jakarta: ['Plus Jakarta Sans', 'sans-serif']
                        },
                        colors: {
                            primary: {
                                50: '#eff6ff',
                                100: '#dbeafe',
                                200: '#bfdbfe',
                                300: '#93c5fd',
                                400: '#60a5fa',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                800: '#1e40af',
                                900: '#1e3a8a'
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            * {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }

            .sidebar-link {
                @apply flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200;
            }

            ::-webkit-scrollbar {
                width: 5px;
                height: 5px;
            }

            ::-webkit-scrollbar-track {
                background: transparent;
            }

            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 99px;
            }

            .dark ::-webkit-scrollbar-thumb {
                background: #334155;
            }

            th.sortable {
                cursor: pointer;
                user-select: none;
            }

            th.sortable:hover {
                color: #3b82f6;
            }

            .toggle-switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
            }

            .toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #cbd5e1;
                transition: .3s;
                border-radius: 24px;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            }

            input:checked+.slider {
                background-color: #3b82f6;
            }

            input:checked+.slider:before {
                transform: translateX(20px);
            }

            .dark .slider {
                background-color: #475569;
            }

            .settings-tab.active {
                background: white;
                color: #1e293b;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .dark .settings-tab.active {
                background: #334155;
                color: white;
            }

            .color-option input:checked~.color-ring {
                display: block;
            }
        </style>
        @yield('style')
    </head>

    <body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300">
        <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"></div>

        @include('partials.sidebar')

        <div class="lg:pl-64 min-h-screen flex flex-col">
            @include('partials.topbar')
            <div class="mt-2">
                @yield('content')
            </div>
        </div>

        <script>
            (function() {
                const html = document.documentElement;

                window.toggleDark = function() {
                    html.classList.toggle('dark');
                    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
                    window.dispatchEvent(new CustomEvent('theme:changed', {
                        detail: {
                            isDark: html.classList.contains('dark')
                        }
                    }));
                };

                window.toggleSidebar = function() {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('overlay');
                    if (!sidebar || !overlay) return;
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('hidden');
                };

                window.toggleNotif = function() {
                    const notif = document.getElementById('notif-dropdown');
                    const profile = document.getElementById('profile-dropdown');
                    if (!notif || !profile) return;
                    notif.classList.toggle('hidden');
                    profile.classList.add('hidden');
                };

                window.toggleProfile = function() {
                    const profile = document.getElementById('profile-dropdown');
                    const notif = document.getElementById('notif-dropdown');
                    if (!profile || !notif) return;
                    profile.classList.toggle('hidden');
                    notif.classList.add('hidden');
                };

                document.addEventListener('click', function(e) {
                    const notif = document.getElementById('notif-dropdown');
                    const profile = document.getElementById('profile-dropdown');
                    if (!notif || !profile) return;

                    if (!e.target.closest('#notif-dropdown') && !e.target.closest(
                        'button[onclick="toggleNotif()"]')) {
                        notif.classList.add('hidden');
                    }
                    if (!e.target.closest('#profile-dropdown') && !e.target.closest(
                            'button[onclick="toggleProfile()"]')) {
                        profile.classList.add('hidden');
                    }
                });
            })();
        </script>
        <script>
            (function() {
                window.initAdminDataTable = function(options) {
                    const state = {
                        data: Array.isArray(options.data) ? [...options.data] : [],
                        filtered: [],
                        currentPage: 1,
                        sortColumn: null,
                        sortAsc: true,
                    };

                    const perPage = options.perPage || 10;
                    const itemLabel = options.itemLabel || 'items';
                    const searchInput = options.searchInputId ? document.getElementById(options.searchInputId) : null;
                    const tbody = document.getElementById(options.tbodyId);
                    const paginationInfo = document.getElementById(options.paginationInfoId);
                    const paginationButtons = document.getElementById(options.paginationButtonsId);

                    const getFilters = () => options.filters || [];
                    const getSearchFields = () => options.searchFields || [];
                    const getSortMap = () => options.sortMap || {};

                    function applyFilters() {
                        const query = (searchInput?.value || '').toLowerCase().trim();
                        const filters = getFilters();
                        const searchFields = getSearchFields();

                        state.filtered = state.data.filter((item) => {
                            const matchesSearch = !query || searchFields.some((field) => String(item[field] ??
                                '').toLowerCase().includes(query));
                            if (!matchesSearch) return false;

                            return filters.every((filter) => {
                                const el = document.getElementById(filter.elementId);
                                const filterVal = el ? el.value : '';
                                if (!filterVal) return true;
                                const raw = filter.accessor ? filter.accessor(item) : item[filter
                                .field];
                                const value = String(raw ?? '').toLowerCase();
                                const expected = String(filterVal).toLowerCase();
                                return (filter.mode || 'exact') === 'includes' ? value.includes(
                                    expected) : value === expected;
                            });
                        });
                    }

                    function applySort() {
                        if (state.sortColumn === null) return;
                        const sortMap = getSortMap();
                        const accessor = sortMap[state.sortColumn];
                        if (!accessor) return;

                        state.filtered.sort((a, b) => {
                            const av = accessor(a);
                            const bv = accessor(b);
                            if (typeof av === 'number' && typeof bv === 'number') {
                                return state.sortAsc ? av - bv : bv - av;
                            }
                            const as = String(av ?? '').toLowerCase();
                            const bs = String(bv ?? '').toLowerCase();
                            return state.sortAsc ? as.localeCompare(bs) : bs.localeCompare(as);
                        });
                    }

                    function render() {
                        if (!tbody || !paginationInfo || !paginationButtons) return;

                        const total = state.filtered.length;
                        const totalPages = Math.max(1, Math.ceil(total / perPage));
                        if (state.currentPage > totalPages) state.currentPage = totalPages;

                        const start = (state.currentPage - 1) * perPage;
                        const pageData = state.filtered.slice(start, start + perPage);

                        if (!pageData.length) {
                            tbody.innerHTML = options.emptyRowHtml ||
                                '<tr><td class="text-center py-10 text-slate-400">No records found</td></tr>';
                        } else {
                            tbody.innerHTML = pageData.map((item, idx) => options.renderRow(item, start + idx)).join(
                            '');
                        }

                        const from = total ? start + 1 : 0;
                        const to = Math.min(start + perPage, total);
                        paginationInfo.textContent = total ? `Showing ${from}-${to} of ${total} ${itemLabel}` :
                            `Showing 0 ${itemLabel}`;

                        paginationButtons.innerHTML = '';
                        const btn = (label, page, disabled, active) => {
                            const b = document.createElement('button');
                            b.innerHTML = label;
                            b.className =
                                `px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors ${active ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'} ${disabled ? 'opacity-40 pointer-events-none' : ''}`;
                            if (!disabled && !active) {
                                b.onclick = () => {
                                    state.currentPage = page;
                                    render();
                                    options.onAfterRender?.(pageData, state.filtered);
                                };
                            }
                            return b;
                        };

                        paginationButtons.appendChild(btn('← Prev', state.currentPage - 1, state.currentPage === 1,
                            false));
                        for (let i = 1; i <= totalPages; i++) {
                            paginationButtons.appendChild(btn(String(i), i, false, i === state.currentPage));
                        }
                        paginationButtons.appendChild(btn('Next →', state.currentPage + 1, state.currentPage ===
                            totalPages, false));

                        options.onAfterRender?.(pageData, state.filtered);
                    }

                    function refresh(resetPage = true) {
                        if (resetPage) state.currentPage = 1;
                        applyFilters();
                        applySort();
                        render();
                    }

                    const filters = getFilters();
                    if (searchInput) searchInput.addEventListener('input', () => refresh(true));
                    filters.forEach((filter) => {
                        const el = document.getElementById(filter.elementId);
                        if (el) el.addEventListener(filter.event || 'change', () => refresh(true));
                    });

                    const api = {
                        refresh,
                        sortBy(columnIndex) {
                            if (state.sortColumn === columnIndex) state.sortAsc = !state.sortAsc;
                            else {
                                state.sortColumn = columnIndex;
                                state.sortAsc = true;
                            }
                            applyFilters();
                            applySort();
                            render();
                        },
                        setData(newData) {
                            state.data = Array.isArray(newData) ? [...newData] : [];
                            refresh(true);
                        },
                        getData() {
                            return [...state.data];
                        }
                    };

                    refresh(true);
                    return api;
                };
            })();
        </script>

        @yield('script')
    </body>

</html>
