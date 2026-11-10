<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { prepareCsrf, request } from '../services/api';
import { trackMobileEvent } from '../services/telemetry';

const config = inject('mobileConfig');
const route = useRoute();
const payment = ref(null);
const error = ref('');
const loading = ref(true);
const proof = ref(null);
let pollTimer = null;
let trackedStatus = '';
const terminal = ['settlement', 'capture', 'paid', 'process', 'kirim', 'selesai', 'completed', 'dibatalkan', 'cancel', 'expire', 'deny', 'failure'];
const formatPrice = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const isTerminal = computed(() => terminal.includes(String(payment.value?.status || '').toLowerCase()));

const load = async (status = false) => {
    window.clearTimeout(pollTimer);
    pollTimer = null;
    try {
        const response = await request(`${config.apiBase}/orders/${encodeURIComponent(route.params.orderId)}/payment${status ? '/status' : ''}`);
        payment.value = response.data;
        const nextStatus = String(payment.value.status || '').toLowerCase();
        if (nextStatus !== trackedStatus) {
            if (['settlement', 'capture', 'paid', 'process', 'kirim', 'selesai', 'completed'].includes(nextStatus)) {
                trackMobileEvent(status ? 'payment_recovered' : 'payment_completed', { status: nextStatus, outcome: 'success' }, route.name);
            } else if (['dibatalkan', 'cancel', 'expire', 'deny', 'failure'].includes(nextStatus)) {
                trackMobileEvent('payment_failed', { status: nextStatus, outcome: 'failed' }, route.name);
            }
            trackedStatus = nextStatus;
        }
        error.value = '';
        if (!isTerminal.value && payment.value.payment_type !== 'manual_transfer') pollTimer = window.setTimeout(() => load(true), 8000);
    } catch (exception) { error.value = exception.message; } finally { loading.value = false; }
};

const upload = async () => {
    if (!proof.value) return;
    try {
        await prepareCsrf();
        const body = new FormData();
        body.append('payment_proof', proof.value);
        const response = await request(`${config.apiBase}/orders/${encodeURIComponent(route.params.orderId)}/payment-proof`, { method: 'POST', body });
        payment.value = response.data;
    } catch (exception) { error.value = exception.message; }
};

const cancel = async () => {
    try {
        await request(`${config.apiBase}/orders/${encodeURIComponent(route.params.orderId)}/cancel`, { method: 'POST', body: { cancel_reason: 'Dibatalkan customer dari aplikasi mobile.' } });
        await load();
    } catch (exception) { error.value = exception.message; }
};

onMounted(load);
onBeforeUnmount(() => window.clearTimeout(pollTimer));
</script>

<template>
    <section class="mobile-screen mobile-payment" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'home' }" class="mobile-back-link">← Home</RouterLink><span class="mobile-eyebrow">Status pembayaran</span><h1 id="mobile-route-heading" tabindex="-1">Pembayaran</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p><div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 3" :key="item"></span></div>
        <div v-else-if="payment" class="mobile-checkout-card">
            <span class="mobile-payment-status">{{ payment.status }}</span><h2>{{ payment.company }}</h2><small>{{ payment.order_id }}</small><strong class="mobile-payment-total">{{ formatPrice(payment.grand_total) }}</strong>
            <template v-if="payment.payment_type === 'manual_transfer'">
                <p>Transfer ke <strong>{{ payment.manual_payment.bank_name }}</strong></p><p class="mobile-payment-code">{{ payment.manual_payment.account_number }}</p><p>a.n. {{ payment.manual_payment.account_name }}</p>
                <form v-if="!payment.proof_uploaded && !isTerminal" class="mobile-proof-form" @submit.prevent="upload"><label for="mobile-payment-proof">Bukti transfer</label><input id="mobile-payment-proof" type="file" accept="image/jpeg,image/png,image/webp" required @change="proof = $event.target.files[0]" /><button class="mobile-primary-button">Upload Bukti</button></form>
                <p v-else-if="payment.proof_uploaded">Bukti transfer sudah diterima dan menunggu verifikasi.</p>
            </template>
            <template v-else><img v-if="payment.qr_url" :src="payment.qr_url" alt="QR pembayaran" class="mobile-payment-qr" /><p v-if="payment.va_number">Virtual Account {{ payment.va_bank }}</p><p v-if="payment.va_number" class="mobile-payment-code">{{ payment.va_number }}</p><button v-if="!isTerminal" type="button" class="mobile-secondary-button" @click="load(true)">Periksa Status</button></template>
            <button v-if="!isTerminal" type="button" class="mobile-clear-button" @click="cancel">Batalkan Pesanan</button><p v-if="route.query.other">Pesanan toko lain: {{ route.query.other }}</p>
        </div>
    </section>
</template>
