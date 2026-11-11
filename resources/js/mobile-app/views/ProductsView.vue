<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileProductCard from '../components/MobileProductCard.vue';
import MobileSearchLauncher from '../components/MobileSearchLauncher.vue';
import { request } from '../services/api';
import { trackMobileEvent } from '../services/telemetry';

const route = useRoute();
const router = useRouter();
const products = ref([]);
const meta = ref(null);
const loading = ref(true);
const error = ref('');
let controller;

const isCategory = computed(() => route.name === 'category-products');
const title = computed(() => isCategory.value ? 'Produk kategori' : (route.query.q ? `Hasil “${route.query.q}”` : 'Semua Produk'));
const filters = ref({
    sort: String(route.query.sort || 'newest'),
    in_stock: route.query.in_stock === '1',
    min_price: String(route.query.min_price || ''),
    max_price: String(route.query.max_price || ''),
});

const load = async () => {
    controller?.abort();
    controller = new AbortController();
    loading.value = true;
    error.value = '';
    const params = new URLSearchParams();
    if (route.query.q) params.set('q', String(route.query.q));
    if (route.params.slug) params.set('category', String(route.params.slug));
    ['sort', 'in_stock', 'min_price', 'max_price', 'page'].forEach((key) => {
        if (route.query[key]) params.set(key, String(route.query[key]));
    });

    try {
        const response = await request(`/api/mobile/v1/products?${params.toString()}`, { signal: controller.signal });
        products.value = response.data || [];
        meta.value = response.meta || null;
        if (route.query.q) trackMobileEvent('search_results_viewed', { result_count: products.value.length }, route.name);
    } catch (exception) {
        if (exception.name !== 'AbortError') error.value = exception.message || 'Produk belum dapat dimuat.';
    } finally {
        loading.value = false;
    }
};

const applyFilters = () => {
    const query = { ...route.query };
    delete query.page;
    ['sort', 'min_price', 'max_price'].forEach((key) => {
        if (filters.value[key]) query[key] = filters.value[key];
        else delete query[key];
    });
    if (filters.value.in_stock) query.in_stock = '1';
    else delete query.in_stock;
    router.replace({ query });
};

const resetFilters = () => {
    filters.value = { sort: 'newest', in_stock: false, min_price: '', max_price: '' };
    router.replace({ query: route.query.q ? { q: route.query.q } : {} });
};

const pageLink = (page) => ({
    name: route.name,
    params: route.params,
    query: { ...route.query, page },
});

watch(() => route.fullPath, () => {
    filters.value = {
        sort: String(route.query.sort || 'newest'),
        in_stock: route.query.in_stock === '1',
        min_price: String(route.query.min_price || ''),
        max_price: String(route.query.max_price || ''),
    };
    load();
}, { immediate: true });
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink v-if="isCategory" :to="{ name: 'categories' }" class="mobile-back-link">← Semua kategori</RouterLink>
        <span class="mobile-eyebrow">Katalog mobile</span>
        <h1 id="mobile-route-heading" tabindex="-1">{{ title }}</h1>
        <div class="mobile-search-sticky"><MobileSearchLauncher :value="String(route.query.q || '')" /></div>

        <details class="mobile-filter-panel">
            <summary>Filter & urutkan <span v-if="meta">{{ meta.total }} produk</span></summary>
            <div class="mobile-filter-fields">
                <label>Urutkan
                    <select v-model="filters.sort">
                        <option value="newest">Terbaru</option>
                        <option value="best_selling">Terlaris</option>
                        <option value="price_asc">Harga terendah</option>
                        <option value="price_desc">Harga tertinggi</option>
                        <option value="name_asc">Nama A–Z</option>
                        <option value="name_desc">Nama Z–A</option>
                    </select>
                </label>
                <div class="mobile-price-filter">
                    <label>Harga minimum<input v-model="filters.min_price" type="number" min="0" inputmode="numeric" /></label>
                    <label>Harga maksimum<input v-model="filters.max_price" type="number" min="0" inputmode="numeric" /></label>
                </div>
                <label class="mobile-check"><input v-model="filters.in_stock" type="checkbox" /> Hanya stok tersedia</label>
                <div class="mobile-filter-actions">
                    <button type="button" class="mobile-secondary-button" @click="resetFilters">Reset</button>
                    <button type="button" class="mobile-primary-button" @click="applyFilters">Terapkan</button>
                </div>
            </div>
        </details>

        <div v-if="loading" class="mobile-product-grid" aria-label="Memuat produk" aria-busy="true">
            <div v-for="item in 6" :key="item" class="mobile-product-skeleton"></div>
        </div>
        <MobileCatalogState v-else-if="error" title="Produk gagal dimuat" :message="error" :retry="load" />
        <MobileCatalogState v-else-if="!products.length" title="Produk tidak ditemukan" message="Coba kata kunci atau filter yang berbeda." />
        <div v-else class="mobile-product-grid">
            <MobileProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>

        <nav v-if="meta && meta.last_page > 1" class="mobile-pagination" aria-label="Halaman produk">
            <RouterLink v-if="meta.current_page > 1" :to="pageLink(meta.current_page - 1)">← Sebelumnya</RouterLink>
            <span>Halaman {{ meta.current_page }} dari {{ meta.last_page }}</span>
            <RouterLink v-if="meta.current_page < meta.last_page" :to="pageLink(meta.current_page + 1)">Berikutnya →</RouterLink>
        </nav>
    </section>
</template>
