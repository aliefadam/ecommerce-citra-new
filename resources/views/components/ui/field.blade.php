@props(['label', 'name', 'type' => 'text', 'value' => null, 'hint' => null, 'error' => null])
@php
    $message = $error ?: $errors->first($name);
    $describedBy = $message ? $name.'-error' : ($hint ? $name.'-hint' : null);
@endphp
<label class="ec-field" for="{{ $name }}">
    <span class="ec-label">{{ $label }}</span>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if($message) aria-invalid="true" @endif {{ $attributes->class('ec-input') }} />
    @if($message)<span id="{{ $name }}-error" class="ec-error">{{ $message }}</span>
    @elseif($hint)<span id="{{ $name }}-hint" class="ec-help">{{ $hint }}</span>@endif
</label>
