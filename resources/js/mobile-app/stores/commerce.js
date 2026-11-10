import { defineStore } from 'pinia';
import { computed, inject, ref } from 'vue';
import { prepareCsrf, request } from '../services/api';

export const useCommerceStore = defineStore('mobile-commerce', () => {
    const config = inject('mobileConfig');
    const cart = ref({ items: [], summary: { line_count: 0, quantity: 0, subtotal: 0, has_issues: false } });
    const wishlist = ref([]);
    const cartReady = ref(false);
    const wishlistReady = ref(false);
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

    const reset = () => {
        cart.value = { items: [], summary: { line_count: 0, quantity: 0, subtotal: 0, has_issues: false } };
        wishlist.value = [];
        cartReady.value = false;
        wishlistReady.value = false;
    };

    return {
        cart, wishlist, cartReady, wishlistReady, loading, error, isWished,
        fetchCart, addCartItem, updateCartItem, removeCartItem, clearCart,
        fetchWishlist, setWishlist, reset,
    };
});
