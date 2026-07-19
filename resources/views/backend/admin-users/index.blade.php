@extends('layouts.app')

@section('title', 'Admin Users')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Admin Users</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage accounts that can sign in to the admin panel.</p>
            </div>
            <a href="{{ route('admin-users.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/40">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Admin User
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Account Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Role</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Permissions</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($adminUsers as $user)
                            @php
                                $isSuperAdmin = strtolower((string) $user->role) === 'admin';
                                $hasCompanyOverrides = !$isSuperAdmin && $user->companyAssignments->whereNotNull('company_id')->isNotEmpty();
                                $permissionCount = $isSuperAdmin ? 'All permissions' : ($user->adminRole ? count($user->adminRole->permissions ?? []) . ' permissions' : 'Bervariasi per perusahaan');
                            @endphp
                            <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $user->name }}</div>
                                    @if ((int) $user->id === (int) auth()->id())
                                        <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">Current account</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $user->email }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isSuperAdmin ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300' }}">
                                        {{ $isSuperAdmin ? 'Super Admin' : 'Staff' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">
                                    {{ $isSuperAdmin ? 'Full access' : ($user->adminRole?->name ?? 'Custom per perusahaan') }}
                                    @if ($hasCompanyOverrides)
                                        <span class="ml-1 text-xs text-slate-400">(+override)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $permissionCount }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin-users.edit', $user) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-blue-500/50 dark:hover:text-blue-300">
                                            Edit
                                        </a>
                                        @if (!$isSuperAdmin && (int) $user->id !== (int) auth()->id())
                                            <form action="{{ route('admin-users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this admin user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400 dark:text-slate-500">No admin users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection
