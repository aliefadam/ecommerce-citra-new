@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Change Password</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 mb-6">Gunakan password kuat minimal 8 karakter.</p>

            <form action="{{ route('pages.change-password.update') }}" method="POST" class="space-y-4 max-w-xl">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Current Password</label>
                    <input type="password" name="current_password" placeholder="••••••••"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                    @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">New Password</label>
                    <input type="password" name="password" placeholder="••••••••"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Update Password</button>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div id="toast" class="fixed bottom-6 right-6 z-50">
                <div class="flex items-center gap-3 bg-slate-800 dark:bg-white text-white dark:text-slate-800 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('script')
    <script>
        const toast = document.getElementById('toast');
        if (toast) setTimeout(() => toast.remove(), 3000);
    </script>
@endsection

