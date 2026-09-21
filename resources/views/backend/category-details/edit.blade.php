@extends('layouts.app')

@section('title', 'Edit Category Detail')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form action="{{ route('category-details.update', $categoryDetail) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Main Category</label>
                    <select name="main_category_id"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih kategori utama</option>
                        @foreach ($mainCategories as $mainCategory)
                            <option value="{{ $mainCategory->id }}" @selected((string) old('main_category_id', $categoryDetail->main_category_id) === (string) $mainCategory->id)>{{ $mainCategory->name }}</option>
                        @endforeach
                    </select>
                    @error('main_category_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category Detail Name</label>
                    <input type="text" name="name" value="{{ old('name', $categoryDetail->name) }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Template Spesifikasi</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select name="specification_template_id" class="min-w-0 flex-1 px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tanpa template (kompatibilitas lama)</option>
                            @foreach ($specificationTemplates as $template)
                                <option value="{{ $template->id }}" @selected((string) old('specification_template_id', $categoryDetail->specification_template_id) === (string) $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                        @if ($categoryDetail->specification_template_id)
                            <a href="{{ route('specification-templates.edit', $categoryDetail->specification_template_id) }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                                Edit Field Template
                            </a>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Perubahan berlaku pada form dan detail produk dalam kategori ini.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('category-details.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl">Update</button>
                </div>
            </form>
        </div>
    </main>
@endsection
