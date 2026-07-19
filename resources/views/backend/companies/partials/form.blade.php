<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6">
        <a href="{{ route('companies.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Perusahaan
        </a>
        <h1 class="mt-4 text-2xl font-bold text-slate-800 dark:text-white">{{ $pageTitle }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $pageDescription }}</p>
    </div>

    <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="max-w-3xl">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nama Perusahaan</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nama Legal (PT)</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('legal_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Alamat</label>
                <textarea name="address" rows="2"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">{{ old('address', $company->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Email</label>
                    <input type="email" name="email" value="{{ old('email', $company->email) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $company->npwp) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('npwp')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Prefix Invoice</label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $company->invoice_prefix) }}" placeholder="mis. BOQ"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    <p class="mt-1 text-xs text-slate-400">Contoh nomor: {{ old('invoice_prefix', $company->invoice_prefix ?: 'PREFIX') }}-INV-2026-0001</p>
                    @error('invoice_prefix')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Urutan Tampil</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $company->sort_order ?? 0) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('sort_order')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $company->id ? $company->is_active : true))
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700">
                        Perusahaan aktif
                    </label>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Logo</label>
                @if ($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" class="mb-2 h-12 w-12 rounded-lg object-contain border border-slate-200 dark:border-slate-600 bg-white">
                @endif
                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                @error('logo')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/40">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Perusahaan' }}
                </button>
                <a href="{{ route('companies.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700/60">
                    Batal
                </a>
            </div>
        </div>
    </form>
</main>
