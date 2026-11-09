<script setup>
defineProps({
    title: { type: String, required: true },
    message: { type: String, required: true },
    retry: { type: Function, default: null },
});
</script>

<template>
    <div class="mobile-state-card" role="status">
        <span class="mobile-state-icon" aria-hidden="true">◇</span>
        <h2>{{ title }}</h2>
        <p>{{ message }}</p>
        <button v-if="retry" type="button" class="mobile-secondary-button" @click="retry">Coba Lagi</button>
    </div>
</template>
