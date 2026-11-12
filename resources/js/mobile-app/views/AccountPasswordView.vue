<script setup>
import { inject, reactive, ref } from 'vue';
import { request } from '../services/api';

const config = inject('mobileConfig');
const saving = ref(false);
const message = ref('');
const error = ref('');
const password = reactive({ current_password: '', password: '', password_confirmation: '' });

const savePassword = async () => {
    saving.value = true; error.value = ''; message.value = '';
    try {
        const response = await request(`${config.apiBase}/account/password`, { method: 'PATCH', body: password });
        message.value = response.message;
        Object.assign(password, { current_password: '', password: '', password_confirmation: '' });
    } catch (exception) { error.value = Object.values(exception.errors || {})[0]?.[0] || exception.message; } finally { saving.value = false; }
};
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'account' }" class="mobile-back-link">← Profil</RouterLink>
        <span class="mobile-eyebrow">Keamanan akun</span><h1 id="mobile-route-heading" tabindex="-1">Ubah Password</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }}</p><p v-if="message" class="mobile-success" role="status">{{ message }}</p>
        <form class="mobile-account-form" @submit.prevent="savePassword">
            <label>Password saat ini<input v-model="password.current_password" type="password" required autocomplete="current-password" /></label>
            <label>Password baru<input v-model="password.password" type="password" required minlength="8" autocomplete="new-password" /></label>
            <label>Konfirmasi password<input v-model="password.password_confirmation" type="password" required minlength="8" autocomplete="new-password" /></label>
            <button class="mobile-primary-button" :disabled="saving">{{ saving ? 'Menyimpan…' : 'Ubah Password' }}</button>
        </form>
    </section>
</template>
