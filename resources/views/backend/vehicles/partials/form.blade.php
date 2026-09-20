<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
    <div class="space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nama Kendaraan</label>
                <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" required
                    placeholder="Contoh: Motor Kurir 1"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:ring-amber-500/20">
                @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Jenis</label>
                <select name="type" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:ring-amber-500/20">
                    @foreach (\App\Models\Vehicle::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $vehicle->type ?: 'motor') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nomor Polisi</label>
                <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" placeholder="B 1234 XYZ"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm uppercase text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:ring-amber-500/20">
                @error('plate_number') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Kapasitas Maks. (kg)</label>
                <input type="number" name="capacity_kg" min="1" value="{{ old('capacity_kg', $vehicle->capacity_kg) }}" placeholder="20"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:ring-amber-500/20">
                @error('capacity_kg') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Urutan</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $vehicle->sort_order ?? 0) }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:ring-amber-500/20">
                @error('sort_order') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Catatan Operasional</label>
            <textarea name="notes" rows="4" placeholder="Wilayah layanan, jenis barang, atau ketentuan khusus..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:ring-amber-500/20">{{ old('notes', $vehicle->notes) }}</textarea>
            @error('notes') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <aside class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-400 text-slate-950 shadow-sm">
            <i data-lucide="calculator" class="h-5 w-5"></i>
        </div>
        <label class="mt-5 block text-sm font-bold text-slate-800 dark:text-white">Tarif per kilogram</label>
        <div class="mt-2 flex overflow-hidden rounded-xl border border-amber-200 bg-white focus-within:ring-2 focus-within:ring-amber-300 dark:border-amber-500/30 dark:bg-slate-800">
            <span class="flex items-center border-r border-amber-100 px-3 text-sm font-bold text-amber-700 dark:border-amber-500/20 dark:text-amber-300">Rp</span>
            <input id="vehicleRate" type="number" name="rate_per_kg" min="0" step="1" value="{{ old('rate_per_kg', $vehicle->rate_per_kg ?? 0) }}" required
                class="min-w-0 flex-1 bg-transparent px-3 py-3 text-lg font-bold text-slate-900 outline-none dark:text-white">
        </div>
        @error('rate_per_kg') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-3 text-xs leading-5 text-amber-900/70 dark:text-amber-100/70">Berat selalu dibulatkan ke atas. Contoh 1,2 kg dihitung 2 kg.</p>

        <div class="mt-5 border-t border-amber-200 pt-5 dark:border-amber-500/20">
            <p class="text-sm font-bold text-slate-800 dark:text-white">Tarif berdasarkan jarak</p>
            <div class="mt-2 grid grid-cols-[90px_minmax(0,1fr)] gap-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-amber-900/70 dark:text-amber-100/70">Setiap</label>
                    <div class="flex overflow-hidden rounded-xl border border-amber-200 bg-white dark:border-amber-500/30 dark:bg-slate-800">
                        <input type="number" name="distance_block_km" min="0.01" step="0.01"
                            value="{{ old('distance_block_km', $vehicle->distance_block_km ?? 5) }}" required
                            class="min-w-0 w-full bg-transparent px-2 py-2.5 text-sm font-bold text-slate-900 outline-none dark:text-white">
                        <span class="flex items-center pr-2 text-xs font-bold text-slate-400">km</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-amber-900/70 dark:text-amber-100/70">Biaya jarak</label>
                    <div class="flex overflow-hidden rounded-xl border border-amber-200 bg-white dark:border-amber-500/30 dark:bg-slate-800">
                        <span class="flex items-center border-r border-amber-100 px-2 text-xs font-bold text-amber-700 dark:border-amber-500/20 dark:text-amber-300">Rp</span>
                        <input type="number" name="rate_per_distance_block" min="0" step="1"
                            value="{{ old('rate_per_distance_block', $vehicle->rate_per_distance_block ?? 5000) }}" required
                            class="min-w-0 w-full bg-transparent px-2 py-2.5 text-sm font-bold text-slate-900 outline-none dark:text-white">
                    </div>
                </div>
            </div>
            @error('distance_block_km') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('rate_per_distance_block') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-xs leading-5 text-amber-900/70 dark:text-amber-100/70">Contoh: setiap 5 km dikenakan Rp5.000. Jarak 8 km dihitung 2 blok.</p>

            <div class="mt-4">
                <label class="mb-1 block text-[11px] font-semibold text-amber-900/70 dark:text-amber-100/70">Jarak maksimal kendaraan</label>
                <div class="flex overflow-hidden rounded-xl border border-amber-200 bg-white focus-within:ring-2 focus-within:ring-amber-300 dark:border-amber-500/30 dark:bg-slate-800">
                    <input type="number" name="max_distance_km" min="0.01" step="0.01"
                        value="{{ old('max_distance_km', $vehicle->max_distance_km ?? 50) }}" required
                        class="min-w-0 flex-1 bg-transparent px-3 py-2.5 text-sm font-bold text-slate-900 outline-none dark:text-white">
                    <span class="flex items-center border-l border-amber-100 px-3 text-xs font-bold text-slate-400 dark:border-amber-500/20">km</span>
                </div>
                @error('max_distance_km') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-[11px] leading-4 text-amber-900/70 dark:text-amber-100/70">Pesanan di atas jarak ini akan ditolak sebagai luar jangkauan.</p>
            </div>
        </div>

        <div class="mt-5 border-t border-amber-200 pt-5 dark:border-amber-500/20">
            <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vehicle->exists ? $vehicle->is_active : true))
                    class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                Dapat dipilih oleh admin
            </label>
        </div>
    </aside>
</div>
