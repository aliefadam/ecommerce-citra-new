// Helpers for turning raw product variants into a buyer-friendly selector.
const EMPTY_VALUES = new Set(['', '-', 'n/a', 'na', 'none', 'null', 'tidak ada']);
const MAX_AXES = 3;

export const attributeText = (attribute) => {
    const value = String(attribute?.value ?? '').trim();
    if (!attribute?.unit || value.toLowerCase().includes(String(attribute.unit).toLowerCase())) return value;
    return `${value} ${attribute.unit}`;
};

export const usableAttributes = (variant) => (variant?.attributes || [])
    .filter((attribute) => !EMPTY_VALUES.has(String(attribute.value ?? '').trim().toLowerCase()))
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));

export const valueOf = (variant, code) => {
    const attribute = usableAttributes(variant).find((item) => item.code === code);
    return attribute ? attributeText(attribute) : '';
};

// Attribute codes whose value differs between variants, in display order.
export const differingCodes = (variants = []) => {
    if (variants.length < 2) return [];
    const entries = new Map();
    variants.forEach((variant) => usableAttributes(variant).forEach((attribute) => {
        if (!entries.has(attribute.code)) entries.set(attribute.code, { order: attribute.sort_order ?? 0, name: attribute.name });
    }));
    return [...entries.entries()]
        .filter(([code]) => new Set(variants.map((variant) => valueOf(variant, code))).size > 1)
        .sort((a, b) => a[1].order - b[1].order)
        .map(([code]) => code);
};

const comboCount = (variants, codes) => new Set(variants.map((variant) => codes.map((code) => valueOf(variant, code)).join('\u0000'))).size;

// Smallest set of attributes (max 3) that tells every variant apart.
// Correlated attributes (diameter, size, thread size…) collapse into one axis.
// Returns null when variants cannot be told apart by attributes alone.
export const chooseAxes = (variants = []) => {
    const codes = differingCodes(variants);
    if (!codes.length) return null;
    const chosen = [];
    let current = 1;
    while (chosen.length < MAX_AXES && current < variants.length) {
        let best = null;
        codes.filter((code) => !chosen.includes(code)).forEach((code) => {
            const count = comboCount(variants, [...chosen, code]);
            if (count > current && (!best || count > best.count)) best = { code, count };
        });
        if (!best) break;
        chosen.push(best.code);
        current = best.count;
    }
    if (current < variants.length) return null;

    return codes.filter((code) => chosen.includes(code)).map((code) => {
        const name = variants.flatMap(usableAttributes).find((attribute) => attribute.code === code)?.name || code;
        const values = [...new Set(variants.map((variant) => valueOf(variant, code)).filter(Boolean))];
        return { code, name, values };
    });
};

// Variant to switch to when the buyer taps `value` on axis `code`:
// keep as many of the other current choices as possible, prefer in-stock.
export const pickVariant = (variants, axes, current, code, value) => {
    const candidates = variants.filter((variant) => valueOf(variant, code) === value);
    let best = null;
    candidates.forEach((variant) => {
        const kept = axes.filter((axis) => axis.code !== code && valueOf(variant, axis.code) === valueOf(current, axis.code)).length;
        const score = (variant.in_stock ? 10 : 0) + kept;
        if (!best || score > best.score) best = { variant, score };
    });
    return best?.variant || null;
};

export const variantLabel = (variant, codes) => {
    const parts = codes.map((code) => valueOf(variant, code)).filter(Boolean);
    return parts.join(' · ') || variant?.sku || 'Varian';
};
