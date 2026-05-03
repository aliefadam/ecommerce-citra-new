@extends('layouts.app')

@section('title', 'Category Details')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Category Details</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola kategori detail.</p>
            </div>
            <a href="{{ route('category-details.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                Add Category Detail
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">#</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Main Category</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Name</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($categoryDetails as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $item->mainCategory?->name }}</td>
                            <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">{{ $item->name }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('category-details.edit', $item) }}"
                                        class="text-blue-600 hover:text-blue-700 text-xs font-semibold">Edit</a>
                                    <form action="{{ route('category-details.destroy', $item) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Hapus kategori detail ini?')"
                                            class="text-red-600 hover:text-red-700 text-xs font-semibold">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-10 text-slate-400">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
@endsection
