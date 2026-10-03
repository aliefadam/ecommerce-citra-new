@props(['compact' => false])

<div {{ $attributes->class([
    'inline-flex items-center rounded-lg border border-slate-200 bg-white p-0.5 text-xs font-bold text-slate-600 shadow-sm',
]) }} role="group" aria-label="{{ __('storefront.language') }}">
    @foreach (['id' => 'ID', 'en' => 'EN'] as $locale => $label)
        <a href="{{ route('locale.switch', ['locale' => $locale, 'redirect' => request()->getRequestUri()]) }}"
            class="rounded-md px-2 py-1.5 transition-colors {{ app()->getLocale() === $locale ? 'bg-blue-900 text-white' : 'hover:bg-slate-100' }}"
            lang="{{ $locale }}" hreflang="{{ $locale }}" @if(app()->getLocale() === $locale) aria-current="true" @endif>
            {{ $label }}
        </a>
    @endforeach
</div>
