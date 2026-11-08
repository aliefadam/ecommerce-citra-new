<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSessionStore } from '../stores/session';

const route = useRoute();
const router = useRouter();
const session = useSessionStore();
const email = ref('');
const password = ref('');
const remember = ref(false);
const fieldErrors = ref({});

const returnUrl = computed(() => {
    const candidate = typeof route.query.return === 'string' ? route.query.return : '/';
    return candidate.startsWith('/') && !candidate.startsWith('//') ? `/app${candidate === '/' ? '' : candidate}` : '/app';
});

const submit = async () => {
    fieldErrors.value = {};
    try {
        const target = await session.login({
            email: email.value,
            password: password.value,
            remember: remember.value,
            return_url: returnUrl.value,
        });
        const routeTarget = target.startsWith('/app') ? (target.slice(4) || '/') : '/';
        await router.replace(routeTarget);
    } catch (exception) {
        fieldErrors.value = exception.errors || {};
    }
};
</script>

<template>
    <section class="mobile-screen mobile-auth-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'home' }" class="mobile-back-link">← Kembali</RouterLink>
        <span class="mobile-eyebrow">Akun customer</span>
        <h1 id="mobile-route-heading" tabindex="-1">Masuk</h1>
        <p class="mobile-lead">Gunakan akun yang sama dengan storefront web.</p>

        <form class="mobile-auth-form" @submit.prevent="submit">
            <div class="mobile-field">
                <label for="mobile-email">Email</label>
                <input
                    id="mobile-email"
                    v-model="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    :aria-invalid="Boolean(fieldErrors.email)"
                    :aria-describedby="fieldErrors.email ? 'mobile-email-error' : undefined"
                />
                <p v-if="fieldErrors.email" id="mobile-email-error" class="mobile-field-error">{{ fieldErrors.email[0] }}</p>
            </div>

            <div class="mobile-field">
                <label for="mobile-password">Password</label>
                <input
                    id="mobile-password"
                    v-model="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    :aria-invalid="Boolean(fieldErrors.password)"
                />
            </div>

            <label class="mobile-check">
                <input v-model="remember" type="checkbox" />
                <span>Ingat saya</span>
            </label>

            <p v-if="session.error" class="mobile-alert" role="alert">{{ session.error }}</p>

            <button class="mobile-primary-button" type="submit" :disabled="session.loading">
                {{ session.loading ? 'Memproses…' : 'Masuk' }}
            </button>
        </form>

        <a class="mobile-web-link" href="/login">Gunakan halaman login versi web</a>
    </section>
</template>
