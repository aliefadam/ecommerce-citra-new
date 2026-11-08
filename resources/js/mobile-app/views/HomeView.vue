<script setup>
import { useSessionStore } from '../stores/session';

const session = useSessionStore();
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <div class="mobile-hero">
            <span class="mobile-eyebrow">Foundation Fase 1</span>
            <h1 id="mobile-route-heading" tabindex="-1">
                {{ session.user ? `Halo, ${session.user.name}` : 'Belanja kebutuhan proyek lebih praktis' }}
            </h1>
            <p>Shell SPA sudah aktif. Katalog dan transaksi akan ditambahkan bertahap setelah quality gate.</p>
        </div>

        <button class="mobile-search-preview" type="button" disabled aria-describedby="search-foundation-note">
            <span aria-hidden="true">⌕</span>
            <span>Cari nama, SKU, ukuran, atau material</span>
        </button>
        <p id="search-foundation-note" class="mobile-help-text">Pencarian tersedia pada Fase 2.</p>

        <div class="mobile-section-heading">
            <div>
                <span class="mobile-eyebrow">Fondasi siap</span>
                <h2>Mobile app shell</h2>
            </div>
        </div>

        <div class="mobile-foundation-grid">
            <article>
                <strong>Navigasi SPA</strong>
                <p>Route, Back/Forward, direct load, dan scroll restoration.</p>
            </article>
            <article>
                <strong>Session aman</strong>
                <p>Same-origin cookie, CSRF, dan Sanctum tanpa token browser.</p>
            </article>
            <article>
                <strong>PWA terisolasi</strong>
                <p>Manifest dan service worker hanya untuk scope /app.</p>
            </article>
            <article>
                <strong>Desktop tetap</strong>
                <p>Storefront Blade existing tidak diganti atau diarahkan paksa.</p>
            </article>
        </div>
    </section>
</template>
