@extends('layouts.app')

@section('title', 'Add Role')

@section('content')
    @include('backend.admin-roles.partials.form', [
        'pageTitle' => 'Add Role',
        'pageDescription' => 'Create a new staff role and choose its permissions.',
        'formAction' => route('admin-roles.store'),
        'isEdit' => false,
    ])
@endsection
