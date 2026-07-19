<?php

namespace App\Support;

/**
 * Mengubah path gambar tersimpan menjadi URL absolut siap pakai untuk
 * konsumen API eksternal. Mengikuti pola resolusi gambar di FrontendController.
 */
class CatalogImageUrl
{
    public static function make(?string $image): ?string
    {
        $value = trim((string) $image);
        if ($value === '') {
            return null;
        }

        if (
            str_starts_with($value, 'http://') ||
            str_starts_with($value, 'https://') ||
            str_starts_with($value, '//') ||
            str_starts_with($value, 'data:')
        ) {
            return $value;
        }

        return asset('storage/'.ltrim($value, '/'));
    }
}
