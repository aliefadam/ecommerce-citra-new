@extends('layouts.app')

@section('title', 'Add Admin User')

@section('content')
    @include('backend.admin-users.partials.form', [
        'pageTitle' => 'Add Admin User',
        'pageDescription' => 'Create a new account with access to the admin panel.',
        'formAction' => route('admin-users.store'),
        'isEdit' => false,
    ])
@endsection
