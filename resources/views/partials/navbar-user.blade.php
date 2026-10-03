@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?: (app()->getLocale() === 'en' ? 'Guest' : 'Tamu');
    $displayFirstName = trim(explode(' ', $displayName)[0] ?? $displayName);
    $cartCount = (int) ($customerNavigation['cartCount'] ?? 0);
    $megaCategories = $customerNavigation['megaCategories'] ?? [];
    $selectedSearchCategory = collect($megaCategories)->firstWhere('key', (string) request('parent'));
@endphp

<nav class="ec-site-header" aria-label="{{ __('storefront.main_navigation') }}" data-storefront-shell>
    <div class="ec-utility-bar">
        <div class="ec-container ec-utility-inner">
            <p class="ec-utility-copy">We Connect The Gaps &amp; One Stop Shop Industrial Supply</p>
            <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}?category=aplikasi#install-aplikasi"
                class="ec-mobile-install" data-pwa-install aria-label="{{ __('storefront.install_app_label', ['store' => $appStoreName]) }}">
                <span class="ec-mobile-install-copy">
                    <small>{{ __('storefront.app_name', ['store' => strtoupper($appStoreName)]) }}</small>
                    <strong>{{ __('storefront.shop_faster_mobile') }}</strong>
                </span>
                <span class="ec-mobile-install-action">
                    INSTALL <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i>
                </span>
            </a>
            <div class="ec-utility-links" aria-label="{{ __('storefront.service_benefits') }}">
                <span><i class="fi fi-rr-shield-check" aria-hidden="true"></i> Trusted by Industry</span>
                <span><i class="fi fi-rr-marker" aria-hidden="true"></i> {{ __('storefront.nationwide_shipping') }}</span>
            </div>
        </div>
    </div>

    <div class="ec-header-main">
        <div class="ec-container ec-header-main-inner">
            <div class="ec-mobile-leading-actions md:hidden">
                <button id="ecMobileNavToggle" type="button" class="ec-header-icon-button" aria-expanded="false" aria-controls="ecMobileNavDrawer" aria-label="{{ __('storefront.open_navigation') }}"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i></button>
                <button id="ecMobileSearchToggle" class="ec-header-icon-button" type="button" aria-expanded="false" aria-controls="ecMobileSearch" aria-label="{{ __('storefront.open_search') }}"><i class="fi fi-rr-search" aria-hidden="true"></i></button>
            </div>

            <a href="{{ route('frontend.index') }}" class="ec-header-logo" aria-label="{{ $appStoreName }}, {{ strtolower(__('storefront.home')) }}">
                <img src="{{ !empty($appStoreLogoUrl) ? $appStoreLogoUrl : asset('logo/BOQ.CO.ID/BOQ.CO.ID-1.png') }}" alt="{{ $appStoreName }}" width="116" height="52" />
            </a>

            <div class="ec-header-search-wrap">
                <form action="{{ route('frontend.search') }}" method="GET" class="ec-search-shell ec-reference-search" role="search">
                    <label for="ecNavSearchDesktop" class="sr-only">{{ __('storefront.search_products') }}</label>
                    <input id="ecNavSearchDesktop" name="q" value="{{ trim(request('q', $query ?? '')) }}" placeholder="{{ __('storefront.search_placeholder') }}"
                        class="ec-reference-search-input" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ecNavSearchDropdownDesktop" data-storefront-autocomplete data-suggestions-url="{{ route('frontend.search.suggestions') }}" />
                    <div class="ec-reference-search-category" data-search-category>
                        <input id="ecNavCategory" type="hidden" name="parent" value="{{ $selectedSearchCategory['key'] ?? '' }}" />
                        <button id="ecNavCategoryTrigger" type="button" class="ec-reference-search-category-trigger"
                            aria-haspopup="listbox" aria-expanded="false" aria-controls="ecNavCategoryDropdown">
                            <span data-search-category-label>{{ $selectedSearchCategory['name'] ?? __('storefront.all_categories') }}</span>
                            <i class="fi fi-rr-angle-small-down" aria-hidden="true"></i>
                        </button>
                        <div id="ecNavCategoryDropdown" class="ec-reference-search-category-dropdown" role="listbox"
                            aria-label="{{ __('storefront.search_categories') }}" hidden>
                            <button type="button" class="ec-reference-search-category-option" role="option"
                                aria-selected="{{ $selectedSearchCategory ? 'false' : 'true' }}" data-category-value="" data-category-label="{{ __('storefront.all_categories') }}">
                                <span>{{ __('storefront.all_categories') }}</span><i class="fi fi-rr-check" aria-hidden="true"></i>
                            </button>
                            @foreach ($megaCategories as $category)
                                <button type="button" class="ec-reference-search-category-option" role="option"
                                    aria-selected="{{ ($selectedSearchCategory['key'] ?? null) === $category['key'] ? 'true' : 'false' }}"
                                    data-category-value="{{ $category['key'] }}" data-category-label="{{ $category['name'] }}">
                                    <span>{{ $category['name'] }}</span><i class="fi fi-rr-check" aria-hidden="true"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <button class="ec-reference-search-button" type="submit" aria-label="{{ __('storefront.search') }}"><i class="fi fi-rr-search" aria-hidden="true"></i></button>
                </form>
                <div id="ecNavSearchDropdownDesktop" class="ec-dropdown left-0 right-0 top-full mt-2 max-h-[28rem] overflow-y-auto" role="listbox" aria-label="{{ __('storefront.product_suggestions') }}" hidden></div>
            </div>

            <div class="ec-header-actions">
                <div class="hidden items-center rounded-lg border border-slate-200 bg-white p-0.5 text-xs font-bold text-slate-600 sm:flex" role="group" aria-label="{{ __('storefront.language') }}">
                    @foreach (['id' => 'ID', 'en' => 'EN'] as $locale => $label)
                        <a href="{{ route('locale.switch', ['locale' => $locale, 'redirect' => request()->getRequestUri()]) }}"
                            class="rounded-md px-2 py-1.5 transition-colors {{ app()->getLocale() === $locale ? 'bg-blue-900 text-white' : 'hover:bg-slate-100' }}"
                            lang="{{ $locale }}" hreflang="{{ $locale }}" @if(app()->getLocale() === $locale) aria-current="true" @endif>{{ $label }}</a>
                    @endforeach
                </div>
                @auth
                    <div class="relative">
                        <button id="ecAccountTrigger" type="button" class="ec-header-action" aria-expanded="false" aria-controls="ecAccountDropdown">
                            @if($authUser->avatar)<img src="{{ $authUser->avatar }}" alt="" class="size-8 rounded-full object-cover" />@else<i class="fi fi-rr-user" aria-hidden="true"></i>@endif
                            <span><strong>{{ $displayFirstName }}</strong><small>{{ __('storefront.my_account') }}</small></span>
                        </button>
                        <div id="ecAccountDropdown" class="ec-dropdown right-0 top-full mt-2 w-64 overflow-hidden p-2" hidden>
                            <p class="border-b border-slate-200 px-3 py-3 text-xs text-slate-500"><strong class="block truncate text-sm text-slate-900">{{ $displayName }}</strong>{{ $authUser->email }}</p>
                            <a href="{{ route('frontend.profil') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-user" aria-hidden="true"></i>{{ __('storefront.my_profile') }}</a>
                            <a href="{{ route('frontend.profil') }}?tab=notif" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-bell" aria-hidden="true"></i>{{ __('storefront.notifications') }}</a>
                            <a href="{{ route('frontend.profil') }}?tab=pesanan" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-box-open-full" aria-hidden="true"></i>{{ __('storefront.orders') }}</a>
                            <form action="{{ route('logout') }}" method="POST" class="border-t border-slate-200 pt-1">@csrf<button class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-bold text-red-700 hover:bg-red-50" type="submit"><i class="fi fi-rr-exit" aria-hidden="true"></i>{{ __('storefront.sign_out') }}</button></form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="ec-header-action"><i class="fi fi-rr-user" aria-hidden="true"></i><span><strong>{{ __('storefront.sign_in_register') }}</strong><small>{{ __('storefront.my_account') }}</small></span></a>
                @endauth
                <a href="{{ route('frontend.cart') }}" class="ec-header-action ec-cart-action" aria-label="{{ __('storefront.cart') }}, {{ trans_choice('storefront.cart_items', $cartCount, ['count' => $cartCount]) }}">
                    <span class="relative"><i class="fi fi-rr-shopping-cart" aria-hidden="true"></i><span id="cartCount" class="ec-header-count" @if($cartCount <= 0) hidden @endif>{{ $cartCount > 99 ? '99+' : $cartCount }}</span></span>
                    <span><strong>{{ __('storefront.cart') }}</strong><small>{{ trans_choice('storefront.cart_items', $cartCount, ['count' => $cartCount]) }}</small></span>
                </a>
            </div>
        </div>
    </div>

    <div class="ec-primary-nav">
        <div class="ec-container ec-primary-nav-inner">
            <div class="relative ec-category-wrap">
                <button id="ecCategoryTrigger" type="button" class="ec-category-button" aria-expanded="false" aria-controls="ecCategoryDropdown"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i><span>{{ __('storefront.all_categories') }}</span><i class="fi fi-rr-angle-small-down" aria-hidden="true"></i></button>
                <div id="ecCategoryDropdown" class="ec-category-mega" hidden>
                    <aside class="ec-mega-family-panel" aria-label="{{ __('storefront.product_categories') }}">
                        <p class="ec-mega-eyebrow">{{ __('storefront.product_categories') }}</p>
                        <div id="ecMegaCategoryMenu" class="ec-mega-family-list" role="tablist" aria-label="{{ __('storefront.product_categories') }}"></div>
                    </aside>
                    <div id="ecMegaCategoryContent" class="ec-mega-catalog" role="tabpanel" aria-live="polite"></div>
                </div>
            </div>
            <div class="ec-primary-links">
                @foreach ([
                    [__('storefront.home'), route('frontend.index'), request()->routeIs('frontend.index')],
                    [__('storefront.products'), route('frontend.kategori'), request()->routeIs('frontend.kategori')],
                    [__('storefront.promotion'), route('frontend.flash-sale'), request()->routeIs('frontend.flash-sale')],
                    [__('storefront.technical'), route('frontend.pages.show', 'technical'), request()->routeIs('frontend.pages.show') && request()->route('slug') === 'technical'],
                    [__('storefront.project'), route('frontend.pages.show', 'project'), request()->routeIs('frontend.pages.show') && request()->route('slug') === 'project'],
                    [__('storefront.how_to_shop'), route('frontend.pages.show', 'cara-belanja'), request()->routeIs('frontend.pages.show') && request()->route('slug') === 'cara-belanja'],
                    [__('storefront.about_boq'), route('frontend.pages.show', 'tentang-boq'), request()->routeIs('frontend.pages.show') && request()->route('slug') === 'tentang-boq'],
                    [__('storefront.contact_us'), route('frontend.pages.show', 'pusat-bantuan'), request()->routeIs('frontend.pages.show') && request()->route('slug') === 'pusat-bantuan'],
                ] as [$label, $url, $isActive])
                    <a href="{{ $url }}" class="{{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>

    <div id="ecMobileNavDrawer" class="border-t border-slate-200 bg-white p-3 shadow-lg md:hidden" hidden>
        <div class="grid gap-1">
            <div class="mb-1 flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold"><span>{{ __('storefront.language') }}</span><span class="flex gap-1">@foreach (['id' => 'ID', 'en' => 'EN'] as $locale => $label)<a href="{{ route('locale.switch', ['locale' => $locale, 'redirect' => request()->getRequestUri()]) }}" class="rounded-md px-2 py-1 {{ app()->getLocale() === $locale ? 'bg-blue-900 text-white' : 'bg-slate-100' }}" lang="{{ $locale }}" hreflang="{{ $locale }}" @if(app()->getLocale() === $locale) aria-current="true" @endif>{{ $label }}</a>@endforeach</span></div>
            <a href="{{ route('frontend.kategori') }}" class="flex min-h-11 items-center justify-between rounded-lg bg-slate-50 px-3 text-sm font-bold text-slate-900 hover:bg-slate-100"><span class="flex items-center gap-3"><i class="fi fi-rr-apps" aria-hidden="true"></i>{{ __('storefront.all_categories') }}</span><i class="fi fi-rr-angle-small-right" aria-hidden="true"></i></a>
            <a href="{{ route('frontend.index') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.home') }}</a>
            <a href="{{ route('frontend.kategori') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.products') }}</a>
            <a href="{{ route('frontend.flash-sale') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.promotion') }}</a>
            <a href="{{ route('frontend.pages.show', 'technical') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.technical') }}</a>
            <a href="{{ route('frontend.pages.show', 'project') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.project') }}</a>
            <a href="{{ route('frontend.pages.show', 'cara-belanja') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.how_to_shop') }}</a>
            <a href="{{ route('frontend.pages.show', 'tentang-boq') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.about_boq') }}</a>
            <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">{{ __('storefront.contact_us') }}</a>
        </div>
    </div>

    <div id="ecMobileSearch" class="border-t border-slate-200 bg-white p-3 md:hidden" hidden>
        <div class="relative">
            <form action="{{ route('frontend.search') }}" method="GET" class="ec-search-shell" role="search"><label for="ecNavSearchMobile" class="sr-only">{{ __('storefront.search_products') }}</label><input id="ecNavSearchMobile" name="q" value="{{ trim(request('q', $query ?? '')) }}" placeholder="{{ __('storefront.search_placeholder') }}" class="min-w-0 flex-1 bg-transparent px-4 outline-none" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ecNavSearchDropdownMobile" data-storefront-autocomplete data-suggestions-url="{{ route('frontend.search.suggestions') }}" /><button class="ec-reference-search-button m-1 rounded-md" type="submit" aria-label="{{ __('storefront.search') }}"><i class="fi fi-rr-search" aria-hidden="true"></i></button></form>
            <div id="ecNavSearchDropdownMobile" class="ec-dropdown left-0 right-0 top-full mt-2 max-h-[60vh] overflow-y-auto" role="listbox" aria-label="{{ __('storefront.product_suggestions') }}" hidden></div>
        </div>
    </div>
</nav>

<script id="ec-shell-config" type="application/json">{!! json_encode([
    'categories' => $megaCategories,
    'cartCountUrl' => auth()->check() ? route('frontend.cart.count') : null,
    'notifUrl' => auth()->check() ? route('frontend.notifications.index') : null,
    'notifReadAllUrl' => auth()->check() ? route('frontend.notifications.read-all') : null,
    'csrfToken' => csrf_token(),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
