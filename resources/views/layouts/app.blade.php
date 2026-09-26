<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        @include('partials.pwa-meta')
        <script>
            (function() {
                const html = document.documentElement;
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) html.classList.add('dark');
                else html.classList.remove('dark');
            })();
        </script>
        <title>{{ $appStoreName ?? 'Ecommerce Citra' }} Admin - @yield('title', 'Dashboard')</title>
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
        <script src="{{ asset('vendor/chart.js/chart.umd.min.js') }}"></script>
        <style>
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
                background: #eff6ff;
                color: #1d4ed8;
                border-radius: 10px;
            }

            .dark .settings-tab.active {
                background: rgba(30, 58, 138, 0.3);
                color: #60a5fa;
            }

            .color-option input:checked~.color-ring {
                display: block;
            }

            @media (max-width: 767px) {
                [data-mobile-table-wrap] {
                    overflow: visible !important;
                }

                table[data-mobile-cards] {
                    display: block;
                    width: 100%;
                    min-width: 0 !important;
                }

                table[data-mobile-cards] thead {
                    display: none;
                }

                table[data-mobile-cards] tbody {
                    display: grid;
                    gap: 0.75rem;
                    padding: 0.75rem;
                }

                table[data-mobile-cards] tbody tr {
                    position: relative;
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                    gap: 0.75rem 1rem;
                    padding: 1rem;
                    overflow: visible;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.875rem;
                    background: #ffffff;
                    box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
                }

                .dark table[data-mobile-cards] tbody tr {
                    border-color: #334155;
                    background: #172033;
                    box-shadow: 0 1px 2px rgb(0 0 0 / 0.16);
                }

                table[data-mobile-cards] tbody td {
                    display: flex;
                    min-width: 0;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.25rem;
                    padding: 0 !important;
                    border: 0 !important;
                    background: transparent !important;
                    overflow-wrap: anywhere;
                    white-space: normal;
                }

                table[data-mobile-cards] tbody td > * {
                    max-width: 100%;
                    min-width: 0;
                }

                table[data-mobile-cards] tbody td > .flex {
                    flex-wrap: wrap;
                }

                table[data-mobile-cards] tbody td code {
                    overflow-wrap: anywhere;
                    white-space: normal !important;
                }

                table[data-mobile-cards] tbody td::before {
                    content: attr(data-mobile-label);
                    color: #64748b;
                    font-size: 0.625rem;
                    font-weight: 700;
                    letter-spacing: 0.055em;
                    line-height: 1rem;
                    text-transform: uppercase;
                }

                .dark table[data-mobile-cards] tbody td::before {
                    color: #7890b4;
                }

                table[data-mobile-cards] tbody td[data-mobile-primary] {
                    grid-column: 1 / -1;
                    padding-right: 2rem !important;
                }

                table[data-mobile-cards] tbody td[data-mobile-primary]::before {
                    display: none;
                }

                table[data-mobile-cards] tbody td[data-mobile-select] {
                    position: absolute;
                    top: 1rem;
                    right: 1rem;
                    z-index: 1;
                    width: auto;
                }

                table[data-mobile-cards] tbody td[data-mobile-select]::before,
                table[data-mobile-cards] tbody td[data-mobile-actions]::before {
                    display: none;
                }

                table[data-mobile-cards] tbody td[data-mobile-actions] {
                    grid-column: 1 / -1;
                    display: flex;
                    flex-direction: row;
                    flex-wrap: wrap;
                    align-items: center;
                    gap: 0.5rem;
                    padding-top: 0.75rem !important;
                    border-top: 1px solid #e2e8f0 !important;
                }

                .dark table[data-mobile-cards] tbody td[data-mobile-actions] {
                    border-top-color: #334155 !important;
                }

                table[data-mobile-cards] tbody td[data-mobile-actions] > a,
                table[data-mobile-cards] tbody td[data-mobile-actions] > button,
                table[data-mobile-cards] tbody td[data-mobile-actions] > form {
                    flex: 1 1 auto;
                }

                table[data-mobile-cards] tbody td[data-mobile-actions] > a,
                table[data-mobile-cards] tbody td[data-mobile-actions] > button,
                table[data-mobile-cards] tbody td[data-mobile-actions] > form > button {
                    justify-content: center;
                }

                table[data-mobile-cards] tbody td[data-mobile-empty] {
                    grid-column: 1 / -1;
                    align-items: center;
                    padding: 1.25rem !important;
                    text-align: center;
                }

                table[data-mobile-cards] tbody td[data-mobile-empty]::before {
                    display: none;
                }
            }
        </style>
        @yield('style')
    </head>

    <body class="admin-shell bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300">
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

                const sidebarNav = document.getElementById('admin-sidebar-nav');
                if (sidebarNav) {
                    const scrollKey = 'admin-sidebar-scroll-top';
                    const rememberSidebarPosition = () => {
                        sessionStorage.setItem(scrollKey, String(sidebarNav.scrollTop));
                    };
                    const savedPosition = sessionStorage.getItem(scrollKey);

                    requestAnimationFrame(() => {
                        if (savedPosition !== null) {
                            sidebarNav.scrollTop = Number(savedPosition) || 0;
                        }

                        requestAnimationFrame(() => {
                            const activeItem = sidebarNav.querySelector('[data-sidebar-active]');
                            if (!activeItem) return;

                            const navBounds = sidebarNav.getBoundingClientRect();
                            const itemBounds = activeItem.getBoundingClientRect();
                            const isVisible = itemBounds.top >= navBounds.top && itemBounds.bottom <= navBounds.bottom;

                            if (!isVisible) {
                                activeItem.scrollIntoView({
                                    block: 'nearest'
                                });
                                rememberSidebarPosition();
                            }
                        });
                    });

                    sidebarNav.addEventListener('scroll', rememberSidebarPosition, {
                        passive: true
                    });
                    sidebarNav.addEventListener('click', (event) => {
                        if (event.target.closest('a[href]')) rememberSidebarPosition();
                    });
                    window.addEventListener('pagehide', rememberSidebarPosition);
                }

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

                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
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
        <script>
            (function() {
                const actionPattern = /^(aksi|action|actions)$/i;

                function cleanHeaderLabel(header) {
                    if (!header) return '';
                    const clone = header.cloneNode(true);
                    clone.querySelectorAll('svg, input, button, span').forEach((element) => element.remove());
                    return (header.dataset.mobileLabel || clone.textContent || '')
                        .replace(/[↕↑↓]/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                }

                function enhanceTable(table) {
                    const headers = Array.from(table.querySelectorAll('thead th'));
                    if (!headers.length) return;

                    const primaryColumn = Math.max(0, Number(table.dataset.mobilePrimaryColumn || 1) - 1);

                    Array.from(table.tBodies).forEach((tbody) => {
                        Array.from(tbody.rows).forEach((row) => {
                            const cells = Array.from(row.cells);
                            if (cells.length === 1 && cells[0].colSpan > 1) {
                                cells[0].setAttribute('data-mobile-empty', '');
                                return;
                            }

                            cells.forEach((cell, index) => {
                                const label = cleanHeaderLabel(headers[index]);
                                cell.dataset.mobileLabel = label;
                                cell.toggleAttribute('data-mobile-primary', index === primaryColumn);
                                cell.toggleAttribute('data-mobile-actions', actionPattern.test(label));
                                cell.toggleAttribute('data-mobile-select', !label && Boolean(cell.querySelector('input[type="checkbox"]')));
                            });
                        });
                    });
                }

                function initMobileCardTable(table) {
                    if (table.dataset.mobileCardsReady === 'true') {
                        enhanceTable(table);
                        return;
                    }

                    table.dataset.mobileCardsReady = 'true';
                    table.closest('.overflow-x-auto')?.setAttribute('data-mobile-table-wrap', '');
                    enhanceTable(table);

                    Array.from(table.tBodies).forEach((tbody) => {
                        new MutationObserver(() => enhanceTable(table)).observe(tbody, {
                            childList: true,
                            subtree: true
                        });
                    });
                }

                window.initMobileCardTables = function(root = document) {
                    root.querySelectorAll('table[data-mobile-cards]').forEach(initMobileCardTable);
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => window.initMobileCardTables());
                } else {
                    window.initMobileCardTables();
                }
            })();
        </script>

        @include('partials.file-dropzone')
        @yield('script')
    </body>

</html>
