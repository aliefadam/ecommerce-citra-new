<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Basis Open Catalog API (read-only, publik).
 * Lihat docs/prd-company-catalog-api.md.
 */
abstract class ApiController extends Controller
{
    /**
     * TTL cache server-side (detik). Katalog jarang berubah, jadi mayoritas
     * request dilayani dari cache tanpa menyentuh DB.
     */
    protected int $cacheTtl = 300;

    /**
     * Resolusi perusahaan dari slug di URL. Hanya perusahaan aktif yang tampil;
     * slug tidak ada / nonaktif -> 404 (tanpa membedakan keduanya).
     */
    protected function resolveCompany(string $slug): Company
    {
        return Company::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Bungkus pembentukan payload dengan cache server-side yang di-key dari
     * perusahaan + seluruh query string, sehingga tiap kombinasi filter/halaman
     * punya entri sendiri dengan TTL pendek.
     */
    protected function cached(string $prefix, Company $company, Request $request, Closure $callback): array
    {
        $params = $request->query();
        ksort($params);

        $key = 'catalog_api:'.$prefix.':'.$company->id.':'.md5(json_encode($params));

        return Cache::remember($key, $this->cacheTtl, $callback);
    }

    protected function perPage(Request $request): int
    {
        return max(1, min(100, (int) $request->query('per_page', 20)));
    }
}
