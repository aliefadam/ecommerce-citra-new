<div class="space-y-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Tier</label>
        <input type="text" name="name" value="{{ old('name', $tier->name) }}"
            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"
            placeholder="Contoh: Silver" required>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Minimum Spending</label>
            <input type="number" name="minimum_spending" min="0" value="{{ old('minimum_spending', $tier->minimum_spending ?? 0) }}"
                class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"
                placeholder="1000000" required>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Warna Badge</label>
            <select name="color"
                class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                @foreach (['slate', 'amber', 'orange', 'emerald', 'blue', 'purple', 'rose'] as $color)
                    <option value="{{ $color }}" @selected(old('color', $tier->color ?: 'slate') === $color)>{{ ucfirst($color) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Urutan</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $tier->sort_order ?? 0) }}"
                class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"
                placeholder="0">
        </div>
        <div class="flex items-center pt-8">
            <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tier->exists ? $tier->is_active : true))
                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Tier aktif
            </label>
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Benefit</label>
        <textarea name="benefits" rows="4"
            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"
            placeholder="Opsional, bisa isi daftar benefit atau catatan tier.">{{ old('benefits', $tier->benefits) }}</textarea>
    </div>
</div>
