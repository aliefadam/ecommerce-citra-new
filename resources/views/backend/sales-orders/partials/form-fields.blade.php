<section class="space-y-6">
    {{-- Customer --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-bold text-slate-800 dark:text-white">Customer</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih customer existing atau input customer baru manual.</p>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-700/70">
                <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 has-[:checked]:bg-white has-[:checked]:text-blue-700 has-[:checked]:shadow-sm dark:text-slate-300 dark:has-[:checked]:bg-slate-800 dark:has-[:checked]:text-blue-300">
                    <input class="sr-only" type="radio" name="customer_mode" value="existing" checked onchange="setSoCustomerMode('existing')">
                    Existing
                </label>
                <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 has-[:checked]:bg-white has-[:checked]:text-blue-700 has-[:checked]:shadow-sm dark:text-slate-300 dark:has-[:checked]:bg-slate-800 dark:has-[:checked]:text-blue-300">
                    <input class="sr-only" type="radio" name="customer_mode" value="manual" onchange="setSoCustomerMode('manual')">
                    Manual (customer baru)
                </label>
            </div>
        </div>

        <div id="soExistingCustomerPanel" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div>
                <label for="so_customer_id" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Customer</label>
                <select id="so_customer_id" name="customer_id" class="w-full"></select>
            </div>
            <div id="soCustomerSnapshot" class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-700/40 dark:text-slate-400">
                Belum ada customer dipilih.
            </div>
        </div>

        <div id="soManualCustomerPanel" class="hidden grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nama</label>
                <input name="manual_customer_name" type="text" value="{{ old('manual_customer_name') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nomor HP</label>
                <input name="manual_customer_phone" type="text" value="{{ old('manual_customer_phone') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Email</label>
                <input name="manual_customer_email" type="email" value="{{ old('manual_customer_email') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </div>
        </div>
    </div>

    {{-- Produk --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-slate-800 dark:text-white">Produk</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan produk, qty, dan harga yang sudah disepakati dengan customer.</p>
            </div>
            <button type="button" onclick="addSoItem()"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-500/20 transition-colors hover:bg-blue-700">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Item
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[46rem] text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-400 dark:bg-slate-700/50 dark:text-slate-500">
                    <tr>
                        <th class="product-col px-3 py-3">Produk</th>
                        <th class="w-20 px-3 py-3">Qty</th>
                        <th class="w-32 px-3 py-3">Harga</th>
                        <th class="w-32 px-3 py-3 text-right">Subtotal</th>
                        <th class="w-10 px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody id="soItemsBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
            </table>
        </div>
    </div>

    {{-- Catatan --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Catatan</label>
        <textarea name="note" rows="3"
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ old('note') }}</textarea>
    </div>
</section>

{{-- Sidebar total --}}
<aside class="xl:sticky xl:top-24 xl:h-fit">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan</h2>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
            <div class="flex items-center justify-between gap-3 px-5 py-3">
                <span class="text-slate-500 dark:text-slate-400 shrink-0">Subtotal</span>
                <span id="soSummarySubtotal" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
            </div>

            <div class="flex items-center gap-3 px-5 py-3">
                <label for="so_discount_amount" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Diskon</label>
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                    <input id="so_discount_amount" name="discount_amount" type="number" min="0" step="1"
                        value="{{ old('discount_amount', 0) }}" oninput="recalculateSalesOrder()"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </div>
                <span id="soSummaryDiscount" class="shrink-0 w-24 text-right font-semibold text-emerald-600">- Rp 0</span>
            </div>

            <div class="flex items-center justify-between gap-3 px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                <span id="soSummaryGrandTotal" class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp 0</span>
            </div>
        </div>

        <div class="px-5 py-4">
            <button type="submit"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition-colors hover:bg-blue-700">
                <i data-lucide="save" class="h-4 w-4"></i>
                Simpan Sales Order
            </button>
        </div>
    </div>
</aside>
