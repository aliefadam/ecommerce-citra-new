const { test, expect } = require('@playwright/test');

const sensitiveFields = new Set([
    'stock',
    'company_id',
    'is_redeem_product',
    'redeem_points',
    'low_stock_threshold',
]);

function findSensitiveFields(value, path = 'response', leaks = []) {
    if (Array.isArray(value)) {
        value.forEach((item, index) => findSensitiveFields(item, `${path}[${index}]`, leaks));

        return leaks;
    }

    if (value === null || typeof value !== 'object') {
        return leaks;
    }

    for (const [key, child] of Object.entries(value)) {
        if (sensitiveFields.has(key)) {
            leaks.push(`${path}.${key}`);
        }
        findSensitiveFields(child, `${path}.${key}`, leaks);
    }

    return leaks;
}

test('company catalog API enforces its public v1 contract end to end', async ({ request }) => {
    const companyPath = '/api/v1/companies/pt-dua-sejahtera';

    const listingResponse = await request.get(`${companyPath}/products?sort=price_desc&per_page=100`, {
        headers: { Origin: 'https://catalog-consumer.test' },
    });
    expect(listingResponse.status()).toBe(200);
    expect(listingResponse.headers()['access-control-allow-origin']).toBe('*');
    expect(listingResponse.headers()['cache-control']).toContain('public');
    expect(listingResponse.headers()['cache-control']).toContain('max-age=300');
    expect(listingResponse.headers().etag).toBeTruthy();

    const listing = await listingResponse.json();
    expect(listing.data.map((product) => product.slug)).toEqual([
        'mur-hex-m10-pt-dua-e2e',
        'mur-hex-m8-pt-dua-e2e',
    ]);
    expect(listing.data.map((product) => product.price_min)).toEqual([2250, 1250]);
    expect(listing.meta.total).toBe(2);
    expect(findSensitiveFields(listing)).toEqual([]);

    const notModified = await request.get(`${companyPath}/products?sort=price_desc&per_page=100`, {
        headers: { 'If-None-Match': listingResponse.headers().etag },
    });
    expect(notModified.status()).toBe(304);

    const detailResponse = await request.get(`${companyPath}/products/mur-hex-m8-pt-dua-e2e`);
    expect(detailResponse.status()).toBe(200);
    const detail = await detailResponse.json();
    expect(detail.data.slug).toBe('mur-hex-m8-pt-dua-e2e');
    expect(detail.data.in_stock).toBe(true);
    expect(detail.data.variants).toHaveLength(1);
    expect(detail.data.variants[0]).toMatchObject({
        sku: 'E2E-PTDUA-M8',
        price: 1250,
        in_stock: true,
    });
    expect(findSensitiveFields(detail)).toEqual([]);

    const searchResponse = await request.get(`${companyPath}/products?search=E2E-PTDUA-M8`);
    const search = await searchResponse.json();
    expect(search.data.map((product) => product.slug)).toEqual(['mur-hex-m8-pt-dua-e2e']);

    const inStockResponse = await request.get(`${companyPath}/products?in_stock=true`);
    const inStock = await inStockResponse.json();
    expect(inStock.data.map((product) => product.slug)).toEqual(['mur-hex-m8-pt-dua-e2e']);

    const pageResponse = await request.get(`${companyPath}/products?sort=name_asc&per_page=1&page=2`);
    const page = await pageResponse.json();
    expect(page.data).toHaveLength(1);
    expect(page.meta).toMatchObject({ current_page: 2, per_page: 1, total: 2, last_page: 2 });

    const categoriesResponse = await request.get(`${companyPath}/categories?with_counts=true`);
    expect(categoriesResponse.status()).toBe(200);
    const categories = await categoriesResponse.json();
    expect(categories.data.length).toBeGreaterThanOrEqual(2);
    expect(categories.data.every((category) => category.products_count === 2)).toBe(true);
    const mainCategory = categories.data.find((category) => category.type === 'main');
    const detailCategory = categories.data.find((category) => category.type === 'detail');
    expect(mainCategory.parent_id).toBeNull();
    expect(detailCategory.parent_id).toBe(mainCategory.id);

    const categoryFilterResponse = await request.get(
        `${companyPath}/products?category_slug=${encodeURIComponent(detailCategory.slug)}`,
    );
    const categoryFilter = await categoryFilterResponse.json();
    expect(categoryFilter.data).toHaveLength(2);

    const categoryDetailResponse = await request.get(`${companyPath}/categories/${mainCategory.slug}`);
    expect(categoryDetailResponse.status()).toBe(200);
    const categoryDetail = await categoryDetailResponse.json();
    expect(categoryDetail.data.slug).toBe(mainCategory.slug);
    expect(categoryDetail.data.children.map((category) => category.id)).toContain(detailCategory.id);

    const primaryCompanyResponse = await request.get('/api/v1/companies/boq/products?per_page=100');
    const primaryCompany = await primaryCompanyResponse.json();
    expect(primaryCompany.data.some((product) => product.slug === 'baut-hex-m8-x-25mm-galvanis')).toBe(true);
    expect(primaryCompany.data.some((product) => product.slug.includes('pt-dua-e2e'))).toBe(false);

    const hiddenPaths = [
        '/api/v1/companies/boq/products/mur-hex-m8-pt-dua-e2e',
        `${companyPath}/products/mur-hex-inactive-pt-dua-e2e`,
        '/api/v1/companies/pt-empat-perkasa/products',
        '/api/v1/companies/perusahaan-tidak-ada/products',
    ];
    for (const path of hiddenPaths) {
        const response = await request.get(path);
        expect(response.status(), path).toBe(404);
        expect(response.headers()['content-type']).toContain('application/json');
        expect(findSensitiveFields(await response.json())).toEqual([]);
    }

    let throttledResponse = null;
    for (let attempt = 0; attempt < 121; attempt += 1) {
        const response = await request.get(`${companyPath}/products?rate_limit_probe=${attempt}`);
        if (response.status() === 429) {
            throttledResponse = response;
            break;
        }
    }

    expect(throttledResponse).not.toBeNull();
    expect(throttledResponse.headers()['retry-after']).toBeTruthy();
    expect(throttledResponse.headers()['x-ratelimit-limit']).toBe('120');
});
