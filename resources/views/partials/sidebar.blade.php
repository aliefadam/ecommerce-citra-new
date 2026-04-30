<aside id="sidebar"
    class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 z-30 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0 shadow-xl lg:shadow-none">
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-200 dark:border-slate-700">
        <div
            class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-200 dark:shadow-blue-900">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
            </svg>
        </div>
        <span class="text-lg font-800 font-extrabold text-slate-800 dark:text-white tracking-tight">Admin<span
                class="text-blue-600">Kit</span></span>
        <button onclick="toggleSidebar()"
            class="ml-auto lg:hidden text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-4 mb-3">Main
            Menu</p>

        <a href="{{ route('pages.index') }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('pages.index') ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40' : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
            </svg>
            Dashboard
        </a>

        <a href="{{ route('pages.datatables') }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('pages.datatables') ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40' : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18" />
            </svg>
            Data Tables
        </a>

        <a href="{{ route('products.index') }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('products.*') ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40' : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                <polyline points="3.29 7 12 12 20.71 7" />
                <line x1="12" y1="22" x2="12" y2="12" />
            </svg>
            Products
        </a>

        <a href="{{ route('pages.charts') }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('pages.charts') ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40' : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
                <line x1="2" y1="20" x2="22" y2="20" />
            </svg>
            Charts
        </a>

        <a href="{{ route('pages.components') }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('pages.components') ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40' : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
            </svg>
            Components
        </a>

        <a href="{{ route('pages.settings') }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('pages.settings') ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40' : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                <path d="M12 2v2M12 20v2M2 12h2M20 12h2" />
            </svg>
            Settings
        </a>

        {{-- Menu dengan Sub Menu --}}
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h16M4 10h16M4 14h8M4 18h8" />
                </svg>
                <span class="flex-1 text-left">Reports</span>
                <svg class="transition-transform duration-200" :class="open ? 'rotate-180' : ''" width="14"
                    height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="mt-1 ml-4 pl-4 border-l-2 border-slate-200 dark:border-slate-700 space-y-0.5">
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    Monthly Report
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    Annual Report
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                    Analytics
                </a>
            </div>
        </div>

        {{-- Menu dengan Sub Menu Bertingkat --}}
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span class="flex-1 text-left">Management</span>
                <svg class="transition-transform duration-200" :class="open ? 'rotate-180' : ''" width="14"
                    height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="mt-1 ml-4 pl-4 border-l-2 border-slate-200 dark:border-slate-700 space-y-0.5">

                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Users
                </a>

                {{-- Sub Menu Bertingkat: Roles --}}
                <div x-data="{ openSub: false }">
                    <button @click="openSub = !openSub"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <span class="flex-1 text-left">Roles & Permissions</span>
                        <svg class="transition-transform duration-200" :class="openSub ? 'rotate-180' : ''"
                            width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div x-show="openSub" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mt-1 ml-3 pl-3 border-l-2 border-slate-200 dark:border-slate-700 space-y-0.5">
                        <a href="#"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            Manage Roles
                        </a>
                        <a href="#"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            Manage Permissions
                        </a>
                        <a href="#"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            Assign Roles
                        </a>
                    </div>
                </div>

                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    Departments
                </a>
            </div>
        </div>

        <div class="pt-4">
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-4 mb-3">
                Account</p>
            <a href="#"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                Profile
            </a>
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-red-500 transition-all duration-200">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Logout
            </a>
            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </nav>

    <div class="px-4 py-4 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff&size=64"
                class="w-9 h-9 rounded-full ring-2 ring-blue-500/30" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate text-slate-800 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
            </div>
            <div class="w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-700"></div>
        </div>
    </div>
</aside>
