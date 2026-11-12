<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MobileIcon from '../components/MobileIcon.vue';
import { request } from '../services/api';
import { useCommerceStore } from '../stores/commerce';
import { useSessionStore } from '../stores/session';

const config = inject('mobileConfig');
const router = useRouter();
const route = useRoute();
const session = useSessionStore();
const commerce = useCommerceStore();
const account = ref(null);
const loading = ref(true);
const error = ref('');
const avatarFailed = ref(false);

// Tautan lama (?tab=address / ?tab=password) diarahkan ke halaman masing-masing.
const legacyTabs = { profile: 'account-profile', address: 'account-addresses', password: 'account-password' };

const groups = computed(() => [
    {
        title: 'Aktivitas Saya',
        items: [
            { key: 'orders', label: 'Pesanan', icon: 'receipt', to: { name: 'orders' } },
            { key: 'notifications', label: 'Notifikasi', icon: 'bell', to: { name: 'notifications' }, badge: commerce.unreadNotifications || null, alert: true },
            { key: 'wishlist', label: 'Wishlist', icon: 'heart', to: { name: 'wishlist' }, badge: commerce.wishlistCount || null },
        ],
    },
    {
        title: 'Pengaturan',
        items: [
            { key: 'profile', label: 'Biodata', icon: 'userPen', to: { name: 'account-profile' } },
            { key: 'addresses', label: 'Alamat Tersimpan', icon: 'mapMarker', to: { name: 'account-addresses' }, badge: account.value && !account.value.addresses.length ? 'Tambah Alamat' : null },
            { key: 'password', label: 'Ubah Password', icon: 'lock', to: { name: 'account-password' } },
        ],
    },
    {
        title: 'Bantuan',
        items: [
            { key: 'faq', label: 'FAQ', icon: 'question', to: { name: 'help-faq' } },
            { key: 'terms', label: 'Syarat dan Ketentuan', icon: 'book', href: '/pages/syarat-ketentuan' },
            { key: 'privacy', label: 'Kebijakan Privasi', icon: 'shield', href: '/pages/kebijakan-privasi' },
        ],
    },
]);

const load = async () => {
    loading.value = true;
    try { account.value = (await request(`${config.apiBase}/account`)).data; }
    catch (exception) { error.value = exception.message; }
    finally { loading.value = false; }
};
const logout = async () => { await session.logout(); commerce.reset(); await router.replace({ name: 'home' }); };

onMounted(() => {
    const legacy = legacyTabs[route.query.tab];
    if (legacy) { router.replace({ name: legacy }); return; }
    load();
});
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Akun customer</span><h1 id="mobile-route-heading" tabindex="-1">Profil</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p>
        <div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 4" :key="item"></span></div>
        <template v-else-if="account">
            <RouterLink :to="{ name: 'account-profile' }" class="mobile-profile-card mobile-profile-card-link" aria-label="Lihat biodata">
                <span class="mobile-profile-avatar" aria-hidden="true"><img v-if="account.profile.avatar && !avatarFailed" :src="account.profile.avatar" alt="" referrerpolicy="no-referrer" @error="avatarFailed = true" /><template v-else>{{ account.profile.name?.charAt(0)?.toUpperCase() || 'U' }}</template></span>
                <div><strong>{{ account.profile.name }}</strong><span>{{ account.profile.email }}</span><small>{{ account.profile.point_balance }} poin</small></div>
                <MobileIcon name="chevron" :size="20" class="mobile-profile-card-chevron" />
            </RouterLink>

            <section v-for="group in groups" :key="group.title" class="mobile-menu-group" :aria-labelledby="`menu-group-${group.title}`">
                <h2 :id="`menu-group-${group.title}`">{{ group.title }}</h2>
                <ul>
                    <li v-for="item in group.items" :key="item.key">
                        <a v-if="item.href" :href="item.href" class="mobile-menu-row">
                            <MobileIcon :name="item.icon" :size="22" class="mobile-menu-row-icon" /><span>{{ item.label }}</span><MobileIcon name="chevron" :size="20" class="mobile-menu-row-chevron" />
                        </a>
                        <RouterLink v-else :to="item.to" class="mobile-menu-row">
                            <MobileIcon :name="item.icon" :size="22" class="mobile-menu-row-icon" /><span>{{ item.label }}</span>
                            <b v-if="item.badge" class="mobile-menu-row-badge" :class="{ 'is-alert': item.alert }">{{ item.badge }}</b>
                            <MobileIcon name="chevron" :size="20" class="mobile-menu-row-chevron" />
                        </RouterLink>
                    </li>
                </ul>
            </section>

            <div class="mobile-account-actions">
                <button class="mobile-secondary-button mobile-logout-button" type="button" :disabled="session.loading" @click="logout"><MobileIcon name="logout" :size="18" />{{ session.loading ? 'Memproses…' : 'Logout' }}</button>
                <a href="/?web=1" class="mobile-secondary-button mobile-button-link">Kembali ke Versi Web</a>
            </div>
        </template>
    </section>
</template>
