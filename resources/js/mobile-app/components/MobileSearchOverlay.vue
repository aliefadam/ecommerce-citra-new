<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MobileIcon from './MobileIcon.vue';
import { request } from '../services/api';
import { useImageFallback } from '../services/images';
import { trackMobileEvent } from '../services/telemetry';

const props = defineProps({
    open: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const RECENT_KEY = 'mobile-spa:recent-searches';
// Dummy popular terms until a real "popular search" endpoint exists.
const POPULAR_POOL = [
    'semen', 'besi beton', 'cat tembok', 'pipa pvc', 'keramik', 'baut', 'kabel listrik', 'triplek',
    'paku', 'genteng', 'bata ringan', 'gypsum', 'sekrup', 'lem kayu', 'kunci pas', 'meteran',
    'sarung tangan', 'helm proyek', 'kawat', 'stop kontak',
];

const route = useRoute();
const router = useRouter();
const input = ref(null);
const query = ref('');
const suggestions = ref([]);
const loading = ref(false);
const activeIndex = ref(-1);
const recent = ref(readRecent());
const popular = ref(pick(POPULAR_POOL, 10));
const categories = ref([]);
const popularCategories = ref([]);
let debounceTimer;
let controller;

const term = computed(() => query.value.trim());
const showSuggestions = computed(() => term.value.length >= 2);

function shuffle(list) {
    const copy = [...list];
    for (let i = copy.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
}
function pick(list, count) {
    return shuffle(list).slice(0, count);
}
function readRecent() {
    try {
        const value = JSON.parse(window.localStorage.getItem(RECENT_KEY) || '[]');
        return Array.isArray(value) ? value.filter((item) => typeof item === 'string').slice(0, 8) : [];
    } catch {
        return [];
    }
}
function saveRecent(value) {
    recent.value = [value, ...recent.value.filter((item) => item.toLowerCase() !== value.toLowerCase())].slice(0, 8);
    try { window.localStorage.setItem(RECENT_KEY, JSON.stringify(recent.value)); } catch { /* storage unavailable */ }
}
const clearRecent = () => {
    recent.value = [];
    try { window.localStorage.removeItem(RECENT_KEY); } catch { /* storage unavailable */ }
};

const refreshPopular = () => { popular.value = pick(POPULAR_POOL, 10); };
const refreshCategories = () => { popularCategories.value = pick(categories.value, 8); };

const loadCategories = async () => {
    if (categories.value.length) return;
    try {
        const response = await request('/api/mobile/v1/categories');
        categories.value = (response.data || []).filter((category) => category.slug);
        refreshCategories();
    } catch {
        categories.value = [];
    }
};

const close = () => {
    clearTimeout(debounceTimer);
    controller?.abort();
    emit('close');
};

const submit = (value = term.value) => {
    const text = value.trim();
    if (text) saveRecent(text);
    trackMobileEvent('search_submitted', { outcome: text ? 'query' : 'browse' }, route.name);
    close();
    router.push({ name: 'search', query: text ? { q: text } : {} });
};

const openProduct = (product) => {
    if (term.value) saveRecent(term.value);
    close();
    router.push({ name: 'product', params: { slug: product.slug } });
};

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        event.preventDefault();
        close();
        return;
    }
    if (!showSuggestions.value || !suggestions.value.length) return;
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = (activeIndex.value + 1) % suggestions.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = activeIndex.value <= 0 ? suggestions.value.length - 1 : activeIndex.value - 1;
    } else if (event.key === 'Enter' && activeIndex.value >= 0) {
        event.preventDefault();
        openProduct(suggestions.value[activeIndex.value]);
    }
};

watch(query, (value) => {
    clearTimeout(debounceTimer);
    controller?.abort();
    suggestions.value = [];
    activeIndex.value = -1;
    const text = value.trim();
    if (text.length < 2) {
        loading.value = false;
        return;
    }
    loading.value = true;
    debounceTimer = window.setTimeout(async () => {
        controller = new AbortController();
        try {
            const response = await request(`/api/mobile/v1/search/suggestions?q=${encodeURIComponent(text)}`, { signal: controller.signal });
            suggestions.value = response.data || [];
        } catch (error) {
            if (error.name !== 'AbortError') suggestions.value = [];
        } finally {
            loading.value = false;
        }
    }, 250);
});

watch(() => props.open, async (isOpen) => {
    document.documentElement.classList.toggle('mobile-search-locked', isOpen);
    if (!isOpen) return;
    query.value = '';
    recent.value = readRecent();
    loadCategories();
    await nextTick();
    input.value?.focus();
});

watch(() => route.fullPath, () => { if (props.open) emit('close'); });

onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
    controller?.abort();
    document.documentElement.classList.remove('mobile-search-locked');
});
</script>

<template>
    <Teleport to="body">
        <Transition name="mobile-search-sheet">
            <div v-if="open" class="mobile-search-sheet" role="dialog" aria-modal="true" aria-label="Pencarian produk">
                <div class="mobile-search-sheet-bar" role="search">
                    <form class="mobile-search-sheet-form" @submit.prevent="submit()">
                        <label class="mobile-visually-hidden" for="mobile-search-sheet-input">Cari produk</label>
                        <MobileIcon name="search" :size="22" />
                        <input
                            id="mobile-search-sheet-input"
                            ref="input"
                            v-model="query"
                            type="search"
                            name="q"
                            enterkeyhint="search"
                            autocomplete="off"
                            placeholder="Cari produk disini"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-controls="mobile-search-sheet-suggestions"
                            :aria-expanded="showSuggestions"
                            :aria-activedescendant="activeIndex >= 0 ? `mobile-sheet-suggestion-${activeIndex}` : undefined"
                            @keydown="onKeydown"
                        />
                        <button v-if="query" type="button" class="mobile-search-sheet-clear" aria-label="Hapus kata kunci" @click="query = ''; input?.focus()">×</button>
                    </form>
                    <button type="button" class="mobile-search-sheet-cancel" @click="close">Batal</button>
                </div>

                <div class="mobile-search-sheet-body">
                    <div class="mobile-visually-hidden" aria-live="polite">
                        {{ showSuggestions ? (loading ? 'Mencari produk' : `${suggestions.length} saran ditemukan`) : '' }}
                    </div>

                    <div v-if="showSuggestions" id="mobile-search-sheet-suggestions" class="mobile-search-sheet-results" role="listbox">
                        <button type="button" class="mobile-search-sheet-query" @click="submit()">
                            <MobileIcon name="search" :size="18" />
                            <span>Cari “<strong>{{ term }}</strong>”</span>
                        </button>
                        <div v-if="loading" class="mobile-suggestion-state">Mencari…</div>
                        <template v-else>
                            <button
                                v-for="(product, index) in suggestions"
                                :id="`mobile-sheet-suggestion-${index}`"
                                :key="product.id"
                                type="button"
                                :class="['mobile-suggestion', { 'is-active': activeIndex === index }]"
                                role="option"
                                :aria-selected="activeIndex === index"
                                @click="openProduct(product)"
                            >
                                <img :src="product.image || '/imgs/product-placeholder.svg'" alt="" @error="useImageFallback" />
                                <span><strong>{{ product.name }}</strong><small>{{ product.sku || product.category?.name }}</small></span>
                            </button>
                            <div v-if="!suggestions.length" class="mobile-suggestion-state">Tidak ada saran. Tekan cari untuk melihat semua hasil.</div>
                        </template>
                    </div>

                    <template v-else>
                        <section v-if="recent.length" class="mobile-search-sheet-section">
                            <div class="mobile-search-sheet-heading">
                                <h2>Pencarian Terakhir</h2>
                                <button type="button" @click="clearRecent">Hapus</button>
                            </div>
                            <div class="mobile-search-chips">
                                <button v-for="item in recent" :key="item" type="button" @click="submit(item)">
                                    <MobileIcon name="clock" :size="14" />{{ item }}
                                </button>
                            </div>
                        </section>

                        <section class="mobile-search-sheet-section">
                            <div class="mobile-search-sheet-heading">
                                <h2>Pencarian Populer</h2>
                                <button type="button" @click="refreshPopular">Refresh</button>
                            </div>
                            <div class="mobile-search-chips">
                                <button v-for="item in popular" :key="item" type="button" @click="submit(item)">{{ item }}</button>
                            </div>
                        </section>

                        <section class="mobile-search-sheet-section">
                            <div class="mobile-search-sheet-heading">
                                <h2>Kategori Populer</h2>
                                <button v-if="categories.length" type="button" @click="refreshCategories">Refresh</button>
                            </div>
                            <div v-if="popularCategories.length" class="mobile-search-categories">
                                <RouterLink
                                    v-for="category in popularCategories"
                                    :key="category.id"
                                    :to="{ name: 'category-products', params: { slug: category.slug } }"
                                    @click="close"
                                >
                                    <img :src="category.image || '/imgs/product-placeholder.svg'" alt="" loading="lazy" @error="useImageFallback" />
                                    <span>{{ category.name }}</span>
                                </RouterLink>
                            </div>
                        </section>
                    </template>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
