<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6">
        <a href="{{ route('admin-roles.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Roles & Permissions
        </a>
        <h1 class="mt-4 text-2xl font-bold text-slate-800 dark:text-white">{{ $pageTitle }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $pageDescription }}</p>
    </div>

    <form action="{{ $formAction }}" method="POST" class="space-y-6">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Role Name</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Description</label>
                    <input type="text" name="description" value="{{ old('description', $role->description) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('description')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Permissions</h2>
            <div class="mt-4 grid gap-5 lg:grid-cols-2">
                @foreach ($permissionGroups as $group)
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $group['label'] }}</h3>
                        <div class="mt-4 space-y-3">
                            @foreach ($group['permissions'] as $permissionKey => $permissionLabel)
                                <label class="flex items-start gap-3 rounded-lg border border-slate-100 px-3 py-2.5 text-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-700 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/5">
                                    <input type="checkbox" name="permissions[]" value="{{ $permissionKey }}"
                                        @checked(in_array($permissionKey, old('permissions', $selectedPermissions), true))
                                        class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $permissionLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions')
                <p class="mt-3 text-xs text-red-500">{{ $message }}</p>
            @enderror
            @error('permissions.*')
                <p class="mt-3 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/40">
                    {{ $isEdit ? 'Save Changes' : 'Create Role' }}
                </button>
                <a href="{{ route('admin-roles.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700/60">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</main>
