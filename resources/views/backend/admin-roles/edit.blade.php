@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
    @include('backend.admin-roles.partials.form', [
        'pageTitle' => 'Edit Role',
        'pageDescription' => 'Update the role name, description, and access rights.',
        'formAction' => route('admin-roles.update', $role),
        'isEdit' => true,
    ])
@endsection
