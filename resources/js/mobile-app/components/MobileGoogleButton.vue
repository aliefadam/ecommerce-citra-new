<script setup>
import { computed } from 'vue';

const props = defineProps({
    returnUrl: { type: String, default: '/app' },
    label: { type: String, default: 'Masuk dengan Google' },
});

// Login Google memakai alur OAuth web (session), lalu kembali ke /app lewat url.intended.
const href = computed(() => `/auth/google/redirect?redirect=${encodeURIComponent(props.returnUrl || '/app')}`);
</script>

<template>
    <div class="mobile-auth-divider" role="presentation"><span>atau</span></div>
    <a :href="href" class="mobile-secondary-button mobile-button-link mobile-google-button">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M21.805 10.023h-9.56v3.957h5.478c-.236 1.272-.952 2.35-2.025 3.072v2.55h3.283c1.922-1.77 3.024-4.378 3.024-7.463 0-.704-.063-1.38-.2-2.116z" fill="#4285F4" />
            <path d="M12.245 22c2.735 0 5.029-.907 6.706-2.448l-3.283-2.55c-.906.61-2.066.972-3.423.972-2.64 0-4.88-1.782-5.68-4.185H3.174v2.625A10.13 10.13 0 0012.245 22z" fill="#34A853" />
            <path d="M6.565 13.79a6.082 6.082 0 010-3.58V7.586H3.174a10.13 10.13 0 000 9.829l3.391-2.625z" fill="#FBBC05" />
            <path d="M12.245 6.026c1.486 0 2.825.51 3.874 1.511l2.907-2.907C17.27 2.999 14.976 2 12.245 2A10.13 10.13 0 003.174 7.586l3.391 2.625c.8-2.404 3.04-4.185 5.68-4.185z" fill="#EA4335" />
        </svg>
        <span>{{ label }}</span>
    </a>
</template>
