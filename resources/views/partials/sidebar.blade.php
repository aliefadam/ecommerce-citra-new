<aside id="sidebar"
    class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 z-30 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0 shadow-xl lg:shadow-none">
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-200 dark:border-slate-700">
        <div
            class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-200 dark:shadow-blue-900 overflow-hidden">
            @if (!empty($appStoreLogoUrl))
                <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}" class="w-full h-full object-contain bg-white p-1">
            @else
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                </svg>
            @endif
        </div>
        <span class="text-lg font-800 font-extrabold text-slate-800 dark:text-white tracking-tight">
            {{ $appStoreName }}
            <span class="text-blue-600"> Admin</span>
        </span>
        <button onclick="toggleSidebar()"
            class="ml-auto lg:hidden text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @foreach (config('sidebar') as $section)
            <section class="{{ $loop->first ? '' : 'mt-6' }}">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-4 mb-4">
                    {{ $section['group'] }}
                </p>

                <div class="space-y-1.5">
                    @foreach ($section['items'] as $item)
                        @if (!empty($item['children']))
                            {{-- Dropdown menu --}}
                            <div x-data="{ open: {{ collect($item['children'])->contains(fn($c) => !empty($c['active']) && request()->routeIs($c['active'])) ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                    @php $icon = $item['icon'] ?? ''; @endphp
                                    @if (!str_contains($icon, '<'))
                                        <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]"></i>
                                    @else
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            {!! $icon !!}
                                        </svg>
                                    @endif
                                    <span class="flex-1 text-left">{{ $item['name'] }}</span>
                                    <svg class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                                        width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="mt-1 ml-4 pl-4 border-l-2 border-slate-200 dark:border-slate-700 space-y-0.5">

                                    @foreach ($item['children'] as $child)
                                        @if (!empty($child['children']))
                                            {{-- Nested dropdown --}}
                                            <div x-data="{ openSub: {{ collect($child['children'])->contains(fn($c) => !empty($c['active']) && request()->routeIs($c['active'])) ? 'true' : 'false' }} }">
                                                <button @click="openSub = !openSub"
                                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                                    @if (!empty($child['icon']))
                                                        @php $icon = $child['icon']; @endphp
                                                        @if (!str_contains($icon, '<'))
                                                            <i data-lucide="{{ $icon }}"
                                                                class="w-[15px] h-[15px]"></i>
                                                        @else
                                                            <svg width="15" height="15" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                {!! $icon !!}
                                                            </svg>
                                                        @endif
                                                    @endif
                                                    <span class="flex-1 text-left">{{ $child['name'] }}</span>
                                                    <svg class="transition-transform duration-200"
                                                        :class="openSub ? 'rotate-180' : ''" width="13"
                                                        height="13" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <polyline points="6 9 12 15 18 9" />
                                                    </svg>
                                                </button>

                                                <div x-show="openSub"
                                                    x-transition:enter="transition ease-out duration-150"
                                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-100"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                                    class="mt-1 ml-3 pl-3 border-l-2 border-slate-200 dark:border-slate-700 space-y-0.5">
                                                    @foreach ($child['children'] as $grandchild)
                                                        <a href="{{ $grandchild['route'] ? route($grandchild['route']) : '#' }}"
                                                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                                            <span
                                                                class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                                            {{ $grandchild['name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            {{-- Single child item --}}
                                            <a href="{{ !empty($child['route']) ? route($child['route']) : '#' }}"
                                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                            {{ !empty($child['active']) && request()->routeIs($child['active'])
                                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
                                                @if (!empty($child['icon']))
                                                    @php $icon = $child['icon']; @endphp
                                                    @if (!str_contains($icon, '<'))
                                                        <i data-lucide="{{ $icon }}"
                                                            class="w-[15px] h-[15px]"></i>
                                                    @else
                                                        <svg width="15" height="15" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            {!! $icon !!}
                                                        </svg>
                                                    @endif
                                                @endif
                                                {{ $child['name'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @elseif (!empty($item['logout']))
                            {{-- Logout item --}}
                            <a href="{{ route($item['route']) }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-red-500 transition-all duration-200">
                                @php $icon = $item['icon'] ?? ''; @endphp
                                @if (!str_contains($icon, '<'))
                                    <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]"></i>
                                @else
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        {!! $icon !!}
                                    </svg>
                                @endif
                                {{ $item['name'] }}
                            </a>
                            <form id="logout-form-sidebar" action="{{ route($item['route']) }}" method="POST"
                                class="hidden">
                                @csrf
                            </form>
                        @else
                            {{-- Single menu item --}}
                            <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-200
                            {{ !empty($item['active']) && request()->routeIs($item['active'])
                                ? 'font-semibold bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/40'
                                : 'font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-blue-600 dark:hover:text-blue-400' }}">
                                @php $icon = $item['icon'] ?? ''; @endphp
                                @if (!str_contains($icon, '<'))
                                    <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]"></i>
                                @else
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        {!! $icon !!}
                                    </svg>
                                @endif
                                {{ $item['name'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>

    <div class="px-4 py-4 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff&size=64"
                class="w-9 h-9 rounded-full ring-2 ring-blue-500/30" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate text-slate-800 dark:text-white">{{ auth()->user()->name }}
                </p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
            </div>
            <div class="w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-700"></div>
        </div>
    </div>
</aside>
