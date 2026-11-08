<script setup>
defineProps({
    title: { type: String, required: true },
    message: { type: String, required: true },
});
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Mobile storefront</span>
        <h1 id="mobile-route-heading" tabindex="-1">{{ title }}</h1>

        <div class="mobile-state-card">
            <span class="mobile-state-icon" aria-hidden="true">◇</span>
            <h2>Fondasi sudah siap</h2>
            <p>{{ message }}</p>
            <RouterLink :to="{ name: 'home' }" class="mobile-secondary-button">Kembali ke Home</RouterLink>
        </div>
    </section>
</template>
