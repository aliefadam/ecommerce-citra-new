@extends('layouts.app')

@section('title', 'Banners')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Banner Management</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola banner homepage frontend.</p>
            </div>
            <a href="{{ route('banners.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Add Banner
            </a>
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
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">#</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Preview</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Title</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Target URL</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Urutan</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($banners as $banner)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3.5 text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $image = (string) $banner->image;
                                        $imageUrl =
                                            str_starts_with($image, 'http://') ||
                                            str_starts_with($image, 'https://') ||
                                            str_starts_with($image, '//') ||
                                            str_starts_with($image, 'data:')
                                                ? $image
                                                : asset('storage/' . ltrim($image, '/'));
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $banner->title ?? 'Banner' }}"
                                        class="w-24 h-12 object-cover rounded-lg border border-slate-200" />
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">{{ $banner->title ?: '-' }}</td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $banner->target_url ?: '-' }}</td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $banner->sort_order }}</td>
                                <td class="px-4 py-3.5">
                                    @if ($banner->is_active)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Active</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex gap-1">
                                        <a href="{{ route('banners.edit', $banner) }}" class="h-fit p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <form action="{{ route('banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Hapus banner ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">Belum ada data banner</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (session('success'))
            <div id="toast" class="fixed bottom-6 right-6 z-50">
                <div class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('script')
    <script>
        const toast = document.getElementById('toast');
        if (toast) setTimeout(() => toast.remove(), 3000);
    </script>
@endsection

