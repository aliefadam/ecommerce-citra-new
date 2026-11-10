<script setup>
import { inject, onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import { request } from '../services/api';
const config = inject('mobileConfig'); const notifications = ref([]); const unread = ref(0); const loading = ref(true); const error = ref('');
const load = async () => { try { const response = await request(`${config.apiBase}/notifications`); notifications.value = response.data; unread.value = response.meta.unread; } catch (exception) { error.value = exception.message; } finally { loading.value = false; } };
const read = async (item) => { if (!item.read) { await request(`${config.apiBase}/notifications/${item.id}/read`, { method: 'POST', body: {} }); item.read = true; unread.value = Math.max(0, unread.value - 1); } };
const readAll = async () => { await request(`${config.apiBase}/notifications/read-all`, { method: 'POST', body: {} }); notifications.value.forEach((item) => { item.read = true; }); unread.value = 0; };
onMounted(load);
</script>
<template><section class="mobile-screen" aria-labelledby="mobile-route-heading"><span class="mobile-eyebrow">Pembaruan akun</span><div class="mobile-section-heading"><h1 id="mobile-route-heading" tabindex="-1">Notifikasi</h1><button v-if="unread" class="mobile-clear-button" @click="readAll">Baca semua</button></div><p v-if="error" class="mobile-alert">{{ error }}</p><div v-if="loading" class="mobile-skeleton-list"><span v-for="item in 4" :key="item"></span></div><MobileCatalogState v-else-if="!notifications.length" title="Belum ada notifikasi" message="Pembaruan pesanan akan tampil di sini." /><div v-else class="mobile-notification-list"><RouterLink v-for="item in notifications" :key="item.id" :to="{ name: 'orders' }" class="mobile-notification-card" :class="{ 'is-unread': !item.read }" @click="read(item)"><strong>{{ item.title }}</strong><p>{{ item.body }}</p><small>{{ new Date(item.created_at).toLocaleString('id-ID') }}</small></RouterLink></div></section></template>
