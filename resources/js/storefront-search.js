const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const loadingHtml = () => Array.from({ length: 3 }, () => `
    <div class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 animate-pulse" aria-hidden="true">
        <div class="w-12 h-12 rounded-lg bg-slate-200"></div>
        <div class="flex-1"><div class="h-3 rounded bg-slate-200 w-40 mb-2"></div><div class="h-3 rounded bg-slate-200 w-24"></div></div>
    </div>
`).join('');

function renderMessage(dropdown, title, detail = '') {
    dropdown.innerHTML = `<div class="px-4 py-5 text-center text-sm text-slate-600" role="status">
        ${escapeHtml(title)}
        ${detail ? `<div class="mt-1 text-xs text-slate-400">${escapeHtml(detail)}</div>` : ''}
    </div>`;
}

function bindAutocomplete(input, dropdown) {
    const endpoint = input.dataset.suggestionsUrl;
    if (!endpoint) return;

    let debounceTimer;
    let controller;
    let activeIndex = -1;

    const close = () => {
        dropdown.hidden = true;
        dropdown.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    };

    const open = () => {
        dropdown.hidden = false;
        dropdown.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    };

    const options = () => [...dropdown.querySelectorAll('[role="option"]')];
    const setActive = (nextIndex) => {
        const items = options();
        if (!items.length) return;
        activeIndex = (nextIndex + items.length) % items.length;
        items.forEach((item, index) => {
            const selected = index === activeIndex;
            item.setAttribute('aria-selected', String(selected));
            item.classList.toggle('bg-slate-50', selected);
        });
        input.setAttribute('aria-activedescendant', items[activeIndex].id);
        items[activeIndex].scrollIntoView({ block: 'nearest' });
    };

    const renderProducts = (products) => {
        if (!products.length) {
            renderMessage(dropdown, 'Produk tidak ditemukan', 'Coba nama, SKU, kategori, atau ukuran lain.');
            return;
        }

        dropdown.innerHTML = products.map((product, index) => `
            <a id="${dropdown.id}-option-${index}" role="option" aria-selected="false" href="${escapeHtml(product.url)}"
               class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none transition-colors">
                <img src="${escapeHtml(product.image)}" alt="" width="48" height="48" loading="lazy"
                     class="w-12 h-12 rounded-lg object-cover border border-slate-100 bg-slate-100" />
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-semibold text-slate-800 truncate">${escapeHtml(product.name)}</span>
                    <span class="block text-xs text-slate-500 truncate">${escapeHtml(product.sku)} · ${escapeHtml(product.variant)}</span>
                    <span class="block text-[11px] text-slate-400 truncate">${escapeHtml(product.company || product.category)}</span>
                </span>
                <span class="text-xs font-semibold text-slate-700 whitespace-nowrap">${escapeHtml(product.price_label)}</span>
            </a>
        `).join('');
    };

    const load = async () => {
        const query = input.value.trim();
        controller?.abort();

        if (query.length < 2) {
            if (query.length === 1) {
                open();
                renderMessage(dropdown, 'Ketik minimal 2 karakter');
            } else {
                close();
            }
            return;
        }

        controller = new AbortController();
        open();
        dropdown.innerHTML = loadingHtml();
        dropdown.setAttribute('aria-busy', 'true');

        try {
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, {
                signal: controller.signal,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            activeIndex = -1;
            renderProducts(Array.isArray(payload.data) ? payload.data : []);
        } catch (error) {
            if (error.name !== 'AbortError') {
                renderMessage(dropdown, 'Pencarian belum dapat dimuat', 'Periksa koneksi lalu coba kembali.');
            }
        } finally {
            dropdown.setAttribute('aria-busy', 'false');
        }
    };

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(load, 250);
    });
    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && !dropdown.children.length) load();
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            options()[activeIndex]?.click();
        } else if (event.key === 'Escape') {
            controller?.abort();
            close();
        }
    });
    document.addEventListener('click', (event) => {
        if (event.target !== input && !dropdown.contains(event.target)) close();
    });
}

function initializeStorefrontSearch() {
    document.querySelectorAll('[data-storefront-autocomplete]').forEach((input) => {
        const dropdown = document.getElementById(input.getAttribute('aria-controls'));
        if (dropdown) bindAutocomplete(input, dropdown);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeStorefrontSearch, { once: true });
} else {
    initializeStorefrontSearch();
}
