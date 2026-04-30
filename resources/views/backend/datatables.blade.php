@extends('layouts.app')

@section('title', 'Data Tables')

@section('content')
    <main class="flex-1 p-4 sm:p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Data Tables</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage and browse all records with search, filter
                    & pagination.</p>
            </div>
            <button onclick="openModal()"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Add New User
            </button>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Table Toolbar -->
            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input id="tableSearch" type="text" placeholder="Search users..." oninput="filterTable()"
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
                <div class="flex gap-2">
                    <select id="statusFilter" onchange="filterTable()"
                        class="text-sm bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Pending">Pending</option>
                    </select>
                    <select id="roleFilter" onchange="filterTable()"
                        class="text-sm bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                        <option value="">All Roles</option>
                        <option value="Admin">Admin</option>
                        <option value="Editor">Editor</option>
                        <option value="Viewer">Viewer</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 w-8">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"
                                    class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
                            </th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortTable(1)">Name <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell"
                                onclick="sortTable(2)">Email <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 hidden sm:table-cell"
                                onclick="sortTable(3)">Role <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortTable(4)">Status <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell"
                                onclick="sortTable(5)">Joined <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="paginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="paginationButtons"></div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 id="modalTitle" class="font-bold text-lg text-slate-800 dark:text-white">Add New User</h2>
                <button onclick="closeModal()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full Name</label>
                    <input id="inputName" type="text" placeholder="John Doe"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email
                        Address</label>
                    <input id="inputEmail" type="email" placeholder="john@example.com"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Role</label>
                    <select id="inputRole"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                        <option>Admin</option>
                        <option>Editor</option>
                        <option>Viewer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                    <select id="inputStatus"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Pending</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 p-6 pt-0">
                <button onclick="closeModal()"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                <button onclick="saveUser()"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Save
                    User</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-200 dark:border-slate-700 p-6 text-center">
            <div
                class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                </svg>
            </div>
            <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-2">Delete User?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">This action cannot be undone. Are you sure you want
                to delete this user?</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                <button onclick="confirmDelete()"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors">Delete</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
        <div
            class="flex items-center gap-3 bg-slate-800 dark:bg-white text-white dark:text-slate-800 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <span id="toastMsg">Success!</span>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const avatarColors = ['3b82f6', '8b5cf6', '10b981', 'f59e0b', 'ec4899', 'ef4444', '06b6d4', '84cc16'];
        let users = [{
            id: 1,
            name: 'Sarah Johnson',
            email: 'sarah@example.com',
            role: 'Admin',
            status: 'Active',
            joined: 'Jan 12, 2024'
        }, {
            id: 2,
            name: 'Mark Wilson',
            email: 'mark@example.com',
            role: 'Editor',
            status: 'Active',
            joined: 'Feb 3, 2024'
        }, {
            id: 3,
            name: 'Emma Davis',
            email: 'emma@example.com',
            role: 'Viewer',
            status: 'Inactive',
            joined: 'Feb 18, 2024'
        }, {
            id: 4,
            name: 'John Lee',
            email: 'john@example.com',
            role: 'Editor',
            status: 'Pending',
            joined: 'Mar 5, 2024'
        }, {
            id: 5,
            name: 'Lisa Park',
            email: 'lisa@example.com',
            role: 'Admin',
            status: 'Active',
            joined: 'Mar 22, 2024'
        }, {
            id: 6,
            name: 'Tom Brown',
            email: 'tom@example.com',
            role: 'Viewer',
            status: 'Active',
            joined: 'Apr 1, 2024'
        }, {
            id: 7,
            name: 'Nina Patel',
            email: 'nina@example.com',
            role: 'Editor',
            status: 'Inactive',
            joined: 'Apr 15, 2024'
        }, {
            id: 8,
            name: 'Carlos M.',
            email: 'carlos@example.com',
            role: 'Viewer',
            status: 'Active',
            joined: 'May 2, 2024'
        }, {
            id: 9,
            name: 'Diana Chen',
            email: 'diana@example.com',
            role: 'Admin',
            status: 'Active',
            joined: 'May 17, 2024'
        }, {
            id: 10,
            name: 'Kevin O.',
            email: 'kevin@example.com',
            role: 'Editor',
            status: 'Pending',
            joined: 'Jun 1, 2024'
        }, {
            id: 11,
            name: 'Olivia R.',
            email: 'olivia@example.com',
            role: 'Viewer',
            status: 'Active',
            joined: 'Jun 5, 2024'
        }, {
            id: 12,
            name: 'Samuel T.',
            email: 'samuel@example.com',
            role: 'Editor',
            status: 'Inactive',
            joined: 'Jun 9, 2024'
        }];

        let tableApi = null;
        let editingId = null;
        let deletingId = null;

        function getAvatarColor(name) {
            let h = 0;
            for (let c of name) h += c.charCodeAt(0);
            return avatarColors[h % avatarColors.length];
        }

        function statusBadge(s) {
            if (s === 'Active')
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Active</span>';
            if (s === 'Inactive')
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Inactive</span>';
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Pending</span>';
        }

        function roleBadge(r) {
            if (r === 'Admin')
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">Admin</span>';
            if (r === 'Editor')
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400">Editor</span>';
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Viewer</span>';
        }

        function buildUserRow(u) {
            return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" data-id="${u.id}">
          <td class="px-4 py-3.5"><input type="checkbox" class="row-check rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500"/></td>
          <td class="px-4 py-3.5">
            <div class="flex items-center gap-3">
              <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=${getAvatarColor(u.name)}&color=fff&size=64" class="w-8 h-8 rounded-full flex-shrink-0"/>
              <span class="font-medium text-slate-800 dark:text-slate-200">${u.name}</span>
            </div>
          </td>
          <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">${u.email}</td>
          <td class="px-4 py-3.5 hidden sm:table-cell">${roleBadge(u.role)}</td>
          <td class="px-4 py-3.5">${statusBadge(u.status)}</td>
          <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden lg:table-cell">${u.joined}</td>
          <td class="px-4 py-3.5">
            <div class="flex items-center gap-1">
              <button onclick="editUser(${u.id})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button onclick="deleteUser(${u.id})" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </div>
          </td>
        </tr>
      `;
        }

        function filterTable() {
            tableApi?.refresh(true);
        }

        function sortTable(col) {
            tableApi?.sortBy(col);
        }

        function toggleSelectAll() {
            const all = document.getElementById('selectAll').checked;
            document.querySelectorAll('.row-check').forEach(c => c.checked = all);
        }

        function openModal(id = null) {
            editingId = id;
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modal').classList.add('flex');
            if (id) {
                const u = users.find(x => x.id === id);
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('inputName').value = u.name;
                document.getElementById('inputEmail').value = u.email;
                document.getElementById('inputRole').value = u.role;
                document.getElementById('inputStatus').value = u.status;
            } else {
                document.getElementById('modalTitle').textContent = 'Add New User';
                document.getElementById('inputName').value = '';
                document.getElementById('inputEmail').value = '';
                document.getElementById('inputRole').value = 'Viewer';
                document.getElementById('inputStatus').value = 'Active';
            }
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
            document.getElementById('modal').classList.remove('flex');
        }

        function saveUser() {
            const name = document.getElementById('inputName').value.trim();
            const email = document.getElementById('inputEmail').value.trim();
            const role = document.getElementById('inputRole').value;
            const status = document.getElementById('inputStatus').value;
            if (!name || !email) {
                alert('Please fill all fields.');
                return;
            }
            if (editingId) {
                const u = users.find(x => x.id === editingId);
                Object.assign(u, {
                    name,
                    email,
                    role,
                    status
                });
            } else {
                users.unshift({
                    id: Date.now(),
                    name,
                    email,
                    role,
                    status,
                    joined: new Date().toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    })
                });
            }
            tableApi.setData(users);
            closeModal();
            showToast(editingId ? 'User updated!' : 'User added!');
        }

        function editUser(id) {
            openModal(id);
        }

        function deleteUser(id) {
            deletingId = id;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        function confirmDelete() {
            users = users.filter(u => u.id !== deletingId);
            tableApi.setData(users);
            closeDeleteModal();
            showToast('User deleted!');
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        tableApi = initAdminDataTable({
            data: users,
            perPage: 7,
            itemLabel: 'users',
            searchInputId: 'tableSearch',
            tbodyId: 'tableBody',
            paginationInfoId: 'paginationInfo',
            paginationButtonsId: 'paginationButtons',
            searchFields: ['name', 'email'],
            filters: [{
                elementId: 'statusFilter',
                field: 'status'
            }, {
                elementId: 'roleFilter',
                field: 'role'
            }],
            sortMap: {
                1: (u) => u.name,
                2: (u) => u.email,
                3: (u) => u.role,
                4: (u) => u.status,
                5: (u) => u.joined,
            },
            renderRow: (user) => buildUserRow(user),
            emptyRowHtml: '<tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-slate-500">No records found</td></tr>',
            onAfterRender: () => {
                document.getElementById('selectAll').checked = false;
            },
        });
    </script>
@endsection
