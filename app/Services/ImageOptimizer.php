<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    public function storeWebp(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1200,
        int $maxHeight = 1200,
        int $quality = 82,
        bool $withStoragePrefix = false
    ): string {
        $image = $this->createImage($file);
        $image = $this->applyOrientation($image, $file);

        [$sourceWidth, $sourceHeight] = [imagesx($image), imagesy($image)];
        [$targetWidth, $targetHeight] = $this->fitDimensions($sourceWidth, $sourceHeight, $maxWidth, $maxHeight);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        ob_start();
        imagewebp($canvas, null, max(1, min(100, $quality)));
        $contents = ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        $path = trim($directory, '/') . '/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($path, $contents);

        return $withStoragePrefix ? 'storage/' . $path : $path;
    }

    public function deletePublicFile(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return;
        }

        $path = Str::startsWith($path, 'storage/') ? Str::after($path, 'storage/') : $path;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function createImage(UploadedFile $file): \GdImage
    {
        $contents = file_get_contents($file->getRealPath());
        $image = $contents !== false ? imagecreatefromstring($contents) : false;

        if (!$image instanceof \GdImage) {
            throw new \RuntimeException('File gambar tidak bisa diproses.');
        }

        return $image;
    }

    private function applyOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (!function_exists('exif_read_data') || !in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated instanceof \GdImage) {
            if ($rotated !== $image) {
                imagedestroy($image);
            }

            return $rotated;
        }

        return $image;
    }

    private function fitDimensions(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        if ($width <= 0 || $height <= 0) {
            return [1, 1];
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }
}
