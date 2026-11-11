<script setup>
import { computed, onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import { useCommerceStore } from '../stores/commerce';

const commerce = useCommerceStore();
const loading = ref(!commerce.notificationsReady);
const error = ref('');
const filter = ref('all');

const tabs = computed(() => [
    { key: 'all', label: 'Semua', count: commerce.notificationMeta.total },
    { key: 'unread', label: 'Belum dibaca', count: commerce.notificationMeta.unread },
    { key: 'read', label: 'Sudah dibaca', count: commerce.notificationMeta.read },
]);
const visible = computed(() => commerce.notifications.filter((item) => (
    filter.value === 'all' ? true : filter.value === 'unread' ? !item.read : item.read
)));
const emptyTitle = computed(() => ({ all: 'Belum ada notifikasi', unread: 'Semua sudah dibaca', read: 'Belum ada yang dibaca' }[filter.value]));

const load = async () => {
    error.value = '';
    try { await commerce.fetchNotifications(true); }
    catch (exception) { error.value = exception.message; }
    finally { loading.value = false; }
};
const read = (item) => commerce.markNotificationRead(item).catch((exception) => { error.value = exception.message; });
const readAll = () => commerce.markAllNotificationsRead().catch((exception) => { error.value = exception.message; });
onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Pembaruan akun</span>
        <div class="mobile-section-heading">
            <h1 id="mobile-route-heading" tabindex="-1">Notifikasi</h1>
            <button v-if="commerce.unreadNotifications" type="button" class="mobile-clear-button" @click="readAll">Baca semua</button>
        </div>
        <div class="mobile-notification-tabs" role="tablist" aria-label="Filter notifikasi">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                role="tab"
                :aria-selected="filter === tab.key"
                :class="{ 'is-active': filter === tab.key }"
                @click="filter = tab.key"
            >{{ tab.label }} <b>{{ tab.count }}</b></button>
        </div>
        <p v-if="error" class="mobile-alert">{{ error }}</p>
        <div v-if="loading" class="mobile-skeleton-list"><span v-for="item in 4" :key="item"></span></div>
        <MobileCatalogState v-else-if="!visible.length" :title="emptyTitle" message="Pembaruan pesanan akan tampil di sini." />
        <div v-else class="mobile-notification-list">
            <RouterLink
                v-for="item in visible"
                :key="item.id"
                :to="{ name: 'orders' }"
                class="mobile-notification-card"
                :class="{ 'is-unread': !item.read }"
                @click="read(item)"
            >
                <span class="mobile-notification-status">{{ item.read ? 'Sudah dibaca' : 'Belum dibaca' }}</span>
                <strong>{{ item.title }}</strong>
                <p>{{ item.body }}</p>
                <small>{{ new Date(item.created_at).toLocaleString('id-ID') }}</small>
            </RouterLink>
        </div>
    </section>
</template>
