@extends('layouts.app')

@section('title', 'Product Reviews')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Product Reviews</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Moderasi ulasan customer yang tampil di halaman produk.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'all' => 'Semua',
                    'visible' => 'Tampil',
                    'hidden' => 'Disembunyikan',
                ] as $key => $label)
                    <a href="{{ route('product-reviews.index', ['status' => $key]) }}"
                        class="rounded-xl px-3 py-2 text-sm font-semibold border transition-colors {{ $status === $key ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300' }}">
                        {{ $label }} ({{ number_format($counts[$key] ?? 0) }})
                    </a>
                @endforeach
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Customer</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Produk</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Rating</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Ulasan</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Foto</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($reviews as $review)
                            @php
                                $detail = $review->transactionDetail;
                                $photos = collect((array) $review->photos)->filter()->values();
                                $photoUrl = function (string $path) {
                                    return str_starts_with($path, 'http://') ||
                                        str_starts_with($path, 'https://') ||
                                        str_starts_with($path, '//') ||
                                        str_starts_with($path, 'data:')
                                            ? $path
                                            : asset(ltrim($path, '/'));
                                };
                            @endphp
                            <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $review->is_hidden ? 'opacity-70' : '' }}">
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $review->user?->name ?? 'Customer' }}</div>
                                    <div class="text-xs text-slate-400">{{ $review->user?->email }}</div>
                                    <div class="text-xs text-slate-400 mt-1">{{ $review->created_at?->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-4 min-w-[220px]">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $detail?->product_name ?? '-' }}</div>
                                    @if ($detail?->variant_name)
                                        <div class="text-xs text-slate-500 mt-1">{{ $detail->variant_name }}</div>
                                    @endif
                                    <div class="text-xs text-slate-400 mt-1">{{ $review->transaction?->invoice_no ?? $review->transaction?->order_id }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-amber-400">{{ str_repeat('★', (int) $review->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - (int) $review->rating) }}</span></div>
                                    <div class="text-xs text-slate-500">{{ $review->rating }}/5</div>
                                </td>
                                <td class="px-4 py-4 min-w-[260px] max-w-md">
                                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">{{ $review->message ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($photos->isNotEmpty())
                                        <div class="flex flex-wrap gap-2 min-w-[120px]">
                                            @foreach ($photos as $photo)
                                                <a href="{{ $photoUrl((string) $photo) }}" target="_blank" class="block">
                                                    <img src="{{ $photoUrl((string) $photo) }}" alt="Foto ulasan"
                                                        class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($review->is_hidden)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Hidden</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Tampil</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1">
                                        <form action="{{ route('product-reviews.toggle', $review) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="{{ $review->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}">
                                                @if ($review->is_hidden)
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                @else
                                                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                                                @endif
                                            </button>
                                        </form>
                                        <form action="{{ route('product-reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Hapus ulasan ini permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">Belum ada ulasan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $reviews->links() }}
        </div>

        @if (session('success'))
            <div id="toast" class="fixed bottom-6 right-6 z-50">
                <div class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('script')
    <script>
        if (window.lucide) window.lucide.createIcons();
        const toast = document.getElementById('toast');
        if (toast) setTimeout(() => toast.remove(), 3000);
    </script>
@endsection
