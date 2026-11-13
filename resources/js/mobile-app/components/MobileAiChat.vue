<script setup>
import { computed, inject, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { request } from '../services/api';
import { useImageFallback } from '../services/images';
import { useSessionStore } from '../stores/session';

const session = useSessionStore();
const config = inject('mobileConfig');
const open = ref(false);
const loading = ref(false);
const input = ref('');
const history = ref([]);
const messageList = ref(null);
const welcome = computed(() => session.user
    ? 'Halo, saya IBOQ! Saya bisa mencarikan produk atau mengecek pesanan Anda. Mau tanya apa?'
    : 'Halo, saya IBOQ! Saya bisa mencarikan produk dan membantu kebutuhan belanja Anda. Login untuk menanyakan pesanan.');
const messages = ref([]);
const quickReplies = [
    ['Cari Produk', 'Saya ingin mencari produk berdasarkan nama, merek, ukuran, spesifikasi, atau harga.'],
    ['Cek Pesanan', 'Cek status pesanan terbaru saya.'],
    ['Lacak Pengiriman', 'Lacak pengiriman pesanan saya.'],
    ['Cara Pembayaran', 'Jelaskan cara pembayaran yang tersedia.'],
];
// Hanya pertanyaan user sendiri yang diringkas ke WhatsApp; jawaban AI dan data internal tidak ikut.
const userQuestions = ref([]);
const whatsappUrl = computed(() => {
    if (!config.supportWhatsapp) return '';
    const summary = userQuestions.value.length
        ? `\n\nRingkasan pertanyaan saya:\n${userQuestions.value.map((question) => `- ${question}`).join('\n')}`
        : '';
    try {
        const url = new URL(config.supportWhatsapp, window.location.origin);
        url.searchParams.set('text', `Halo CS BOQ, saya membutuhkan bantuan setelah menggunakan Tanya IBOQ.${summary}`);
        return url.toString();
    } catch {
        return config.supportWhatsapp;
    }
});

const reset = (notice = null) => {
    history.value = [];
    userQuestions.value = [];
    messages.value = [{ role: 'assistant', content: notice || welcome.value }];
};

const show = () => {
    if (!messages.value.length) reset();
    open.value = true;
};

const sendQuickReply = (prompt) => {
    input.value = prompt;
    submit();
};

onMounted(() => window.addEventListener('mobile-spa:open-ai-chat', show));
onBeforeUnmount(() => window.removeEventListener('mobile-spa:open-ai-chat', show));

const scrollToBottom = async () => {
    await nextTick();
    if (messageList.value) messageList.value.scrollTop = messageList.value.scrollHeight;
};

const formatPrice = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
}).format(value);

const messageParts = (content) => {
    const parts = [];
    const pattern = /(https?:\/\/[^\s]+)/g;
    let cursor = 0;

    for (const match of content.matchAll(pattern)) {
        if (match.index > cursor) parts.push({ type: 'text', value: content.slice(cursor, match.index) });
        const url = match[0].replace(/[),.;!?]+$/, '');
        parts.push({ type: 'link', value: url });
        if (url.length < match[0].length) parts.push({ type: 'text', value: match[0].slice(url.length) });
        cursor = match.index + match[0].length;
    }
    if (cursor < content.length) parts.push({ type: 'text', value: content.slice(cursor) });

    return parts;
};

const submit = async () => {
    const content = input.value.trim();
    if (!content || loading.value) return;
    const previousHistory = history.value.slice(-6);
    messages.value.push({ role: 'user', content });
    const question = content.replace(/\s+/g, ' ').slice(0, 160);
    userQuestions.value = [...userQuestions.value, question].slice(-3);
    input.value = '';
    loading.value = true;
    await scrollToBottom();

    try {
        const data = await request('/chat-ai', {
            method: 'POST',
            body: { message: content, history: previousHistory },
        });
        if (data.reset) {
            history.value = [];
            messages.value.push({ role: 'assistant', content: data.message, products: data.products || [] });
        } else {
            messages.value.push({ role: 'assistant', content: data.message, products: data.products || [] });
            history.value = [...previousHistory, { role: 'user', content }, { role: 'assistant', content: data.message }].slice(-6);
        }
    } catch (error) {
        messages.value.push({ role: 'assistant', content: error.message || 'IBOQ sedang tidak tersedia.' });
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
                <img :src="config.boqiMascotUrl" alt="" class="mobile-ai-header-avatar" />
                <div><h2 id="mobile-ai-title">IBOQ</h2><span>Asisten belanja BOQ</span></div>
                <button type="button" class="mobile-ai-new-chat" data-testid="boqi-new-chat-mobile" @click="reset()">Chat Baru</button>
                <button type="button" aria-label="Tutup Tanya IBOQ" @click="open = false">×</button>
            </header>
            <div ref="messageList" class="mobile-ai-messages" aria-live="polite">
                <div v-for="(message, index) in messages" :key="index" :class="['mobile-ai-message', `is-${message.role}`]">
                    <img v-if="message.role === 'assistant'" :src="config.boqiMascotUrl" alt="" class="mobile-ai-avatar" />
                    <p>
                        <template v-for="(part, partIndex) in messageParts(message.content)" :key="partIndex">
                            <a v-if="part.type === 'link'" :href="part.value" target="_blank" rel="noopener noreferrer">{{ part.value }}</a>
                            <template v-else>{{ part.value }}</template>
                        </template>
                    </p>
                    <RouterLink
                        v-for="product in (message.products || []).slice(0, 3)"
                        :key="product.id"
                        :to="{ name: 'product', params: { slug: product.slug } }"
                        class="mobile-ai-product"
                        data-testid="boqi-product-card-mobile"
                    >
                        <img :src="product.image || '/imgs/product-placeholder.svg'" :alt="product.name" loading="lazy" @error="useImageFallback" />
                        <span>
                            <strong>{{ product.name }}</strong>
                            <small :class="{ 'is-empty': !product.in_stock }">{{ product.in_stock ? 'Tersedia' : 'Stok habis' }}</small>
                            <b>{{ product.price_min === null ? 'Hubungi kami' : `${formatPrice(product.price_min)}${product.price_max > product.price_min ? ` – ${formatPrice(product.price_max)}` : ''}` }}</b>
                        </span>
                    </RouterLink>
                </div>
            </div>
            <p v-if="loading" class="mobile-ai-loading" role="status">IBOQ sedang menyiapkan jawaban…</p>
            <div class="mobile-ai-quick" aria-label="Pilihan cepat Tanya IBOQ">
                <button v-for="reply in quickReplies" :key="reply[0]" type="button" @click="sendQuickReply(reply[1])">{{ reply[0] }}</button>
                <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener noreferrer">Hubungi CS</a>
            </div>
            <form class="mobile-ai-form" @submit.prevent="submit">
                <label class="mobile-visually-hidden" for="mobile-ai-input">Tulis pertanyaan untuk IBOQ</label>
                <textarea id="mobile-ai-input" v-model="input" rows="1" maxlength="1000" required placeholder="Tanya IBOQ…" @keydown="handleKeydown"></textarea>
                <button type="submit" :disabled="loading" aria-label="Kirim pesan">➤</button>
            </form>
        </section>
    </div>
</template>
