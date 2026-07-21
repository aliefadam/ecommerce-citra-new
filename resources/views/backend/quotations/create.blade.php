@extends('layouts.app')

@section('title', 'Buat Quotation')

@section('style')
    @include('backend.quotations.partials.select2-style')
@endsection

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('quotations.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke quotations</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Buat Quotation</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Penawaran harga untuk customer B2B.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="quotationForm" method="POST" action="{{ route('quotations.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            @include('backend.quotations.partials.form-fields')
        </form>
    </main>
@endsection

@section('script')
    @include('backend.quotations.partials.form-script', ['seedItems' => [], 'seedCustomer' => null])
@endsection
