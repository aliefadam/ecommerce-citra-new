# Open Catalog API — Panduan Pemakaian (v1)

API **read-only, publik** untuk menampilkan katalog satu perusahaan di website eksternal.
Tidak butuh API key. Perusahaan ditentukan lewat **slug** di URL.

Base URL: `https://<domain>/api/v1/companies/{companySlug}`

- `{companySlug}` = slug perusahaan (mis. `boq`, `pt-dua-sejahtera`).
- Semua response JSON. Timestamp ISO-8601.
- Rate limit: **120 request/menit per IP** (`429` bila terlampaui).
- Cache: response membawa `Cache-Control: public, max-age=300` + `ETag` (dukung `If-None-Match` → `304`).
- CORS: semua origin diizinkan (bisa dipanggil langsung dari browser).

## Endpoint

### 1. Daftar produk

```
GET /api/v1/companies/{companySlug}/products
```

Query opsional:

| Param | Contoh | Keterangan |
|---|---|---|
| `page` | `2` | Halaman (paginasi) |
| `per_page` | `20` | Item per halaman (default 20, maks 100) |
| `main_category_id` | `1` | Filter per main category |
| `category_detail_id` | `5` | Filter per category detail |
| `category_id` | `1` | Cocok ke main **atau** detail dengan id tsb |
| `category_slug` | `baut` | Cocok ke slug main **atau** detail |
| `search` | `baut m8` | Cari pada nama produk / SKU varian |
| `in_stock` | `true` | Hanya produk yang tersedia |
| `sort` | `price_asc` | `newest` (default), `name_asc`, `name_desc`, `price_asc`, `price_desc` |

Contoh response:

```json
{
  "data": [
    {
      "id": 13,
      "name": "Baut Mur Baja 10.9",
      "slug": "baut-mur-baja-109",
      "category": {
        "main_category_id": 1, "main_category": "Baut",
        "category_detail_id": 5, "category_detail": "Baut HTB"
      },
      "image": "https://.../foto.jpg",
      "price_min": 3050, "price_max": 17400,
      "in_stock": true,
      "created_at": "2026-05-12T02:08:03+07:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 20, "total": 13, "last_page": 1 }
}
```

### 2. Detail produk

```
GET /api/v1/companies/{companySlug}/products/{id|slug}
```

`{id|slug}` boleh id numerik atau slug produk. Response memuat `description`, `images[]`,
dan `variants[]` (harga normal, `in_stock`, dimensi, atribut). **Angka stok tidak ditampilkan.**

```json
{
  "data": {
    "id": 13, "name": "Baut Mur Baja 10.9", "slug": "baut-mur-baja-109",
    "description": "...",
    "price_min": 3050, "price_max": 17400, "in_stock": true,
    "image": "https://.../utama.jpg",
    "images": ["https://.../1.jpg"],
    "variants": [
      {
        "id": 113, "sku": "BAUT-MUR-BAJA-109-M10-X-20MM-FULL-DRAT",
        "price": 3050, "in_stock": true,
        "image": "https://.../v.jpg", "weight_grams": 40,
        "dimensions": { "length_cm": 3.5, "width_cm": 2.2, "height_cm": 2.2 },
        "attributes": [ { "code": "diameter", "name": "Diameter", "value": "M10" } ],
        "attribute_summary": "Diameter: M10 | Panjang: 20mm | ..."
      }
    ]
  }
}
```

### 3. Daftar kategori

```
GET /api/v1/companies/{companySlug}/categories
```

Hanya kategori yang punya produk aktif milik perusahaan. Bentuk **flat + `parent_id`**;
`type` = `main` (induk) atau `detail` (anak, `parent_id` = id main-nya). Tambahkan
`?with_counts=true` untuk `products_count`.

```json
{
  "data": [
    { "id": 1, "type": "main", "name": "Baut", "slug": "baut", "parent_id": null, "image": "https://.../baut.jpg", "products_count": 3 },
    { "id": 1, "type": "detail", "name": "Baut Hex", "slug": "baut-baut-hex", "parent_id": 1, "products_count": 1 }
  ]
}
```

### 4. Detail kategori

```
GET /api/v1/companies/{companySlug}/categories/{id|slug}
```

Untuk `main` menyertakan `children[]` (category detail-nya). Untuk `detail`, `children` kosong.

## Kode error

| Status | Arti |
|---|---|
| `200` | OK |
| `304` | Not Modified (ETag cocok) |
| `404` | Perusahaan/produk/kategori tidak ada, nonaktif, atau milik perusahaan lain |
| `429` | Melewati rate limit (lihat header `Retry-After`) |
| `500` | Kesalahan server |

Body error selalu: `{ "message": "..." }`.

## Catatan

- Harga = harga normal varian; flash sale belum diperhitungkan (rencana fase lanjut).
- Field internal (`stock` angka, `is_redeem_product`, `redeem_points`, `company_id`) sengaja tidak diekspos.
- Perubahan breaking akan naik ke `/api/v2`.
