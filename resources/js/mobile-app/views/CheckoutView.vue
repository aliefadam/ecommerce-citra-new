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
const newTaxProfile = 'new';
const taxForm = reactive({ taxpayer_name: '', taxpayer_number: '', taxpayer_address: '', taxpayer_email: '', save_profile: true });
const shippingLoading = reactive({});
const shippingErrors = reactive({});
const couponErrors = reactive({});
const couponLoading = reactive({});
const formatPrice = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const selectedAddress = computed(() => checkout.value?.addresses.find((item) => item.id === Number(addressId.value)));
const usesNewTaxProfile = computed(() => taxProfileId.value === newTaxProfile);
const taxInvoiceValid = computed(() => {
    if (!invoiceRequested.value) return true;
    if (!usesNewTaxProfile.value) return Boolean(taxProfileId.value);

    return Boolean(taxForm.taxpayer_name.trim() && taxForm.taxpayer_number.trim() && taxForm.taxpayer_address.trim() && /\S+@\S+\.\S+/.test(taxForm.taxpayer_email));
});
const submitBlocker = computed(() => {
    if (!checkout.value?.groups.length) return '';
    if (!addressId.value) return 'Pilih alamat penerima terlebih dahulu.';
    if (!checkout.value.groups.every((group) => selectedShipping[group.company_id])) return 'Pilih metode pengiriman terlebih dahulu.';
    if (!taxInvoiceValid.value) return usesNewTaxProfile.value ? 'Lengkapi data faktur pajak.' : 'Pilih profil pajak untuk faktur pajak.';

    return '';
});
const selectedShippingOption = (companyId) => shipping[companyId]?.find((option) => option.quote_token === selectedShipping[companyId]) || null;
const allShippingSelected = computed(() => Boolean(checkout.value?.groups.length) && checkout.value.groups.every((group) => selectedShippingOption(group.company_id)));
const grandTotal = computed(() => (checkout.value?.groups || []).reduce(
    (total, group) => total + Number(group.total_before_shipping || 0) + Number(selectedShippingOption(group.company_id)?.cost || 0),
    0,
));
const canSubmit = computed(() => Boolean(checkout.value?.groups.length) && !submitBlocker.value && !submitting.value);
const taxInvoicePayload = () => {
    if (!invoiceRequested.value) return { requested: false };
    if (!usesNewTaxProfile.value) return { requested: true, profile_id: Number(taxProfileId.value) };

    return { requested: true, ...taxForm, set_default_profile: taxForm.save_profile && !checkout.value?.tax_profiles.length };
};
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
        taxProfileId.value ||= response.data.tax_profiles.find((item) => item.is_default)?.id
            || response.data.tax_profiles[0]?.id
            || newTaxProfile;
    } catch (exception) {
        error.value = exception.message;
    } finally {
        loading.value = false;
    }
};

const shippingRequests = {};
const loadShipping = async (group) => {
    const companyId = group.company_id;
    if (!addressId.value) {
        shippingErrors[companyId] = 'Pilih atau tambah alamat penerima terlebih dahulu.';
        return;
    }
    const requestId = (shippingRequests[companyId] || 0) + 1;
    shippingRequests[companyId] = requestId;
    shippingLoading[companyId] = true;
    shippingErrors[companyId] = '';
    shipping[companyId] = [];
    selectedShipping[companyId] = '';
    try {
        const response = await request(`${config.apiBase}/checkout/shipping-options`, {
            method: 'POST',
            body: { company_id: companyId, cart_ids: group.cart_ids, address_id: Number(addressId.value) },
        });
        if (shippingRequests[companyId] !== requestId) return;
        shipping[companyId] = response.data || [];
        selectedShipping[companyId] = response.data?.[0]?.quote_token || '';
        if (!shipping[companyId].length) shippingErrors[companyId] = 'Belum ada layanan pengiriman untuk alamat ini. Coba lagi atau gunakan alamat lain.';
    } catch (exception) {
        if (shippingRequests[companyId] !== requestId) return;
        shippingErrors[companyId] = exception.message || 'Gagal memuat ongkos kirim. Silakan coba lagi.';
    } finally {
        if (shippingRequests[companyId] === requestId) shippingLoading[companyId] = false;
    }
};

const refreshShipping = () => checkout.value?.groups.forEach(loadShipping);

const applyCoupon = async (group) => {
    const companyId = group.company_id;
    if (couponLoading[companyId]) return;
    couponLoading[companyId] = true;
    couponErrors[companyId] = '';
    try {
        await request(`${config.apiBase}/checkout/coupon`, {
            method: 'POST',
            body: { company_id: companyId, cart_ids: group.cart_ids, code: coupons[companyId] || '' },
        });
        await load();
        refreshShipping();
    } catch (exception) {
        couponErrors[companyId] = exception.message;
    } finally {
        couponLoading[companyId] = false;
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
                    tax_invoice: taxInvoicePayload(),
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
        document.querySelector('.mobile-checkout .mobile-alert')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } finally {
        submitting.value = false;
    }
};

onMounted(async () => {
    await load();
    if (addressId.value) refreshShipping();
});
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
                <div class="mobile-coupon-row"><input v-model="coupons[group.company_id]" type="text" maxlength="50" placeholder="Kode voucher" aria-label="Kode voucher" @keydown.enter.prevent="applyCoupon(group)" /><button type="button" :disabled="couponLoading[group.company_id]" @click="applyCoupon(group)">{{ couponLoading[group.company_id] ? '…' : 'Pakai' }}</button></div>
                <p v-if="couponErrors[group.company_id]" class="mobile-inline-error" role="alert">{{ couponErrors[group.company_id] }}</p>
                <div class="mobile-checkout-line"><span>Diskon</span><strong>−{{ formatPrice(group.discount_amount) }}</strong></div>
                <div class="mobile-checkout-line"><span>{{ group.tax_name || 'Pajak' }}</span><strong>{{ formatPrice(group.tax_amount) }}</strong></div>
                <div v-if="selectedShippingOption(group.company_id)" class="mobile-checkout-line"><span>Ongkos kirim</span><strong>{{ formatPrice(selectedShippingOption(group.company_id).cost) }}</strong></div>
                <div class="mobile-shipping-block" :aria-busy="shippingLoading[group.company_id] ? 'true' : 'false'">
                    <h3>Pengiriman</h3>
                    <p v-if="shippingLoading[group.company_id]" class="mobile-shipping-loading">Memuat pilihan pengiriman…</p>
                    <template v-else-if="shippingErrors[group.company_id]">
                        <p class="mobile-inline-error" role="alert">{{ shippingErrors[group.company_id] }}</p>
                        <button type="button" class="mobile-text-button" @click="loadShipping(group)">Muat ulang pengiriman</button>
                    </template>
                    <label v-for="option in shipping[group.company_id]" :key="option.quote_token" class="mobile-choice-row"><input v-model="selectedShipping[group.company_id]" type="radio" :name="`shipping-${group.company_id}`" :value="option.quote_token" /><span><strong>{{ option.name || option.code }} {{ option.service }}</strong><small>{{ option.etd ? `${option.etd} · ` : '' }}{{ formatPrice(option.cost) }}</small></span></label>
                </div>
            </article>

            <fieldset class="mobile-checkout-card">
                <legend>3. Pembayaran</legend>
                <select v-model="paymentMethod" aria-label="Metode pembayaran"><option value="manual">Transfer manual</option><option value="qris">QRIS</option><option value="bca">Virtual Account BCA</option><option value="bni">Virtual Account BNI</option><option value="bri">Virtual Account BRI</option><option value="mandiri">Virtual Account Mandiri</option><option value="cimb">Virtual Account CIMB</option></select>
                <label class="mobile-choice-row"><input v-model="invoiceRequested" type="checkbox" /><span><strong>Minta faktur pajak</strong></span></label>
                <template v-if="invoiceRequested">
                    <select v-model="taxProfileId" aria-label="Profil pajak" required><option :value="null" disabled>Pilih profil pajak</option><option v-for="profile in checkout.tax_profiles" :key="profile.id" :value="profile.id">{{ profile.name }} · {{ profile.number }}</option><option :value="newTaxProfile">+ Isi data pajak baru</option></select>
                    <div v-if="usesNewTaxProfile" class="mobile-tax-form">
                        <label>Nama wajib pajak<input v-model="taxForm.taxpayer_name" type="text" maxlength="255" autocomplete="organization" required /></label>
                        <label>NPWP / NIK<input v-model="taxForm.taxpayer_number" type="text" inputmode="numeric" maxlength="32" required /></label>
                        <label>Alamat NPWP<textarea v-model="taxForm.taxpayer_address" rows="2" maxlength="2000" required></textarea></label>
                        <label>Email faktur<input v-model="taxForm.taxpayer_email" type="email" maxlength="255" autocomplete="email" required /></label>
                        <label class="mobile-choice-row"><input v-model="taxForm.save_profile" type="checkbox" /><span><strong>Simpan sebagai profil pajak</strong></span></label>
                    </div>
                </template>
            </fieldset>

            <div class="mobile-action-bar mobile-checkout-submit">
                <p aria-live="polite">{{ submitBlocker }}</p>
                <div>
                    <span class="mobile-action-total"><small>{{ allShippingSelected ? 'Total pembayaran' : 'Total (belum ongkir)' }}</small><strong>{{ formatPrice(grandTotal) }}</strong></span>
                    <button type="button" class="mobile-primary-button" :disabled="!canSubmit" @click="submit">{{ submitting ? 'Memproses…' : 'Buat Pesanan' }}</button>
                </div>
            </div>
        </template>
    </section>
</template>
