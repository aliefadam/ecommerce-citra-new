<script setup>
defineProps({
    product: { type: Object, required: true },
});

const formatPrice = (value) => value === null || value === undefined
    ? 'Hubungi kami'
    : new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <RouterLink :to="{ name: 'product', params: { slug: product.slug } }" class="mobile-product-card">
        <div class="mobile-product-image-wrap">
            <img
                :src="product.image || '/imgs/product-placeholder.svg'"
                :alt="product.name"
                class="mobile-product-image"
                loading="lazy"
            />
            <span :class="['mobile-stock-badge', { 'is-empty': !product.in_stock }]">
                {{ product.in_stock ? 'Tersedia' : 'Stok habis' }}
            </span>
        </div>
        <div class="mobile-product-body">
            <span class="mobile-product-category">{{ product.category?.name || 'Produk' }}</span>
            <strong>{{ product.name }}</strong>
            <span class="mobile-product-variant">{{ product.variant_summary || product.sku || 'Varian standar' }}</span>
            <span class="mobile-product-price">{{ formatPrice(product.price_min) }}</span>
            <span class="mobile-product-seller">{{ product.seller?.name }}</span>
        </div>
    </RouterLink>
</template>
