const focusableSelector = [
    'a[href]', 'button:not([disabled])', 'input:not([disabled])', 'select:not([disabled])',
    'textarea:not([disabled])', '[tabindex]:not([tabindex="-1"])',
].join(',');

const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
})[character]);

function setupDialogs() {
    let activeDialog = null;
    let returnFocus = null;

    const close = (dialog = activeDialog) => {
        if (!dialog) return;
        dialog.hidden = true;
        document.body.style.overflow = '';
        activeDialog = null;
        returnFocus?.focus();
        returnFocus = null;
    };

    const open = (dialog, trigger) => {
        if (!dialog) return;
        activeDialog = dialog;
        returnFocus = trigger || document.activeElement;
        dialog.hidden = false;
        document.body.style.overflow = 'hidden';
        const panel = dialog.querySelector('[role="dialog"]');
        (panel?.querySelector(focusableSelector) || panel)?.focus();
    };

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-ec-dialog-open]');
        if (opener) {
            event.preventDefault();
            open(document.getElementById(opener.dataset.ecDialogOpen), opener);
            return;
        }
        if (event.target.closest('[data-ec-dialog-close]')) close(event.target.closest('[data-ec-dialog]'));
    });

    document.addEventListener('keydown', (event) => {
        if (!activeDialog) return;
        if (event.key === 'Escape') return close();
        if (event.key !== 'Tab') return;
        const items = [...activeDialog.querySelectorAll(focusableSelector)].filter((item) => !item.hidden);
        if (!items.length) return;
        const first = items[0];
        const last = items.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
}

function setupQuantities() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-quantity-minus], [data-quantity-plus]');
        if (!button) return;
        const input = button.closest('[data-quantity]')?.querySelector('input[type="number"]');
        if (!input) return;
        const step = Number(input.step || 1);
        const min = Number(input.min || 0);
        const max = input.max === '' ? Infinity : Number(input.max);
        input.value = String(Math.min(max, Math.max(min, Number(input.value || min) + (button.hasAttribute('data-quantity-plus') ? step : -step))));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

function setupDropdowns() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-ec-dropdown-trigger]');
        document.querySelectorAll('[data-ec-dropdown-panel]').forEach((panel) => {
            const ownTrigger = panel.closest('[data-ec-dropdown]')?.querySelector('[data-ec-dropdown-trigger]');
            const shouldOpen = trigger === ownTrigger ? panel.hidden : false;
            panel.hidden = !shouldOpen;
            ownTrigger?.setAttribute('aria-expanded', String(shouldOpen));
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[data-ec-dropdown-panel]').forEach((panel) => { panel.hidden = true; });
        document.querySelectorAll('[data-ec-dropdown-trigger][aria-expanded="true"]').forEach((trigger) => { trigger.setAttribute('aria-expanded', 'false'); trigger.focus(); });
    });
}

function setupStorefrontShell() {
    const root = document.querySelector('[data-storefront-shell]');
    if (!root) return;
    const configNode = document.getElementById('ec-shell-config');
    const config = configNode ? JSON.parse(configNode.textContent) : {};
    const get = (id) => document.getElementById(id);
    const toggle = (trigger, panel, force) => {
        if (!trigger || !panel) return false;
        const opening = force ?? panel.hidden;
        panel.hidden = !opening;
        trigger.setAttribute('aria-expanded', String(opening));
        return opening;
    };
    const closables = [
        [get('ecNavCategoryTrigger'), get('ecNavCategoryDropdown')],
        [get('ecCategoryTrigger'), get('ecCategoryDropdown')],
        [get('ecAccountTrigger'), get('ecAccountDropdown')],
        [get('ecNotifTrigger'), get('ecNotifDropdown')],
        [get('ecMobileNavToggle'), get('ecMobileNavDrawer')],
        [get('ecMobileSearchToggle'), get('ecMobileSearch')],
    ];

    closables.forEach(([trigger, panel]) => trigger?.addEventListener('click', (event) => {
        event.stopPropagation();
        closables.forEach(([otherTrigger, otherPanel]) => { if (otherPanel !== panel) toggle(otherTrigger, otherPanel, false); });
        const opening = toggle(trigger, panel);
        if (opening && panel === get('ecMobileSearch')) get('ecNavSearchMobile')?.focus();
    }));

    document.addEventListener('click', (event) => closables.forEach(([trigger, panel]) => {
        if (panel && !panel.hidden && !panel.contains(event.target) && !trigger?.contains(event.target)) toggle(trigger, panel, false);
    }));
    root.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closables.forEach(([trigger, panel]) => toggle(trigger, panel, false));
        event.target.closest('button, a, input')?.focus();
    });

    const searchCategory = root.querySelector('[data-search-category]');
    const searchCategoryTrigger = get('ecNavCategoryTrigger');
    const searchCategoryDropdown = get('ecNavCategoryDropdown');
    const searchCategoryInput = get('ecNavCategory');
    const searchCategoryLabel = searchCategory?.querySelector('[data-search-category-label]');
    const searchCategoryOptions = () => [...(searchCategoryDropdown?.querySelectorAll('[role="option"]') || [])];

    searchCategoryDropdown?.addEventListener('click', (event) => {
        const option = event.target.closest('[data-category-value]');
        if (!option || !searchCategoryInput || !searchCategoryLabel) return;
        searchCategoryInput.value = option.dataset.categoryValue || '';
        searchCategoryLabel.textContent = option.dataset.categoryLabel || 'Semua Kategori';
        searchCategoryOptions().forEach((item) => item.setAttribute('aria-selected', String(item === option)));
        toggle(searchCategoryTrigger, searchCategoryDropdown, false);
        searchCategoryTrigger.focus();
    });
    searchCategoryTrigger?.addEventListener('keydown', (event) => {
        if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        event.preventDefault();
        toggle(searchCategoryTrigger, searchCategoryDropdown, true);
        const options = searchCategoryOptions();
        const selectedIndex = Math.max(0, options.findIndex((option) => option.getAttribute('aria-selected') === 'true'));
        options[event.key === 'ArrowUp' ? Math.max(0, selectedIndex - 1) : selectedIndex]?.focus();
    });
    searchCategoryDropdown?.addEventListener('keydown', (event) => {
        const options = searchCategoryOptions();
        const currentIndex = options.indexOf(document.activeElement);
        if (event.key === 'Escape') {
            event.stopPropagation();
            toggle(searchCategoryTrigger, searchCategoryDropdown, false);
            searchCategoryTrigger?.focus();
            return;
        }
        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        let nextIndex = currentIndex;
        if (event.key === 'Home') nextIndex = 0;
        else if (event.key === 'End') nextIndex = options.length - 1;
        else nextIndex = (currentIndex + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
        options[nextIndex]?.focus();
    });

    const categories = Array.isArray(config.categories) ? config.categories : [];
    const categoryMenu = get('ecMegaCategoryMenu');
    const categoryContent = get('ecMegaCategoryContent');
    let activeCategory = categories[0]?.key;

    const categoryMeta = (category) => {
        const identity = `${category?.key || ''} ${category?.name || ''}`.toLowerCase();
        const icons = {
            bolt: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M4 12.5 9 7.5h7l3 3-5 5-3-3H4v0Z"/><path d="m15.5 14.5 10 10m-7-13 10 10M23 22l-5.5 5.5M26 19l-5.5 5.5"/></svg>`,
            fitting: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M7 4v9a12 12 0 0 0 12 12h9"/><path d="M3 4h8M3 8h8m17 13v8m-4-8v8"/><path d="M11 12h5a5 5 0 0 1 5 5v4"/></svg>`,
            nut: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 4 10 6v12l-10 6-10-6V10l10-6Z"/><circle cx="16" cy="16" r="5"/><path d="M13 12.2 19 19.8m0-7.6L13 19.8"/></svg>`,
            screw: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 8h11l3 3-5 5-3-3H5V8Z"/><path d="m15.5 14.5 10 10m-7-13 10 10M20 22l5 5m-7-2 4 4m1-9 5 5"/></svg>`,
            washer: `<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="11"/><circle cx="16" cy="16" r="5"/><path d="M8.2 8.2 12 12m8 8 3.8 3.8"/></svg>`,
            chemical: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M11 4h10m-8 0v6l-5 8a6 6 0 0 0 5 9h6a6 6 0 0 0 5-9l-5-8V4"/><path d="M10 19h12M14 14h4"/></svg>`,
            clamp: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 5v15a7 7 0 0 0 14 0v-5"/><path d="M5 5h6m8 7h6m-3-3v6M5 9h6"/><circle cx="15" cy="20" r="3"/></svg>`,
            safety: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 4 27 8v7c0 7-4.5 11-11 14C9.5 26 5 22 5 15V8l11-4Z"/><path d="m11 16 3 3 7-7"/></svg>`,
            default: `<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 9h22v14H5zM9 5v4m14-4v4M9 23v4m14-4v4"/><path d="M10 14h12m-12 4h8"/></svg>`,
        };
        const descriptions = {
            bolt: 'Fastening Components',
            fitting: 'Pipe & Flow Components',
            nut: 'Threaded Fasteners',
            screw: 'Precision Fasteners',
            washer: 'Load Distribution',
            chemical: 'Chemical & Bonding',
            clamp: 'Mounting & Support',
            safety: 'Safety & Surface Prep',
        };
        const matchers = [
            ['washer', /washer|\bring\b/],
            ['fitting', /fitting|\bpipe\b|\bpipa\b/],
            ['nut', /\bnut\b|\bmur\b/],
            ['screw', /screw|sekrup|\bpaku\b/],
            ['chemical', /chemical|kimia|\blem\b|adhesive/],
            ['clamp', /klem|clamp|bracket/],
            ['safety', /safety|abrasive|pelindung/],
            ['bolt', /bolt|baut/],
        ];
        const type = matchers.find(([, pattern]) => pattern.test(identity))?.[0] || 'default';

        return {
            icon: icons[type],
            description: descriptions[type] || 'Industrial Components',
        };
    };

    const renderCategories = () => {
        if (!categoryMenu || !categoryContent) return;
        categoryMenu.innerHTML = categories.length ? categories.map((category) => {
            const meta = categoryMeta(category);
            return `
                <button type="button" role="tab" aria-selected="${category.key === activeCategory}" aria-controls="ecMegaCategoryContent" data-category-key="${escapeHtml(category.key)}" class="ec-mega-family-item">
                    <span class="ec-mega-family-icon">${meta.icon}</span>
                    <span class="ec-mega-family-copy"><strong>${escapeHtml(category.name)}</strong><small>${escapeHtml(meta.description)}</small></span>
                    <span class="ec-mega-family-chevron" aria-hidden="true">&#8250;</span>
                </button>`;
        }).join('') : '<p class="ec-mega-unavailable">Kategori belum tersedia.</p>';
        const category = categories.find((item) => item.key === activeCategory) || categories[0];
        if (!category) {
            categoryContent.innerHTML = '<div class="ec-mega-empty"><p>Katalog sedang disiapkan.</p></div>';
            return;
        }
        const meta = categoryMeta(category);
        const items = (category.columns || []).flatMap((column) => column.items || []);
        categoryContent.innerHTML = `
            <header class="ec-mega-catalog-head">
                <div>
                    <p class="ec-mega-eyebrow">Product Family / ${String(items.length).padStart(2, '0')} Subcategories</p>
                    <h2>${escapeHtml(category.name)}</h2>
                    <p>${escapeHtml(meta.description)}</p>
                </div>
                <a href="${escapeHtml(category.url)}">Lihat Semua ${escapeHtml(category.name)} <span aria-hidden="true">&rarr;</span></a>
            </header>
            ${items.length ? `<div class="ec-mega-subcategory-grid">${items.map((item) => `
                <a class="ec-mega-subcategory" href="${escapeHtml(item.url)}">
                    <span>${escapeHtml(item.name)}</span><span aria-hidden="true">&rarr;</span>
                </a>`).join('')}</div>` : `
                <div class="ec-mega-empty">
                    <span class="ec-mega-empty-icon">${meta.icon}</span>
                    <div><p>Belum ada subkategori untuk <strong>${escapeHtml(category.name)}</strong>.</p>
                    <a href="${escapeHtml(category.url)}">Lihat semua produk ${escapeHtml(category.name)} <span aria-hidden="true">&rarr;</span></a></div>
                </div>`}`;
    };
    const activateCategory = (event, shouldFocus = false) => {
        const tab = event.target.closest('[data-category-key]');
        if (!tab || tab.dataset.categoryKey === activeCategory) return;
        activeCategory = tab.dataset.categoryKey;
        renderCategories();
        if (shouldFocus) categoryMenu.querySelector('[aria-selected="true"]')?.focus();
    };
    categoryMenu?.addEventListener('click', (event) => activateCategory(event, true));
    categoryMenu?.addEventListener('mouseover', (event) => activateCategory(event));
    categoryMenu?.addEventListener('keydown', (event) => {
        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const keys = categories.map((category) => category.key);
        let index = keys.indexOf(activeCategory);
        if (event.key === 'Home') index = 0;
        else if (event.key === 'End') index = keys.length - 1;
        else index = (index + (event.key === 'ArrowDown' ? 1 : -1) + keys.length) % keys.length;
        activeCategory = keys[index]; renderCategories(); categoryMenu.querySelector('[aria-selected="true"]')?.focus();
    });
    renderCategories();

    const applyCartCount = (value) => ['cartCount', 'mobileCartBadge'].forEach((id) => {
        const badge = get(id); if (!badge) return;
        const count = Math.max(0, Number(value || 0));
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.hidden = count === 0;
    });
    const refreshCart = async () => {
        if (!config.cartCountUrl) return;
        try { const response = await fetch(config.cartCountUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }); const data = response.ok ? await response.json() : {}; applyCartCount(data.count); } catch { /* keep server-rendered value */ }
    };
    window.addEventListener('cart:updated', refreshCart);
    refreshCart();

    const notifList = get('ecNotifList');
    const notifBadge = get('ecNotifBadge');
    const applyNotifications = (payload) => {
        const unread = Number(payload.unread || 0);
        if (notifBadge) { notifBadge.textContent = unread > 9 ? '9+' : String(unread); notifBadge.hidden = unread === 0; }
        if (!notifList) return;
        const notifications = Array.isArray(payload.notifications) ? payload.notifications.slice(0, 5) : [];
        notifList.innerHTML = notifications.length ? notifications.map((notification) => `<a href="${escapeHtml(notification.url || '#')}" class="block border-b border-slate-100 p-4 hover:bg-slate-50"><strong class="block text-sm text-slate-900">${escapeHtml(notification.title)}</strong><span class="mt-1 block text-xs text-slate-600">${escapeHtml(notification.body)}</span><time class="mt-1 block text-xs text-slate-500">${escapeHtml(notification.created_at)}</time></a>`).join('') : '<p class="p-6 text-center text-sm text-slate-500">Belum ada notifikasi.</p>';
    };
    const refreshNotifications = async () => {
        if (!config.notifUrl) return;
        try { const response = await fetch(config.notifUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }); if (response.ok) applyNotifications(await response.json()); }
        catch { if (notifList) notifList.innerHTML = '<p class="p-6 text-center text-sm text-red-700">Notifikasi gagal dimuat. Coba lagi.</p>'; }
    };
    get('ecNotifTrigger')?.addEventListener('click', refreshNotifications, { once: true });
    get('ecNotifReadAll')?.addEventListener('click', async () => {
        if (!config.notifReadAllUrl) return;
        const response = await fetch(config.notifReadAllUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrfToken, 'X-Requested-With': 'XMLHttpRequest' } });
        if (response.ok) refreshNotifications();
    });
}

window.ecToast = (message, options = {}) => {
    const region = document.getElementById('ecToastRegion');
    if (!region) return;
    const toast = document.createElement('div');
    toast.className = 'ec-toast';
    toast.setAttribute('role', options.tone === 'danger' ? 'alert' : 'status');
    toast.textContent = message;
    region.append(toast);
    window.setTimeout(() => toast.remove(), options.duration || 3500);
};

setupDialogs();
setupQuantities();
setupDropdowns();
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupStorefrontShell, { once: true });
else setupStorefrontShell();
