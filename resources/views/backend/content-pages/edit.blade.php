@extends('layouts.app')

@section('title', 'Edit Konten')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Konten</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $contentPage->title }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('content-pages.update', $contentPage) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('backend.content-pages.partials.form')
    </form>
</main>
@endsection
