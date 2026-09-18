@props(['label' => 'Pilihan tampilan'])
<div {{ $attributes->class('border-b border-slate-200') }} role="tablist" aria-label="{{ $label }}">{{ $slot }}</div>
