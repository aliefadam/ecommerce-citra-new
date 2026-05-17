@extends('layouts.app')
@section('title', 'Edit Promo Page')
@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Promo Page</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Perbarui landing page promo campaign.</p>
    </div>
    <form action="{{ route('promo-pages.update', $promoPage) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('backend.promo-pages.partials.form')
    </form>
</main>
@endsection
