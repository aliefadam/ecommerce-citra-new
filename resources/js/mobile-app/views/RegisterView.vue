<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSessionStore } from '../stores/session';
import MobileGoogleButton from '../components/MobileGoogleButton.vue';

const route = useRoute();
const router = useRouter();
const session = useSessionStore();
const form = ref({ name: '', email: '', password: '', password_confirmation: '' });
const fieldErrors = ref({});
const returnUrl = computed(() => {
    const candidate = typeof route.query.return === 'string' ? route.query.return : '/';
    return candidate.startsWith('/') && !candidate.startsWith('//') ? `/app${candidate === '/' ? '' : candidate}` : '/app';
});

const submit = async () => {
    fieldErrors.value = {};
    try {
        const target = await session.register({ ...form.value, return_url: returnUrl.value });
        await router.replace(target.startsWith('/app') ? (target.slice(4) || '/') : '/');
    } catch (exception) {
        fieldErrors.value = exception.errors || {};
    }
};
</script>

<template>
    <section class="mobile-screen mobile-auth-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'login', query: route.query.return ? { return: route.query.return } : {} }" class="mobile-back-link">← Masuk</RouterLink>
        <span class="mobile-eyebrow">Akun customer</span>
        <h1 id="mobile-route-heading" tabindex="-1">Daftar</h1>
        <form class="mobile-auth-form" @submit.prevent="submit">
            <div v-for="field in [{ key: 'name', label: 'Nama', type: 'text', autocomplete: 'name' }, { key: 'email', label: 'Email', type: 'email', autocomplete: 'email' }]" :key="field.key" class="mobile-field">
                <label :for="`mobile-register-${field.key}`">{{ field.label }}</label>
                <input :id="`mobile-register-${field.key}`" v-model="form[field.key]" :type="field.type" :autocomplete="field.autocomplete" required :aria-invalid="Boolean(fieldErrors[field.key])" />
                <p v-if="fieldErrors[field.key]" class="mobile-field-error">{{ fieldErrors[field.key][0] }}</p>
            </div>
            <div class="mobile-field">
                <label for="mobile-register-password">Password</label>
                <input id="mobile-register-password" v-model="form.password" type="password" autocomplete="new-password" minlength="6" required />
            </div>
            <div class="mobile-field">
                <label for="mobile-register-confirmation">Ulangi password</label>
                <input id="mobile-register-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" minlength="6" required />
                <p v-if="fieldErrors.password" class="mobile-field-error">{{ fieldErrors.password[0] }}</p>
            </div>
            <p v-if="session.error" class="mobile-alert" role="alert">{{ session.error }}</p>
            <button class="mobile-primary-button" type="submit" :disabled="session.loading">{{ session.loading ? 'Memproses…' : 'Buat Akun' }}</button>
        </form>

        <MobileGoogleButton :return-url="returnUrl" label="Daftar dengan Google" />
    </section>
</template>
