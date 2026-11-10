const FALLBACK_IMAGE = '/imgs/product-placeholder.svg';

export const useImageFallback = (event) => {
    const image = event.currentTarget;
    if (!(image instanceof HTMLImageElement) || image.dataset.fallbackApplied === 'true') return;

    image.dataset.fallbackApplied = 'true';
    image.src = FALLBACK_IMAGE;
};
