<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileIcon from '../components/MobileIcon.vue';
import MobileProductCard from '../components/MobileProductCard.vue';
import MobileSearchOverlay from '../components/MobileSearchOverlay.vue';
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
let autoplayDelay;

const searchOpen = ref(false);

// Dummy hero artwork (public/imgs/mobile-hero). Replace with real campaign art when available.
const heroSlides = [
    { id: 'hero-1', image: '/imgs/mobile-hero/hero-1.svg', title: 'Material pilihan asli', to: { name: 'search', query: { sort: 'newest' } } },
    { id: 'hero-2', image: '/imgs/mobile-hero/hero-2.svg', title: 'Diskon hingga 30%', to: { name: 'search', query: { sort: 'price_asc' } } },
    { id: 'hero-3', image: '/imgs/mobile-hero/hero-3.svg', title: 'Gratis ongkir', to: { name: 'search', query: { in_stock: '1' } } },
    { id: 'hero-4', image: '/imgs/mobile-hero/hero-4.svg', title: 'Perkakas profesional', to: { name: 'categories' } },
];
// Dummy delivery promise shown in the hero, Astro style.
const heroEta = { label: 'Cepat sampai', value: '25 Menit' };
const banners = computed(() => heroSlides);
const promoBanners = computed(() => home.value?.banners || []);
const addressLabel = computed(() => (session.user ? `Halo, ${session.user.name} · Atur alamat pengiriman` : 'Masuk untuk atur alamat pengiriman'));

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
    } catch (exception) {
        error.value = exception.message || 'Beranda belum dapat dimuat.';
    } finally {
        loading.value = false;
    }
};

const goToBanner = (index) => {
    const track = carousel.value;
    if (!track) return;
    track.scrollTo({ left: index * track.clientWidth, behavior: 'smooth' });
};

const onCarouselScroll = () => {
    const track = carousel.value;
    if (!track || !track.clientWidth) return;
    activeBanner.value = Math.round(track.scrollLeft / track.clientWidth);
};

const stopAutoplay = () => {
    window.clearTimeout(autoplayDelay);
    window.clearInterval(autoplayTimer);
};
function startAutoplay() {
    stopAutoplay();
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return;
    autoplayTimer = window.setInterval(() => {
        if (banners.value.length < 2 || document.hidden) return;
        goToBanner((activeBanner.value + 1) % banners.value.length);
    }, 5000);
}

onMounted(() => {
    window.addEventListener('mobile-spa:route-started', stopAutoplay);
    autoplayDelay = window.setTimeout(startAutoplay, 3000);
    load();
});
onBeforeUnmount(() => {
    stopAutoplay();
    window.removeEventListener('mobile-spa:route-started', stopAutoplay);
});
</script>

<template>
    <section class="mobile-screen mobile-home" aria-labelledby="mobile-route-heading">
        <div class="mobile-astro-hero">
            <h1 id="mobile-route-heading" class="mobile-visually-hidden" tabindex="-1">
                {{ session.user ? `Halo, ${session.user.name}` : 'Temukan material yang tepat' }}
            </h1>

            <div
                ref="carousel"
                class="mobile-astro-hero-track"
                aria-roledescription="carousel"
                aria-label="Promo pilihan"
                @scroll.passive="onCarouselScroll"
                @pointerdown="stopAutoplay"
                @focusin="stopAutoplay"
            >
                <RouterLink
                    v-for="(slide, index) in banners"
                    :key="slide.id"
                    :to="slide.to"
                    class="mobile-astro-hero-slide"
                    :aria-label="slide.title"
                    :tabindex="activeBanner === index ? 0 : -1"
                >
                    <img :src="slide.image" alt="" :fetchpriority="index === 0 ? 'high' : 'auto'" :loading="index === 0 ? 'eager' : 'lazy'" draggable="false" @error="useImageFallback" />
                </RouterLink>
            </div>

            <div class="mobile-astro-hero-overlay">
                <div class="mobile-astro-hero-top">
                    <RouterLink :to="{ name: 'account' }" class="mobile-astro-eta">
                        <small>{{ heroEta.label }}</small>
                        <strong>{{ heroEta.value }}</strong>
                        <span><em>{{ addressLabel }}</em><MobileIcon name="chevronDown" :size="16" :stroke="2.4" /></span>
                    </RouterLink>
                    <div class="mobile-astro-actions">
                        <a href="/pages/pusat-bantuan?web=1" class="mobile-astro-chat">
                            <MobileIcon name="headset" :size="18" /> Chat CS
                        </a>
                        <RouterLink :to="{ name: 'categories' }" class="mobile-astro-menu" aria-label="Menu kategori">
                            <MobileIcon name="menu" :size="20" />
                        </RouterLink>
                    </div>
                </div>

                <button type="button" class="mobile-astro-search" aria-label="Cari produk disini" aria-haspopup="dialog" @click="searchOpen = true">
                    <MobileIcon name="search" :size="22" />
                    <span>Cari produk disini</span>
                </button>
            </div>

            <div v-if="banners.length > 1" class="mobile-astro-hero-dots">
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

        <MobileSearchOverlay :open="searchOpen" @close="searchOpen = false" />

        <div class="mobile-home-body">
            <div v-if="loading" class="mobile-home-skeleton" aria-label="Memuat beranda" aria-busy="true">
                <span></span><span></span><span></span>
            </div>
            <MobileCatalogState v-else-if="error" title="Beranda gagal dimuat" :message="error" :retry="load" />

            <template v-else-if="home">
                <template v-if="promoBanners.length">
                    <div class="mobile-section-heading mobile-section-row">
                        <h2>Promo Pilihan</h2>
                    </div>
                    <div class="mobile-banner-carousel mobile-promo-rail" aria-label="Banner promo">
                        <template v-for="(banner, index) in promoBanners" :key="banner.id">
                            <a v-if="banner.target_url" :href="banner.target_url" class="mobile-banner" :aria-label="banner.title || `Promo ${index + 1}`">
                                <img :src="banner.image" :alt="banner.title || 'Promo pilihan'" loading="lazy" @error="useImageFallback" />
                            </a>
                            <div v-else class="mobile-banner">
                                <img :src="banner.image" :alt="banner.title || 'Promo pilihan'" loading="lazy" @error="useImageFallback" />
                            </div>
                        </template>
                    </div>
                </template>

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
