<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import MobileIcon from './MobileIcon.vue';
import { chooseAxes, differingCodes, pickVariant, valueOf, variantLabel } from '../services/variants';

const props = defineProps({
    variants: { type: Array, required: true },
    modelValue: { type: [Number, String], default: null },
    formatPrice: { type: Function, required: true },
});
const emit = defineEmits(['update:modelValue', 'select']);

const AXIS_PREVIEW = 8;
const SHEET_THRESHOLD = 12;

const selected = computed(() => props.variants.find((variant) => variant.id === props.modelValue) || props.variants[0]);
const axes = computed(() => chooseAxes(props.variants));
const labelCodes = computed(() => axes.value?.map((axis) => axis.code) || differingCodes(props.variants).slice(0, 3));
const expanded = ref({});
const showAllList = ref(false);
const sheetOpen = ref(false);
const sheetQuery = ref('');
const sheetInput = ref(null);

const choose = (variant) => {
    if (!variant || variant.id === props.modelValue) return;
    emit('update:modelValue', variant.id);
    emit('select', variant);
};

// Chip state for one value on one axis, given the buyer's other current choices.
const chipState = (axis, value) => {
    const exact = props.variants.find((variant) => valueOf(variant, axis.code) === value
        && axes.value.every((other) => other.code === axis.code || valueOf(variant, other.code) === valueOf(selected.value, other.code)));
    const anyInStock = props.variants.some((variant) => valueOf(variant, axis.code) === value && variant.in_stock);
    return {
        selected: valueOf(selected.value, axis.code) === value,
        soldOut: exact ? !exact.in_stock : !anyInStock,
        unavailable: !exact,
        exact,
    };
};

const onChip = (axis, value) => {
    const state = chipState(axis, value);
    if (state.selected || state.soldOut) return;
    choose(state.exact || pickVariant(props.variants, axes.value, selected.value, axis.code, value));
};

const visibleValues = (axis) => (expanded.value[axis.code] || axis.values.length <= AXIS_PREVIEW
    ? axis.values
    : axis.values.slice(0, AXIS_PREVIEW));

// Fallback list (variants that attributes cannot tell apart).
const listVariants = computed(() => {
    if (showAllList.value || props.variants.length <= AXIS_PREVIEW) return props.variants;
    const preview = props.variants.slice(0, AXIS_PREVIEW);
    return preview.includes(selected.value) ? preview : [...preview.slice(0, AXIS_PREVIEW - 1), selected.value];
});

const sheetResults = computed(() => {
    const terms = sheetQuery.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
    if (!terms.length) return props.variants;
    return props.variants.filter((variant) => {
        const haystack = [variant.sku, variant.attribute_summary, variantLabel(variant, labelCodes.value)].join(' ').toLowerCase();
        return terms.every((term) => haystack.includes(term));
    });
});

const openSheet = async () => {
    sheetQuery.value = '';
    sheetOpen.value = true;
    await nextTick();
    sheetInput.value?.focus();
};
const pickFromSheet = (variant) => {
    if (!variant.in_stock) return;
    choose(variant);
    sheetOpen.value = false;
};
const onSheetKeydown = (event) => {
    if (event.key === 'Escape') sheetOpen.value = false;
};

watch(sheetOpen, (open) => document.documentElement.classList.toggle('mobile-search-locked', open));
watch(() => props.variants, () => { expanded.value = {}; showAllList.value = false; });
onBeforeUnmount(() => document.documentElement.classList.remove('mobile-search-locked'));
</script>

<template>
    <div v-if="variants.length > 1" class="mobile-variant-selector">
        <template v-if="axes">
            <div v-for="axis in axes" :key="axis.code" class="mobile-variant-axis" role="group" :aria-label="`Pilih ${axis.name}`">
                <p class="mobile-variant-axis-title">
                    <span>{{ axis.name }}: <strong>{{ valueOf(selected, axis.code) || '–' }}</strong></span>
                    <small>{{ axis.values.length }} pilihan</small>
                </p>
                <div class="mobile-variant-chips">
                    <button
                        v-for="value in visibleValues(axis)"
                        :key="value"
                        type="button"
                        :class="['mobile-variant-chip', {
                            'is-selected': chipState(axis, value).selected,
                            'is-disabled': chipState(axis, value).soldOut,
                            'is-unavailable': !chipState(axis, value).selected && chipState(axis, value).unavailable && !chipState(axis, value).soldOut,
                        }]"
                        :aria-pressed="chipState(axis, value).selected"
                        :aria-disabled="chipState(axis, value).soldOut || undefined"
                        :title="chipState(axis, value).soldOut ? 'Stok habis' : undefined"
                        @click="onChip(axis, value)"
                    >
                        <strong>{{ value }}</strong>
                        <small v-if="axes.length === 1">{{ chipState(axis, value).exact?.in_stock ? formatPrice(chipState(axis, value).exact.price) : 'Habis' }}</small>
                    </button>
                </div>
                <button v-if="axis.values.length > AXIS_PREVIEW" type="button" class="mobile-variant-more" @click="expanded[axis.code] = !expanded[axis.code]">
                    {{ expanded[axis.code] ? 'Tampilkan lebih sedikit' : `Lihat semua ${axis.values.length} ${axis.name}` }}
                </button>
            </div>
        </template>

        <div v-else class="mobile-variant-axis" role="group" aria-label="Pilih varian">
            <p class="mobile-variant-axis-title"><span>Pilih varian</span><small>{{ variants.length }} pilihan</small></p>
            <div class="mobile-variant-chips">
                <button
                    v-for="variant in listVariants"
                    :key="variant.id"
                    type="button"
                    :class="['mobile-variant-chip', { 'is-selected': variant.id === selected?.id, 'is-disabled': !variant.in_stock }]"
                    :aria-pressed="variant.id === selected?.id"
                    :aria-disabled="!variant.in_stock || undefined"
                    @click="variant.in_stock && choose(variant)"
                >
                    <strong>{{ variantLabel(variant, labelCodes) }}</strong>
                    <small>{{ variant.in_stock ? formatPrice(variant.price) : 'Habis' }}</small>
                </button>
            </div>
            <button v-if="variants.length > AXIS_PREVIEW" type="button" class="mobile-variant-more" @click="showAllList = !showAllList">
                {{ showAllList ? 'Tampilkan lebih sedikit' : `Lihat semua ${variants.length} varian` }}
            </button>
        </div>

        <button v-if="variants.length > SHEET_THRESHOLD" type="button" class="mobile-variant-search" aria-haspopup="dialog" @click="openSheet">
            <MobileIcon name="search" :size="18" />
            <span>Cari dari {{ variants.length }} varian</span>
            <MobileIcon name="chevron" :size="16" />
        </button>

        <Teleport to="body">
            <Transition name="mobile-variant-sheet">
                <div v-if="sheetOpen" class="mobile-variant-sheet-backdrop" @click.self="sheetOpen = false" @keydown="onSheetKeydown">
                    <div class="mobile-variant-sheet" role="dialog" aria-modal="true" aria-labelledby="mobile-variant-sheet-title">
                        <div class="mobile-variant-sheet-head">
                            <h2 id="mobile-variant-sheet-title">Pilih varian</h2>
                            <button type="button" aria-label="Tutup" @click="sheetOpen = false">×</button>
                        </div>
                        <label class="mobile-visually-hidden" for="mobile-variant-sheet-input">Cari varian</label>
                        <div class="mobile-variant-sheet-search">
                            <MobileIcon name="search" :size="18" />
                            <input id="mobile-variant-sheet-input" ref="sheetInput" v-model="sheetQuery" type="search" placeholder="Cari ukuran, SKU, material…" autocomplete="off" />
                        </div>
                        <p class="mobile-variant-sheet-count" aria-live="polite">{{ sheetResults.length }} varian</p>
                        <ul class="mobile-variant-sheet-list">
                            <li v-for="variant in sheetResults" :key="variant.id">
                                <button
                                    type="button"
                                    :class="{ 'is-selected': variant.id === selected?.id, 'is-disabled': !variant.in_stock }"
                                    :aria-pressed="variant.id === selected?.id"
                                    :aria-disabled="!variant.in_stock || undefined"
                                    @click="pickFromSheet(variant)"
                                >
                                    <span>
                                        <strong>{{ variantLabel(variant, labelCodes) }}</strong>
                                        <small>{{ variant.sku }}</small>
                                    </span>
                                    <em>{{ variant.in_stock ? formatPrice(variant.price) : 'Habis' }}</em>
                                </button>
                            </li>
                        </ul>
                        <p v-if="!sheetResults.length" class="mobile-suggestion-state">Varian tidak ditemukan.</p>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
