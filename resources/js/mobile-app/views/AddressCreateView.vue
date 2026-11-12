<script setup>
import { inject, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { request } from '../services/api';

const config = inject('mobileConfig');
const router = useRouter();
const route = useRoute();
const error = ref('');
const saving = ref(false);
const locations = reactive({ provinces: [], cities: [], districts: [], subdistricts: [] });
const form = reactive({ label: 'Rumah', recipient_name: '', phone_country_code: '+62', phone_number: '', province_id: null, city_id: null, district_id: null, subdistrict_id: null, province: '', city: '', district: '', subdistrict: '', postal_code: '', destination_id: null, address_line: '', is_primary: true });
const itemName = (item) => String(item?.label || item?.name || '');
const fetchLocations = async (type, query = '') => {
    const response = await request(`${config.apiBase}/checkout/locations/${type}${query}`);
    locations[type] = response.data || [];
};
const selectProvince = async () => {
    const item = locations.provinces.find((entry) => Number(entry.id) === Number(form.province_id));
    form.province = itemName(item); form.city_id = null; form.district_id = null; form.subdistrict_id = null;
    await fetchLocations('cities', `?province_id=${form.province_id}`);
};
const selectCity = async () => {
    const item = locations.cities.find((entry) => Number(entry.id) === Number(form.city_id));
    form.city = itemName(item); form.district_id = null; form.subdistrict_id = null;
    await fetchLocations('districts', `?city_id=${form.city_id}`);
};
const selectDistrict = async () => {
    const item = locations.districts.find((entry) => Number(entry.id) === Number(form.district_id));
    form.district = itemName(item); form.subdistrict_id = null;
    await fetchLocations('subdistricts', `?district_id=${form.district_id}`);
};
const selectSubdistrict = () => {
    const item = locations.subdistricts.find((entry) => Number(entry.id) === Number(form.subdistrict_id));
    form.subdistrict = itemName(item); form.destination_id = Number(item?.destination_id || item?.id || 0); form.postal_code ||= String(item?.zip_code || item?.postal_code || '');
};
const submit = async () => {
    saving.value = true; error.value = '';
    try { await request(`${config.apiBase}/addresses`, { method: 'POST', body: form }); await router.replace(route.query.return === 'account' ? { name: 'account-addresses' } : { name: 'checkout' }); }
    catch (exception) { error.value = Object.values(exception.errors || {})[0]?.[0] || exception.message; }
    finally { saving.value = false; }
};
onMounted(() => fetchLocations('provinces').catch((exception) => { error.value = exception.message; }));
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="route.query.return === 'account' ? { name: 'account-addresses' } : { name: 'checkout' }" class="mobile-back-link">← {{ route.query.return === 'account' ? 'Alamat Tersimpan' : 'Checkout' }}</RouterLink><span class="mobile-eyebrow">Tujuan pengiriman</span><h1 id="mobile-route-heading" tabindex="-1">Tambah Alamat</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p>
        <form class="mobile-address-form" @submit.prevent="submit">
            <label>Label<select v-model="form.label"><option>Rumah</option><option>Kantor</option><option>Lainnya</option></select></label>
            <label>Nama penerima<input v-model="form.recipient_name" required maxlength="100" /></label>
            <label>Nomor telepon<input v-model="form.phone_number" required inputmode="tel" maxlength="30" /></label>
            <label>Provinsi<select v-model.number="form.province_id" required @change="selectProvince"><option :value="null">Pilih provinsi</option><option v-for="item in locations.provinces" :key="item.id" :value="item.id">{{ itemName(item) }}</option></select></label>
            <label>Kota/Kabupaten<select v-model.number="form.city_id" required :disabled="!locations.cities.length" @change="selectCity"><option :value="null">Pilih kota</option><option v-for="item in locations.cities" :key="item.id" :value="item.id">{{ itemName(item) }}</option></select></label>
            <label>Kecamatan<select v-model.number="form.district_id" required :disabled="!locations.districts.length" @change="selectDistrict"><option :value="null">Pilih kecamatan</option><option v-for="item in locations.districts" :key="item.id" :value="item.id">{{ itemName(item) }}</option></select></label>
            <label>Kelurahan<select v-model.number="form.subdistrict_id" required :disabled="!locations.subdistricts.length" @change="selectSubdistrict"><option :value="null">Pilih kelurahan</option><option v-for="item in locations.subdistricts" :key="item.id" :value="item.id">{{ itemName(item) }}</option></select></label>
            <label>Kode pos<input v-model="form.postal_code" maxlength="12" /></label>
            <label>Alamat lengkap<textarea v-model="form.address_line" required maxlength="1000" rows="4"></textarea></label>
            <label class="mobile-choice-row"><input v-model="form.is_primary" type="checkbox" /><span><strong>Jadikan alamat utama</strong></span></label>
            <button class="mobile-primary-button" :disabled="saving || !form.destination_id">{{ saving ? 'Menyimpan…' : 'Simpan Alamat' }}</button>
        </form>
    </section>
</template>
