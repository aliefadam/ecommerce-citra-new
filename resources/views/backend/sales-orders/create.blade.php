@extends('layouts.app')

@section('title', 'Buat Sales Order')

@section('style')
    @include('backend.quotations.partials.select2-style')
@endsection

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('sales-orders.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke sales orders</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Buat Sales Order Langsung</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Untuk order yang tidak butuh Quotation/negosiasi harga formal — harga di bawah langsung dipakai apa adanya.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="salesOrderForm" method="POST" action="{{ route('sales-orders.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            @include('backend.sales-orders.partials.form-fields')
        </form>
    </main>
@endsection

@section('script')
    @include('backend.sales-orders.partials.form-script')
@endsection
