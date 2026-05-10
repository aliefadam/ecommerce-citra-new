<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6">
        <a href="{{ route('admin-users.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Admin Users
        </a>
        <h1 class="mt-4 text-2xl font-bold text-slate-800 dark:text-white">{{ $pageTitle }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $pageDescription }}</p>
    </div>

    <form action="{{ $formAction }}" method="POST" class="max-w-3xl">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        @php
            $selectedType = old('account_type', strtolower((string) $adminUser->role) === 'admin' ? 'super_admin' : 'staff');
        @endphp

        <div class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Name</label>
                    <input type="text" name="name" value="{{ old('name', $adminUser->name) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Email</label>
                    <input type="email" name="email" value="{{ old('email', $adminUser->email) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Account Type</label>
                    <select name="account_type" id="account_type"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                        <option value="super_admin" @selected($selectedType === 'super_admin')>Super Admin</option>
                        <option value="staff" @selected($selectedType === 'staff')>Staff</option>
                    </select>
                    @error('account_type')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div id="role_wrap">
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Role</label>
                    <select name="admin_role_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                        <option value="">Select a role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('admin_role_id', $adminUser->admin_role_id) === (string) $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('admin_role_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Password {{ $isEdit ? '(leave blank to keep current password)' : '' }}
                    </label>
                    <input type="password" name="password"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/40">
                    {{ $isEdit ? 'Save Changes' : 'Create Admin User' }}
                </button>
                <a href="{{ route('admin-users.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700/60">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</main>

@section('script')
    @parent
    <script>
        function syncAdminRoleField() {
            const type = document.getElementById('account_type');
            const wrap = document.getElementById('role_wrap');
            if (!type || !wrap) return;
            wrap.style.display = type.value === 'staff' ? '' : 'none';
        }

        document.getElementById('account_type')?.addEventListener('change', syncAdminRoleField);
        syncAdminRoleField();
    </script>
@endsection
