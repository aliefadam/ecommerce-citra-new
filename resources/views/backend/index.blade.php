@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <main class="flex-1 p-4 sm:p-6">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Welcome back, Admin! Here's what's happening today.
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <!-- Card 1 -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg hover:shadow-slate-200/60 dark:hover:shadow-slate-900/60 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <span
                        class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 px-2.5 py-1 rounded-full">+12.5%</span>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">24,521</p>
                <p class="text-sm text-slate-400 mt-1 font-medium">Total Users</p>
                <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full w-3/4"></div>
                </div>
            </div>

            <!-- Card 2 -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg hover:shadow-slate-200/60 dark:hover:shadow-slate-900/60 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <span
                        class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 px-2.5 py-1 rounded-full">+8.2%</span>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">$48,295</p>
                <p class="text-sm text-slate-400 mt-1 font-medium">Revenue</p>
                <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full w-4/5"></div>
                </div>
            </div>

            <!-- Card 3 -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg hover:shadow-slate-200/60 dark:hover:shadow-slate-900/60 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <path d="M16 10a4 4 0 0 1-8 0" />
                        </svg>
                    </div>
                    <span
                        class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 px-2.5 py-1 rounded-full">+3.1%</span>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">1,893</p>
                <p class="text-sm text-slate-400 mt-1 font-medium">Total Orders</p>
                <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-violet-500 rounded-full w-1/2"></div>
                </div>
            </div>

            <!-- Card 4 -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg hover:shadow-slate-200/60 dark:hover:shadow-slate-900/60 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                        </svg>
                    </div>
                    <span
                        class="text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/30 dark:text-red-400 px-2.5 py-1 rounded-full">-1.4%</span>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">98.3%</p>
                <p class="text-sm text-slate-400 mt-1 font-medium">Server Uptime</p>
                <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full w-[98%]"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
            <!-- Line Chart -->
            <div
                class="xl:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-white">Revenue Overview</h2>
                        <p class="text-sm text-slate-400 mt-0.5">Monthly performance this year</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded-lg">2024</button>
                        <button
                            class="px-3 py-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">2023</button>
                    </div>
                </div>
                <canvas id="lineChart" height="120"></canvas>
            </div>

            <!-- Doughnut Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <div class="mb-5">
                    <h2 class="font-bold text-slate-800 dark:text-white">Traffic Sources</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Where visitors come from</p>
                </div>
                <canvas id="doughnutChart" height="180"></canvas>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 rounded-sm bg-blue-500 inline-block"></span><span
                                class="text-slate-600 dark:text-slate-300">Organic</span></div>
                        <span class="font-semibold text-slate-800 dark:text-white">42%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 rounded-sm bg-violet-500 inline-block"></span><span
                                class="text-slate-600 dark:text-slate-300">Social</span></div>
                        <span class="font-semibold text-slate-800 dark:text-white">28%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span><span
                                class="text-slate-600 dark:text-slate-300">Referral</span></div>
                        <span class="font-semibold text-slate-800 dark:text-white">18%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 rounded-sm bg-amber-400 inline-block"></span><span
                                class="text-slate-600 dark:text-slate-300">Direct</span></div>
                        <span class="font-semibold text-slate-800 dark:text-white">12%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 mb-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="font-bold text-slate-800 dark:text-white">Recent Transactions</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Latest 5 transactions</p>
                </div>
                <a href="{{ route('pages.datatables') }}"
                    class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700">
                            <th class="text-left pb-3 font-semibold text-slate-500 dark:text-slate-400">Customer</th>
                            <th class="text-left pb-3 font-semibold text-slate-500 dark:text-slate-400">Product</th>
                            <th
                                class="text-left pb-3 font-semibold text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                Date</th>
                            <th class="text-left pb-3 font-semibold text-slate-500 dark:text-slate-400">Amount</th>
                            <th class="text-left pb-3 font-semibold text-slate-500 dark:text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Sarah+Jones&background=3b82f6&color=fff&size=32"
                                        class="w-8 h-8 rounded-full" />
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Sarah Jones</span>
                                </div>
                            </td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400">Pro Plan</td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">Jun 14, 2024
                            </td>
                            <td class="py-3.5 pr-4 font-semibold text-slate-800 dark:text-white">$99.00</td>
                            <td class="py-3.5"><span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Paid</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Mark+Wilson&background=8b5cf6&color=fff&size=32"
                                        class="w-8 h-8 rounded-full" />
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Mark Wilson</span>
                                </div>
                            </td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400">Starter Plan</td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">Jun 13, 2024
                            </td>
                            <td class="py-3.5 pr-4 font-semibold text-slate-800 dark:text-white">$29.00</td>
                            <td class="py-3.5"><span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Pending</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Emma+Davis&background=10b981&color=fff&size=32"
                                        class="w-8 h-8 rounded-full" />
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Emma Davis</span>
                                </div>
                            </td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400">Enterprise</td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">Jun 12, 2024
                            </td>
                            <td class="py-3.5 pr-4 font-semibold text-slate-800 dark:text-white">$299.00</td>
                            <td class="py-3.5"><span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Paid</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=John+Lee&background=f59e0b&color=fff&size=32"
                                        class="w-8 h-8 rounded-full" />
                                    <span class="font-medium text-slate-800 dark:text-slate-200">John Lee</span>
                                </div>
                            </td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400">Pro Plan</td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">Jun 11, 2024
                            </td>
                            <td class="py-3.5 pr-4 font-semibold text-slate-800 dark:text-white">$99.00</td>
                            <td class="py-3.5"><span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">Failed</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Lisa+Park&background=ec4899&color=fff&size=32"
                                        class="w-8 h-8 rounded-full" />
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Lisa Park</span>
                                </div>
                            </td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400">Starter Plan</td>
                            <td class="py-3.5 pr-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">Jun 10, 2024
                            </td>
                            <td class="py-3.5 pr-4 font-semibold text-slate-800 dark:text-white">$29.00</td>
                            <td class="py-3.5"><span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Paid</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Row: Activity + Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Activity Feed -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-4">Recent Activity</h2>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="relative flex flex-col items-center">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center z-10">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <div class="flex-1 w-px bg-slate-200 dark:bg-slate-700 mt-1"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-slate-800 dark:text-white">New user <span
                                    class="text-blue-600">@mike_dev</span> registered</p>
                            <p class="text-xs text-slate-400 mt-0.5">5 minutes ago</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="relative flex flex-col items-center">
                            <div
                                class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center z-10">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <div class="flex-1 w-px bg-slate-200 dark:bg-slate-700 mt-1"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-slate-800 dark:text-white">Order <span
                                    class="text-emerald-600">#10432</span> has been completed</p>
                            <p class="text-xs text-slate-400 mt-0.5">22 minutes ago</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="relative flex flex-col items-center">
                            <div
                                class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center z-10">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 w-px bg-slate-200 dark:bg-slate-700 mt-1"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-slate-800 dark:text-white">New comment on <span
                                    class="text-violet-600">blog post</span></p>
                            <p class="text-xs text-slate-400 mt-0.5">1 hour ago</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div
                            class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-white">Server CPU usage exceeded <span
                                    class="text-amber-600">80%</span></p>
                            <p class="text-xs text-slate-400 mt-0.5">2 hours ago</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Progress -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button
                        class="flex flex-col items-center gap-2 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors text-blue-600 dark:text-blue-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span class="text-xs font-semibold">Add User</span>
                    </button>
                    <button
                        class="flex flex-col items-center gap-2 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors text-emerald-600 dark:text-emerald-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                            <line x1="3" y1="6" x2="21" y2="6" />
                        </svg>
                        <span class="text-xs font-semibold">New Order</span>
                    </button>
                    <button
                        class="flex flex-col items-center gap-2 p-4 rounded-xl bg-violet-50 dark:bg-violet-900/20 hover:bg-violet-100 dark:hover:bg-violet-900/40 transition-colors text-violet-600 dark:text-violet-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <span class="text-xs font-semibold">Report</span>
                    </button>
                    <button
                        class="flex flex-col items-center gap-2 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors text-amber-600 dark:text-amber-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                        </svg>
                        <span class="text-xs font-semibold">Settings</span>
                    </button>
                </div>
                <h3 class="font-semibold text-slate-700 dark:text-slate-300 text-sm mb-3">Project Progress</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1.5"><span
                                class="font-medium text-slate-600 dark:text-slate-300">Website Redesign</span><span
                                class="text-slate-400">78%</span></div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width:78%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1.5"><span
                                class="font-medium text-slate-600 dark:text-slate-300">Mobile App</span><span
                                class="text-slate-400">45%</span></div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-violet-500 rounded-full" style="width:45%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1.5"><span
                                class="font-medium text-slate-600 dark:text-slate-300">API Integration</span><span
                                class="text-slate-400">92%</span></div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width:92%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        // Charts
        const isDark = () => document.documentElement.classList.contains('dark');
        const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
        const textColor = () => isDark() ? '#94a3b8' : '#64748b';

        const lineCtx = document.getElementById('lineChart').getContext('2d');
        const lineGrad = lineCtx.createLinearGradient(0, 0, 0, 300);
        lineGrad.addColorStop(0, 'rgba(59,130,246,0.25)');
        lineGrad.addColorStop(1, 'rgba(59,130,246,0)');

        const lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 35000, 31000, 40000, 37000,
                        48295
                    ],
                    borderColor: '#3b82f6',
                    backgroundColor: lineGrad,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor()
                        },
                        ticks: {
                            color: textColor(),
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor()
                        },
                        ticks: {
                            color: textColor(),
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 11
                            },
                            callback: v => '$' + (v / 1000).toFixed(0) + 'k'
                        }
                    }
                }
            }
        });

        const doughnutChart = new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Organic', 'Social', 'Referral', 'Direct'],
                datasets: [{
                    data: [42, 28, 18, 12],
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endsection
