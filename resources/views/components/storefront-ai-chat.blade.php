@php
    $chatWelcome = auth()->check()
        ? 'Halo, saya BOQI! Saya bisa mencarikan produk atau mengecek pesanan Anda. Mau tanya apa?'
        : 'Halo, saya BOQI! Saya bisa mencarikan produk dan membantu kebutuhan belanja Anda. Login untuk menanyakan pesanan.';
    $boqiWhatsapp = trim((string) ($appStoreSettings['social_whatsapp'] ?? ''));
@endphp

<div id="storefrontAiChat" class="ec-chat ec-ai-chat fixed right-4 md:right-6" style="--ec-ai-mobile-offset: {{ $mobileChatOffset ?? '16px' }}" data-endpoint="{{ route('storefront.chat-ai') }}" data-welcome="{{ $chatWelcome }}" data-whatsapp="{{ $boqiWhatsapp }}">
    <section id="storefrontAiPanel" class="mb-3 hidden h-[min(32rem,calc(100vh-8rem))] w-[min(24rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl" role="dialog" aria-modal="false" aria-labelledby="storefrontAiTitle">
        <header class="flex items-center justify-between bg-[#0a3268] px-4 py-3 text-white">
            <div class="flex min-w-0 items-center gap-2.5">
                <span class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-full bg-white/95"><img src="{{ asset('imgs/boqi/boqi-mascot.webp') }}" alt="" class="h-12 w-12 object-contain object-top" /></span>
                <div>
                    <h2 id="storefrontAiTitle" class="text-base font-bold">BOQI</h2>
                    <p class="text-xs text-blue-100">Asisten belanja BOQ</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button id="storefrontAiNewChat" type="button" class="rounded-lg px-2 py-2 text-xs font-semibold hover:bg-white/10" data-testid="boqi-new-chat">Chat Baru</button>
                <button id="storefrontAiClose" type="button" class="grid h-9 w-9 place-items-center rounded-lg hover:bg-white/10" aria-label="Tutup Tanya BOQI">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                </button>
            </div>
        </header>

        <div id="storefrontAiMessages" class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4" aria-live="polite"></div>
        <div id="storefrontAiQuickReplies" class="flex gap-2 overflow-x-auto border-t border-slate-100 bg-white px-3 py-2" aria-label="Pilihan cepat Tanya BOQI">
            <button type="button" data-boqi-prompt="Saya ingin mencari produk berdasarkan nama, merek, ukuran, spesifikasi, atau harga." class="shrink-0 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800">Cari Produk</button>
            <button type="button" data-boqi-prompt="Cek status pesanan terbaru saya." class="shrink-0 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Cek Pesanan</button>
            <button type="button" data-boqi-prompt="Lacak pengiriman pesanan saya." class="shrink-0 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Lacak Pengiriman</button>
            <button type="button" data-boqi-prompt="Jelaskan cara pembayaran yang tersedia." class="shrink-0 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Cara Pembayaran</button>
            @if ($boqiWhatsapp !== '')
                <a href="{{ $boqiWhatsapp }}" target="_blank" rel="noopener noreferrer" data-boqi-whatsapp class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">Hubungi CS</a>
            @endif
        </div>
        <div id="storefrontAiStatus" class="hidden px-4 py-1 text-xs text-slate-500" role="status">BOQI sedang menyiapkan jawaban…</div>

        <form id="storefrontAiForm" class="flex gap-2 border-t border-slate-200 bg-white p-3">
            <label for="storefrontAiInput" class="sr-only">Tulis pertanyaan untuk BOQI</label>
            <textarea id="storefrontAiInput" rows="1" maxlength="1000" required class="max-h-28 min-h-11 flex-1 resize-none rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-700 focus:outline-none" placeholder="Tanya BOQI…"></textarea>
            <button type="submit" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-600 text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Kirim pesan">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </button>
        </form>
    </section>

    <button id="storefrontAiToggle" type="button" class="flex min-h-14 items-center gap-2.5 rounded-full bg-white py-1.5 pl-1.5 pr-5 text-[#18222d] shadow-[0_10px_35px_rgba(10,50,104,.28)] ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-xl" aria-label="Buka Tanya BOQI" aria-expanded="false" aria-controls="storefrontAiPanel" data-testid="boqi-launcher">
        <span class="grid h-12 w-12 place-items-center overflow-hidden rounded-full bg-blue-50"><img src="{{ asset('imgs/boqi/boqi-mascot.webp') }}" alt="" class="h-14 w-14 object-contain object-top" /></span>
        <span class="text-base font-bold">Tanya BOQI</span>
    </button>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.getElementById('storefrontAiChat');
            if (!root) return;

            const panel = document.getElementById('storefrontAiPanel');
            const toggle = document.getElementById('storefrontAiToggle');
            const close = document.getElementById('storefrontAiClose');
            const newChat = document.getElementById('storefrontAiNewChat');
            const form = document.getElementById('storefrontAiForm');
            const input = document.getElementById('storefrontAiInput');
            const messages = document.getElementById('storefrontAiMessages');
            const status = document.getElementById('storefrontAiStatus');
            const submit = form.querySelector('button[type="submit"]');
            const welcome = root.dataset.welcome;
            let history = [];

            const appendRichText = (element, content) => {
                const urlPattern = /(https?:\/\/[^\s]+)/g;
                let cursor = 0;
                for (const match of content.matchAll(urlPattern)) {
                    if (match.index > cursor) element.append(document.createTextNode(content.slice(cursor, match.index)));
                    const url = match[0].replace(/[),.;!?]+$/, '');
                    const trailing = match[0].slice(url.length);
                    const link = document.createElement('a');
                    link.href = url;
                    link.textContent = url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.className = 'font-semibold underline decoration-1 underline-offset-2';
                    element.append(link);
                    if (trailing) element.append(document.createTextNode(trailing));
                    cursor = match.index + match[0].length;
                }
                if (cursor < content.length) element.append(document.createTextNode(content.slice(cursor)));
            };

            const formatPrice = (value) => new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
            }).format(value);

            const addMessage = (content, role, products = []) => {
                const row = document.createElement('div');
                row.className = role === 'user' ? 'flex justify-end' : 'flex items-start gap-2';
                const block = document.createElement('div');
                block.className = role === 'user' ? 'max-w-[85%]' : 'max-w-[92%] space-y-2';
                if (role === 'assistant') {
                    const avatar = document.createElement('img');
                    avatar.src = '/imgs/boqi/boqi-mascot.webp';
                    avatar.alt = '';
                    avatar.className = 'mt-0.5 h-8 w-8 shrink-0 rounded-full border border-blue-100 bg-white object-contain object-top';
                    row.appendChild(avatar);
                }
                const bubble = document.createElement('p');
                bubble.className = role === 'user'
                    ? 'whitespace-pre-wrap rounded-2xl rounded-br-md bg-[#0a3268] px-3 py-2 text-sm text-white'
                    : 'whitespace-pre-wrap rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700';
                appendRichText(bubble, content);
                block.appendChild(bubble);

                products.slice(0, 3).forEach((product) => {
                    const card = document.createElement('a');
                    card.href = product.url;
                    card.dataset.testid = 'boqi-product-card';
                    card.className = 'group flex gap-3 rounded-xl border border-slate-200 bg-white p-2.5 text-left shadow-sm transition hover:border-orange-400 hover:shadow-md';

                    const image = document.createElement('img');
                    image.src = product.image || '/imgs/product-placeholder.svg';
                    image.alt = product.name;
                    image.loading = 'lazy';
                    image.className = 'h-20 w-20 shrink-0 rounded-lg border border-slate-100 object-contain';
                    image.addEventListener('error', () => { image.src = '/imgs/product-placeholder.svg'; }, { once: true });

                    const detail = document.createElement('span');
                    detail.className = 'min-w-0 flex-1';
                    const title = document.createElement('strong');
                    title.className = 'line-clamp-2 block text-sm leading-5 text-slate-800 group-hover:text-orange-700';
                    title.textContent = product.name;
                    const stock = document.createElement('small');
                    stock.className = product.in_stock ? 'mt-1 block text-emerald-700' : 'mt-1 block text-rose-700';
                    stock.textContent = product.in_stock ? 'Tersedia' : 'Stok habis';
                    const price = document.createElement('span');
                    price.className = 'mt-1 block font-bold text-orange-600';
                    price.textContent = product.price_min === null
                        ? 'Hubungi kami'
                        : `${formatPrice(product.price_min)}${product.price_max > product.price_min ? ` – ${formatPrice(product.price_max)}` : ''}`;
                    detail.append(title, stock, price);
                    card.append(image, detail);
                    block.appendChild(card);
                });

                row.appendChild(block);
                messages.appendChild(row);
                messages.scrollTop = messages.scrollHeight;
            };

            // Hanya pertanyaan user sendiri yang diringkas ke WhatsApp; jawaban AI dan data internal tidak ikut.
            let userQuestions = [];
            const whatsappLinks = Array.from(document.querySelectorAll('[data-boqi-whatsapp]'))
                .map((link) => ({ link, base: link.getAttribute('href') }));
            const updateWhatsapp = () => {
                const summary = userQuestions.length
                    ? `\n\nRingkasan pertanyaan saya:\n${userQuestions.map((question) => `- ${question}`).join('\n')}`
                    : '';
                whatsappLinks.forEach(({ link, base }) => {
                    try {
                        const url = new URL(base, window.location.origin);
                        url.searchParams.set('text', `Halo CS BOQ, saya membutuhkan bantuan setelah menggunakan Tanya BOQI.${summary}`);
                        link.href = url.toString();
                    } catch (_) {
                        // URL pengaturan tetap digunakan apa adanya jika tidak dapat diproses.
                    }
                });
            };
            const rememberQuestion = (content) => {
                const question = content.replace(/\s+/g, ' ').trim().slice(0, 160);
                if (!question) return;
                userQuestions = [...userQuestions, question].slice(-3);
                updateWhatsapp();
            };

            const resetConversation = (notice = null) => {
                history = [];
                userQuestions = [];
                updateWhatsapp();
                messages.replaceChildren();
                addMessage(notice || welcome, 'assistant');
            };

            const setOpen = (open) => {
                panel.classList.toggle('hidden', !open);
                panel.classList.toggle('flex', open);
                toggle.classList.toggle('hidden', open);
                toggle.setAttribute('aria-expanded', String(open));
                if (open) window.setTimeout(() => input.focus(), 0);
            };

            resetConversation();
            toggle.addEventListener('click', () => setOpen(true));
            close.addEventListener('click', () => setOpen(false));
            newChat.addEventListener('click', () => {
                resetConversation();
                input.value = '';
                input.focus();
            });
            document.querySelectorAll('[data-boqi-prompt]').forEach((button) => {
                button.addEventListener('click', () => {
                    input.value = button.dataset.boqiPrompt || '';
                    form.requestSubmit();
                });
            });
            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    form.requestSubmit();
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const content = input.value.trim();
                if (!content || submit.disabled) return;

                const previousHistory = history.slice(-6);
                addMessage(content, 'user');
                rememberQuestion(content);
                input.value = '';
                submit.disabled = true;
                status.classList.remove('hidden');

                try {
                    const response = await fetch(root.dataset.endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message: content, history: previousHistory }),
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'BOQI sedang tidak tersedia.');

                    if (data.reset) {
                        history = [];
                        addMessage(data.message, 'assistant', data.products || []);
                    } else {
                        addMessage(data.message, 'assistant', data.products || []);
                        history = [...previousHistory, { role: 'user', content }, { role: 'assistant', content: data.message }].slice(-6);
                    }
                } catch (error) {
                    addMessage(error.message || 'BOQI sedang tidak tersedia. Silakan coba lagi.', 'assistant');
                } finally {
                    submit.disabled = false;
                    status.classList.add('hidden');
                    input.focus();
                }
            });
        });
    </script>
@endonce
