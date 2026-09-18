@props(['selected' => false, 'controls' => null])
<button type="button" role="tab" aria-selected="{{ $selected ? 'true' : 'false' }}" @if($controls) aria-controls="{{ $controls }}" @endif
    {{ $attributes->class(['min-h-11 border-b-2 px-4 text-sm font-bold', 'border-blue-700 text-blue-800' => $selected, 'border-transparent text-slate-600' => !$selected]) }}>{{ $slot }}</button>
