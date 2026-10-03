@php
    $socialLinks = collect([
        ['key' => 'social_instagram', 'label' => 'Instagram', 'icon' => 'ri-instagram-line'],
        ['key' => 'social_facebook', 'label' => 'Facebook', 'icon' => 'ri-facebook-line'],
        ['key' => 'social_youtube', 'label' => 'YouTube', 'icon' => 'ri-youtube-line'],
        ['key' => 'social_whatsapp', 'label' => 'WhatsApp', 'icon' => 'ri-whatsapp-line'],
    ])->filter(fn ($social) => !empty($appStoreSettings[$social['key']]));
@endphp

<footer @class([
    'bg-slate-950 text-slate-300',
    'mt-10 border-t-4 border-orange-500' => !request()->routeIs('frontend.index'),
])>
    <div class="ec-container py-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            <div>
                <a href="{{ route('frontend.index') }}" class="inline-flex min-h-11 items-center text-white" aria-label="{{ $appStoreName }}, {{ strtolower(__('storefront.home')) }}">
                    @if(!empty($appStoreLogoUrl))<img src="{{ $appStoreLogoUrl }}" alt="" width="140" height="44" class="h-10 w-auto max-w-36 object-contain brightness-0 invert" />
                    @else<span class="ec-display grid size-10 place-items-center rounded-lg bg-white text-sm text-slate-950" aria-hidden="true">EC</span>@endif
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">{{ __('storefront.supply_partner') }}</p>
                <p class="mt-4 border-l-2 border-orange-500 pl-3 text-xs font-semibold uppercase tracking-wider text-slate-300">Professional partner for your professional results</p>
            </div>

            <div>
                <h2 class="ec-display text-base text-white">{{ __('storefront.shopping') }}</h2>
                <ul class="mt-4 grid gap-1 text-sm">
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.kategori') }}">{{ __('storefront.all_products') }}</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.flash-sale') }}">{{ __('storefront.active_promotions') }}</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.search', ['sort' => 'newest']) }}">{{ __('storefront.new_products') }}</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.order-tracking.index') }}">{{ __('storefront.track_order') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="ec-display text-base text-white">{{ __('storefront.information') }}</h2>
                <ul class="mt-4 grid gap-1 text-sm">
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'pusat-bantuan') }}">{{ __('storefront.help_center') }}</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'cara-belanja') }}">{{ __('storefront.how_to_shop') }}</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'kebijakan-privasi') }}">{{ __('storefront.privacy_policy') }}</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'syarat-ketentuan') }}">{{ __('storefront.terms') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="ec-display text-base text-white">{{ __('storefront.technical_support') }}</h2>
                <p class="mt-4 text-sm leading-6 text-slate-400">{{ __('storefront.support_hours') }}</p>
                @if(!empty($appStoreSettings['social_whatsapp']))
                    <a href="{{ $appStoreSettings['social_whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="ec-btn ec-btn-primary mt-5">
                        <i class="ri-whatsapp-line text-base" aria-hidden="true"></i>
                        {{ __('storefront.whatsapp_consultation') }}
                    </a>
                @else
                    <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}" class="ec-btn ec-btn-outline mt-5 border-slate-600 bg-transparent text-white">{{ __('storefront.open_help_center') }}</a>
                @endif
                @if($socialLinks->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('storefront.social_media') }}">
                        @foreach($socialLinks as $social)
                            <a href="{{ $appStoreSettings[$social['key']] }}" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center rounded-lg border border-slate-700 text-xl hover:border-slate-500 hover:text-white" aria-label="{{ $social['label'] }}">
                                <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-slate-800 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $appStoreName }}. {{ __('storefront.copyright') }}</p>
            <p>PT Citra Abadi Teknik Indonesia · Indonesia</p>
        </div>
    </div>
</footer>
