import { defineStore } from 'pinia';
import { computed, inject, ref } from 'vue';
import { prepareCsrf, request } from '../services/api';

export const useCommerceStore = defineStore('mobile-commerce', () => {
    const config = inject('mobileConfig');
    const cart = ref({ items: [], summary: { line_count: 0, quantity: 0, subtotal: 0, has_issues: false } });
    const wishlist = ref([]);
    const cartReady = ref(false);
    const wishlistReady = ref(false);
    const notifications = ref([]);
    const notificationMeta = ref({ unread: 0, read: 0, total: 0 });
    const notificationsReady = ref(false);
    const loading = ref(false);
    const error = ref('');

    const mutate = async (path, options) => {
        await prepareCsrf();
        return request(`${config.apiBase}${path}`, options);
    };

    const fetchCart = async (force = false) => {
        if (cartReady.value && !force) return;
        loading.value = true;
        error.value = '';
        try {
            const response = await request(`${config.apiBase}/cart`);
            cart.value = response.data;
            cartReady.value = true;
        } catch (exception) {
            error.value = exception.message;
            throw exception;
        } finally {
            loading.value = false;
        }
    };

    const addCartItem = async (productVariantId, quantity = 1) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await mutate('/cart/items', {
                method: 'POST',
                body: { product_variant_id: productVariantId, quantity },
            });
            cart.value = response.data;
            cartReady.value = true;
        } catch (exception) {
            error.value = exception.message;
            throw exception;
        } finally {
            loading.value = false;
        }
    };

    const updateCartItem = async (id, quantity) => {
        const response = await mutate(`/cart/items/${id}`, { method: 'PATCH', body: { quantity } });
        cart.value = response.data;
        cartReady.value = true;
    };

    const removeCartItem = async (id) => {
        await mutate(`/cart/items/${id}`, { method: 'DELETE' });
        await fetchCart(true);
    };

    const clearCart = async () => {
        await mutate('/cart', { method: 'DELETE' });
        cart.value = { items: [], summary: { line_count: 0, quantity: 0, subtotal: 0, has_issues: false } };
        cartReady.value = true;
    };

    const fetchWishlist = async (force = false) => {
        if (wishlistReady.value && !force) return;
        const response = await request(`${config.apiBase}/wishlist`);
        wishlist.value = response.data || [];
        wishlistReady.value = true;
    };

    const isWished = computed(() => (slug) => wishlist.value.some((product) => product.slug === slug));

    const setWishlist = async (slug, wished) => {
        await mutate(`/wishlist/${encodeURIComponent(slug)}`, { method: wished ? 'PUT' : 'DELETE' });
        await fetchWishlist(true);
    };

    const wishlistCount = computed(() => wishlist.value.length);
    const unreadNotifications = computed(() => Number(notificationMeta.value.unread || 0));

    const fetchNotifications = async (force = false) => {
        if (notificationsReady.value && !force) return;
        const response = await request(`${config.apiBase}/notifications`);
        notifications.value = response.data || [];
        const meta = response.meta || {};
        const total = Number(meta.total ?? notifications.value.length);
        const unread = Number(meta.unread ?? notifications.value.filter((item) => !item.read).length);
        notificationMeta.value = { unread, total, read: Number(meta.read ?? Math.max(0, total - unread)) };
        notificationsReady.value = true;
    };

    const markNotificationRead = async (item) => {
        if (item.read) return;
        await mutate(`/notifications/${item.id}/read`, { method: 'POST', body: {} });
        item.read = true;
        const unread = Math.max(0, notificationMeta.value.unread - 1);
        notificationMeta.value = { ...notificationMeta.value, unread, read: Math.max(0, notificationMeta.value.total - unread) };
    };

    const markAllNotificationsRead = async () => {
        await mutate('/notifications/read-all', { method: 'POST', body: {} });
        notifications.value.forEach((item) => { item.read = true; });
        notificationMeta.value = { ...notificationMeta.value, unread: 0, read: notificationMeta.value.total };
    };

    const reset = () => {
        cart.value = { items: [], summary: { line_count: 0, quantity: 0, subtotal: 0, has_issues: false } };
        wishlist.value = [];
        cartReady.value = false;
        wishlistReady.value = false;
        notifications.value = [];
        notificationMeta.value = { unread: 0, read: 0, total: 0 };
        notificationsReady.value = false;
    };

    return {
        cart, wishlist, cartReady, wishlistReady, loading, error, isWished,
        fetchCart, addCartItem, updateCartItem, removeCartItem, clearCart,
        fetchWishlist, setWishlist, reset,
        notifications, notificationMeta, notificationsReady, wishlistCount, unreadNotifications,
        fetchNotifications, markNotificationRead, markAllNotificationsRead,
    };
});
