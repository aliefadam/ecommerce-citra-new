<script setup>
import { inject, onMounted, ref } from 'vue';
import { request } from '../services/api';

const config = inject('mobileConfig');
const addresses = ref([]);
const loading = ref(true);
const error = ref('');

const errorText = (exception) => Object.values(exception.errors || {})[0]?.[0] || exception.message;
const load = async () => {
    loading.value = true;
    try { addresses.value = (await request(`${config.apiBase}/account`)).data.addresses; }
    catch (exception) { error.value = exception.message; }
    finally { loading.value = false; }
};
const removeAddress = async (address) => {
    if (!window.confirm(`Hapus alamat ${address.label}?`)) return;
    error.value = '';
    try { await request(`${config.apiBase}/addresses/${address.id}`, { method: 'DELETE' }); await load(); }
    catch (exception) { error.value = errorText(exception); }
};
const setPrimary = async (address) => {
    error.value = '';
    try { await request(`${config.apiBase}/addresses/${address.id}`, { method: 'PATCH', body: { ...address, is_primary: true } }); await load(); }
    catch (exception) { error.value = errorText(exception); }
};
onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'account' }" class="mobile-back-link">← Profil</RouterLink>
        <div class="mobile-section-heading mobile-page-heading"><div><span class="mobile-eyebrow">Pengaturan akun</span><h1 id="mobile-route-heading" tabindex="-1">Alamat Tersimpan</h1></div><RouterLink :to="{ name: 'checkout-address-new', query: { return: 'account' } }" class="mobile-chip-link">+ Tambah</RouterLink></div>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p>
        <div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 3" :key="item"></span></div>
        <div v-else class="mobile-account-menu">
            <article v-for="address in addresses" :key="address.id" class="mobile-address-card"><strong>{{ address.label }} <small v-if="address.is_primary">Utama</small></strong><span>{{ address.recipient_name }}</span><p>{{ address.address_line }}, {{ address.district }}, {{ address.city }}</p><div class="mobile-inline-actions"><button v-if="!address.is_primary" type="button" class="mobile-clear-button" @click="setPrimary(address)">Jadikan utama</button><button type="button" class="mobile-clear-button" @click="removeAddress(address)">Hapus</button></div></article>
            <div v-if="!addresses.length" class="mobile-state-card"><p class="mobile-muted">Belum ada alamat tersimpan.</p><RouterLink :to="{ name: 'checkout-address-new', query: { return: 'account' } }" class="mobile-primary-button mobile-button-link">Tambah Alamat</RouterLink></div>
        </div>
    </section>
</template>
