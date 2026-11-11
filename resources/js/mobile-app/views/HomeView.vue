<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileIcon from '../components/MobileIcon.vue';
import MobileProductCard from '../components/MobileProductCard.vue';
import MobileSearchLauncher from '../components/MobileSearchLauncher.vue';
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

const config = inject('mobileConfig');
const heroSearch = ref(null);
const stickySearch = ref(false);
let searchObserver;

// Fallback hero artwork (public/imgs/mobile-hero), used until admins upload "Hero Mobile" banners.
const fallbackHeroSlides = [
    { id: 'hero-1', image: '/imgs/mobile-hero/hero-1.svg', title: 'Material pilihan asli', to: { name: 'search', query: { sort: 'newest' } } },
    { id: 'hero-2', image: '/imgs/mobile-hero/hero-2.svg', title: 'Diskon hingga 30%', to: { name: 'search', query: { sort: 'price_asc' } } },
    { id: 'hero-3', image: '/imgs/mobile-hero/hero-3.svg', title: 'Gratis ongkir', to: { name: 'search', query: { in_stock: '1' } } },
    { id: 'hero-4', image: '/imgs/mobile-hero/hero-4.svg', title: 'Perkakas profesional', to: { name: 'categories' } },
];
// Map an internal target ("/app/search?x=1") to an in-app route; other same-origin paths open as normal links.
const toAppRoute = (target) => {
    if (!target) return null;
    if (target === '/app' || target === '/app/') return '/';
    if (target.startsWith('/app/')) return target.slice(4);
    if (target.startsWith('/app?')) return `/${target.slice(4)}`;
    return null;
};
const banners = computed(() => {
    if (!home.value) {
        return loading.value ? [{ id: 'hero-loading', image: '', title: 'Memuat promo' }] : fallbackHeroSlides;
    }
    const remote = home.value.hero_banners || [];
    if (!remote.length) return fallbackHeroSlides;
    return remote.map((banner) => {
        const to = toAppRoute(banner.target_url);
        return {
            id: `hero-${banner.id}`,
            image: banner.image,
            title: banner.title || 'Promo pilihan',
            to,
            href: to ? null : banner.target_url,
        };
    });
});
const promoBanners = computed(() => home.value?.banners || []);
const greeting = computed(() => (session.user ? `Halo, ${session.user.name.split(' ')[0]}` : 'Material & perlengkapan proyek'));

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
    if ('IntersectionObserver' in window && heroSearch.value) {
        searchObserver = new IntersectionObserver(([entry]) => { stickySearch.value = !entry.isIntersecting; }, { threshold: 0 });
        searchObserver.observe(heroSearch.value);
    }
    load();
});
onBeforeUnmount(() => {
    stopAutoplay();
    searchObserver?.disconnect();
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
                <component
                    :is="slide.to ? RouterLink : (slide.href ? 'a' : 'div')"
                    v-for="(slide, index) in banners"
                    :key="slide.id"
                    :to="slide.to || undefined"
                    :href="slide.href || undefined"
                    class="mobile-astro-hero-slide"
                    :aria-label="slide.title"
                    :tabindex="activeBanner === index ? 0 : -1"
                >
                    <img v-if="slide.image" :src="slide.image" alt="" :fetchpriority="index === 0 ? 'high' : 'auto'" :loading="index === 0 ? 'eager' : 'lazy'" draggable="false" @error="useImageFallback" />
                </component>
            </div>

            <div class="mobile-astro-hero-overlay">
                <div class="mobile-astro-hero-top">
                    <div class="mobile-astro-brand">
                        <span class="mobile-astro-logo"><img :src="config.logoUrl" :alt="config.appName" width="116" height="52" /></span>
                        <small>{{ greeting }}</small>
                    </div>
                    <div class="mobile-astro-actions">
                        <a href="/pages/pusat-bantuan?web=1" class="mobile-astro-chat">
                            <MobileIcon name="headset" :size="18" /> Chat CS
                        </a>
                        <RouterLink :to="{ name: 'notifications' }" class="mobile-astro-menu" aria-label="Pemberitahuan">
                            <MobileIcon name="bell" :size="20" />
                        </RouterLink>
                    </div>
                </div>

                <div ref="heroSearch">
                    <MobileSearchLauncher />
                </div>
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

        <Transition name="mobile-sticky-search">
            <div v-if="stickySearch" class="mobile-home-sticky-search">
                <MobileSearchLauncher />
            </div>
        </Transition>

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
