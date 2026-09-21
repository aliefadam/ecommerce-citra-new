@extends('layouts.app')

@section('title', 'Konfigurasi '.$specificationTemplate->name)

@section('content')
@php
    $configured = $specificationTemplate->fields->keyBy('attribute_definition_id');
    $options = $specificationTemplate->options->where('is_active', true)->groupBy('attribute_definition_id');
@endphp
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6">
        <a href="{{ route('specification-templates.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">&larr; Template spesifikasi</a>
        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Konfigurasi teknis</p><h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $specificationTemplate->name }}</h1></div>
            <p class="font-mono text-xs text-slate-400">{{ $specificationTemplate->code }}</p>
        </div>
    </div>

    @if (session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('specification-templates.update', $specificationTemplate) }}" class="space-y-5">
        @csrf @method('PUT')
        <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800 md:grid-cols-2">
            <div><label class="mb-1.5 block text-sm font-bold text-slate-700">Nama template</label><input name="name" value="{{ old('name', $specificationTemplate->name) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm"></div>
            <div><label class="mb-1.5 block text-sm font-bold text-slate-700">Kode stabil</label><input name="code" value="{{ old('code', $specificationTemplate->code) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm"></div>
            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-bold text-slate-700">Deskripsi</label><textarea name="description" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">{{ old('description', $specificationTemplate->description) }}</textarea></div>
            <label class="inline-flex items-center gap-3 text-sm font-bold text-slate-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $specificationTemplate->is_active)) class="rounded border-slate-300 text-blue-600"> Template aktif</label>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-200 bg-slate-950 px-5 py-4 text-white">
                <h2 class="font-bold">Field spesifikasi</h2><p class="mt-1 text-xs text-slate-300">Aktifkan field, tentukan urutan dan daftar pilihan. Satu baris per pilihan.</p>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ($definitions as $definition)
                    @php $field = $configured->get($definition->id); @endphp
                    <article x-data="{ enabled: {{ old("fields.{$definition->id}.enabled", (bool) ($field?->is_active)) ? 'true' : 'false' }} }" class="p-5">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="fields[{{ $definition->id }}][enabled]" value="1" x-model="enabled" class="mt-1 rounded border-slate-300 text-blue-600">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-900 dark:text-white">{{ $definition->name }}</h3><code class="rounded bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500">{{ $definition->code }}</code><span class="text-xs text-slate-400">{{ $definition->unit ?: 'tanpa unit' }}</span></div>
                                <div x-show="enabled" x-transition class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                    <div><label class="mb-1 block text-xs font-bold text-slate-500">Label khusus</label><input name="fields[{{ $definition->id }}][label_override]" value="{{ old("fields.{$definition->id}.label_override", $field?->label_override) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="{{ $definition->name }}"></div>
                                    <div><label class="mb-1 block text-xs font-bold text-slate-500">Tipe input</label><select name="fields[{{ $definition->id }}][input_type]" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">@foreach(['text'=>'Teks','number'=>'Angka','decimal'=>'Desimal','select'=>'Pilihan'] as $value=>$label)<option value="{{ $value }}" @selected(old("fields.{$definition->id}.input_type", $field?->input_type ?: ($definition->data_type === 'number' ? 'number' : 'text')) === $value)>{{ $label }}</option>@endforeach</select></div>
                                    <div><label class="mb-1 block text-xs font-bold text-slate-500">Urutan</label><input type="number" min="0" name="fields[{{ $definition->id }}][sort_order]" value="{{ old("fields.{$definition->id}.sort_order", $field?->sort_order ?? $definition->sort_order) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                                    <div class="flex flex-col gap-2 pt-1 text-xs font-semibold text-slate-600">
                                        @foreach (['is_required'=>'Wajib','is_filterable'=>'Filterable','affects_variant'=>'Pembeda varian','allow_custom_value'=>'Boleh nilai custom'] as $key=>$label)
                                            <label class="flex items-center gap-2"><input type="checkbox" name="fields[{{ $definition->id }}][{{ $key }}]" value="1" @checked(old("fields.{$definition->id}.{$key}", (bool) ($field?->{$key}))) class="rounded border-slate-300 text-blue-600"> {{ $label }}</label>
                                        @endforeach
                                    </div>
                                    <div class="md:col-span-2 xl:col-span-4"><label class="mb-1 block text-xs font-bold text-slate-500">Pilihan nilai</label><textarea name="fields[{{ $definition->id }}][options]" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-sm" placeholder="Satu nilai per baris">{{ old("fields.{$definition->id}.options", $options->get($definition->id, collect())->pluck('value')->implode("\n")) }}</textarea></div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
        <div class="sticky bottom-4 flex justify-end"><button class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700">Simpan Konfigurasi</button></div>
    </form>

    <section class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 dark:border-slate-600 dark:bg-slate-800/50">
        <h2 class="font-bold text-slate-900 dark:text-white">Butuh field yang belum tersedia?</h2>
        <p class="mt-1 text-xs text-slate-500">Buat definisi baru dan langsung hubungkan ke template ini.</p>
        <form method="POST" action="{{ route('specification-templates.attributes.store', $specificationTemplate) }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <input name="attribute_name" required placeholder="Nama field" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm">
            <input name="attribute_code" required placeholder="kode_stabil" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-mono text-sm">
            <select name="data_type" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="text">Teks</option><option value="number">Angka</option></select>
            <input name="unit" placeholder="Unit (opsional)" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Tambah Field</button>
        </form>
    </section>
</main>
@endsection
