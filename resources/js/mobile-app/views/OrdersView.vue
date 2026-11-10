<script setup>
import { inject, onMounted, ref, watch } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import { request } from '../services/api';

const config = inject('mobileConfig');
const orders = ref([]); const loading = ref(true); const error = ref(''); const status = ref('');
const formatPrice = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const formatDate = (value) => value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)) : '';
const load = async () => { loading.value = true; error.value = ''; try { orders.value = (await request(`${config.apiBase}/orders${status.value ? `?status=${status.value}` : ''}`)).data; } catch (exception) { error.value = exception.message; } finally { loading.value = false; } };
watch(status, load); onMounted(load);
</script>
<template><section class="mobile-screen" aria-labelledby="mobile-route-heading"><span class="mobile-eyebrow">Riwayat belanja</span><h1 id="mobile-route-heading" tabindex="-1">Transaksi</h1>
    <label class="mobile-filter-label">Filter status<select v-model="status"><option value="">Semua</option><option value="pending">Menunggu</option><option value="process">Diproses</option><option value="kirim">Dikirim</option><option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option></select></label>
    <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p><div v-if="loading" class="mobile-skeleton-list"><span v-for="item in 4" :key="item"></span></div><MobileCatalogState v-else-if="!orders.length" title="Belum ada transaksi" message="Pesanan Anda akan tampil di sini." />
    <div v-else class="mobile-order-list"><RouterLink v-for="order in orders" :key="order.order_id" :to="{ name: 'order-detail', params: { orderId: order.order_id } }" class="mobile-order-card"><div><span class="mobile-payment-status">{{ order.status }}</span><small>{{ formatDate(order.created_at) }}</small></div><strong>{{ order.company }}</strong><span>{{ order.item_preview }} · {{ order.item_count }} item</span><b>{{ formatPrice(order.grand_total) }}</b></RouterLink></div>
</section></template>
