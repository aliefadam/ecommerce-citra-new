@extends('layouts.app')

@section('title', 'Edit Quotation')

@section('style')
    @include('backend.quotations.partials.select2-style')
@endsection

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('quotations.show', $quotation) }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke detail quotation</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Edit Quotation {{ $quotation->quotation_no }}</h1>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="quotationForm" method="POST" action="{{ route('quotations.update', $quotation) }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            @method('PUT')
            @include('backend.quotations.partials.form-fields', ['quotation' => $quotation])
        </form>
    </main>
@endsection

@section('script')
    @php
        $seedItems = $quotation->details->map(fn ($detail) => [
            'product_variant_id' => $detail->product_variant_id,
            'text' => $detail->product_name . ($detail->variant_name ? ' - ' . $detail->variant_name : ''),
            'product_name' => $detail->product_name,
            'sku' => $detail->sku,
            'qty' => $detail->quantity,
            'price' => $detail->price,
            'note' => $detail->item_note,
        ])->values();

        $seedCustomer = $quotation->user ? [
            'id' => $quotation->user->id,
            'text' => $quotation->user->name . ' — ' . $quotation->user->email,
        ] : null;
    @endphp
    @include('backend.quotations.partials.form-script', ['seedItems' => $seedItems, 'seedCustomer' => $seedCustomer])
@endsection
