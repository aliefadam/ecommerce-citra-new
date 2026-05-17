@extends('layouts.app')

@section('title', 'Konten Website')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Konten Website</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola halaman statis dan artikel blog dari admin.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('content-pages.create', ['type' => 'page']) }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition-colors">Tambah Halaman</a>
            <a href="{{ route('content-pages.create', ['type' => 'post']) }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">Tambah Blog</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('content-pages.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ empty($type) ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Semua</a>
        <a href="{{ route('content-pages.index', ['type' => 'page']) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $type === 'page' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Halaman</a>
        <a href="{{ route('content-pages.index', ['type' => 'post']) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $type === 'post' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Blog</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Judul</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Tipe</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">URL</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Publikasi</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Status</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($contents as $content)
                        <tr>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $content->title }}</div>
                                @if ($content->excerpt)
                                    <div class="mt-0.5 max-w-md truncate text-xs text-slate-400">{{ $content->excerpt }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $content->type === 'post' ? 'bg-violet-50 text-violet-700' : 'bg-blue-50 text-blue-700' }}">
                                    {{ $content->type === 'post' ? 'Blog' : 'Halaman' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500">{{ $content->type === 'post' ? '/blog/' : '/pages/' }}{{ $content->slug }}</td>
                            <td class="px-4 py-3.5 text-slate-500">{{ optional($content->published_at)->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $content->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $content->is_active ? 'Active' : 'Draft' }}</span></td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex gap-2">
                                    @if ($content->is_active)
                                        <a href="{{ $content->public_url }}" target="_blank" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat</a>
                                    @endif
                                    <a href="{{ route('content-pages.edit', $content) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-100">Edit</a>
                                    <form action="{{ route('content-pages.destroy', $content) }}" method="POST" onsubmit="return confirm('Hapus konten ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada konten.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $contents->links() }}</div>
</main>
@endsection
