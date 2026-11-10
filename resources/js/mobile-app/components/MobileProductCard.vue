<script setup>
import { computed } from 'vue';
import { useImageFallback } from '../services/images';

const props = defineProps({
    product: { type: Object, required: true },
    eager: { type: Boolean, default: false },
});

const formatPrice = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const hasRange = computed(() => props.product.price_max !== null && props.product.price_max !== undefined && props.product.price_max > props.product.price_min);
const hasPrice = computed(() => props.product.price_min !== null && props.product.price_min !== undefined);
</script>

<template>
    <RouterLink :to="{ name: 'product', params: { slug: product.slug } }" :class="['mobile-product-card', { 'is-out': !product.in_stock }]">
        <div class="mobile-product-image-wrap">
            <img
                :src="product.image || '/imgs/product-placeholder.svg'"
                :alt="product.name"
                class="mobile-product-image"
                :loading="eager ? 'eager' : 'lazy'"
                @error="useImageFallback"
            />
            <span v-if="!product.in_stock" class="mobile-stock-badge is-empty">Stok habis</span>
        </div>
        <div class="mobile-product-body">
            <strong>{{ product.name }}</strong>
            <span class="mobile-product-variant">{{ product.variant_summary || product.sku || 'Varian standar' }}</span>
            <span class="mobile-product-price">
                <small v-if="hasRange">Mulai</small>
                {{ hasPrice ? formatPrice(product.price_min) : 'Hubungi kami' }}
            </span>
            <span v-if="product.seller?.name" class="mobile-product-seller">{{ product.seller.name }}</span>
            <span class="mobile-product-cta" aria-hidden="true">{{ product.in_stock ? 'Pilih Varian' : 'Lihat Detail' }}</span>
        </div>
    </RouterLink>
</template>
