<header
    class="fixed top-0 lg:left-64 left-0 right-0 z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700">
    <div class="flex items-center gap-4 px-4 sm:px-6 h-16">
        <button onclick="toggleSidebar()"
            class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>

        @php($currentTitle = trim($__env->yieldContent('title', 'Dashboard')))
        <nav class="hidden sm:flex items-center text-sm" aria-label="Breadcrumb">
            <a href="{{ route('pages.index') }}"
                class="font-medium text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                Dashboard
            </a>
            <svg class="mx-2 text-slate-300 dark:text-slate-600" width="14" height="14" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6" />
            </svg>
            <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $currentTitle }}</span>
        </nav>

        <!-- Search input (commented per request)
        <div class="relative hidden sm:block">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" placeholder="Search anything..."
                class="pl-9 pr-4 py-2 text-sm bg-slate-100 dark:bg-slate-700 border border-transparent dark:border-slate-600 rounded-xl w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-slate-600 transition-all placeholder-slate-400 dark:text-slate-200" />
        </div>
        -->

        <div class="flex items-center gap-2 ml-auto">
            <button onclick="toggleDark()"
                class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                title="Toggle Dark Mode">
                <svg id="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="hidden dark:block">
                    <circle cx="12" cy="12" r="5" />
                    <line x1="12" y1="1" x2="12" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="23" />
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                    <line x1="1" y1="12" x2="3" y2="12" />
                    <line x1="21" y1="12" x2="23" y2="12" />
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                </svg>
                <svg id="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="block dark:hidden">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>
            </button>

            <div class="relative">
                <button onclick="toggleNotif()"
                    class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors relative">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <div id="notif-dropdown"
                    class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
                    <div
                        class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <span class="font-semibold text-sm">Notifications</span>
                        <span
                            class="text-xs bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 px-2 py-0.5 rounded-full font-semibold">3
                            new</span>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <div class="flex gap-3 items-start">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="#3b82f6" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">New user registered</p>
                                    <p class="text-xs text-slate-400 mt-0.5">2 minutes ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <div class="flex gap-3 items-start">
                                <div
                                    class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="#10b981" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Payment received</p>
                                    <p class="text-xs text-slate-400 mt-0.5">1 hour ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <div class="flex gap-3 items-start">
                                <div
                                    class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="#f59e0b" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                        <line x1="12" y1="9" x2="12" y2="13" />
                                        <line x1="12" y1="17" x2="12.01" y2="17" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Server warning detected</p>
                                    <p class="text-xs text-slate-400 mt-0.5">3 hours ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 text-center border-t border-slate-200 dark:border-slate-700">
                        <a href="#"
                            class="text-sm text-blue-600 dark:text-blue-400 font-medium hover:underline">View all
                            notifications</a>
                    </div>
                </div>
            </div>

            <div class="relative">
                <button onclick="toggleProfile()"
                    class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff&size=64"
                        class="w-8 h-8 rounded-full ring-2 ring-blue-500/30" />
                    <span class="hidden sm:block text-sm font-semibold">Admin</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-slate-400 hidden sm:block">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
                <div id="profile-dropdown"
                    class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="#"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        My Profile
                    </a>
                    <a href="{{ route('pages.settings') }}"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                        </svg>
                        Settings
                    </a>
                    <div class="border-t border-slate-200 dark:border-slate-700">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form-topbar').submit();"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Logout
                        </a>
                        <form id="logout-form-topbar" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
