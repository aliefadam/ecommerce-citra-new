<script setup>
import { onMounted } from 'vue';
import MobileCatalogState from '../components/MobileCatalogState.vue';
import MobileProductCard from '../components/MobileProductCard.vue';
import { useCommerceStore } from '../stores/commerce';

const commerce = useCommerceStore();
onMounted(() => commerce.fetchWishlist(true).catch(() => {}));
</script>

<template>
    <section class="mobile-screen" aria-labelledby="mobile-route-heading">
        <RouterLink :to="{ name: 'account' }" class="mobile-back-link">← Profil</RouterLink>
        <span class="mobile-eyebrow">Produk tersimpan</span>
        <h1 id="mobile-route-heading" tabindex="-1">Wishlist</h1>
        <div v-if="!commerce.wishlistReady" class="mobile-product-grid" aria-busy="true"><div v-for="item in 4" :key="item" class="mobile-product-skeleton"></div></div>
        <MobileCatalogState v-else-if="!commerce.wishlist.length" title="Wishlist masih kosong" message="Simpan produk dari halaman detail untuk menemukannya kembali." />
        <div v-else class="mobile-product-grid">
            <div v-for="product in commerce.wishlist" :key="product.id" class="mobile-wishlist-item">
                <MobileProductCard :product="product" />
                <button type="button" :aria-label="`Hapus ${product.name} dari wishlist`" @click="commerce.setWishlist(product.slug, false)">Hapus</button>
            </div>
        </div>
    </section>
</template>
