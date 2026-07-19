@extends('layouts.app')

@section('title', 'Tambah Perusahaan')

@section('content')
    @include('backend.companies.partials.form', [
        'pageTitle' => 'Tambah Perusahaan',
        'pageDescription' => 'Daftarkan perusahaan (PT) baru ke dalam sistem.',
        'formAction' => route('companies.store'),
        'isEdit' => false,
    ])
@endsection
