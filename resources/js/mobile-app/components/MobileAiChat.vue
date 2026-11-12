<script setup>
import { computed, nextTick, ref } from 'vue';
import { request } from '../services/api';
import { useSessionStore } from '../stores/session';

const session = useSessionStore();
const open = ref(false);
const loading = ref(false);
const input = ref('');
const history = ref([]);
const messageList = ref(null);
const welcome = computed(() => session.user
    ? 'Halo! Saya bisa membantu mencari produk atau mengecek pesanan Anda. Apa yang ingin ditanyakan?'
    : 'Halo! Saya bisa membantu mencari produk. Login untuk menanyakan pesanan Anda.');
const messages = ref([]);

const reset = (notice = null) => {
    history.value = [];
    messages.value = [{ role: 'assistant', content: notice || welcome.value }];
};

const show = () => {
    if (!messages.value.length) reset();
    open.value = true;
};

const scrollToBottom = async () => {
    await nextTick();
    if (messageList.value) messageList.value.scrollTop = messageList.value.scrollHeight;
};

const submit = async () => {
    const content = input.value.trim();
    if (!content || loading.value) return;
    const previousHistory = history.value.slice(-6);
    messages.value.push({ role: 'user', content });
    input.value = '';
    loading.value = true;
    await scrollToBottom();

    try {
        const data = await request('/chat-ai', {
            method: 'POST',
            body: { message: content, history: previousHistory },
        });
        if (data.reset) {
            reset(data.message);
        } else {
            messages.value.push({ role: 'assistant', content: data.message });
            history.value = [...previousHistory, { role: 'user', content }, { role: 'assistant', content: data.message }].slice(-6);
        }
    } catch (error) {
        messages.value.push({ role: 'assistant', content: error.message || 'Chat AI sedang tidak tersedia.' });
    } finally {
        loading.value = false;
        scrollToBottom();
    }
};

const handleKeydown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        submit();
    }
};
</script>

<template>
    <div class="mobile-ai-chat">
        <section v-if="open" class="mobile-ai-panel" role="dialog" aria-labelledby="mobile-ai-title">
            <header class="mobile-ai-header">
                <div><strong id="mobile-ai-title">Chat CS</strong><span>Asisten produk & pesanan</span></div>
                <button type="button" aria-label="Tutup chat" @click="open = false">×</button>
            </header>
            <div ref="messageList" class="mobile-ai-messages" aria-live="polite">
                <p v-for="(message, index) in messages" :key="index" :class="`is-${message.role}`">{{ message.content }}</p>
            </div>
            <p v-if="loading" class="mobile-ai-loading" role="status">Sedang menyiapkan jawaban…</p>
            <form class="mobile-ai-form" @submit.prevent="submit">
                <label class="mobile-visually-hidden" for="mobile-ai-input">Tanya produk atau pesanan</label>
                <textarea id="mobile-ai-input" v-model="input" rows="1" maxlength="1000" required placeholder="Tanya produk atau pesanan…" @keydown="handleKeydown"></textarea>
                <button type="submit" :disabled="loading" aria-label="Kirim pesan">➤</button>
            </form>
        </section>
        <button v-else type="button" class="mobile-ai-toggle" aria-label="Buka Chat CS" @click="show">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><path d="M18 19c1.1 0 2-.9 2-2v-3h-3v5h1ZM6 19c-1.1 0-2-.9-2-2v-3h3v5H6ZM18 19c0 1.1-.9 2-2 2h-3"/></svg>
            <span>Chat CS</span>
        </button>
    </div>
</template>
