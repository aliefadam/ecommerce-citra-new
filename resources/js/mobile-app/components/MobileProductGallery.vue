<script setup>
import { ref, watch } from 'vue';
import { useImageFallback } from '../services/images';

const props = defineProps({
    images: { type: Array, default: () => [] },
    alt: { type: String, default: '' },
    // Image URL to bring into view (e.g. the selected variant's image).
    focusImage: { type: String, default: '' },
    badge: { type: String, default: '' },
});

const track = ref(null);
const active = ref(0);

const goTo = (index, behavior = 'smooth') => {
    const element = track.value;
    if (!element || !props.images.length) return;
    const target = (index + props.images.length) % props.images.length;
    element.scrollTo({ left: target * element.clientWidth, behavior });
    active.value = target;
};

const onScroll = () => {
    const element = track.value;
    if (!element || !element.clientWidth) return;
    active.value = Math.round(element.scrollLeft / element.clientWidth);
};

const onKeydown = (event) => {
    if (event.key === 'ArrowRight') { event.preventDefault(); goTo(active.value + 1); }
    if (event.key === 'ArrowLeft') { event.preventDefault(); goTo(active.value - 1); }
};

watch(() => props.focusImage, (image) => {
    const index = props.images.indexOf(image);
    if (index >= 0 && index !== active.value) goTo(index);
});

watch(() => props.images, () => goTo(Math.max(props.images.indexOf(props.focusImage), 0), 'auto'), { flush: 'post' });
</script>

<template>
    <div class="mobile-gallery" aria-roledescription="carousel" :aria-label="`Foto ${alt}`">
        <div
            ref="track"
            class="mobile-gallery-track"
            tabindex="0"
            :aria-label="`Foto ${active + 1} dari ${images.length}`"
            @scroll.passive="onScroll"
            @keydown="onKeydown"
        >
            <div v-for="(image, index) in images" :key="image" class="mobile-gallery-slide" :aria-hidden="index !== active">
                <img
                    :src="image"
                    :alt="images.length > 1 ? `${alt} – foto ${index + 1}` : alt"
                    :loading="index === 0 ? 'eager' : 'lazy'"
                    draggable="false"
                    @error="useImageFallback"
                />
            </div>
        </div>

        <span v-if="badge" class="mobile-gallery-badge">{{ badge }}</span>

        <template v-if="images.length > 1">
            <button type="button" class="mobile-gallery-arrow is-prev" aria-label="Foto sebelumnya" @click="goTo(active - 1)">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6" /></svg>
            </button>
            <button type="button" class="mobile-gallery-arrow is-next" aria-label="Foto berikutnya" @click="goTo(active + 1)">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg>
            </button>
            <span class="mobile-gallery-count" aria-hidden="true">{{ active + 1 }}/{{ images.length }}</span>
            <div class="mobile-gallery-dots">
                <button
                    v-for="(image, index) in images"
                    :key="image"
                    type="button"
                    :class="{ 'is-active': active === index }"
                    :aria-label="`Tampilkan foto ${index + 1}`"
                    :aria-current="active === index ? 'true' : undefined"
                    @click="goTo(index)"
                ><span></span></button>
            </div>
        </template>
    </div>
</template>
