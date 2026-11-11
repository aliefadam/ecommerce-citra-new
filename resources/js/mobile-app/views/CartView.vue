<script setup>
import { onMounted, ref } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import { useImageFallback } from '../services/images';
import { useCommerceStore } from '../stores/commerce';

const commerce = useCommerceStore();
const actionError = ref('');
const formatPrice = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);

const update = async (item, quantity) => {
    actionError.value = '';
    try {
        if (quantity < 1) return;
        await commerce.updateCartItem(item.id, quantity);
    } catch (exception) {
        actionError.value = exception.message;
    }
};

const remove = async (item) => {
    actionError.value = '';
    try { await commerce.removeCartItem(item.id); } catch (exception) { actionError.value = exception.message; }
};

onMounted(() => commerce.fetchCart(true).catch(() => {}));
</script>

<template>
    <section class="mobile-screen mobile-cart-screen" aria-labelledby="mobile-route-heading">
        <span class="mobile-eyebrow">Belanja member</span>
        <h1 id="mobile-route-heading" tabindex="-1">Keranjang</h1>
        <p v-if="actionError" class="mobile-alert" role="alert">{{ actionError }}</p>

        <div v-if="commerce.loading && !commerce.cartReady" class="mobile-skeleton-list" aria-busy="true"><span v-for="item in 3" :key="item"></span></div>
        <MobileCatalogState v-else-if="commerce.error && !commerce.cartReady" title="Keranjang gagal dimuat" :message="commerce.error" :retry="() => commerce.fetchCart(true)" />
        <MobileCatalogState v-else-if="!commerce.cart.items.length" title="Keranjang masih kosong" message="Pilih varian produk dari katalog untuk mulai berbelanja." />
        <div v-else class="mobile-cart-list">
            <article v-for="item in commerce.cart.items" :key="item.id" :class="['mobile-cart-item', { 'has-issue': !item.available }]">
                <img :src="item.product.image || '/imgs/product-placeholder.svg'" :alt="item.product.name" @error="useImageFallback" />
                <div class="mobile-cart-content">
                    <RouterLink v-if="item.product.slug" :to="{ name: 'product', params: { slug: item.product.slug } }"><strong>{{ item.product.name }}</strong></RouterLink>
                    <strong v-else>{{ item.product.name }}</strong>
                    <small>{{ item.variant.summary || item.variant.sku }} · {{ item.product.seller }}</small>
                    <span>{{ formatPrice(item.unit_price) }}</span>
                    <p v-if="item.issue" class="mobile-cart-issue">{{ item.issue }}</p>
                    <div class="mobile-cart-actions">
                        <button type="button" :disabled="item.quantity <= 1" :aria-label="`Kurangi ${item.product.name}`" @click="update(item, item.quantity - 1)">−</button>
                        <span>{{ item.quantity }}</span>
                        <button type="button" :disabled="item.quantity >= item.max_quantity" :aria-label="`Tambah ${item.product.name}`" @click="update(item, item.quantity + 1)">+</button>
                        <button type="button" class="is-remove" :aria-label="`Hapus ${item.product.name}`" @click="remove(item)">Hapus</button>
                    </div>
                </div>
            </article>
            <div class="mobile-cart-summary">
                <span>Subtotal produk tersedia</span><strong>{{ formatPrice(commerce.cart.summary.subtotal) }}</strong>
                <button type="button" class="mobile-clear-button" @click="commerce.clearCart">Kosongkan keranjang</button>
            </div>
            <div class="mobile-action-bar has-nav">
                <p aria-live="polite">{{ commerce.cart.summary.has_issues ? 'Selesaikan item bermasalah sebelum checkout.' : '' }}</p>
                <div>
                    <span class="mobile-action-total"><small>Subtotal</small><strong>{{ formatPrice(commerce.cart.summary.subtotal) }}</strong></span>
                    <RouterLink
                        :to="{ name: 'checkout' }"
                        class="mobile-primary-button mobile-button-link"
                        :aria-disabled="commerce.cart.summary.has_issues"
                        :class="{ 'is-disabled': commerce.cart.summary.has_issues }"
                        @click="commerce.cart.summary.has_issues && $event.preventDefault()"
                    >Lanjut Checkout</RouterLink>
                </div>
            </div>
        </div>
    </section>
</template>
