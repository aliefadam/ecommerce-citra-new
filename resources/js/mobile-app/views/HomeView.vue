<script setup>
import { onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileProductCard from '../components/MobileProductCard.vue';
import MobileSearchBox from '../components/MobileSearchBox.vue';
import { request } from '../services/api';
import { useImageFallback } from '../services/images';
import { useSessionStore } from '../stores/session';

const session = useSessionStore();
const home = ref(null);
const loading = ref(true);
const error = ref('');

const load = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await request('/api/mobile/v1/home');
        home.value = response.data;
    } catch (exception) {
        error.value = exception.message || 'Beranda belum dapat dimuat.';
    } finally {
        loading.value = false;
    }
};

onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <div class="mobile-hero">
            <span class="mobile-eyebrow">Katalog kebutuhan proyek</span>
            <h1 id="mobile-route-heading" tabindex="-1">
                {{ session.user ? `Halo, ${session.user.name}` : 'Temukan material yang tepat' }}
            </h1>
            <p>Cari berdasarkan nama, SKU, ukuran, atau spesifikasi teknis.</p>
        </div>

        <MobileSearchBox />

        <div v-if="loading" class="mobile-home-skeleton" aria-label="Memuat beranda" aria-busy="true">
            <span></span><span></span><span></span>
        </div>
        <MobileCatalogState v-else-if="error" title="Beranda gagal dimuat" :message="error" :retry="load" />

        <template v-else-if="home">
            <a
                v-if="home.banners[0]?.target_url"
                :href="home.banners[0].target_url"
                class="mobile-banner"
            >
                <img :src="home.banners[0].image" :alt="home.banners[0].title || 'Promo pilihan'" @error="useImageFallback" />
            </a>
            <div v-else-if="home.banners[0]" class="mobile-banner">
                <img :src="home.banners[0].image" :alt="home.banners[0].title || 'Promo pilihan'" @error="useImageFallback" />
            </div>

            <div class="mobile-section-heading mobile-section-row">
                <div><span class="mobile-eyebrow">Jelajahi</span><h2>Kategori</h2></div>
                <RouterLink :to="{ name: 'categories' }">Lihat semua</RouterLink>
            </div>
            <div class="mobile-home-categories">
                <RouterLink v-for="category in home.categories" :key="category.id" :to="{ name: 'category-products', params: { slug: category.slug } }">
                    <img :src="category.image || '/imgs/product-placeholder.svg'" alt="" @error="useImageFallback" />
                    <strong>{{ category.name }}</strong>
                    <small>{{ category.products_count }} produk</small>
                </RouterLink>
            </div>

            <div class="mobile-section-heading mobile-section-row">
                <div><span class="mobile-eyebrow">Baru tersedia</span><h2>Produk terbaru</h2></div>
                <RouterLink :to="{ name: 'search' }">Lihat semua</RouterLink>
            </div>
            <div v-if="home.featured_products.length" class="mobile-product-grid">
                <MobileProductCard v-for="product in home.featured_products" :key="product.id" :product="product" />
            </div>
            <MobileCatalogState v-else title="Belum ada produk" message="Produk terbaru akan tampil di sini." />
        </template>
    </section>
</template>
