<script setup>
import { onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileSearchBox from '../components/MobileSearchBox.vue';
import { request } from '../services/api';

const categories = ref([]);
const loading = ref(true);
const error = ref('');

const load = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await request('/api/mobile/v1/categories');
        categories.value = response.data || [];
    } catch (exception) {
        error.value = exception.message || 'Katalog belum dapat dimuat.';
    } finally {
        loading.value = false;
    }
};

onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Jelajahi produk</span>
        <h1 id="mobile-route-heading" tabindex="-1">Katalog</h1>
        <MobileSearchBox />

        <div v-if="loading" class="mobile-skeleton-list" aria-label="Memuat kategori" aria-busy="true">
            <span v-for="item in 5" :key="item"></span>
        </div>
        <MobileCatalogState v-else-if="error" title="Katalog gagal dimuat" :message="error" :retry="load" />
        <MobileCatalogState v-else-if="!categories.length" title="Belum ada kategori" message="Produk akan tampil setelah katalog tersedia." />

        <div v-else class="mobile-category-list">
            <article v-for="category in categories" :key="category.id" class="mobile-category-group">
                <RouterLink :to="{ name: 'category-products', params: { slug: category.slug } }" class="mobile-category-main">
                    <img :src="category.image || '/imgs/product-placeholder.svg'" alt="" />
                    <span><strong>{{ category.name }}</strong><small>{{ category.products_count }} produk</small></span>
                    <span aria-hidden="true">›</span>
                </RouterLink>
                <div v-if="category.children.length" class="mobile-category-children">
                    <RouterLink
                        v-for="child in category.children"
                        :key="child.id"
                        :to="{ name: 'category-products', params: { slug: child.slug } }"
                    >
                        {{ child.name }} <small>{{ child.products_count }}</small>
                    </RouterLink>
                </div>
            </article>
        </div>
    </section>
</template>
