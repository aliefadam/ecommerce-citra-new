@extends('layouts.app')

@section('title', 'Create Membership Tier')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Create Membership Tier</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Atur threshold spending untuk level member customer.</p>
        </div>

        <form action="{{ route('member-tiers.store') }}" method="POST"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-6">
            @csrf

            @include('backend.member-tiers.partials.form')

            <div class="flex justify-end gap-3">
                <a href="{{ route('member-tiers.index') }}"
                    class="px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                    Save Tier
                </button>
            </div>
        </form>
    </main>
@endsection
