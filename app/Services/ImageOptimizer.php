<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Store an image as a WebP with an exact output size.
     *
     * The source is cropped from the centre (cover behaviour), which makes this
     * suitable for fixed-ratio placements such as homepage banners.
     */
    public function storeWebpCover(
        UploadedFile $file,
        string $directory,
        int $width,
        int $height,
        int $quality = 82,
        bool $withStoragePrefix = false,
        string $disk = 'public'
    ): string {
        $image = $this->createImage($file);
        $image = $this->applyOrientation($image, $file);

        [$sourceWidth, $sourceHeight] = [imagesx($image), imagesy($image)];
        $targetRatio = $width / $height;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = max(1, (int) round($sourceHeight * $targetRatio));
            $sourceX = max(0, (int) floor(($sourceWidth - $cropWidth) / 2));
            $sourceY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = max(1, (int) round($sourceWidth / $targetRatio));
            $sourceX = 0;
            $sourceY = max(0, (int) floor(($sourceHeight - $cropHeight) / 2));
        }

        $canvas = $this->transparentCanvas($width, $height);
        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            $sourceX,
            $sourceY,
            $width,
            $height,
            $cropWidth,
            $cropHeight
        );

        return $this->persistWebp(
            $image,
            $canvas,
            $directory,
            $quality,
            $withStoragePrefix,
            $disk
        );
    }

    public function storeWebp(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1200,
        int $maxHeight = 1200,
        int $quality = 82,
        bool $withStoragePrefix = false,
        string $disk = 'public'
    ): string {
        $image = $this->createImage($file);
        $image = $this->applyOrientation($image, $file);

        [$sourceWidth, $sourceHeight] = [imagesx($image), imagesy($image)];
        [$targetWidth, $targetHeight] = $this->fitDimensions($sourceWidth, $sourceHeight, $maxWidth, $maxHeight);

        $canvas = $this->transparentCanvas($targetWidth, $targetHeight);

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        return $this->persistWebp(
            $image,
            $canvas,
            $directory,
            $quality,
            $withStoragePrefix,
            $disk
        );
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

    public function deleteStoredFile(?string $path): void
    {
        $path = trim((string) $path);
        if (str_starts_with($path, 'private/')) {
            Storage::disk('local')->delete(substr($path, 8));

            return;
        }

        $this->deletePublicFile($path);
    }

    private function createImage(UploadedFile $file): \GdImage
    {
        $contents = file_get_contents($file->getRealPath());
        // Some PNG files contain an invalid embedded ICC profile. GD/libpng can still
        // decode the image, but emits a warning that Laravel may convert to an exception.
        $image = $contents !== false ? @imagecreatefromstring($contents) : false;

        if (! $image instanceof \GdImage) {
            throw new \RuntimeException('File gambar tidak bisa diproses.');
        }

        return $image;
    }

    private function applyOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
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

    private function transparentCanvas(int $width, int $height): \GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        return $canvas;
    }

    private function persistWebp(
        \GdImage $source,
        \GdImage $canvas,
        string $directory,
        int $quality,
        bool $withStoragePrefix,
        string $disk
    ): string {
        ob_start();
        $encoded = imagewebp($canvas, null, max(1, min(100, $quality)));
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if (! $encoded || ! is_string($contents) || $contents === '') {
            throw new \RuntimeException('Gambar gagal dikonversi ke format WebP.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        if (! Storage::disk($disk)->put($path, $contents)) {
            throw new \RuntimeException('Gambar WebP gagal disimpan.');
        }

        return $withStoragePrefix
            ? ($disk === 'public' ? 'storage/'.$path : 'private/'.$path)
            : $path;
    }
}
