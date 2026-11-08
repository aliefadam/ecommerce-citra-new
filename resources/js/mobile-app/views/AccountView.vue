<script setup>
import { useRouter } from 'vue-router';
import { useSessionStore } from '../stores/session';

const router = useRouter();
const session = useSessionStore();

const logout = async () => {
    await session.logout();
    await router.replace({ name: 'home' });
};
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Akun customer</span>
        <h1 id="mobile-route-heading" tabindex="-1">Profil</h1>

        <div class="mobile-profile-card">
            <span class="mobile-profile-avatar" aria-hidden="true">{{ session.user?.name?.charAt(0) || 'U' }}</span>
            <div>
                <strong>{{ session.user?.name }}</strong>
                <span>{{ session.user?.email }}</span>
            </div>
        </div>

        <div class="mobile-state-card">
            <h2>Foundation account aktif</h2>
            <p>Profil lengkap, alamat, dan pengaturan akun akan tersedia pada fase berikutnya.</p>
        </div>

        <button class="mobile-secondary-button" type="button" :disabled="session.loading" @click="logout">
            {{ session.loading ? 'Memproses…' : 'Keluar' }}
        </button>
    </section>
</template>
