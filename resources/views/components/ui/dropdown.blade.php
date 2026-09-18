@props(['id', 'label'])
<div class="relative" data-ec-dropdown>
    <button type="button" class="ec-btn ec-btn-outline" aria-expanded="false" aria-controls="{{ $id }}" data-ec-dropdown-trigger>{{ $label }} <span aria-hidden="true">⌄</span></button>
    <div id="{{ $id }}" class="ec-dropdown right-0 top-full mt-2 min-w-52 p-2" data-ec-dropdown-panel hidden>{{ $slot }}</div>
</div>
