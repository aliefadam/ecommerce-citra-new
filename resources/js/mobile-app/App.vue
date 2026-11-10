<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, RouterView, useRoute } from 'vue-router';
import MobileIcon from './components/MobileIcon.vue';
import { useCommerceStore } from './stores/commerce';
import { useSessionStore } from './stores/session';
import { trackMobileEvent } from './services/telemetry';

defineProps({
    config: { type: Object, required: true },
});

const route = useRoute();
const session = useSessionStore();
const commerce = useCommerceStore();
const online = ref(navigator.onLine);
const updateAvailable = ref(false);
const serviceWorkerError = ref(false);
const routeLoading = ref(false);
let routeFeedbackTimer;

const navigation = [
    { name: 'home', label: 'Home', icon: 'home' },
    { name: 'categories', label: 'Katalog', icon: 'grid' },
    { name: 'cart', label: 'Keranjang', icon: 'cart' },
    { name: 'orders', label: 'Transaksi', icon: 'receipt' },
    { name: 'account', label: 'Profil', icon: 'user' },
];

const cartCount = computed(() => (commerce.cartReady ? Number(commerce.cart.summary?.quantity || 0) : 0));
const isHome = computed(() => route.name === 'home');
const isActiveNav = (item) => route.name === item.name || route.meta.navSection === item.name;

const showNavigation = computed(() => !route.meta.hideNavigation);
const syncOnline = () => { online.value = navigator.onLine; };
const showUpdate = () => { updateAvailable.value = true; };
const showServiceWorkerError = () => { serviceWorkerError.value = true; };
const trackInstallPrompt = () => trackMobileEvent('install_prompted', { install_state: 'available' }, route.name);
const trackInstalled = () => trackMobileEvent('install_accepted', { install_state: 'installed' }, route.name);
const showRouteFeedback = () => {
    window.clearTimeout(routeFeedbackTimer);
    routeLoading.value = true;
    window.requestAnimationFrame(() => window.dispatchEvent(new CustomEvent('mobile-spa:route-feedback')));
};
const hideRouteFeedback = () => {
    routeFeedbackTimer = window.setTimeout(() => { routeLoading.value = false; }, 150);
};
const applyUpdate = () => window.__mobilePwaUpdate?.();
const retryBootstrap = () => session.bootstrap();
const focusRouteHeading = () => {
    document.querySelector('#mobile-route-heading')?.focus({ preventScroll: true });
};

onMounted(() => {
    window.addEventListener('online', syncOnline);
    window.addEventListener('offline', syncOnline);
    window.addEventListener('mobile-spa:update-available', showUpdate);
    window.addEventListener('mobile-spa:service-worker-error', showServiceWorkerError);
    window.addEventListener('beforeinstallprompt', trackInstallPrompt);
    window.addEventListener('appinstalled', trackInstalled);
    window.addEventListener('mobile-spa:route-started', showRouteFeedback);
    window.addEventListener('mobile-spa:route-finished', hideRouteFeedback);
});

onBeforeUnmount(() => {
    window.removeEventListener('online', syncOnline);
    window.removeEventListener('offline', syncOnline);
    window.removeEventListener('mobile-spa:update-available', showUpdate);
    window.removeEventListener('mobile-spa:service-worker-error', showServiceWorkerError);
    window.removeEventListener('beforeinstallprompt', trackInstallPrompt);
    window.removeEventListener('appinstalled', trackInstalled);
    window.removeEventListener('mobile-spa:route-started', showRouteFeedback);
    window.removeEventListener('mobile-spa:route-finished', hideRouteFeedback);
    window.clearTimeout(routeFeedbackTimer);
});
</script>

<template>
    <div class="mobile-app-stage">
        <div class="mobile-shell" data-testid="mobile-spa-shell">
            <a class="mobile-skip-link" href="#mobile-main">Lewati ke konten utama</a>

            <div v-if="routeLoading" class="mobile-route-progress" role="status" aria-label="Memuat halaman"></div>

            <div v-if="!online" class="mobile-network-status" role="status">
                Anda sedang offline. Beberapa fungsi belum tersedia.
            </div>

            <header v-if="!isHome" class="mobile-header">
                <RouterLink :to="{ name: 'home' }" class="mobile-brand" :aria-label="`${config.appName} beranda`">
                    <img :src="config.logoUrl" :alt="config.appName" class="mobile-brand-logo" width="116" height="52" />
                </RouterLink>
                <div class="mobile-header-actions">
                    <RouterLink :to="{ name: 'wishlist' }" class="mobile-header-button" aria-label="Produk tersimpan">
                        <MobileIcon name="heart" :size="20" />
                    </RouterLink>
                    <RouterLink :to="{ name: 'notifications' }" class="mobile-header-button" aria-label="Pemberitahuan">
                        <MobileIcon name="bell" :size="20" />
                    </RouterLink>
                </div>
            </header>

            <div v-if="updateAvailable" class="mobile-update-notice" role="status">
                <span>Versi baru tersedia.</span>
                <button type="button" @click="applyUpdate">Perbarui</button>
            </div>

            <div v-if="serviceWorkerError" class="mobile-service-notice" role="status">
                Mode instalasi belum tersedia, tetapi aplikasi tetap dapat digunakan.
            </div>

            <div v-if="session.error && !session.ready" class="mobile-bootstrap-error" role="alert">
                <span>{{ session.error }}</span>
                <button type="button" :disabled="session.loading" @click="retryBootstrap">
                    {{ session.loading ? 'Memuat…' : 'Coba Lagi' }}
                </button>
            </div>

            <main id="mobile-main" class="mobile-main" :class="{ 'has-mobile-nav': showNavigation }">
                <RouterView v-slot="{ Component }">
                    <Transition name="mobile-route" mode="out-in" @after-enter="focusRouteHeading">
                        <component :is="Component" />
                    </Transition>
                </RouterView>
            </main>

            <nav v-if="showNavigation" class="mobile-bottom-nav" aria-label="Navigasi aplikasi mobile">
                <RouterLink
                    v-for="item in navigation"
                    :key="item.name"
                    :to="{ name: item.name }"
                    class="mobile-nav-item"
                    active-class=""
                    exact-active-class=""
                    :class="{ 'router-link-active': isActiveNav(item) }"
                    :aria-label="item.label"
                >
                    <span class="mobile-nav-icon" aria-hidden="true">
                        <MobileIcon :name="item.icon" :size="23" :stroke="isActiveNav(item) ? 2.4 : 1.9" />
                        <span v-if="item.name === 'cart' && cartCount" class="mobile-nav-badge">{{ cartCount > 99 ? '99+' : cartCount }}</span>
                    </span>
                    <span>{{ item.label }}</span>
                </RouterLink>
            </nav>
        </div>
    </div>
</template>
