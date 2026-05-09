@extends('layouts.app')

@section('title', 'Coupons')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Coupons</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola voucher diskon untuk checkout.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <form method="POST" action="{{ route('coupons.store') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-4">
                @csrf
                <h2 class="font-bold text-slate-800 dark:text-white">Tambah Voucher</h2>
                <input name="code" placeholder="Kode, contoh: HEMAT10" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                <input name="name" placeholder="Nama voucher" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                <select name="type" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                    <option value="fixed">Nominal</option>
                    <option value="percent">Persen</option>
                </select>
                <input name="value" type="number" min="1" placeholder="Nilai diskon" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                <input name="max_discount" type="number" min="0" placeholder="Maks diskon (opsional)" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                <input name="min_purchase" type="number" min="0" placeholder="Minimal belanja" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                <input name="usage_limit" type="number" min="1" placeholder="Batas penggunaan (opsional)" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                <div class="grid grid-cols-2 gap-3">
                    <input name="starts_at" type="datetime-local" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                    <input name="ends_at" type="datetime-local" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm dark:text-slate-200">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
                <button class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan Voucher</button>
            </form>

            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-500">Kode</th>
                                <th class="text-left px-4 py-3 text-slate-500">Diskon</th>
                                <th class="text-left px-4 py-3 text-slate-500">Minimal</th>
                                <th class="text-left px-4 py-3 text-slate-500">Pakai</th>
                                <th class="text-left px-4 py-3 text-slate-500">Status</th>
                                <th class="text-left px-4 py-3 text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($coupons as $coupon)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $coupon->code }}
                                        <p class="text-xs font-normal text-slate-400">{{ $coupon->name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ $coupon->type === 'percent' ? $coupon->value . '%' : 'Rp ' . number_format($coupon->value, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('coupons.destroy', $coupon) }}" onsubmit="return confirm('Hapus voucher ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-12 text-slate-400">Belum ada voucher.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection
