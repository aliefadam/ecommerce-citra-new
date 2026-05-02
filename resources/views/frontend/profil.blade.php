@extends('layouts.user')

@section('title', 'Profil - Ecommerce Citra')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #2563eb;
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .search-dropdown {
            display: none;
        }

        .search-wrapper:focus-within .search-dropdown {
            display: block;
        }

        .sidebar-item {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-item.active {
            background: #eff6ff;
            color: #1d4ed8;
            border-left-color: #2563eb;
        }

        .sidebar-item:hover:not(.active) {
            background: #f8fafc;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .toast {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .status-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-selesai {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-proses {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-kirim {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-batal {
            background: #fee2e2;
            color: #dc2626;
        }

        .avatar-ring {
            background: conic-gradient(#2563eb, #0ea5e9, #8b5cf6, #ec4899, #2563eb);
            padding: 3px;
            border-radius: 9999px;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        .strength-bar {
            height: 4px;
            border-radius: 9999px;
            transition: all 0.3s;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e2e8f0;
            border-radius: 24px;
            transition: 0.3s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: 0.3s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        input:checked+.slider {
            background-color: #2563eb;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }
    </style>
@endsection

@section('content')
    @php
        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $displayName = $fullName !== '' ? $fullName : $user->name ?? 'User';
        $avatarLetter = strtoupper(substr($displayName, 0, 1));
    @endphp
    <!-- Toast -->
    <div id="toast" class="fixed top-4 right-4 z-[9999] hidden">
        <div class="toast bg-blue-500 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span id="toast-msg">Berhasil!</span>
        </div>
    </div>

    <!-- NAVBAR -->
    @include('partials.navbar-user')

    <!-- BREADCRUMB -->
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-800 font-medium">Profil Saya</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- SIDEBAR -->
            <aside class="lg:w-72 flex-shrink-0">
                <!-- Profile Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="avatar-ring mb-3">
                            <div
                                class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-3xl font-extrabold ring-4 ring-white">
                                {{ $avatarLetter }}</div>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg">{{ $displayName }}</h3>
                        <p class="text-slate-500 text-sm">{{ $user->email }}</p>
                        <div class="flex items-center gap-1 mt-2">
                            <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                            <span class="text-xs text-blue-600 font-medium">Akun Terverifikasi</span>
                        </div>
                        <div class="flex gap-4 mt-4 w-full border-t border-slate-100 pt-4">
                            <div class="flex-1 text-center">
                                <p class="font-bold text-slate-800 text-xl">28</p>
                                <p class="text-xs text-slate-500">Pesanan</p>
                            </div>
                            <div class="w-px bg-slate-100"></div>
                            <div class="flex-1 text-center">
                                <p class="font-bold text-slate-800 text-xl">12</p>
                                <p class="text-xs text-slate-500">Ulasan</p>
                            </div>
                            <div class="w-px bg-slate-100"></div>
                            <div class="flex-1 text-center">
                                <p class="font-bold text-slate-800 text-xl">45</p>
                                <p class="text-xs text-slate-500">Wishlist</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level Badge -->
                <div class="bg-gradient-to-r from-amber-400 to-orange-500 rounded-2xl p-4 mb-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="text-xs font-medium text-amber-100">Level Member</p>
                            <p class="font-bold text-lg">Gold Member</p>
                        </div>
                        {{-- <div class="text-3xl">VIP</div> --}}
                    </div>
                    <div class="bg-white/20 rounded-full h-2 mb-1">
                        <div class="bg-white rounded-full h-2" style="width:65%"></div>
                    </div>
                    <p class="text-xs text-amber-100">6.500 / 10.000 poin menuju Platinum</p>
                </div>

                <!-- Navigation Menu -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="py-1">
                        <button onclick="showTab('biodata')" id="nav-biodata"
                            class="sidebar-item active w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Biodata Diri
                        </button>
                        <button onclick="showTab('keamanan')" id="nav-keamanan"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Password & Keamanan
                        </button>
                        <button onclick="showTab('alamat')" id="nav-alamat"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            Alamat Saya
                        </button>
                        <button onclick="showTab('pesanan')" id="nav-pesanan"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Riwayat Pesanan
                            <span
                                class="ml-auto bg-blue-100 text-blue-600 text-xs font-bold px-2 py-0.5 rounded-full">3</span>
                        </button>
                        <button onclick="showTab('wishlist')" id="nav-wishlist"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            Wishlist
                        </button>
                        <button onclick="showTab('notif')" id="nav-notif"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notifikasi
                        </button>
                        <div class="border-t border-slate-100 mt-1">
                            <button onclick="confirmLogout()"
                                class="w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Keluar
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="flex-1">

                <!-- ============ BIODATA ============ -->
                <div id="tab-biodata" class="tab-content active">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div
                            class="px-6 py-5 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-bold text-slate-800 text-lg">Biodata Diri</h2>
                                <p class="text-slate-500 text-sm mt-0.5">Kelola informasi profil kamu</p>
                            </div>
                            <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">?</span>
                        </div>
                        <div class="p-6">
                            <!-- Avatar Section -->
                            <div class="flex items-center gap-5 mb-8 pb-6 border-b border-slate-100">
                                <div class="relative">
                                    <div
                                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                                        A</div>
                                    <button
                                        class="absolute -bottom-2 -right-2 w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-md hover:bg-blue-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 mb-1">Foto Profil</p>
                                    <p class="text-sm text-slate-500 mb-2">Format: JPG, PNG. Ukuran maks 2MB</p>
                                    <div class="flex gap-2">
                                        <button
                                            class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium px-4 py-1.5 rounded-lg border border-blue-200 transition-colors">Ganti
                                            Foto</button>
                                        <button
                                            class="text-red-400 hover:text-red-500 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Biodata -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nama Depan</label>
                                    <input id="firstName" type="text" value="Andi"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nama Belakang</label>
                                    <input id="lastName" type="text" value="Pratama"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Username</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">@</span>
                                        <input id="username" type="text" value="andi.pratama"
                                            class="w-full border border-slate-200 rounded-xl pl-8 pr-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Jenis Kelamin</label>
                                    <div class="flex gap-3">
                                        <label
                                            class="flex items-center gap-2 flex-1 p-3 border-2 border-blue-400 bg-blue-50 rounded-xl cursor-pointer">
                                            <input type="radio" name="gender" value="male" class="accent-blue-500"
                                                checked /> <span class="text-sm font-medium text-slate-700">Pria</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 flex-1 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-slate-300">
                                            <input type="radio" name="gender" value="female"
                                                class="accent-blue-500" /> <span
                                                class="text-sm font-medium text-slate-700">Wanita</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Email</label>
                                    <div class="relative">
                                        <input id="email" type="email" value="{{ $user->email }}"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all pr-12" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-500"
                                            title="Terverifikasi">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nomor Telepon</label>
                                    <div class="flex">
                                        <select id="phoneCode"
                                            class="border border-r-0 border-slate-200 rounded-l-xl px-3 py-3 text-sm bg-slate-50 outline-none">
                                            <option>+62</option>
                                        </select>
                                        <input id="phoneNumber" type="tel" value="812-3456-7890"
                                            class="flex-1 border border-slate-200 rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Tanggal Lahir</label>
                                    <input id="birthDate" type="date" value="1995-07-15"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Website / Media
                                        Sosial</label>
                                    <input id="socialUrl" type="url" placeholder="https://instagram.com/..."
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Bio</label>
                                    <textarea id="bio" placeholder="Ceritakan sedikit tentang diri kamu..."
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all resize-none h-20">Suka belanja online, terutama fashion dan elektronik ?</textarea>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100">
                                <button
                                    class="border border-slate-200 text-slate-600 font-semibold px-6 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-sm">Batal</button>
                                <button onclick="saveProfile()"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ KEAMANAN ============ -->
                <div id="tab-keamanan" class="tab-content">
                    <div class="space-y-5">
                        <!-- Ubah Password -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="px-6 py-5 border-b border-slate-100">
                                <h2 class="font-bold text-slate-800 text-lg">Ubah Password</h2>
                                <p class="text-slate-500 text-sm mt-0.5">Pastikan password kamu kuat dan unik</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Password Saat
                                        Ini</label>
                                    <div class="relative">
                                        <input type="password" id="currPwd" placeholder="Masukkan password saat ini"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 pr-12" />
                                        <button onclick="togglePwd('currPwd')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Password Baru</label>
                                    <div class="relative">
                                        <input type="password" id="newPwd" placeholder="Min. 8 karakter"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 pr-12"
                                            oninput="checkStrength(this.value)" />
                                        <button onclick="togglePwd('newPwd')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Strength Meter -->
                                    <div class="mt-2">
                                        <div class="flex gap-1">
                                            <div id="s1" class="strength-bar flex-1 bg-slate-200"></div>
                                            <div id="s2" class="strength-bar flex-1 bg-slate-200"></div>
                                            <div id="s3" class="strength-bar flex-1 bg-slate-200"></div>
                                            <div id="s4" class="strength-bar flex-1 bg-slate-200"></div>
                                        </div>
                                        <p id="strengthText" class="text-xs text-slate-400 mt-1">Masukkan password untuk
                                            melihat kekuatan</p>
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        <div id="req-len" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Min. 8 karakter</div>
                                        <div id="req-upper" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Huruf kapital</div>
                                        <div id="req-num" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Angka</div>
                                        <div id="req-sym" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Simbol (!@#$...)</div>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Konfirmasi Password
                                        Baru</label>
                                    <div class="relative">
                                        <input type="password" id="confPwd" placeholder="Ulangi password baru"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 pr-12" />
                                        <button onclick="togglePwd('confPwd')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button onclick="changePassword()"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm">Ubah
                                        Password</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ ALAMAT ============ -->
                <div id="tab-alamat" class="tab-content">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div
                            class="px-6 py-5 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-bold text-slate-800 text-lg">Alamat Saya</h2>
                                <p class="text-slate-500 text-sm mt-0.5">Kelola alamat pengiriman kamu</p>
                            </div>
                            <button onclick="showNewAddressForm()"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Alamat
                            </button>
                        </div>
                        <div class="p-6 space-y-4" id="addressCards">
                            @forelse ($addresses as $address)
                                @if ($address->is_primary)
                                    <div class="border-2 border-blue-400 bg-blue-50 rounded-2xl p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ $address->label }}</span>
                                                <span class="bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">Utama</span>
                                            </div>
                                            <div class="flex gap-2">
                                                <button class="text-xs text-blue-600 hover:text-blue-700 font-semibold border border-blue-300 px-3 py-1 rounded-lg hover:bg-blue-100 transition-colors">Ubah</button>
                                                <button class="text-xs text-red-400 hover:text-red-500 font-semibold border border-red-200 px-3 py-1 rounded-lg hover:bg-red-50 transition-colors">Hapus</button>
                                            </div>
                                        </div>
                                        <p class="font-semibold text-slate-800 mb-0.5">{{ $address->recipient_name }}</p>
                                        <p class="text-sm text-slate-600 mb-0.5">{{ $address->phone_country_code }} {{ $address->phone_number }}</p>
                                        <p class="text-sm text-slate-600">{{ $address->address_line }}, {{ $address->city }}, {{ $address->province }}{{ $address->postal_code ? ' ' . $address->postal_code : '' }}</p>
                                    </div>
                                @else
                                    <div class="border border-slate-200 rounded-2xl p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2.5 py-1 rounded-full">{{ $address->label }}</span>
                                            <div class="flex gap-2">
                                                <button onclick="setMainAddress(this)" data-id="{{ $address->id }}"
                                                    class="text-xs text-blue-600 hover:text-blue-700 font-semibold border border-blue-300 px-3 py-1 rounded-lg hover:bg-blue-50 transition-colors">Jadikan Utama</button>
                                                <button class="text-xs text-slate-500 hover:text-slate-700 font-semibold border border-slate-200 px-3 py-1 rounded-lg hover:bg-slate-50 transition-colors">Ubah</button>
                                                <button class="text-xs text-red-400 hover:text-red-500 font-semibold border border-red-200 px-3 py-1 rounded-lg hover:bg-red-50 transition-colors">Hapus</button>
                                            </div>
                                        </div>
                                        <p class="font-semibold text-slate-800 mb-0.5">{{ $address->recipient_name }}</p>
                                        <p class="text-sm text-slate-600 mb-0.5">{{ $address->phone_country_code }} {{ $address->phone_number }}</p>
                                        <p class="text-sm text-slate-600">{{ $address->address_line }}, {{ $address->city }}, {{ $address->province }}{{ $address->postal_code ? ' ' . $address->postal_code : '' }}</p>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-12">
                                    <div class="text-5xl mb-3">📍</div>
                                    <p class="text-slate-500 font-medium">Belum ada alamat tersimpan</p>
                                    <p class="text-slate-400 text-sm mt-1">Tambahkan alamat pengiriman kamu</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- New Address Form (hidden by default) -->
                        <div id="newAddressForm" class="hidden px-6 pb-6">
                            <div class="border-2 border-dashed border-blue-300 rounded-2xl p-5 bg-blue-50">
                                <h3 class="font-bold text-slate-800 mb-4">Tambah Alamat Baru</h3>
                                <input data-address-field="label" type="hidden" value="Rumah" />
                                <input data-address-field="phone_country_code" type="hidden" value="+62" />
                                <input data-address-field="is_primary" type="hidden" value="0" />
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Nama Penerima
                                            *</label>
                                        <input data-address-field="recipient_name" type="text"
                                            placeholder="Nama lengkap"
                                            class="w-full border border-slate-200 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400" />
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">No. Telepon *</label>
                                        <input data-address-field="phone_number" type="text"
                                            placeholder="08xx-xxxx-xxxx"
                                            class="w-full border border-slate-200 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400" />
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Provinsi *</label>
                                        <select data-address-field="province"
                                            class="w-full border border-slate-200 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                                            <option>Pilih Provinsi</option>
                                            <option>DKI Jakarta</option>
                                            <option>Jawa Barat</option>
                                            <option>Jawa Timur</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kota/Kabupaten
                                            *</label>
                                        <input data-address-field="city" type="text" placeholder="Kota"
                                            class="w-full border border-slate-200 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Alamat Lengkap
                                            *</label>
                                        <textarea data-address-field="address_line" placeholder="Nama jalan, nomor, RT/RW, kelurahan..."
                                            class="w-full border border-slate-200 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 resize-none h-16"></textarea>
                                    </div>
                                </div>
                                <div class="flex gap-3 mt-4">
                                    <button onclick="hideNewAddressForm()"
                                        class="flex-1 border border-slate-200 text-slate-600 text-sm font-semibold py-2.5 rounded-xl bg-white hover:bg-slate-50 transition-colors">Batal</button>
                                    <button type="button" onclick="saveNewAddress()"
                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan
                                        Alamat</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ PESANAN ============ -->
                <div id="tab-pesanan" class="tab-content">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100">
                            <h2 class="font-bold text-slate-800 text-lg">Riwayat Pesanan</h2>
                        </div>
                        <!-- Order Status Tabs -->
                        <div class="flex overflow-x-auto border-b border-slate-100">
                            <button onclick="filterOrder('semua')"
                                class="order-tab px-5 py-3 text-sm font-semibold text-blue-600 border-b-2 border-blue-500 whitespace-nowrap">Semua</button>
                            <button onclick="filterOrder('proses')"
                                class="order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700">Diproses</button>
                            <button onclick="filterOrder('kirim')"
                                class="order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700">Dikirim</button>
                            <button onclick="filterOrder('selesai')"
                                class="order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700">Selesai</button>
                            <button onclick="filterOrder('batal')"
                                class="order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700">Dibatalkan</button>
                        </div>
                        <div class="p-6 space-y-4" id="orderList">
                        </div>
                    </div>
                </div>

                <!-- ============ WISHLIST ============ -->
                <div id="tab-wishlist" class="tab-content">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div
                            class="px-6 py-5 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="font-bold text-slate-800 text-lg">Wishlist Saya (45)</h2>
                            <button class="text-sm text-blue-600 font-medium hover:text-blue-700">Bagikan Wishlist</button>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="wishlistGrid"></div>
                        </div>
                    </div>
                </div>

                <!-- ============ NOTIFIKASI ============ -->
                <div id="tab-notif" class="tab-content">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div
                            class="px-6 py-5 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="font-bold text-slate-800 text-lg">Notifikasi</h2>
                            <button class="text-sm text-blue-600 font-medium hover:text-blue-700">Tandai semua
                                dibaca</button>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <div class="p-5 flex gap-4 bg-blue-50">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 text-xl">
                                    ?</div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 text-sm mb-0.5">Pesanan Dikirim!</p>
                                    <p class="text-sm text-slate-600">Pesanan #TK-2025-ABCD123 sedang dalam perjalanan.
                                        Estimasi tiba 20 Jan 2025.</p>
                                    <p class="text-xs text-slate-400 mt-1">5 menit lalu</p>
                                </div>
                                <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></span>
                            </div>
                            <div class="p-5 flex gap-4 bg-blue-50">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 text-xl">
                                    ?</div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 text-sm mb-0.5">Voucher Baru Tersedia!</p>
                                    <p class="text-sm text-slate-600">Dapatkan diskon Rp 75.000 untuk pembelian pertama
                                        bulan ini. Gunakan kode: HEMAT75</p>
                                    <p class="text-xs text-slate-400 mt-1">1 jam lalu</p>
                                </div>
                                <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></span>
                            </div>
                            <div class="p-5 flex gap-4">
                                <div
                                    class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0 text-xl">
                                    ?</div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 text-sm mb-0.5">Beri Ulasan Produk</p>
                                    <p class="text-sm text-slate-600">Bagaimana pengalamanmu dengan Kemeja Oxford Slim Fit?
                                        Berikan ulasan dan dapatkan poin.</p>
                                    <p class="text-xs text-slate-400 mt-1">Kemarin, 15:30</p>
                                </div>
                            </div>
                            <div class="p-5 flex gap-4">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 text-xl">
                                    ?</div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 text-sm mb-0.5">Pesanan Selesai</p>
                                    <p class="text-sm text-slate-600">Pesanan #TK-2025-XYZ789 telah selesai. Terima kasih
                                        sudah berbelanja di Ecommerce Citra!</p>
                                    <p class="text-xs text-slate-400 mt-1">3 hari lalu</p>
                                </div>
                            </div>
                            <div class="p-5 flex gap-4">
                                <div
                                    class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 text-xl">
                                    ?</div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 text-sm mb-0.5">Flash Sale Dimulai!</p>
                                    <p class="text-sm text-slate-600">Diskon hingga 70% untuk ribuan produk pilihan. Jangan
                                        sampai kehabisan!</p>
                                    <p class="text-xs text-slate-400 mt-1">5 hari lalu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-8 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                    </svg>
                </div>
                <span class="text-white font-bold">Ecommerce Citra</span>
            </div>
            <p class="text-sm">?</p>
            <div class="flex flex-wrap justify-center gap-4 text-sm">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-400">Beranda</a>
                <a href="{{ route('frontend.kategori') }}" class="hover:text-blue-400">Kategori</a>
                <a href="{{ route('frontend.checkout') }}" class="hover:text-blue-400">Checkout</a>
            </div>
        </div>
    </footer>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center">
            <div class="text-5xl mb-4">?</div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Keluar Akun?</h3>
            <p class="text-slate-500 text-sm mb-6">Kamu akan keluar dari akun Ecommerce Citra. Sampai jumpa lagi!</p>
            <div class="flex gap-3">
                <button onclick="closeLogout()"
                    class="flex-1 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                <a href="{{ route('frontend.index') }}"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl transition-colors">Keluar</a>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @php
        $profileUserPayload = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'gender' => $user->gender,
            'email' => $user->email,
            'phone_country_code' => $user->phone_country_code,
            'phone_number' => $user->phone_number,
            'birth_date' => optional($user->birth_date)->format('Y-m-d'),
            'social_url' => $user->social_url,
            'bio' => $user->bio,
        ];
        $profileAddressesPayload = $addresses
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'label' => $a->label,
                    'recipient_name' => $a->recipient_name,
                    'phone_country_code' => $a->phone_country_code,
                    'phone_number' => $a->phone_number,
                    'province' => $a->province,
                    'city' => $a->city,
                    'postal_code' => $a->postal_code,
                    'address_line' => $a->address_line,
                    'is_primary' => (bool) $a->is_primary,
                ];
            })
            ->values();
    @endphp
    <script>
        const profileUser = @json($profileUserPayload);
        const profileAddresses = @json($profileAddressesPayload);
        const csrfToken = @json(csrf_token());

        const orders = [{
                id: '#TK-2025-ABCD123',
                date: '18 Jan 2025',
                status: 'kirim',
                items: ['Kemeja Oxford Slim Fit', 'Sneakers Urban Street'],
                total: 648000,
                img: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=80&h=80&fit=crop'
            },
            {
                id: '#TK-2025-EFGH456',
                date: '10 Jan 2025',
                status: 'selesai',
                items: ['Hoodie Oversized Fleece'],
                total: 299000,
                img: 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=80&h=80&fit=crop'
            },
            {
                id: '#TK-2025-IJKL789',
                date: '5 Jan 2025',
                status: 'selesai',
                items: ['Serum Vitamin C', 'Lip Gloss Korea'],
                total: 278000,
                img: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=80&h=80&fit=crop'
            },
            {
                id: '#TK-2024-XYZ001',
                date: '25 Des 2024',
                status: 'proses',
                items: ['Smart Watch Series 5'],
                total: 1299000,
                img: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=80&h=80&fit=crop'
            },
            {
                id: '#TK-2024-MNOP23',
                date: '15 Des 2024',
                status: 'batal',
                items: ['Wireless Earbuds Pro'],
                total: 599000,
                img: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=80&h=80&fit=crop'
            },
        ];

        const statusMap = {
            selesai: {
                label: 'Selesai',
                class: 'badge-selesai'
            },
            proses: {
                label: 'Diproses',
                class: 'badge-proses'
            },
            kirim: {
                label: 'Dikirim',
                class: 'badge-kirim'
            },
            batal: {
                label: 'Dibatalkan',
                class: 'badge-batal'
            }
        };

        function renderOrders(filter = 'semua') {
            const list = document.getElementById('orderList');
            const filtered = filter === 'semua' ? orders : orders.filter(o => o.status === filter);
            if (filtered.length === 0) {
                list.innerHTML =
                    '<div class="text-center py-12"><div class="text-5xl mb-3">?</div><p class="text-slate-500">Tidak ada pesanan</p></div>';
                return;
            }
            list.innerHTML = filtered.map(o => {
                const s = statusMap[o.status];
                return `<div class="border border-slate-200 rounded-2xl p-5 hover:border-slate-300 transition-colors">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
            <div>
              <span class="font-mono font-bold text-slate-800 text-sm">${o.id}</span>
              <span class="text-slate-400 text-xs ml-2">• ${o.date}</span>
            </div>
            <span class="status-badge ${s.class}">${s.label}</span>
          </div>
          <div class="flex gap-3 mb-3">
            <img src="${o.img}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0" />
            <div>
              ${o.items.map(item => `<p class="text-sm font-medium text-slate-700">${item}</p>`).join('')}
              ${o.items.length > 1 ? `<p class="text-xs text-slate-400">+${o.items.length - 1} produk lainnya</p>` : ''}
            </div>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-3 border-t border-slate-100">
            <div>
              <span class="text-xs text-slate-500">Total: </span>
              <span class="font-bold text-slate-800">Rp ${o.total.toLocaleString('id-ID')}</span>
            </div>
            <div class="flex flex-wrap gap-2">
              ${o.status === 'selesai' ? '<button onclick="showToast(\'Ulasan berhasil dikirim!\')" class="text-xs border border-yellow-300 text-yellow-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-yellow-50 transition-colors">Beri Ulasan</button>' : ''}
              ${o.status === 'kirim' ? '<button onclick="showToast(\'Lacak pesanan dibuka\')" class="text-xs border border-blue-300 text-blue-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">Lacak Pesanan</button>' : ''}
              <a href="{{ route('frontend.detail-produk') }}" class="text-xs border border-blue-300 text-blue-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">Beli Lagi</a>
            </div>
          </div>
        </div>`;
            }).join('');
        }

        function filterOrder(status) {
            document.querySelectorAll('.order-tab').forEach(t => {
                t.className =
                    'order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700';
            });
            event.currentTarget.className =
                'order-tab px-5 py-3 text-sm font-semibold text-blue-600 border-b-2 border-blue-500 whitespace-nowrap';
            renderOrders(status);
        }

        const wishlistItems = [{
                name: "Nike Air Max 270",
                price: 1299000,
                image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=300&h=300&fit=crop",
                rating: 4.8
            },
            {
                name: "Samsung Galaxy Tab",
                price: 4599000,
                image: "https://images.unsplash.com/photo-1553830591-d8b75e2b5e3c?w=300&h=300&fit=crop",
                rating: 4.7
            },
            {
                name: "Parfum Maison",
                price: 899000,
                image: "https://images.unsplash.com/photo-1541643600914-78b084683702?w=300&h=300&fit=crop",
                rating: 4.9
            },
            {
                name: "Running Shorts Pro",
                price: 199000,
                image: "https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=300&h=300&fit=crop",
                rating: 4.6
            },
            {
                name: "Kamera DSLR Canon",
                price: 8999000,
                image: "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=300&h=300&fit=crop",
                rating: 4.9
            },
            {
                name: "Tas Kulit Premium",
                price: 1450000,
                image: "https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=300&h=300&fit=crop",
                rating: 4.7
            },
        ];

        document.getElementById('wishlistGrid').innerHTML = wishlistItems.map(w => `
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group hover:border-blue-300 transition-colors">
        <div class="relative overflow-hidden">
          <img src="${w.image}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300" />
          <button onclick="showToast('Dihapus dari wishlist')" class="absolute top-2 right-2 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center text-red-400 hover:text-red-500 text-xs opacity-0 group-hover:opacity-100 transition-all">?</button>
        </div>
        <div class="p-3">
          <p class="text-xs font-semibold text-slate-800 line-clamp-2 mb-1">${w.name}</p>
          <p class="font-bold text-slate-900 text-sm mb-2">Rp ${w.price.toLocaleString('id-ID')}</p>
          <button onclick="showToast('Ditambahkan ke keranjang! ?')" class="w-full text-xs bg-blue-50 hover:bg-blue-500 text-blue-600 hover:text-white font-semibold py-1.5 rounded-lg border border-blue-200 hover:border-blue-500 transition-all">+ Keranjang</button>
        </div>
      </div>`).join('');

        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.sidebar-item').forEach(b => {
                b.classList.remove('active');
                b.classList.add('text-slate-600');
            });
            document.getElementById('tab-' + tab).classList.add('active');
            const navEl = document.getElementById('nav-' + tab);
            navEl.classList.add('active');
            navEl.classList.remove('text-slate-600');
        }

        function saveProfile() {
            postForm("{{ route('frontend.profil.biodata.update') }}", {
                first_name: document.getElementById('firstName')?.value ?? '',
                last_name: document.getElementById('lastName')?.value ?? '',
                username: document.getElementById('username')?.value ?? '',
                gender: document.querySelector('input[name="gender"]:checked')?.value ?? '',
                email: document.getElementById('email')?.value ?? '',
                phone_country_code: document.getElementById('phoneCode')?.value ?? '',
                phone_number: document.getElementById('phoneNumber')?.value ?? '',
                birth_date: document.getElementById('birthDate')?.value ?? '',
                social_url: document.getElementById('socialUrl')?.value ?? '',
                bio: document.getElementById('bio')?.value ?? '',
            });
        }

        function changePassword() {
            const curr = document.getElementById('currPwd').value;
            const newP = document.getElementById('newPwd').value;
            const conf = document.getElementById('confPwd').value;
            if (!curr || !newP || !conf) {
                showToast('Lengkapi semua field password');
                return;
            }
            if (newP !== conf) {
                showToast('Password baru tidak cocok!');
                return;
            }
            if (newP.length < 8) {
                showToast('Password min. 8 karakter');
                return;
            }
            postForm("{{ route('frontend.profil.password.update') }}", {
                current_password: curr,
                password: newP,
                password_confirmation: conf,
            });
        }

        function checkStrength(val) {
            const hasLen = val.length >= 8;
            const hasUpper = /[A-Z]/.test(val);
            const hasNum = /[0-9]/.test(val);
            const hasSym = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val);
            const score = [hasLen, hasUpper, hasNum, hasSym].filter(Boolean).length;
            const colors = ['', 'bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-blue-500'];
            const texts = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
            const textColors = ['', 'text-red-500', 'text-orange-500', 'text-yellow-600', 'text-blue-600'];
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('s' + i);
                el.className = 'strength-bar flex-1 ' + (i <= score ? colors[score] : 'bg-slate-200');
            }
            document.getElementById('strengthText').textContent = val ? texts[score] :
                'Masukkan password untuk melihat kekuatan';
            document.getElementById('strengthText').className = 'text-xs mt-1 ' + (val ? textColors[score] :
                'text-slate-400');
            const setReq = (id, ok) => {
                const el = document.getElementById(id);
                el.className = 'flex items-center gap-2 text-xs ' + (ok ? 'text-blue-600' : 'text-slate-400');
                el.firstElementChild.textContent = ok ? '?' : '?';
            };
            setReq('req-len', hasLen);
            setReq('req-upper', hasUpper);
            setReq('req-num', hasNum);
            setReq('req-sym', hasSym);
        }

        function togglePwd(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        function toggle2FA(checkbox, method) {
            const label = {
                authenticator: 'Authenticator App',
                sms: 'SMS/WhatsApp',
                email: 'Email'
            } [method];
            showToast(checkbox.checked ? `${label} 2FA diaktifkan` : `${label} 2FA dinonaktifkan`);
        }

        function setMainAddress(btn) {
            const id = btn?.dataset?.id;
            if (!id) return;
            const found = profileAddresses.find((a) => String(a.id) === String(id));
            if (!found) return;
            postForm(`{{ url('/profil/addresses') }}/${id}`, {
                _method: 'PUT',
                label: found.label,
                recipient_name: found.recipient_name,
                phone_country_code: found.phone_country_code,
                phone_number: found.phone_number,
                province: found.province,
                city: found.city,
                postal_code: found.postal_code,
                address_line: found.address_line,
                is_primary: 1,
            });
        }

        function showNewAddressForm() {
            document.getElementById('newAddressForm').classList.remove('hidden');
        }

        function hideNewAddressForm() {
            document.getElementById('newAddressForm').classList.add('hidden');
        }

        function saveNewAddress() {
            const payload = {};
            document.querySelectorAll('#newAddressForm [data-address-field]').forEach((field) => {
                payload[field.dataset.addressField] = field.type === 'checkbox' ? (field.checked ? 1 : 0) : field
                    .value;
            });
            postForm("{{ route('frontend.profil.addresses.store') }}", payload);
        }

        function postForm(url, payload) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            Object.entries(payload).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value ?? '';
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }

        function confirmLogout() {
            const m = document.getElementById('logoutModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeLogout() {
            const m = document.getElementById('logoutModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function hydrateProfileFromBackend() {
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el && val !== null && val !== undefined) el.value = val;
            };
            setVal('firstName', profileUser.first_name ?? '');
            setVal('lastName', profileUser.last_name ?? '');
            setVal('username', profileUser.username ?? '');
            setVal('email', profileUser.email ?? '');
            setVal('phoneCode', profileUser.phone_country_code ?? '+62');
            setVal('phoneNumber', profileUser.phone_number ?? '');
            setVal('birthDate', profileUser.birth_date ?? '');
            setVal('socialUrl', profileUser.social_url ?? '');
            setVal('bio', profileUser.bio ?? '');
            const selectedGender = document.querySelector(`input[name="gender"][value="${profileUser.gender}"]`);
            if (selectedGender) selectedGender.checked = true;
        }

        hydrateProfileFromBackend();
        renderOrders();

        // Navbar mega dropdown
        function toggleCategoryMenu(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('category-dropdown');
            if (!menu) return;
            menu.classList.toggle('hidden');
        }
        const megaCategoryData = {
            'rumah-tangga': [{
                    title: 'Dekorasi',
                    items: ['Hiasan Dinding', 'Jam Dinding', 'Lilin Aroma', 'Karpet Ruang']
                },
                {
                    title: 'Kamar Mandi',
                    items: ['Cermin Kamar Mandi', 'Dispenser Sabun', 'Rak Toilet', 'Handuk Mandi']
                },
                {
                    title: 'Kebutuhan Rumah',
                    items: ['Baterai', 'Humidifier', 'Payung', 'Termometer']
                },
                {
                    title: 'Tempat Penyimpanan',
                    items: ['Box Plastik', 'Keranjang', 'Rak Serbaguna', 'Lemari Kecil']
                }
            ],
            'fashion-pria': [{
                    title: 'Atasan',
                    items: ['Kemeja', 'Kaos', 'Polo Shirt', 'Hoodie']
                },
                {
                    title: 'Bawahan',
                    items: ['Celana Chino', 'Jeans', 'Celana Pendek', 'Jogger']
                },
                {
                    title: 'Aksesoris',
                    items: ['Topi', 'Ikat Pinggang', 'Dompet', 'Jam Tangan']
                },
                {
                    title: 'Sepatu Pria',
                    items: ['Sneakers', 'Pantofel', 'Boots', 'Sandal']
                }
            ],
            'fashion-wanita': [{
                    title: 'Atasan Wanita',
                    items: ['Blouse', 'Kemeja Wanita', 'Tunik', 'Crop Top']
                },
                {
                    title: 'Bawahan Wanita',
                    items: ['Rok', 'Jeans Wanita', 'Celana Kulot', 'Legging']
                },
                {
                    title: 'Dress',
                    items: ['Dress Kasual', 'Dress Formal', 'Maxi Dress', 'Midi Dress']
                },
                {
                    title: 'Aksesoris',
                    items: ['Tas Wanita', 'Perhiasan', 'Hijab', 'Sepatu Wanita']
                }
            ],
            'elektronik': [{
                    title: 'Komputer',
                    items: ['Laptop', 'PC Desktop', 'Monitor', 'Keyboard']
                },
                {
                    title: 'Gadget',
                    items: ['Smartphone', 'Tablet', 'Smartwatch', 'Earbuds']
                },
                {
                    title: 'Gaming',
                    items: ['Konsol', 'Gamepad', 'Mouse Gaming', 'Headset Gaming']
                },
                {
                    title: 'Aksesoris',
                    items: ['Power Bank', 'Charger', 'Kabel Data', 'Storage']
                }
            ],
            'kecantikan': [{
                    title: 'Perawatan Wajah',
                    items: ['Facial Wash', 'Toner', 'Serum', 'Moisturizer']
                },
                {
                    title: 'Makeup',
                    items: ['Lipstik', 'Foundation', 'Compact Powder', 'Maskara']
                },
                {
                    title: 'Perawatan Tubuh',
                    items: ['Body Lotion', 'Body Scrub', 'Sabun', 'Hand Cream']
                },
                {
                    title: 'Perawatan Rambut',
                    items: ['Shampoo', 'Conditioner', 'Hair Mask', 'Hair Tonic']
                }
            ],
            'olahraga': [{
                    title: 'Fitness',
                    items: ['Dumbbell', 'Resistance Band', 'Yoga Mat', 'Kettlebell']
                },
                {
                    title: 'Lari',
                    items: ['Sepatu Lari', 'Jaket Lari', 'Celana Lari', 'Botol Minum']
                },
                {
                    title: 'Sepak Bola',
                    items: ['Jersey', 'Sepatu Bola', 'Bola', 'Shin Guard']
                },
                {
                    title: 'Outdoor',
                    items: ['Tenda', 'Carrier', 'Sleeping Bag', 'Jaket Gunung']
                }
            ],
            'ibu-bayi': [{
                    title: 'Makanan Bayi',
                    items: ['Sereal Bayi', 'Puree', 'Snack Bayi', 'Susu Formula']
                },
                {
                    title: 'Perlengkapan Bayi',
                    items: ['Popok', 'Botol Susu', 'Stroller', 'Baby Carrier']
                },
                {
                    title: 'Perawatan Bayi',
                    items: ['Sabun Bayi', 'Minyak Telon', 'Lotion Bayi', 'Tisu Basah']
                },
                {
                    title: 'Ibu Menyusui',
                    items: ['Pompa ASI', 'Breast Pad', 'Cooler Bag', 'Bantal Menyusui']
                }
            ],
            'makanan-minuman': [{
                    title: 'Makanan Ringan',
                    items: ['Keripik', 'Biskuit', 'Cokelat', 'Kacang']
                },
                {
                    title: 'Minuman',
                    items: ['Kopi', 'Teh', 'Susu UHT', 'Minuman Isotonik']
                },
                {
                    title: 'Bahan Pokok',
                    items: ['Beras', 'Minyak Goreng', 'Gula', 'Tepung']
                },
                {
                    title: 'Makanan Instan',
                    items: ['Mie Instan', 'Sarden', 'Kornet', 'Frozen Food']
                }
            ]
        };

        function renderMegaCategoryContent(key) {
            const container = document.getElementById('category-mega-content');
            if (!container) return;
            const sections = megaCategoryData[key] || megaCategoryData['rumah-tangga'];
            container.innerHTML =
                `<div class="grid grid-cols-4 gap-6">${sections.map(s => `<div><h5 class="text-sm font-semibold text-slate-800 mb-3">${s.title}</h5><ul class="space-y-2">${s.items.map(i => `<li><a href="#" class="text-sm text-slate-600 hover:text-blue-600">${i}</a></li>`).join('')}</ul></div>`).join('')}</div>`;
        }

        function setMegaCategory(key) {
            document.querySelectorAll('.mega-cat-btn').forEach(b => {
                b.classList.remove('bg-blue-50', 'text-blue-700', 'font-semibold');
                b.classList.add('text-slate-700');
            });
            const a = document.querySelector(`.mega-cat-btn[data-cat-key="${key}"]`);
            if (a) {
                a.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
                a.classList.remove('text-slate-700');
            }
            renderMegaCategoryContent(key);
        }
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('category-dropdown');
            const trigger = document.getElementById('category-trigger');
            if (!menu || !trigger) return;
            if (!menu.contains(e.target) && !trigger.contains(e.target)) menu.classList.add('hidden');
        });

        function toggleMobileSearch() {
            document.getElementById('mobileSearch').classList.toggle('hidden');
        }
        setMegaCategory('rumah-tangga');
    </script>
@endsection
