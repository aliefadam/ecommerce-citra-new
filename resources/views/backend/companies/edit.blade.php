@extends('layouts.app')

@section('title', 'Edit Perusahaan')

@section('content')
    @include('backend.companies.partials.form', [
        'pageTitle' => 'Edit Perusahaan',
        'pageDescription' => 'Perbarui data perusahaan ' . $company->name . '.',
        'formAction' => route('companies.update', $company),
        'isEdit' => true,
    ])
@endsection
