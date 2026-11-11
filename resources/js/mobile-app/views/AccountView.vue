<script setup>
import { inject, onMounted, reactive, ref, watch } from 'vue';
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
const saving = ref(false);
const message = ref('');
const error = ref('');
const profile = reactive({});
const password = reactive({ current_password: '', password: '', password_confirmation: '' });
const avatarFailed = ref(false);
const tabs = [
    { key: 'profile', label: 'Biodata' },
    { key: 'address', label: 'Alamat' },
    { key: 'password', label: 'Ubah Password' },
];
const tabFromQuery = (value) => (tabs.some((tab) => tab.key === value) ? value : 'profile');
const activeTab = ref(tabFromQuery(route.query.tab));
const selectTab = (key) => {
    if (activeTab.value === key) return;
    activeTab.value = key; message.value = ''; error.value = '';
    router.replace({ query: { ...route.query, tab: key === 'profile' ? undefined : key } });
};
watch(() => route.query.tab, (value) => { activeTab.value = tabFromQuery(value); });
watch(() => profile.avatar, () => { avatarFailed.value = false; });

const load = async () => {
    loading.value = true;
    try {
        account.value = (await request(`${config.apiBase}/account`)).data;
        Object.assign(profile, account.value.profile);
    } catch (exception) { error.value = exception.message; } finally { loading.value = false; }
};
const saveProfile = async () => {
    saving.value = true; error.value = ''; message.value = '';
    try {
        const response = await request(`${config.apiBase}/account/profile`, { method: 'PATCH', body: profile });
        Object.assign(profile, response.data); session.user = { ...session.user, name: response.data.name, email: response.data.email };
        message.value = 'Profil berhasil diperbarui.';
    } catch (exception) { error.value = Object.values(exception.errors || {})[0]?.[0] || exception.message; } finally { saving.value = false; }
};
const savePassword = async () => {
    saving.value = true; error.value = ''; message.value = '';
    try {
        const response = await request(`${config.apiBase}/account/password`, { method: 'PATCH', body: password });
        message.value = response.message; Object.assign(password, { current_password: '', password: '', password_confirmation: '' });
    } catch (exception) { error.value = Object.values(exception.errors || {})[0]?.[0] || exception.message; } finally { saving.value = false; }
};
const removeAddress = async (address) => {
    if (!window.confirm(`Hapus alamat ${address.label}?`)) return;
    try { await request(`${config.apiBase}/addresses/${address.id}`, { method: 'DELETE' }); await load(); }
    catch (exception) { error.value = exception.message; }
};
const setPrimary = async (address) => {
    try { await request(`${config.apiBase}/addresses/${address.id}`, { method: 'PATCH', body: { ...address, is_primary: true } }); await load(); }
    catch (exception) { error.value = Object.values(exception.errors || {})[0]?.[0] || exception.message; }
};
const logout = async () => { await session.logout(); commerce.reset(); await router.replace({ name: 'home' }); };
onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Akun customer</span><h1 id="mobile-route-heading" tabindex="-1">Profil</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p><p v-if="message" class="mobile-success" role="status">{{ message }}</p>
        <div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 4" :key="item"></span></div>
        <template v-else-if="account">
            <div class="mobile-profile-card"><span class="mobile-profile-avatar" aria-hidden="true"><img v-if="profile.avatar && !avatarFailed" :src="profile.avatar" alt="" referrerpolicy="no-referrer" @error="avatarFailed = true" /><template v-else>{{ profile.name?.charAt(0)?.toUpperCase() || 'U' }}</template></span><div><strong>{{ profile.name }}</strong><span>{{ profile.email }}</span><small>{{ profile.point_balance }} poin</small></div></div>
            <nav class="mobile-account-menu" aria-label="Menu akun">
                <RouterLink :to="{ name: 'orders' }" class="mobile-account-link"><span><strong>Pesanan</strong><small>Riwayat, tracking, dan invoice</small></span><span>›</span></RouterLink>
                <RouterLink :to="{ name: 'notifications' }" class="mobile-account-link"><span><strong>Notifikasi<span v-if="commerce.unreadNotifications" class="mobile-account-count">{{ commerce.unreadNotifications }}</span></strong><small>{{ commerce.notificationMeta.unread }} belum dibaca · {{ commerce.notificationMeta.read }} sudah dibaca</small></span><span>›</span></RouterLink>
                <RouterLink :to="{ name: 'wishlist' }" class="mobile-account-link"><span><strong>Wishlist</strong><small>{{ commerce.wishlistCount }} produk disimpan</small></span><span>›</span></RouterLink>
            </nav>
            <div class="mobile-notification-tabs mobile-account-tabs" role="tablist" aria-label="Pengaturan akun">
                <button v-for="tab in tabs" :id="`account-tab-${tab.key}`" :key="tab.key" type="button" role="tab" :aria-selected="activeTab === tab.key" :aria-controls="`account-panel-${tab.key}`" :class="{ 'is-active': activeTab === tab.key }" @click="selectTab(tab.key)">{{ tab.label }}<b v-if="tab.key === 'address'">{{ account.addresses.length }}</b></button>
            </div>
            <form v-if="activeTab === 'profile'" id="account-panel-profile" role="tabpanel" aria-labelledby="account-tab-profile" class="mobile-account-form" @submit.prevent="saveProfile">
                <h2>Biodata</h2><div class="mobile-form-grid"><label>Nama depan<input v-model="profile.first_name" required maxlength="100" /></label><label>Nama belakang<input v-model="profile.last_name" maxlength="100" /></label></div>
                <label>Email<input v-model="profile.email" type="email" required /></label><div class="mobile-form-grid"><label>Kode negara<input v-model="profile.phone_country_code" maxlength="8" /></label><label>Nomor telepon<input v-model="profile.phone_number" inputmode="tel" maxlength="30" /></label></div>
                <label>Tanggal lahir<input v-model="profile.birth_date" type="date" /></label><label>Bio<textarea v-model="profile.bio" rows="3" maxlength="1000"></textarea></label>
                <button class="mobile-primary-button" :disabled="saving">Simpan Profil</button>
            </form>
            <section v-else-if="activeTab === 'address'" id="account-panel-address" role="tabpanel" aria-labelledby="account-tab-address" class="mobile-account-section"><div class="mobile-section-heading"><h2>Alamat</h2><RouterLink :to="{ name: 'checkout-address-new', query: { return: 'account' } }">Tambah</RouterLink></div>
                <article v-for="address in account.addresses" :key="address.id" class="mobile-address-card"><strong>{{ address.label }} <small v-if="address.is_primary">Utama</small></strong><span>{{ address.recipient_name }}</span><p>{{ address.address_line }}, {{ address.district }}, {{ address.city }}</p><div class="mobile-inline-actions"><button v-if="!address.is_primary" type="button" class="mobile-clear-button" @click="setPrimary(address)">Jadikan utama</button><button type="button" class="mobile-clear-button" @click="removeAddress(address)">Hapus</button></div></article>
                <p v-if="!account.addresses.length" class="mobile-muted">Belum ada alamat tersimpan.</p>
            </section>
            <form v-else id="account-panel-password" role="tabpanel" aria-labelledby="account-tab-password" class="mobile-account-form" @submit.prevent="savePassword"><h2>Ubah password</h2><label>Password saat ini<input v-model="password.current_password" type="password" required /></label><label>Password baru<input v-model="password.password" type="password" required minlength="8" /></label><label>Konfirmasi password<input v-model="password.password_confirmation" type="password" required minlength="8" /></label><button class="mobile-secondary-button" :disabled="saving">Ubah Password</button></form>
            <button class="mobile-secondary-button mobile-logout-button" type="button" :disabled="session.loading" @click="logout"><MobileIcon name="logout" :size="18" />{{ session.loading ? 'Memproses…' : 'Logout' }}</button>
            <a href="/?web=1" class="mobile-secondary-button mobile-button-link">Kembali ke Versi Web</a>
        </template>
    </section>
</template>
