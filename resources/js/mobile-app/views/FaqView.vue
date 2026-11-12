<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import { request } from '../services/api';

const config = inject('mobileConfig');
const help = ref(null);
const loading = ref(true);
const error = ref('');
const query = ref('');
const category = ref('all');
const openId = ref(null);

const load = async () => {
    loading.value = true; error.value = '';
    try { help.value = (await request(`${config.apiBase}/help/faqs`)).data; }
    catch (exception) { error.value = exception.message; }
    finally { loading.value = false; }
};
const categoryLabel = (key) => help.value?.categories.find((item) => item.key === key)?.label || '';
const usedCategories = computed(() => (help.value?.categories || []).filter((item) => help.value.faqs.some((faq) => faq.category === item.key)));
const filtered = computed(() => {
    const term = query.value.trim().toLowerCase();
    return (help.value?.faqs || []).filter((faq) => (category.value === 'all' || faq.category === category.value)
        && (!term || `${faq.question} ${faq.answer} ${categoryLabel(faq.category)}`.toLowerCase().includes(term)));
});
const toggle = (id) => { openId.value = openId.value === id ? null : id; };
const reset = () => { query.value = ''; category.value = 'all'; };
onMounted(load);
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'account' }" class="mobile-back-link">← Profil</RouterLink>
        <span class="mobile-eyebrow">Bantuan</span><h1 id="mobile-route-heading" tabindex="-1">FAQ</h1>
        <p v-if="error" class="mobile-alert" role="alert">{{ error }} <button type="button" class="mobile-clear-button" @click="load">Coba lagi</button></p>
        <div v-if="loading" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 5" :key="item"></span></div>
        <template v-else-if="help">
            <p class="mobile-muted mobile-faq-intro">{{ help.intro }}</p>
            <label class="mobile-faq-search"><span class="sr-only-mobile">Cari pertanyaan</span><input v-model="query" type="search" placeholder="Cari pertanyaan…" /></label>
            <div class="mobile-faq-chips" role="group" aria-label="Kategori FAQ">
                <button type="button" :class="{ 'is-active': category === 'all' }" :aria-pressed="category === 'all'" @click="category = 'all'">Semua</button>
                <button v-for="item in usedCategories" :key="item.key" type="button" :class="{ 'is-active': category === item.key }" :aria-pressed="category === item.key" @click="category = item.key">{{ item.label }}</button>
            </div>
            <div v-if="filtered.length" class="mobile-faq-list">
                <article v-for="faq in filtered" :key="faq.id" class="mobile-faq-item">
                    <h2><button type="button" :aria-expanded="openId === faq.id" :aria-controls="`faq-${faq.id}`" @click="toggle(faq.id)"><span>{{ faq.question }}</span><b aria-hidden="true">{{ openId === faq.id ? '−' : '+' }}</b></button></h2>
                    <div v-show="openId === faq.id" :id="`faq-${faq.id}`" class="mobile-faq-answer">
                        <p>{{ faq.answer }}</p>
                        <div v-for="platform in faq.platforms" :key="platform.name" class="mobile-faq-platform"><strong>{{ platform.name }}</strong><ol><li v-for="step in platform.steps" :key="step">{{ step }}</li></ol></div>
                    </div>
                </article>
            </div>
            <div v-else class="mobile-state-card"><p class="mobile-muted">Jawaban belum ditemukan.</p><button type="button" class="mobile-clear-button" @click="reset">Reset pencarian</button></div>
            <a :href="help.help_center_url" class="mobile-secondary-button mobile-button-link mobile-faq-more">Buka Pusat Bantuan lengkap</a>
        </template>
    </section>
</template>
