<?php

namespace App\Http\Controllers;

use App\Models\MainCategory;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MainCategoryController extends Controller
{
    public function index()
    {
        $mainCategories = MainCategory::query()->latest()->get();
        return view('backend.main-categories.index', compact('mainCategories'));
    }

    public function create()
    {
        return view('backend.main-categories.create');
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('main_categories', 'name')],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ], $this->imageValidationMessages($request));

        try {
            $image = $this->resolveImageValue($request, $imageOptimizer, (string) ($validated['image_url'] ?? ''));
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['image_file' => 'Gambar gagal diproses. Gunakan file JPG, PNG, atau WebP yang valid.'])
                ->withInput();
        }

        MainCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'image' => $image,
        ]);
        return redirect()->route('main-categories.index')->with('success', 'Kategori utama berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(MainCategory $mainCategory)
    {
        return view('backend.main-categories.edit', compact('mainCategory'));
    }

    public function update(Request $request, MainCategory $mainCategory, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('main_categories', 'name')->ignore($mainCategory->id)],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ], $this->imageValidationMessages($request));

        $oldImage = (string) $mainCategory->image;
        try {
            $image = $this->resolveImageValue($request, $imageOptimizer, (string) ($validated['image_url'] ?? ''), $oldImage);
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['image_file' => 'Gambar gagal diproses. Gunakan file JPG, PNG, atau WebP yang valid.'])
                ->withInput();
        }

        $mainCategory->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $mainCategory->id),
            'image' => $image,
        ]);
        if ($image !== $oldImage) {
            $imageOptimizer->deletePublicFile($oldImage);
        }
        return redirect()->route('main-categories.index')->with('success', 'Kategori utama berhasil diperbarui.');
    }

    public function destroy(MainCategory $mainCategory)
    {
        app(ImageOptimizer::class)->deletePublicFile((string) $mainCategory->image);
        $mainCategory->delete();
        return redirect()->route('main-categories.index')->with('success', 'Kategori utama berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignore = null): string
    {
        $base = Str::slug($name) ?: 'kategori-utama';
        $slug = $base;
        $counter = 2;
        while (MainCategory::query()->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }

    private function resolveImageValue(Request $request, ImageOptimizer $imageOptimizer, string $imageUrl, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image_file')) {
            return $imageOptimizer->storeWebp($request->file('image_file'), 'main-categories', 600, 600, 82);
        }

        $trimmed = trim($imageUrl);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return $fallback;
    }

    private function imageValidationMessages(Request $request): array
    {
        return [
            'image_file.uploaded' => $this->uploadFailureMessage($request),
            'image_file.image' => 'File yang dipilih harus berupa gambar yang valid.',
            'image_file.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image_file.max' => 'Ukuran file gambar maksimal 12 MB sebelum dikompres.',
        ];
    }

    private function uploadFailureMessage(Request $request): string
    {
        $file = $request->file('image_file');
        if (! $file || $file->isValid()) {
            return 'File gambar gagal diunggah. Silakan pilih ulang gambar dan coba lagi.';
        }

        $message = match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File gambar melebihi batas upload server. Pilih ulang gambar agar dikompres otomatis sebelum dikirim.',
            UPLOAD_ERR_PARTIAL => 'Upload gambar terputus sebelum selesai. Periksa koneksi lalu coba lagi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara untuk upload belum tersedia di server. Hubungi administrator server.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file upload. Periksa ruang penyimpanan dan izin folder sementara.',
            UPLOAD_ERR_EXTENSION => 'Upload gambar dihentikan oleh ekstensi PHP pada server.',
            default => 'File gambar gagal diunggah. Silakan pilih ulang gambar dan coba lagi.',
        };

        logger()->warning('Main category image upload rejected before processing.', [
            'main_category_id' => $request->route('main_category') instanceof MainCategory
                ? $request->route('main_category')->getKey()
                : null,
            'error_code' => $file->getError(),
            'error_message' => $file->getErrorMessage(),
            'client_name' => $file->getClientOriginalName(),
        ]);

        return $message;
    }
}
