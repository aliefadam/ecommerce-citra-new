@extends('layouts.app')

@section('title', 'Edit Admin User')

@section('content')
    @include('backend.admin-users.partials.form', [
        'pageTitle' => 'Edit Admin User',
        'pageDescription' => 'Update account details and role assignment.',
        'formAction' => route('admin-users.update', $adminUser),
        'isEdit' => true,
    ])
@endsection
