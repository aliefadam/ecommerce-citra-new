<?php

namespace App\Http\Resources\Api\V1;

use App\Support\CatalogImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Kategori dalam bentuk flat + parent_id. `type` membedakan node induk
 * (main category) dari anak (category detail). Lihat PRD §3.
 *
 * @property-read array $catalog
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => (int) $this->resource['id'],
            'type' => (string) $this->resource['type'],
            'name' => (string) $this->resource['name'],
            'slug' => (string) $this->resource['slug'],
            'parent_id' => $this->resource['parent_id'] !== null ? (int) $this->resource['parent_id'] : null,
        ];

        if (array_key_exists('image', $this->resource)) {
            $data['image'] = CatalogImageUrl::make($this->resource['image']);
        }

        if (array_key_exists('products_count', $this->resource)) {
            $data['products_count'] = (int) $this->resource['products_count'];
        }

        return $data;
    }
}
