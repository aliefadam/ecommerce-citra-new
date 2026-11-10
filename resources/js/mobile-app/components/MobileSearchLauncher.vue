<script setup>
import { ref } from 'vue';
import MobileIcon from './MobileIcon.vue';
import MobileSearchOverlay from './MobileSearchOverlay.vue';

defineProps({
    value: { type: String, default: '' },
    placeholder: { type: String, default: 'Cari produk disini' },
});

const open = ref(false);
</script>

<template>
    <button
        type="button"
        class="mobile-astro-search"
        :class="{ 'has-value': value }"
        :aria-label="value ? `Ubah pencarian: ${value}` : placeholder"
        aria-haspopup="dialog"
        @click="open = true"
    >
        <MobileIcon name="search" :size="22" />
        <span>{{ value || placeholder }}</span>
    </button>
    <MobileSearchOverlay :open="open" :initial-value="value" @close="open = false" />
</template>
