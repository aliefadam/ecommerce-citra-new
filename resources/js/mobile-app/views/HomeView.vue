<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileIcon from '../components/MobileIcon.vue';
import MobileProductCard from '../components/MobileProductCard.vue';
import MobileSearchBox from '../components/MobileSearchBox.vue';
import { request } from '../services/api';
import { useImageFallback } from '../services/images';
import { useSessionStore } from '../stores/session';

const session = useSessionStore();
const home = ref(null);
const loading = ref(true);
const error = ref('');
const carousel = ref(null);
const activeBanner = ref(0);
let autoplayTimer;

const banners = computed(() => home.value?.banners || []);

const shortcuts = [
    { label: 'Produk Terbaru', icon: 'sparkle', tone: 'sky', to: { name: 'search', query: { sort: 'newest' } } },
    { label: 'Harga Termurah', icon: 'tag', tone: 'mint', to: { name: 'search', query: { sort: 'price_asc' } } },
    { label: 'Stok Tersedia', icon: 'box', tone: 'sun', to: { name: 'search', query: { in_stock: '1' } } },
    { label: 'Semua Kategori', icon: 'grid', tone: 'lilac', to: { name: 'categories' } },
];

const load = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await request('/api/mobile/v1/home');
        home.value = response.data;
        activeBanner.value = 0;
        startAutoplay();
    } catch (exception) {
        error.value = exception.message || 'Beranda belum dapat dimuat.';
    } finally {
        loading.value = false;
    }
};

const goToBanner = (index) => {
    const track = carousel.value;
    if (!track) return;
    const slide = track.children[index];
    if (slide) track.scrollTo({ left: slide.offsetLeft - track.offsetLeft, behavior: 'smooth' });
};

const onCarouselScroll = () => {
    const track = carousel.value;
    if (!track || !track.clientWidth) return;
    activeBanner.value = Math.round(track.scrollLeft / track.clientWidth);
};

const stopAutoplay = () => window.clearInterval(autoplayTimer);
function startAutoplay() {
    stopAutoplay();
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return;
    autoplayTimer = window.setInterval(() => {
        if (banners.value.length < 2 || document.hidden) return;
        goToBanner((activeBanner.value + 1) % banners.value.length);
    }, 5000);
}

onMounted(load);
onBeforeUnmount(stopAutoplay);
</script>

<template>
    <section class="mobile-screen mobile-home" aria-labelledby="mobile-route-heading">
        <div class="mobile-home-top">
            <div class="mobile-hero">
                <span class="mobile-eyebrow">Katalog kebutuhan proyek</span>
                <h1 id="mobile-route-heading" tabindex="-1">
                    {{ session.user ? `Halo, ${session.user.name}` : 'Temukan material yang tepat' }}
                </h1>
                <p>Cari berdasarkan nama, SKU, ukuran, atau spesifikasi teknis.</p>
            </div>

            <MobileSearchBox />

            <div v-if="loading" class="mobile-banner-skeleton" aria-hidden="true"></div>
            <div v-else-if="banners.length" class="mobile-banner-area">
                <div
                    ref="carousel"
                    class="mobile-banner-carousel"
                    aria-roledescription="carousel"
                    aria-label="Promo pilihan"
                    @scroll.passive="onCarouselScroll"
                    @pointerdown="stopAutoplay"
                    @focusin="stopAutoplay"
                >
                    <template v-for="(banner, index) in banners" :key="banner.id">
                        <a v-if="banner.target_url" :href="banner.target_url" class="mobile-banner" :aria-label="banner.title || `Promo ${index + 1}`">
                            <img :src="banner.image" :alt="banner.title || 'Promo pilihan'" :fetchpriority="index === 0 ? 'high' : 'auto'" @error="useImageFallback" />
                        </a>
                        <div v-else class="mobile-banner">
                            <img :src="banner.image" :alt="banner.title || 'Promo pilihan'" :fetchpriority="index === 0 ? 'high' : 'auto'" @error="useImageFallback" />
                        </div>
                    </template>
                </div>
                <div v-if="banners.length > 1" class="mobile-banner-dots">
                    <button
                        v-for="(banner, index) in banners"
                        :key="banner.id"
                        type="button"
                        :class="{ 'is-active': activeBanner === index }"
                        :aria-label="`Tampilkan promo ${index + 1}`"
                        :aria-current="activeBanner === index ? 'true' : undefined"
                        @click="stopAutoplay(); goToBanner(index)"
                    ><span></span></button>
                </div>
            </div>
        </div>

        <div class="mobile-home-body">
            <div v-if="loading" class="mobile-home-skeleton" aria-label="Memuat beranda" aria-busy="true">
                <span></span><span></span><span></span>
            </div>
            <MobileCatalogState v-else-if="error" title="Beranda gagal dimuat" :message="error" :retry="load" />

            <template v-else-if="home">
                <div class="mobile-section-heading mobile-section-row">
                    <h2>Pilihan Cepat</h2>
                </div>
                <div class="mobile-shortcut-grid">
                    <RouterLink v-for="item in shortcuts" :key="item.label" :to="item.to" :class="['mobile-shortcut', `is-${item.tone}`]">
                        <strong>{{ item.label }}</strong>
                        <span class="mobile-shortcut-icon"><MobileIcon :name="item.icon" :size="26" /></span>
                    </RouterLink>
                </div>

                <div class="mobile-section-heading mobile-section-row">
                    <h2>Kategori</h2>
                    <RouterLink :to="{ name: 'categories' }">Lihat semua <MobileIcon name="chevron" :size="16" /></RouterLink>
                </div>
                <div class="mobile-home-categories">
                    <RouterLink v-for="category in home.categories" :key="category.id" :to="{ name: 'category-products', params: { slug: category.slug } }">
                        <strong>{{ category.name }}</strong>
                        <small>{{ category.products_count }} produk</small>
                        <img :src="category.image || '/imgs/product-placeholder.svg'" alt="" @error="useImageFallback" />
                    </RouterLink>
                </div>

                <div class="mobile-section-heading mobile-section-row">
                    <h2>Produk Terbaru</h2>
                    <RouterLink :to="{ name: 'search' }">Lihat semua <MobileIcon name="chevron" :size="16" /></RouterLink>
                </div>
                <div v-if="home.featured_products.length" class="mobile-product-rail">
                    <MobileProductCard v-for="product in home.featured_products" :key="product.id" :product="product" eager />
                </div>
                <MobileCatalogState v-else title="Belum ada produk" message="Produk terbaru akan tampil di sini." />
            </template>
        </div>
    </section>
</template>
