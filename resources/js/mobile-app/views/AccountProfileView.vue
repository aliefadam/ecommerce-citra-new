<script setup>
import { inject, onMounted, reactive, ref, watch } from 'vue';
import { request } from '../services/api';
import { useSessionStore } from '../stores/session';

const config = inject('mobileConfig');
const session = useSessionStore();
const loading = ref(true);
const saving = ref(false);
const message = ref('');
const error = ref('');
const profile = reactive({});
const avatarFailed = ref(false);
watch(() => profile.avatar, () => { avatarFailed.value = false; });

const errorText = (exception) => Object.values(exception.errors || {})[0]?.[0] || exception.message;
const load = async () => {
    loading.value = true;
    try { Object.assign(profile, (await request(`${config.apiBase}/account`)).data.profile); }
    catch (exception) { error.value = exception.message; }
    finally { loading.value = false; }
};
const saveProfile = async () => {
    saving.value = true; error.value = ''; message.value = '';
    try {
        const response = await request(`${config.apiBase}/account/profile`, { method: 'PATCH', body: profile });
        Object.assign(profile, response.data);
        session.user = { ...session.user, name: response.data.name, email: response.data.email };
        message.value = 'Profil berhasil diperbarui.';
    } catch (exception) { error.value = errorText(exception); } finally { saving.value = false; }
};
onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'account' }" class="mobile-back-link">← Profil</RouterLink>
        <span class="mobile-eyebrow">Pengaturan akun</span><h1 id="mobile-route-heading" tabindex="-1">Biodata</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p><p v-if="message" class="mobile-success" role="status">{{ message }}</p>
        <div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 4" :key="item"></span></div>
        <form v-else class="mobile-account-form" @submit.prevent="saveProfile">
            <div class="mobile-account-avatar-row"><span class="mobile-profile-avatar" aria-hidden="true"><img v-if="profile.avatar && !avatarFailed" :src="profile.avatar" alt="" referrerpolicy="no-referrer" @error="avatarFailed = true" /><template v-else>{{ profile.name?.charAt(0)?.toUpperCase() || 'U' }}</template></span><div><strong>{{ profile.name }}</strong><small>{{ profile.point_balance }} poin</small></div></div>
            <div class="mobile-form-grid"><label>Nama depan<input v-model="profile.first_name" required maxlength="100" /></label><label>Nama belakang<input v-model="profile.last_name" maxlength="100" /></label></div>
            <label>Email<input v-model="profile.email" type="email" required /></label>
            <div class="mobile-form-grid"><label>Kode negara<input v-model="profile.phone_country_code" maxlength="8" /></label><label>Nomor telepon<input v-model="profile.phone_number" inputmode="tel" maxlength="30" /></label></div>
            <label>Tanggal lahir<input v-model="profile.birth_date" type="date" /></label>
            <label>Bio<textarea v-model="profile.bio" rows="3" maxlength="1000"></textarea></label>
            <button class="mobile-primary-button" :disabled="saving">{{ saving ? 'Menyimpan…' : 'Simpan Profil' }}</button>
        </form>
    </section>
</template>
