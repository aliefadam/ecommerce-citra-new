@extends('layouts.app')

@section('title', 'Create Main Category')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form action="{{ route('main-categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('main-categories.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl">Save</button>
                </div>
            </form>
        </div>
    </main>
@endsection
