<?php

namespace App\Http\Controllers;

use App\Models\Company;

/**
 * Halaman dokumentasi Open Catalog API di admin panel.
 * Read-only: menampilkan base URL per perusahaan + referensi endpoint,
 * supaya sistem/website lain gampang diintegrasikan.
 * Lihat docs/prd-company-catalog-api.md & docs/api-catalog-usage.md.
 */
class ApiDocController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_active']);

        return view('backend.api-docs.index', $this->viewData($companies));
    }

    /**
     * Versi publik (tanpa login) dari halaman yang sama, untuk dibagikan
     * ke developer/website eksternal yang mengonsumsi Open Catalog API.
     * Hanya menampilkan perusahaan aktif (yang memang bisa diakses via API).
     */
    public function publicIndex()
    {
        $companies = Company::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('public.api-docs.index', $this->viewData($companies));
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData($companies): array
    {
        return [
            'companies' => $companies,
            'baseUrl' => rtrim(url('/api/v1/companies'), '/'),
            'sampleSlug' => $companies->first()?->slug ?? 'company-slug',
            'endpoints' => $this->endpoints(),
        ];
    }

    /**
     * Referensi endpoint untuk halaman dokumentasi.
     *
     * @return array<int, array<string, mixed>>
     */
    private function endpoints(): array
    {
        return [
            [
                'title' => 'Daftar produk',
                'suffix' => '/products',
                'path' => '/{slug}/products',
                'desc' => 'Daftar produk aktif milik perusahaan, dengan paginasi.',
                'params' => [
                    ['page', 'Halaman paginasi'],
                    ['per_page', 'Item per halaman (default 20, maks 100)'],
                    ['main_category_id', 'Filter per main category'],
                    ['category_detail_id', 'Filter per category detail'],
                    ['category_slug', 'Filter per slug kategori (main atau detail)'],
                    ['search', 'Cari pada nama produk / SKU'],
                    ['in_stock', 'true = hanya yang tersedia'],
                    ['sort', 'newest | name_asc | name_desc | price_asc | price_desc'],
                ],
                'example' => <<<'JSON'
{
  "data": [
    {
      "id": 13,
      "name": "Baut Mur Baja 10.9",
      "slug": "baut-mur-baja-109",
      "category": { "main_category": "Baut", "category_detail": "Baut HTB" },
      "image": "https://.../foto.jpg",
      "price_min": 3050, "price_max": 17400,
      "in_stock": true,
      "created_at": "2026-05-12T02:08:03+07:00"
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 13, "last_page": 1 }
}
JSON,
            ],
            [
                'title' => 'Detail produk',
                'suffix' => '/products/{id|slug}',
                'path' => '/{slug}/products/{id|slug}',
                'desc' => 'Detail satu produk + varian (harga, atribut, in_stock). Angka stok tidak ditampilkan.',
                'params' => [],
                'example' => <<<'JSON'
{
  "data": {
    "id": 13, "name": "Baut Mur Baja 10.9",
    "description": "...",
    "price_min": 3050, "price_max": 17400, "in_stock": true,
    "images": ["https://.../1.jpg"],
    "variants": [
      {
        "id": 113, "sku": "BAUT-...-M10",
        "price": 3050, "in_stock": true,
        "dimensions": { "length_cm": 3.5, "width_cm": 2.2, "height_cm": 2.2 },
        "attributes": [ { "code": "diameter", "name": "Diameter", "value": "M10" } ]
      }
    ]
  }
}
JSON,
            ],
            [
                'title' => 'Daftar kategori',
                'suffix' => '/categories',
                'path' => '/{slug}/categories',
                'desc' => 'Kategori (flat + parent_id) yang punya produk aktif. Tambah ?with_counts=true untuk jumlah produk.',
                'params' => [
                    ['with_counts', 'true = sertakan products_count'],
                ],
                'example' => <<<'JSON'
{
  "data": [
    { "id": 1, "type": "main", "name": "Baut", "slug": "baut", "parent_id": null, "products_count": 3 },
    { "id": 1, "type": "detail", "name": "Baut Hex", "slug": "baut-baut-hex", "parent_id": 1, "products_count": 1 }
  ]
}
JSON,
            ],
            [
                'title' => 'Detail kategori',
                'suffix' => '/categories/{id|slug}',
                'path' => '/{slug}/categories/{id|slug}',
                'desc' => 'Detail kategori. Untuk main category menyertakan children[] (category detail-nya).',
                'params' => [],
                'example' => <<<'JSON'
{
  "data": {
    "id": 1, "type": "main", "name": "Baut", "slug": "baut", "parent_id": null,
    "children": [
      { "id": 1, "type": "detail", "name": "Baut Hex", "slug": "baut-baut-hex", "parent_id": 1 }
    ]
  }
}
JSON,
            ],
        ];
    }
}
