@php
    $chatWelcome = auth()->check()
        ? 'Halo! Saya bisa membantu mencari produk atau mengecek pesanan Anda. Apa yang ingin ditanyakan?'
        : 'Halo! Saya bisa membantu mencari produk dan menjawab pertanyaan seputar belanja. Login untuk menanyakan pesanan Anda.';
@endphp

<div id="storefrontAiChat" class="ec-chat ec-ai-chat fixed right-4 md:right-6" style="--ec-ai-mobile-offset: {{ $mobileChatOffset ?? '16px' }}" data-endpoint="{{ route('storefront.chat-ai') }}" data-welcome="{{ $chatWelcome }}">
    <section id="storefrontAiPanel" class="mb-3 hidden h-[min(32rem,calc(100vh-8rem))] w-[min(24rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl" role="dialog" aria-modal="false" aria-labelledby="storefrontAiTitle">
        <header class="flex items-center justify-between bg-[#0a3268] px-4 py-3 text-white">
            <div>
                <h2 id="storefrontAiTitle" class="text-base font-bold">Chat CS</h2>
                <p class="text-xs text-blue-100">Asisten produk & pesanan</p>
            </div>
            <button id="storefrontAiClose" type="button" class="grid h-9 w-9 place-items-center rounded-lg hover:bg-white/10" aria-label="Tutup chat">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </header>

        <div id="storefrontAiMessages" class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4" aria-live="polite"></div>
        <div id="storefrontAiStatus" class="hidden px-4 py-1 text-xs text-slate-500" role="status">Sedang menyiapkan jawaban…</div>

        <form id="storefrontAiForm" class="flex gap-2 border-t border-slate-200 bg-white p-3">
            <label for="storefrontAiInput" class="sr-only">Tulis pertanyaan tentang produk atau pesanan</label>
            <textarea id="storefrontAiInput" rows="1" maxlength="1000" required class="max-h-28 min-h-11 flex-1 resize-none rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-700 focus:outline-none" placeholder="Tanya produk atau pesanan…"></textarea>
            <button type="submit" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-600 text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Kirim pesan">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </button>
        </form>
    </section>

    <button id="storefrontAiToggle" type="button" class="flex min-h-14 items-center gap-3 rounded-full bg-white px-5 text-[#18222d] shadow-[0_10px_35px_rgba(10,50,104,.28)] ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-xl" aria-expanded="false" aria-controls="storefrontAiPanel">
        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><path d="M18 19c1.1 0 2-.9 2-2v-3h-3v5h1ZM6 19c-1.1 0-2-.9-2-2v-3h3v5H6ZM18 19c0 1.1-.9 2-2 2h-3"/></svg>
        <span class="text-base font-bold">Chat CS</span>
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
            const form = document.getElementById('storefrontAiForm');
            const input = document.getElementById('storefrontAiInput');
            const messages = document.getElementById('storefrontAiMessages');
            const status = document.getElementById('storefrontAiStatus');
            const submit = form.querySelector('button[type="submit"]');
            const welcome = root.dataset.welcome;
            let history = [];

            const addMessage = (content, role) => {
                const row = document.createElement('div');
                row.className = role === 'user' ? 'flex justify-end' : 'flex justify-start';
                const bubble = document.createElement('p');
                bubble.className = role === 'user'
                    ? 'max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-br-md bg-[#0a3268] px-3 py-2 text-sm text-white'
                    : 'max-w-[88%] whitespace-pre-wrap rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700';
                bubble.textContent = content;
                row.appendChild(bubble);
                messages.appendChild(row);
                messages.scrollTop = messages.scrollHeight;
            };

            const resetConversation = (notice = null) => {
                history = [];
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
                    if (!response.ok) throw new Error(data.message || 'Chat AI sedang tidak tersedia.');

                    if (data.reset) {
                        resetConversation(data.message);
                    } else {
                        addMessage(data.message, 'assistant');
                        history = [...previousHistory, { role: 'user', content }, { role: 'assistant', content: data.message }].slice(-6);
                    }
                } catch (error) {
                    addMessage(error.message || 'Chat AI sedang tidak tersedia. Silakan coba lagi.', 'assistant');
                } finally {
                    submit.disabled = false;
                    status.classList.add('hidden');
                    input.focus();
                }
            });
        });
    </script>
@endonce
