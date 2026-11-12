<script setup>
import { computed } from 'vue';

// Flaticon UIcons (rounded) — the same icon family used by the desktop storefront.
// Each entry is [regular-rounded codepoint, solid-rounded codepoint].
const glyphs = {
    home: [0xf7b8, 0xf7ad],
    grid: [0xf16c, 0xf16d],
    cart: [0xfbfc, 0xfbd6],
    receipt: [0xfb16, 0xfaf4],
    user: [0xfea0, 0xfe78],
    search: [0xfbba, 0xfb98],
    bell: [0xf239, 0xf237],
    heart: [0xf7a0, 0xf794],
    chevron: [0xf155, 0xf156],
    back: [0xf154, 0xf155],
    share: [0xfbd5, 0xfbaf],
    sparkle: [0xfc84, 0xfc5d],
    tag: [0xfd51, 0xfd27],
    flame: [0xf684, 0xf677],
    box: [0xf2b6, 0xf2b4],
    headset: [0xf794, 0xf788],
    menu: [0xf925, 0xf916],
    chevronDown: [0xf153, 0xf154],
    clock: [0xf453, 0xf44b],
    wifiOff: [0xff07, 0xfedd],
    logout: [0xfc0c, 0xfbe6],
    userPen: [0xfe90, 0xfe68],
    mapMarker: [0xf8f8, 0xf8ea],
    lock: [0xf8d5, 0xf8c8],
    question: [0xf81e, 0xf812],
    book: [0xf274, 0xf272],
    shield: [0xfbdb, 0xfbb5],
};

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 22 },
    // Kept for backwards compatibility with existing callers; glyph icons have no stroke.
    stroke: { type: [Number, String], default: 2 },
    filled: { type: Boolean, default: false },
});

const glyph = computed(() => {
    const codes = glyphs[props.name];
    return codes ? String.fromCodePoint(codes[props.filled ? 1 : 0]) : '';
});

const style = computed(() => {
    const size = typeof props.size === 'number' || /^\d+(\.\d+)?$/.test(String(props.size)) ? `${props.size}px` : props.size;
    return { fontSize: size, width: size, height: size };
});
</script>

<template>
    <i
        class="mobile-icon"
        :class="filled ? 'mobile-icon-solid' : 'mobile-icon-regular'"
        :style="style"
        aria-hidden="true"
    >{{ glyph }}</i>
</template>
