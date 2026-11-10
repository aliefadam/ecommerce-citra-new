<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { request } from '../services/api';
import { useImageFallback } from '../services/images';
import { trackMobileEvent } from '../services/telemetry';

const props = defineProps({
    initialValue: { type: String, default: '' },
    autofocus: { type: Boolean, default: false },
});

const route = useRoute();
const router = useRouter();
const query = ref(props.initialValue);
const suggestions = ref([]);
const loading = ref(false);
const expanded = ref(false);
const activeIndex = ref(-1);
let debounceTimer;
let controller;

watch(() => props.initialValue, (value) => {
    query.value = value;
});

watch(query, (value) => {
    clearTimeout(debounceTimer);
    controller?.abort();
    suggestions.value = [];
    activeIndex.value = -1;

    const term = value.trim();
    if (term.length < 2) {
        loading.value = false;
        expanded.value = false;
        return;
    }

    loading.value = true;
    expanded.value = true;
    debounceTimer = window.setTimeout(async () => {
        controller = new AbortController();
        try {
            const response = await request(`/api/mobile/v1/search/suggestions?q=${encodeURIComponent(term)}`, {
                signal: controller.signal,
            });
            suggestions.value = response.data || [];
        } catch (error) {
            if (error.name !== 'AbortError') suggestions.value = [];
        } finally {
            loading.value = false;
        }
    }, 250);
});

const submit = () => {
    const term = query.value.trim();
    expanded.value = false;
    trackMobileEvent('search_submitted', { outcome: term ? 'query' : 'browse' }, route.name);
    router.push({ name: 'search', query: term ? { q: term } : {} });
};

const selectActive = () => {
    const product = suggestions.value[activeIndex.value];
    if (product) router.push({ name: 'product', params: { slug: product.slug } });
    else submit();
};

const onKeydown = (event) => {
    if (!expanded.value || !suggestions.value.length) return;
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = (activeIndex.value + 1) % suggestions.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = activeIndex.value <= 0 ? suggestions.value.length - 1 : activeIndex.value - 1;
    } else if (event.key === 'Enter' && activeIndex.value >= 0) {
        event.preventDefault();
        selectActive();
    } else if (event.key === 'Escape') {
        expanded.value = false;
    }
};

onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
    controller?.abort();
});
</script>

<template>
    <div class="mobile-search" role="search">
        <form class="mobile-search-form" @submit.prevent="submit">
            <label class="mobile-visually-hidden" :for="`mobile-catalog-search-${route.name}`">Cari produk</label>
            <span aria-hidden="true">⌕</span>
            <input
                :id="`mobile-catalog-search-${route.name}`"
                v-model="query"
                type="search"
                name="q"
                placeholder="Cari nama, SKU, ukuran, material"
                autocomplete="off"
                :autofocus="autofocus"
                role="combobox"
                aria-autocomplete="list"
                aria-controls="mobile-search-suggestions"
                :aria-expanded="expanded"
                :aria-activedescendant="activeIndex >= 0 ? `mobile-suggestion-${activeIndex}` : undefined"
                @focus="expanded = query.trim().length >= 2"
                @keydown="onKeydown"
            />
            <button type="submit" aria-label="Cari">Cari</button>
        </form>

        <div class="mobile-visually-hidden" aria-live="polite">
            {{ loading ? 'Mencari produk' : `${suggestions.length} saran ditemukan` }}
        </div>

        <div v-if="expanded" id="mobile-search-suggestions" class="mobile-suggestions" role="listbox">
            <div v-if="loading" class="mobile-suggestion-state">Mencari…</div>
            <RouterLink
                v-for="(product, index) in suggestions"
                v-else
                :id="`mobile-suggestion-${index}`"
                :key="product.id"
                :to="{ name: 'product', params: { slug: product.slug } }"
                :class="['mobile-suggestion', { 'is-active': activeIndex === index }]"
                role="option"
                :aria-selected="activeIndex === index"
                @click="expanded = false"
            >
                <img :src="product.image || '/imgs/product-placeholder.svg'" alt="" @error="useImageFallback" />
                <span><strong>{{ product.name }}</strong><small>{{ product.sku || product.category?.name }}</small></span>
            </RouterLink>
            <div v-if="!loading && !suggestions.length" class="mobile-suggestion-state">Tidak ada saran.</div>
        </div>
    </div>
</template>
