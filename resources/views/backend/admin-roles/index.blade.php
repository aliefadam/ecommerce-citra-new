@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Roles & Permissions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create staff roles and choose exactly which admin features they can access.</p>
            </div>
            <a href="{{ route('admin-roles.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/40">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Role
            </a>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.3fr,0.9fr]">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Role</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Description</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Users</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Permissions</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-500 dark:text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse ($roles as $role)
                                <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $role->name }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $role->slug }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $role->description ?: '-' }}</td>
                                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $role->users_count }}</td>
                                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ count($role->permissions ?? []) }} permissions</td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin-roles.edit', $role) }}"
                                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-blue-500/50 dark:hover:text-blue-300">
                                                Edit
                                            </a>
                                            @if (!$role->is_system)
                                                <form action="{{ route('admin-roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role?')">
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
                                    <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400 dark:text-slate-500">No roles available yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Available Permissions</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($permissionGroups as $group)
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $group['label'] }}</h3>
                            <div class="mt-3 space-y-3">
                                @foreach (($group['modules'] ?? []) as $module)
                                    <div>
                                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $module['label'] }}</p>
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            @foreach (($module['permissions'] ?? []) as $permission)
                                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">{{ $permission['label'] }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
@endsection
