<script setup>
import { computed, inject, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import { prepareCsrf, request } from '../services/api';
import { useCommerceStore } from '../stores/commerce';
import { trackMobileEvent } from '../services/telemetry';

const config = inject('mobileConfig');
const router = useRouter();
const route = useRoute();
const commerce = useCommerceStore();
const checkout = ref(null);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const addressId = ref(null);
const paymentMethod = ref('manual');
const shipping = reactive({});
const selectedShipping = reactive({});
const coupons = reactive({});
const orderKeys = reactive({});
const invoiceRequested = ref(false);
const taxProfileId = ref(null);
const formatPrice = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const selectedAddress = computed(() => checkout.value?.addresses.find((item) => item.id === Number(addressId.value)));
const canSubmit = computed(() => checkout.value?.groups.length
    && addressId.value
    && checkout.value.groups.every((group) => selectedShipping[group.company_id])
    && !submitting.value);
const orderKey = (companyId) => {
    orderKeys[companyId] ||= crypto.randomUUID();

    return orderKeys[companyId];
};

const load = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await request(`${config.apiBase}/checkout`);
        checkout.value = response.data;
        addressId.value ||= response.data.addresses.find((item) => item.is_primary)?.id || response.data.addresses[0]?.id || null;
        taxProfileId.value ||= response.data.tax_profiles.find((item) => item.is_default)?.id || null;
    } catch (exception) {
        error.value = exception.message;
    } finally {
        loading.value = false;
    }
};

const loadShipping = async (group) => {
    if (!addressId.value) return;
    error.value = '';
    shipping[group.company_id] = [];
    selectedShipping[group.company_id] = '';
    try {
        const response = await request(`${config.apiBase}/checkout/shipping-options`, {
            method: 'POST',
            body: { company_id: group.company_id, cart_ids: group.cart_ids, address_id: Number(addressId.value) },
        });
        shipping[group.company_id] = response.data || [];
        selectedShipping[group.company_id] = response.data?.[0]?.quote_token || '';
    } catch (exception) {
        error.value = exception.message;
    }
};

const refreshShipping = () => checkout.value?.groups.forEach(loadShipping);

const applyCoupon = async (group) => {
    error.value = '';
    try {
        await request(`${config.apiBase}/checkout/coupon`, {
            method: 'POST',
            body: { company_id: group.company_id, cart_ids: group.cart_ids, code: coupons[group.company_id] || '' },
        });
        await load();
        refreshShipping();
    } catch (exception) {
        error.value = exception.message;
    }
};

const submit = async () => {
    if (!canSubmit.value) return;
    submitting.value = true;
    error.value = '';
    try {
        await prepareCsrf();
        const orders = [];
        for (const group of checkout.value.groups) {
            const response = await request(`${config.apiBase}/checkout/orders`, {
                method: 'POST',
                headers: { 'Idempotency-Key': orderKey(group.company_id) },
                body: {
                    company_id: group.company_id,
                    cart_ids: group.cart_ids,
                    address_id: Number(addressId.value),
                    shipping_quote_token: selectedShipping[group.company_id],
                    checkout_version: group.checkout_version,
                    payment_method: paymentMethod.value,
                    tax_invoice: { requested: invoiceRequested.value, profile_id: taxProfileId.value },
                },
            });
            orders.push(response.order_id);
            trackMobileEvent('checkout_step_completed', { step: 'order_created', outcome: 'success' }, route.name);
        }
        commerce.reset();
        await router.replace({ name: 'payment', params: { orderId: orders[0] }, query: orders.length > 1 ? { other: orders.slice(1).join(',') } : {} });
    } catch (exception) {
        trackMobileEvent('checkout_validation_failed', { status: String(exception.status || 0), step: 'order_create', outcome: 'failed' }, route.name);
        error.value = exception.errors?.checkout_version?.[0] || exception.message;
        if (exception.status === 422) await load();
    } finally {
        submitting.value = false;
    }
};

onMounted(load);
</script>

<template>
    <section class="mobile-screen mobile-checkout" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'cart' }" class="mobile-back-link">← Keranjang</RouterLink>
        <span class="mobile-eyebrow">Konfirmasi pesanan</span>
        <h1 id="mobile-route-heading" tabindex="-1">Checkout</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p>
        <div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 4" :key="item"></span></div>
        <MobileCatalogState v-else-if="!checkout?.groups.length" title="Tidak ada item untuk checkout" message="Keranjang Anda kosong atau perlu diperbarui." />
        <template v-else>
            <fieldset class="mobile-checkout-card">
                <legend>1. Alamat penerima</legend>
                <label v-for="address in checkout.addresses" :key="address.id" class="mobile-choice-row">
                    <input v-model="addressId" type="radio" :value="address.id" @change="refreshShipping" />
                    <span><strong>{{ address.label }} · {{ address.recipient_name }}</strong><small>{{ address.address_line }}, {{ address.district }}, {{ address.city }}</small></span>
                </label>
                <RouterLink :to="{ name: 'checkout-address-new' }" class="mobile-secondary-button mobile-button-link">Tambah Alamat</RouterLink>
            </fieldset>

            <article v-for="group in checkout.groups" :key="group.company_id" class="mobile-checkout-card">
                <h2>2. {{ group.company_name }}</h2>
                <div v-for="item in group.items" :key="item.productVariantId" class="mobile-checkout-line"><span>{{ item.name }} × {{ item.qty }}</span><strong>{{ formatPrice(item.price * item.qty) }}</strong></div>
                <div class="mobile-coupon-row"><input v-model="coupons[group.company_id]" type="text" maxlength="50" placeholder="Kode voucher" /><button type="button" @click="applyCoupon(group)">Pakai</button></div>
                <div class="mobile-checkout-line"><span>Diskon</span><strong>−{{ formatPrice(group.discount_amount) }}</strong></div>
                <div class="mobile-checkout-line"><span>{{ group.tax_name || 'Pajak' }}</span><strong>{{ formatPrice(group.tax_amount) }}</strong></div>
                <button v-if="!shipping[group.company_id]?.length" type="button" class="mobile-secondary-button" :disabled="!selectedAddress" @click="loadShipping(group)">Pilih Pengiriman</button>
                <label v-for="option in shipping[group.company_id]" :key="option.quote_token" class="mobile-choice-row"><input v-model="selectedShipping[group.company_id]" type="radio" :value="option.quote_token" /><span><strong>{{ option.name || option.code }} {{ option.service }}</strong><small>{{ option.etd }} · {{ formatPrice(option.cost) }}</small></span></label>
            </article>

            <fieldset class="mobile-checkout-card">
                <legend>3. Pembayaran</legend>
                <select v-model="paymentMethod" aria-label="Metode pembayaran"><option value="manual">Transfer manual</option><option value="qris">QRIS</option><option value="bca">Virtual Account BCA</option><option value="bni">Virtual Account BNI</option><option value="bri">Virtual Account BRI</option><option value="mandiri">Virtual Account Mandiri</option><option value="cimb">Virtual Account CIMB</option></select>
                <label class="mobile-choice-row"><input v-model="invoiceRequested" type="checkbox" /><span><strong>Minta faktur pajak</strong></span></label>
                <select v-if="invoiceRequested" v-model="taxProfileId" aria-label="Profil pajak" required><option :value="null">Pilih profil pajak</option><option v-for="profile in checkout.tax_profiles" :key="profile.id" :value="profile.id">{{ profile.name }} · {{ profile.number }}</option></select>
            </fieldset>

            <button type="button" class="mobile-primary-button mobile-checkout-submit" :disabled="!canSubmit || (invoiceRequested && !taxProfileId)" @click="submit">{{ submitting ? 'Membuat pesanan…' : 'Buat Pesanan' }}</button>
        </template>
    </section>
</template>
