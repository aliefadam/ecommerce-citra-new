@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Users</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar pengguna terdaftar (kecuali admin).</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input id="userSearch" type="text" placeholder="Cari nama / email..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 w-12">#</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Nama</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Email</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Total Belanja</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Total Transaksi</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Terdaftar Pada</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="usersPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="usersPaginationButtons"></div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @php
        $userItems = $users
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'total_spent' => (int) ($u->total_spent ?? 0),
                    'transactions_count' => (int) ($u->transactions_count ?? 0),
                    'registered_at' => optional($u->created_at)->format('d M Y H:i'),
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        const userItems = @json($userItems);

        function renderUserRow(user, visibleIndex) {
            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${visibleIndex + 1}</td>
                    <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">${user.name || '-'}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${user.email || '-'}</td>
                    <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">Rp ${Number(user.total_spent || 0).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${Number(user.transactions_count || 0).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${user.registered_at || '-'}</td>
                </tr>
            `;
        }

        initAdminDataTable({
            data: userItems,
            perPage: 10,
            itemLabel: 'users',
            searchInputId: 'userSearch',
            tbodyId: 'usersTableBody',
            paginationInfoId: 'usersPaginationInfo',
            paginationButtonsId: 'usersPaginationButtons',
            searchFields: ['name', 'email'],
            renderRow: (user, index) => renderUserRow(user, index),
            emptyRowHtml: '<tr><td colspan="6" class="text-center py-12 text-slate-400 dark:text-slate-500">No users found</td></tr>',
        });
    </script>
@endsection

