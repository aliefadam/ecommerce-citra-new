<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileProductGallery from '../components/MobileProductGallery.vue';
import { request } from '../services/api';
import { useCommerceStore } from '../stores/commerce';
import { trackMobileEvent } from '../services/telemetry';
import { useSessionStore } from '../stores/session';

const route = useRoute();
const router = useRouter();
const session = useSessionStore();
const commerce = useCommerceStore();
const product = ref(null);
const selectedVariantId = ref(null);
const loading = ref(true);
const error = ref('');
const shareStatus = ref('');
const actionStatus = ref('');
const headingElement = ref(null);

const selectedVariant = computed(() => product.value?.variants.find((variant) => variant.id === selectedVariantId.value) || product.value?.variants[0]);
const galleryImages = computed(() => {
    if (!product.value) return [];
    const images = [
        product.value.image,
        ...(product.value.images || []),
        ...product.value.variants.map((variant) => variant.image),
        product.value.specification_image,
    ].filter((image) => typeof image === 'string' && image !== '');
    const unique = [...new Set(images)];
    return unique.length ? unique : ['/imgs/product-placeholder.svg'];
});
const formatPrice = (value) => value === null || value === undefined
    ? 'Hubungi kami'
    : new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const load = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await request(`/api/mobile/v1/products/${encodeURIComponent(route.params.slug)}`);
        product.value = response.data;
        selectedVariantId.value = response.data.variants.find((variant) => variant.in_stock)?.id || response.data.variants[0]?.id;
        if (session.user) await commerce.fetchWishlist().catch(() => {});
        await nextTick();
        requestAnimationFrame(() => headingElement.value?.focus({ preventScroll: true }));
    } catch (exception) {
        error.value = exception.status === 404 ? 'Produk tidak ditemukan atau sudah tidak tersedia.' : exception.message;
    } finally {
        loading.value = false;
    }
};

const requireLogin = async () => {
    if (session.user) return true;
    await router.push({ name: 'login', query: { return: route.fullPath } });
    return false;
};

const addToCart = async () => {
    if (!await requireLogin()) return;
    actionStatus.value = '';
    try {
        await commerce.addCartItem(selectedVariant.value.id, 1);
        trackMobileEvent('cart_item_added', { item_count: 1 }, route.name);
        actionStatus.value = 'Produk ditambahkan ke keranjang.';
    } catch (exception) {
        actionStatus.value = exception.message;
    }
};

const toggleWishlist = async () => {
    if (!await requireLogin()) return;
    const wished = !commerce.isWished(product.value.slug);
    try {
        await commerce.setWishlist(product.value.slug, wished);
        actionStatus.value = wished ? 'Produk disimpan ke wishlist.' : 'Produk dihapus dari wishlist.';
    } catch (exception) {
        actionStatus.value = exception.message;
    }
};

const share = async () => {
    const data = { title: product.value.name, text: product.value.name, url: window.location.href };
    try {
        if (navigator.share) await navigator.share(data);
        else await navigator.clipboard.writeText(window.location.href);
        shareStatus.value = navigator.share ? 'Produk dibagikan.' : 'Tautan disalin.';
    } catch (exception) {
        if (exception.name !== 'AbortError') shareStatus.value = 'Tautan belum dapat dibagikan.';
    }
};

watch(() => route.params.slug, load, { immediate: true });
</script>

<template>
    <section class="mobile-screen mobile-product-detail" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'categories' }" class="mobile-back-link">← Katalog</RouterLink>

        <div v-if="loading" class="mobile-detail-skeleton" aria-label="Memuat detail produk" aria-busy="true"></div>
        <MobileCatalogState v-else-if="error" title="Detail produk gagal dimuat" :message="error" :retry="load" />

        <template v-else-if="product">
            <MobileProductGallery
                :images="galleryImages"
                :alt="product.name"
                :focus-image="selectedVariant?.image || ''"
            />
            <div class="mobile-detail-heading">
                <div>
                    <span class="mobile-eyebrow">{{ product.category.name || 'Produk' }}</span>
                    <h1 id="mobile-route-heading" ref="headingElement" tabindex="-1">{{ product.name }}</h1>
                </div>
                <button type="button" class="mobile-share-button" aria-label="Bagikan produk" @click="share">↗</button>
            </div>
            <p class="mobile-share-status" aria-live="polite">{{ shareStatus }}</p>
            <p class="mobile-detail-price">{{ formatPrice(selectedVariant?.price ?? product.price_min) }}</p>
            <p class="mobile-detail-seller">Dijual oleh <strong>{{ product.seller.name }}</strong></p>

            <fieldset v-if="product.variants.length" class="mobile-variant-picker">
                <legend>Pilih varian</legend>
                <label v-for="variant in product.variants" :key="variant.id" :class="{ 'is-selected': selectedVariantId === variant.id }">
                    <input v-model="selectedVariantId" type="radio" name="variant" :value="variant.id" :disabled="!variant.in_stock" @change="trackMobileEvent('variant_selected', {}, route.name)" />
                    <span><strong>{{ variant.attribute_summary || variant.sku }}</strong><small>{{ formatPrice(variant.price) }} · {{ variant.in_stock ? 'Tersedia' : 'Habis' }}</small></span>
                </label>
            </fieldset>

            <section class="mobile-detail-section">
                <h2>Deskripsi</h2>
                <p>{{ product.description || 'Deskripsi produk belum tersedia.' }}</p>
            </section>

            <section v-if="product.specifications.length" class="mobile-detail-section">
                <h2>Spesifikasi</h2>
                <dl class="mobile-specifications">
                    <template v-for="specification in product.specifications" :key="specification.code">
                        <dt>{{ specification.name }}</dt>
                        <dd>{{ specification.values.join(', ') }}{{ specification.unit ? ` ${specification.unit}` : '' }}</dd>
                    </template>
                </dl>
            </section>

            <div class="mobile-detail-action">
                <p aria-live="polite">{{ actionStatus }}</p>
                <div>
                    <button type="button" class="mobile-secondary-button" :disabled="commerce.loading" @click="toggleWishlist">
                        {{ commerce.isWished(product.slug) ? '♥ Tersimpan' : '♡ Wishlist' }}
                    </button>
                    <button type="button" class="mobile-primary-button" :disabled="!selectedVariant?.in_stock || commerce.loading" @click="addToCart">
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
        </template>
    </section>
</template>
