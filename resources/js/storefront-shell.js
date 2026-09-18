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

    const categories = Array.isArray(config.categories) ? config.categories : [];
    const categoryMenu = get('ecMegaCategoryMenu');
    const categoryContent = get('ecMegaCategoryContent');
    let activeCategory = categories[0]?.key;
    const renderCategories = () => {
        if (!categoryMenu || !categoryContent) return;
        categoryMenu.innerHTML = categories.length ? categories.map((category) => `
            <button type="button" role="tab" aria-selected="${category.key === activeCategory}" data-category-key="${escapeHtml(category.key)}"
                class="w-full min-h-11 rounded-lg px-3 text-left text-sm font-bold ${category.key === activeCategory ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'}">${escapeHtml(category.name)}</button>
        `).join('') : '<p class="p-3 text-sm text-slate-500">Kategori belum tersedia.</p>';
        const category = categories.find((item) => item.key === activeCategory) || categories[0];
        if (!category) { categoryContent.innerHTML = '<p class="p-5 text-sm text-slate-500">Katalog sedang disiapkan.</p>'; return; }
        const items = (category.columns || []).flatMap((column) => column.items || []);
        categoryContent.innerHTML = `<div class="flex items-center justify-between border-b border-slate-200 pb-3"><div><p class="ec-display text-xl">${escapeHtml(category.name)}</p><p class="text-xs text-slate-500">${items.length} subkategori</p></div><a class="ec-btn ec-btn-text" href="${escapeHtml(category.url)}">Lihat semua</a></div>
            <div class="grid grid-cols-2 gap-1 py-3 sm:grid-cols-3">${items.map((item) => `<a class="min-h-11 rounded-lg p-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-800" href="${escapeHtml(item.url)}">${escapeHtml(item.name)}</a>`).join('') || '<p class="p-3 text-sm text-slate-500">Belum ada subkategori.</p>'}</div>`;
    };
    categoryMenu?.addEventListener('click', (event) => {
        const tab = event.target.closest('[data-category-key]');
        if (!tab) return;
        activeCategory = tab.dataset.categoryKey;
        renderCategories();
        categoryMenu.querySelector('[aria-selected="true"]')?.focus();
    });
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
