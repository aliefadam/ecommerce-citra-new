<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, RouterView, useRoute } from 'vue-router';
import { useSessionStore } from './stores/session';

defineProps({
    config: { type: Object, required: true },
});

const route = useRoute();
const session = useSessionStore();
const online = ref(navigator.onLine);
const updateAvailable = ref(false);
const serviceWorkerError = ref(false);

const navigation = [
    { name: 'home', label: 'Home', icon: '⌂' },
    { name: 'categories', label: 'Katalog', icon: '▦' },
    { name: 'cart', label: 'Keranjang', icon: '▱' },
    { name: 'orders', label: 'Transaksi', icon: '▤' },
    { name: 'account', label: 'Profil', icon: '○' },
];

const showNavigation = computed(() => !route.meta.hideNavigation);
const syncOnline = () => { online.value = navigator.onLine; };
const showUpdate = () => { updateAvailable.value = true; };
const showServiceWorkerError = () => { serviceWorkerError.value = true; };
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
});

onBeforeUnmount(() => {
    window.removeEventListener('online', syncOnline);
    window.removeEventListener('offline', syncOnline);
    window.removeEventListener('mobile-spa:update-available', showUpdate);
    window.removeEventListener('mobile-spa:service-worker-error', showServiceWorkerError);
});
</script>

<template>
    <div class="mobile-app-stage">
        <div class="mobile-shell" data-testid="mobile-spa-shell">
            <a class="mobile-skip-link" href="#mobile-main">Lewati ke konten utama</a>

            <div v-if="!online" class="mobile-network-status" role="status">
                Anda sedang offline. Beberapa fungsi belum tersedia.
            </div>

            <header class="mobile-header">
                <div>
                    <span class="mobile-eyebrow">Storefront mobile</span>
                    <strong>{{ config.appName }}</strong>
                </div>
                <span class="mobile-pilot-badge">PILOT</span>
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
                    :class="{ 'router-link-active': route.meta.navSection === item.name }"
                    :aria-label="item.label"
                >
                    <span class="mobile-nav-icon" aria-hidden="true">{{ item.icon }}</span>
                    <span>{{ item.label }}</span>
                </RouterLink>
            </nav>
        </div>
    </div>
</template>
