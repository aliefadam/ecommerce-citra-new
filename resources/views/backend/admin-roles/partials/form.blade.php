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
            @php
                $actions = config('admin_permissions.actions', []);
                $selected = old('permissions', $selectedPermissions);
            @endphp

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Permissions</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih akses per module dan per action.</p>
                </div>
                <label class="inline-flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                    <input type="checkbox" id="permission-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Checklist All
                </label>
            </div>

            <div class="mt-5 space-y-6">
                @foreach ($permissionGroups as $groupKey => $group)
                    @php
                        $modules = $group['modules'] ?? [];
                        $groupPermissionKeys = collect($modules)
                            ->flatMap(fn($module) => collect($module['permissions'] ?? [])->pluck('key'))
                            ->values();
                    @endphp

                    <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700" data-permission-group="{{ $groupKey }}">
                        <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/40 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                @if (!empty($group['icon']))
                                    <i data-lucide="{{ $group['icon'] }}" class="h-5 w-5 text-blue-500"></i>
                                @endif
                                <h3 class="font-bold text-slate-800 dark:text-white">{{ $group['label'] }}</h3>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <input type="checkbox" class="permission-group-check rounded border-slate-300 text-blue-600 focus:ring-blue-500" data-target-group="{{ $groupKey }}">
                                All {{ $group['label'] }}
                            </label>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-sm">
                                <thead class="bg-white dark:bg-slate-800">
                                    <tr class="border-b border-slate-100 text-left dark:border-slate-700">
                                        <th class="w-[260px] px-4 py-3 font-bold text-slate-600 dark:text-slate-300">Module</th>
                                        @foreach ($actions as $actionLabel)
                                            <th class="px-4 py-3 text-center font-bold text-slate-600 dark:text-slate-300">{{ $actionLabel }}</th>
                                        @endforeach
                                        <th class="px-4 py-3 text-center font-bold text-slate-600 dark:text-slate-300">All</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach ($modules as $moduleKey => $module)
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/20" data-permission-module="{{ $groupKey }}-{{ $moduleKey }}">
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $module['label'] }}</p>
                                                <p class="mt-1 text-xs text-slate-400">{{ count($module['permissions'] ?? []) }} permission</p>
                                            </td>
                                            @foreach ($actions as $actionKey => $actionLabel)
                                                @php $permission = $module['permissions'][$actionKey] ?? null; @endphp
                                                <td class="px-4 py-4 text-center">
                                                    @if ($permission)
                                                        <label class="inline-flex cursor-pointer items-center justify-center" title="{{ $permission['label'] }}">
                                                            <input type="checkbox" name="permissions[]" value="{{ $permission['key'] }}"
                                                                @checked(in_array($permission['key'], $selected, true))
                                                                class="permission-checkbox h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                                data-group="{{ $groupKey }}"
                                                                data-module="{{ $groupKey }}-{{ $moduleKey }}">
                                                        </label>
                                                    @else
                                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" class="permission-module-check h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" data-target-module="{{ $groupKey }}-{{ $moduleKey }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
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

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allCheck = document.getElementById('permission-all');
            const permissionChecks = () => Array.from(document.querySelectorAll('.permission-checkbox'));
            const groupChecks = () => Array.from(document.querySelectorAll('.permission-group-check'));
            const moduleChecks = () => Array.from(document.querySelectorAll('.permission-module-check'));

            function syncParents() {
                const checks = permissionChecks();
                allCheck.checked = checks.length > 0 && checks.every((check) => check.checked);
                allCheck.indeterminate = checks.some((check) => check.checked) && !allCheck.checked;

                groupChecks().forEach((groupCheck) => {
                    const groupItems = checks.filter((check) => check.dataset.group === groupCheck.dataset.targetGroup);
                    groupCheck.checked = groupItems.length > 0 && groupItems.every((check) => check.checked);
                    groupCheck.indeterminate = groupItems.some((check) => check.checked) && !groupCheck.checked;
                });

                moduleChecks().forEach((moduleCheck) => {
                    const moduleItems = checks.filter((check) => check.dataset.module === moduleCheck.dataset.targetModule);
                    moduleCheck.checked = moduleItems.length > 0 && moduleItems.every((check) => check.checked);
                    moduleCheck.indeterminate = moduleItems.some((check) => check.checked) && !moduleCheck.checked;
                });
            }

            allCheck?.addEventListener('change', function() {
                permissionChecks().forEach((check) => check.checked = allCheck.checked);
                syncParents();
            });

            groupChecks().forEach((groupCheck) => {
                groupCheck.addEventListener('change', function() {
                    permissionChecks()
                        .filter((check) => check.dataset.group === groupCheck.dataset.targetGroup)
                        .forEach((check) => check.checked = groupCheck.checked);
                    syncParents();
                });
            });

            moduleChecks().forEach((moduleCheck) => {
                moduleCheck.addEventListener('change', function() {
                    permissionChecks()
                        .filter((check) => check.dataset.module === moduleCheck.dataset.targetModule)
                        .forEach((check) => check.checked = moduleCheck.checked);
                    syncParents();
                });
            });

            permissionChecks().forEach((check) => check.addEventListener('change', syncParents));
            syncParents();
        });
    </script>
@endsection
