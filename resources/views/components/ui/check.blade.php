@props(['label', 'name', 'type' => 'checkbox', 'value' => '1'])
<label class="inline-flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold text-slate-700">
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes->class('size-5 accent-blue-700') }} />
    <span>{{ $label }}</span>
</label>
