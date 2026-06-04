@extends('layouts.user')

@section('title', 'Profil - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        .badge-bayar {
            background: #dcfce7;
            color: #166534;
        }

        .badge-batal {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-menunggu {
            background: #fef3c7;
            color: #92400e;
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
        $avatarUrl = trim((string) ($user->avatar ?? ''));
        $hasAvatarImage = $avatarUrl !== '';
    @endphp
    <!-- Modal Lacak Pesanan -->
    <div id="trackingModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeTrackingModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Lacak Pesanan</h3>
                    <p id="trackingResi" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button onclick="closeTrackingModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fi fi-rr-cross-small text-sm leading-none"></i>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 px-6 py-5">
                {{-- Courier info --}}
                <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3 mb-5 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                        <i class="fi fi-rr-truck-side text-base text-blue-600 leading-none"></i>
                    </div>
                    <div class="min-w-0">
                        <p id="trackingCourier" class="text-sm font-semibold text-slate-800"></p>
                        <p id="trackingResiSmall" class="text-xs text-slate-500 truncate"></p>
                    </div>
                    <span id="trackingStatusBadge" class="ml-auto shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-700"></span>
                </div>

                {{-- Timeline --}}
                <div id="trackingTimeline" class="space-y-0"></div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed top-4 right-4 z-[9999] hidden">
        <div class="toast bg-blue-500 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3">
            <i class="fi fi-rr-badge-check text-base leading-none"></i>
            <span id="toast-msg">Berhasil!</span>
        </div>
    </div>

    @if (session('success') || $errors->any())
        <div class="fixed top-4 right-4 z-[9999] max-w-sm rounded-xl shadow-2xl px-5 py-3 text-sm font-medium {{ session('success') ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
            {{ session('success') ?: $errors->first() }}
        </div>
    @endif

    <!-- NAVBAR -->
    @include('partials.navbar-user')

    <!-- BREADCRUMB -->
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <i class="fi fi-rr-angle-small-right text-xs leading-none"></i>
                <span class="text-slate-800 font-medium">Profil Saya</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- SIDEBAR -->
            <aside class="lg:w-72 flex-shrink-0">
                <!-- Profile Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="avatar-ring mb-3">
                            @if ($hasAvatarImage)
                                <img src="{{ $avatarUrl }}" alt="{{ $displayName }}"
                                    class="w-20 h-20 rounded-full object-cover ring-4 ring-white" />
                            @else
                                <div
                                    class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-3xl font-extrabold ring-4 ring-white">
                                    {{ $avatarLetter }}</div>
                            @endif
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg">{{ $displayName }}</h3>
                        <p class="text-slate-500 text-sm">{{ $user->email }}</p>
                        <div class="flex items-center gap-1 mt-2">
                            <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                            <span class="text-xs text-blue-600 font-medium">Akun Terverifikasi</span>
                        </div>
                        <div class="flex gap-4 mt-4 w-full border-t border-slate-100 pt-4">
                            <div class="flex-1 text-center">
                                <p class="font-bold text-slate-800 text-xl">{{ (int) ($profileStats['orders'] ?? 0) }}</p>
                                <p class="text-xs text-slate-500">Pesanan</p>
                            </div>
                            <div class="w-px bg-slate-100"></div>
                            <div class="flex-1 text-center">
                                <p class="font-bold text-slate-800 text-xl">{{ (int) ($profileStats['reviews'] ?? 0) }}</p>
                                <p class="text-xs text-slate-500">Ulasan</p>
                            </div>
                            <div class="w-px bg-slate-100"></div>
                            <div class="flex-1 text-center">
                                <p id="profileWishlistStat" class="font-bold text-slate-800 text-xl">{{ (int) ($profileStats['wishlists'] ?? 0) }}</p>
                                <p class="text-xs text-slate-500">Wishlist</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level Badge -->
                @php
                    $memberTier = $membershipSummary['current_tier'] ?? null;
                    $nextTier = $membershipSummary['next_tier'] ?? null;
                    $memberColor = $memberTier?->color ?? 'amber';
                    $memberGradient = match ($memberColor) {
                        'emerald' => 'from-emerald-400 to-green-500',
                        'blue' => 'from-sky-400 to-blue-500',
                        'purple' => 'from-violet-400 to-purple-500',
                        'rose' => 'from-rose-400 to-pink-500',
                        'orange' => 'from-orange-400 to-orange-600',
                        'slate' => 'from-slate-400 to-slate-600',
                        default => 'from-amber-400 to-orange-500',
                    };
                @endphp
                <div class="bg-gradient-to-r {{ $memberGradient }} rounded-2xl p-4 mb-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="text-xs font-medium text-amber-100">Level Member</p>
                            <p class="font-bold text-lg">{{ $memberTier?->name ?? 'Member' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-white/75">Total Spending</p>
                            <p class="text-sm font-semibold">Rp {{ number_format((int) ($membershipSummary['total_spending'] ?? 0), 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-full h-2 mb-1">
                        <div class="bg-white rounded-full h-2" style="width:{{ (int) ($membershipSummary['progress_percent'] ?? 0) }}%"></div>
                    </div>
                    @if (!$memberTier && !$nextTier)
                        <p class="text-xs text-amber-100">Tier membership belum diatur oleh admin.</p>
                    @elseif ($nextTier)
                        <p class="text-xs text-amber-100">Rp {{ number_format((int) ($membershipSummary['total_spending'] ?? 0), 0, ',', '.') }} / Rp {{ number_format((int) ($membershipSummary['next_minimum'] ?? 0), 0, ',', '.') }} menuju {{ $nextTier->name }}</p>
                    @else
                        <p class="text-xs text-amber-100">Anda sudah berada di tier tertinggi.</p>
                    @endif
                </div>

                <!-- Navigation Menu -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="py-1">
                        <button onclick="showTab('biodata')" id="nav-biodata"
                            class="sidebar-item active w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium">
                            <i class="fi fi-rr-user text-sm leading-none"></i>
                            Biodata Diri
                        </button>
                        <button onclick="showTab('keamanan')" id="nav-keamanan"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <i class="fi fi-rr-lock text-sm leading-none"></i>
                            Password & Keamanan
                        </button>
                        <button onclick="showTab('alamat')" id="nav-alamat"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <i class="fi fi-rr-marker text-sm leading-none"></i>
                            Alamat Saya
                        </button>
                        <a href="{{ route('frontend.cart') }}"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            <i class="fi fi-rr-shopping-cart text-sm leading-none"></i>
                            Keranjang
                        </a>
                        <button onclick="showTab('pesanan')" id="nav-pesanan"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <i class="fi fi-rr-box-open-full text-sm leading-none"></i>
                            Riwayat Pesanan
                            <span
                                class="ml-auto bg-blue-100 text-blue-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ isset($transactions) ? $transactions->count() : 0 }}</span>
                        </button>
                        <button onclick="showTab('wishlist')" id="nav-wishlist"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <i class="fi fi-rr-heart text-sm leading-none"></i>
                            Wishlist
                        </button>
                        <button onclick="showTab('notif')" id="nav-notif"
                            class="sidebar-item w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-slate-600">
                            <i class="fi fi-rr-bell text-sm leading-none"></i>
                            Notifikasi
                        </button>
                        <div class="border-t border-slate-100 mt-1">
                            <button onclick="confirmLogout()"
                                class="w-full flex items-center gap-3 px-5 py-3.5 text-sm font-medium text-red-500 hover:bg-red-50 transition-colors">
                                <i class="fi fi-rr-exit text-sm leading-none"></i>
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
                        <form id="profileForm" action="{{ route('frontend.profil.biodata.update') }}" method="POST"
                            enctype="multipart/form-data" class="p-6">
                            @csrf
                            <!-- Avatar Section -->
                            <div class="flex items-center gap-5 mb-8 pb-6 border-b border-slate-100">
                                <div class="relative">
                                    @if ($hasAvatarImage)
                                        <img id="profileAvatarPreview" src="{{ $avatarUrl }}" alt="{{ $displayName }}"
                                            class="w-20 h-20 rounded-2xl object-cover" />
                                    @else
                                        <div id="profileAvatarInitial"
                                            class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                                            {{ $avatarLetter }}</div>
                                        <img id="profileAvatarPreview" src="" alt="{{ $displayName }}"
                                            class="hidden w-20 h-20 rounded-2xl object-cover" />
                                    @endif
                                    <button type="button" onclick="chooseProfilePhoto()"
                                        class="absolute -bottom-2 -right-2 w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-md hover:bg-blue-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <input id="profileAvatarFile" name="avatar_file" type="file" accept="image/png,image/jpeg"
                                        class="hidden" />
                                    <input id="profileAvatarRemove" name="avatar_remove" type="hidden" value="0" />
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 mb-1">Foto Profil</p>
                                    <p class="text-sm text-slate-500 mb-2">Format: JPG, PNG. Ukuran maks 2MB</p>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="chooseProfilePhoto()"
                                            class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium px-4 py-1.5 rounded-lg border border-blue-200 transition-colors">Ganti
                                            Foto</button>
                                        <button type="button" onclick="removeProfilePhoto()"
                                            class="text-red-400 hover:text-red-500 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Biodata -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nama Depan</label>
                                    <input id="firstName" name="first_name" type="text" value="Andi"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nama Belakang</label>
                                    <input id="lastName" name="last_name" type="text" value="Pratama"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Username</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">@</span>
                                        <input id="username" name="username" type="text" value="andi.pratama"
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
                                        <input id="email" name="email" type="email" value="{{ $user->email }}"
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
                                        <select id="phoneCode" name="phone_country_code"
                                            class="border border-r-0 border-slate-200 rounded-l-xl px-3 py-3 text-sm bg-slate-50 outline-none">
                                            <option>+62</option>
                                        </select>
                                        <input id="phoneNumber" name="phone_number" type="tel" value="812-3456-7890"
                                            class="flex-1 border border-slate-200 rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Tanggal Lahir</label>
                                    <input id="birthDate" name="birth_date" type="date" value="1995-07-15"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Website / Media
                                        Sosial</label>
                                    <input id="socialUrl" name="social_url" type="url" placeholder="https://instagram.com/..."
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Bio</label>
                                    <textarea id="bio" name="bio" placeholder="Ceritakan sedikit tentang diri kamu..."
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all resize-none h-20">Sering belanja kebutuhan bengkel, baut, mur, dan perkakas proyek.</textarea>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100">
                                <button
                                    class="border border-slate-200 text-slate-600 font-semibold px-6 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-sm">Batal</button>
                                <button type="button" onclick="saveProfile()"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
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
                                            <span>?</span> Min. 8 karakter
                                        </div>
                                        <div id="req-upper" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Huruf kapital
                                        </div>
                                        <div id="req-num" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Angka
                                        </div>
                                        <div id="req-sym" class="flex items-center gap-2 text-xs text-slate-400">
                                            <span>?</span> Simbol (!@#$...)
                                        </div>
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
                                                <span
                                                    class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ $address->label }}</span>
                                                <span
                                                    class="bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">Utama</span>
                                            </div>
                                            <div class="flex gap-2">
                                                <button onclick="editAddress(this)" data-id="{{ $address->id }}"
                                                    class="text-xs text-blue-600 hover:text-blue-700 font-semibold border border-blue-300 px-3 py-1 rounded-lg hover:bg-blue-100 transition-colors">Ubah</button>
                                                <button onclick="deleteAddress(this)" data-id="{{ $address->id }}"
                                                    class="text-xs text-red-400 hover:text-red-500 font-semibold border border-red-200 px-3 py-1 rounded-lg hover:bg-red-50 transition-colors">Hapus</button>
                                            </div>
                                        </div>
                                        <p class="font-semibold text-slate-800 mb-0.5">{{ $address->recipient_name }}</p>
                                        <p class="text-sm text-slate-600 mb-0.5">{{ $address->phone_country_code }}
                                            {{ $address->phone_number }}</p>
                                        <p class="text-sm text-slate-600">{{ $address->address_line }},
                                            {{ $address->city }},
                                            {{ $address->province }}{{ $address->postal_code ? ' ' . $address->postal_code : '' }}
                                        </p>
                                    </div>
                                @else
                                    <div class="border border-slate-200 rounded-2xl p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <span
                                                class="bg-slate-100 text-slate-600 text-xs font-bold px-2.5 py-1 rounded-full">{{ $address->label }}</span>
                                            <div class="flex gap-2">
                                                <button onclick="setMainAddress(this)" data-id="{{ $address->id }}"
                                                    class="text-xs text-blue-600 hover:text-blue-700 font-semibold border border-blue-300 px-3 py-1 rounded-lg hover:bg-blue-50 transition-colors">Jadikan
                                                    Utama</button>
                                                <button onclick="editAddress(this)" data-id="{{ $address->id }}"
                                                    class="text-xs text-slate-500 hover:text-slate-700 font-semibold border border-slate-200 px-3 py-1 rounded-lg hover:bg-slate-50 transition-colors">Ubah</button>
                                                <button onclick="deleteAddress(this)" data-id="{{ $address->id }}"
                                                    class="text-xs text-red-400 hover:text-red-500 font-semibold border border-red-200 px-3 py-1 rounded-lg hover:bg-red-50 transition-colors">Hapus</button>
                                            </div>
                                        </div>
                                        <p class="font-semibold text-slate-800 mb-0.5">{{ $address->recipient_name }}</p>
                                        <p class="text-sm text-slate-600 mb-0.5">{{ $address->phone_country_code }}
                                            {{ $address->phone_number }}</p>
                                        <p class="text-sm text-slate-600">{{ $address->address_line }},
                                            {{ $address->city }},
                                            {{ $address->province }}{{ $address->postal_code ? ' ' . $address->postal_code : '' }}
                                        </p>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-12">
                                    <div class="text-5xl mb-3">??</div>
                                    <p class="text-slate-500 font-medium">Belum ada alamat tersimpan</p>
                                    <p class="text-slate-400 text-sm mt-1">Tambahkan alamat pengiriman kamu</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- New Address Form (hidden by default) -->
                        <div id="newAddressForm" class="hidden px-6 pb-6">
                            <div class="border-2 border-dashed border-blue-300 rounded-2xl p-5 bg-blue-50">
                                <h3 id="addressFormTitle" class="font-bold text-slate-800 mb-4">Tambah Alamat Baru</h3>
                                <div id="addressFormLoading" class="hidden mb-4">
                                    <div
                                        class="rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm text-slate-600 flex items-center gap-3">
                                        <svg class="w-4 h-4 animate-spin text-blue-500" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                                                class="opacity-20"></circle>
                                            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3"
                                                stroke-linecap="round"></path>
                                        </svg>
                                        <span>Memuat data alamat...</span>
                                    </div>
                                </div>
                                <div id="addressFormBody">
                                <input data-address-field="label" type="hidden" value="Rumah" />
                                <input data-address-field="phone_country_code" type="hidden" value="+62" />
                                <input data-address-field="is_primary" type="hidden" value="0" />
                                <input data-address-field="province_id" type="hidden" value="" />
                                <input data-address-field="city_id" type="hidden" value="" />
                                <input data-address-field="district_id" type="hidden" value="" />
                                <input data-address-field="subdistrict_id" type="hidden" value="" />
                                <input data-address-field="destination_id" type="hidden" value="" />
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
                                        <div class="relative">
                                            <input id="provinceInput" data-address-field="province" type="text"
                                                placeholder="Cari provinsi" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                                class="w-full border border-slate-200 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                                            <span id="provinceChevron" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                            </span>
                                            <span id="provinceSpinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                            </span>
                                            <div id="provinceDropdown" class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kota/Kabupaten *</label>
                                        <div class="relative">
                                            <input id="cityInput" data-address-field="city" type="text" disabled
                                                placeholder="Pilih provinsi dulu" data-placeholder-enabled="Cari kota/kabupaten"
                                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                                class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                                            <span id="cityChevron" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                            </span>
                                            <span id="citySpinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                            </span>
                                            <div id="cityDropdown" class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kecamatan *</label>
                                        <div class="relative">
                                            <input id="districtInput" data-address-field="district" type="text" disabled
                                                placeholder="Pilih kota dulu" data-placeholder-enabled="Cari kecamatan"
                                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                                class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                                            <span id="districtChevron" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                            </span>
                                            <span id="districtSpinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                            </span>
                                            <div id="districtDropdown" class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kelurahan *</label>
                                        <div class="relative">
                                            <input id="subdistrictInput" data-address-field="subdistrict" type="text" disabled
                                                placeholder="Pilih kecamatan dulu" data-placeholder-enabled="Cari kelurahan"
                                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                                class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 pr-9 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50" />
                                            <span id="subdistrictChevron" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                            </span>
                                            <span id="subdistrictSpinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                                <svg class="animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                            </span>
                                            <div id="subdistrictDropdown" class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kode Pos</label>
                                        <input data-address-field="postal_code" id="postalCodeInput" type="text"
                                            placeholder="Kode Pos"
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
                                    <button id="addressFormSubmitBtn" type="button" onclick="saveNewAddress()"
                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan
                                        Alamat</button>
                                </div>
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
                            <button onclick="filterOrder('menunggu')"
                                class="order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700">Menunggu
                                Pembayaran</button>
                            <button onclick="filterOrder('dibayar')"
                                class="order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700">Dibayar</button>
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
                            <h2 id="wishlistTitle" class="font-bold text-slate-800 text-lg">Wishlist Saya (0)</h2>
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
                        <div class="px-6 py-5 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-bold text-slate-800 text-lg">Notifikasi</h2>
                                <p class="text-slate-500 text-sm mt-0.5">Semua aktivitas pesanan dan akun kamu</p>
                            </div>
                            @php $unreadCount = $notifications->whereNull('read_at')->count(); @endphp
                            @if($unreadCount > 0)
                            <form method="POST" action="{{ route('frontend.notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="text-sm text-blue-600 font-medium hover:text-blue-700">
                                    Tandai semua dibaca ({{ $unreadCount }})
                                </button>
                            </form>
                            @endif
                        </div>

                        @php
                            $typeConfig = [
                                'transaction_created' => ['bg' => 'bg-blue-100',   'color' => 'text-blue-600'],
                                'payment_received'    => ['bg' => 'bg-green-100',  'color' => 'text-green-600'],
                                'payment_rejected'    => ['bg' => 'bg-rose-100',   'color' => 'text-rose-600'],
                                'order_processed'     => ['bg' => 'bg-indigo-100', 'color' => 'text-indigo-600'],
                                'order_shipped'       => ['bg' => 'bg-purple-100', 'color' => 'text-purple-600'],
                                'order_completed'     => ['bg' => 'bg-emerald-100', 'color' => 'text-emerald-600'],
                            ];
                            $typeIcons = [
                                'transaction_created' => 'fi fi-rr-receipt',
                                'payment_received'    => 'fi fi-rr-badge-check',
                                'payment_rejected'    => 'fi fi-rr-triangle-warning',
                                'order_processed'     => 'fi fi-rr-box-open-full',
                                'order_shipped'       => 'fi fi-rr-truck-side',
                                'order_completed'     => 'fi fi-rr-badge-check',
                            ];
                            $defaultIcon = 'fi fi-rr-bell';
                        @endphp

                        <div class="divide-y divide-slate-100">
                            @forelse($notifications as $notif)
                                @php
                                    $cfg  = $typeConfig[$notif->type] ?? ['bg' => 'bg-slate-100', 'color' => 'text-slate-500'];
                                    $icon = $typeIcons[$notif->type] ?? $defaultIcon;
                                    $isUnread = is_null($notif->read_at);
                                @endphp
                                <div class="p-5 flex gap-4 {{ $isUnread ? 'bg-blue-50/50' : '' }} hover:bg-slate-50 transition-colors">
                                    <div class="w-10 h-10 {{ $cfg['bg'] }} rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="{{ $icon }} text-base {{ $cfg['color'] }} leading-none"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-800 text-sm mb-0.5">{{ $notif->title }}</p>
                                        <p class="text-sm text-slate-600">{{ $notif->body }}</p>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <p class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</p>
                                            @if($notif->url)
                                                <a href="{{ $notif->url }}" class="inline-flex items-center gap-1 text-xs text-blue-600 font-medium hover:underline">Lihat <i class="fi fi-rr-angle-small-right text-[10px] leading-none"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                    @if($isUnread)
                                        <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></span>
                                    @endif
                                </div>
                            @empty
                                <div class="px-6 py-12 text-center">
                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fi fi-rr-bell text-xl text-slate-400 leading-none"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">Belum ada notifikasi</p>
                                    <p class="text-xs text-slate-400 mt-1">Notifikasi pesanan dan aktivitas akun akan muncul di sini</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    
    <div id="orderDetailModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <div>
                    <h3 class="font-bold text-lg text-slate-800">Detail Pesanan</h3>
                    <p id="orderDetailInvoice" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeOrderDetailModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fi fi-rr-cross-small text-sm leading-none"></i>
                </button>
            </div>
            <div id="orderDetailContent" class="overflow-y-auto flex-1 px-6 py-4 space-y-5"></div>
        </div>
    </div>
    <!-- Modal Batalkan Pesanan -->
    <div id="orderCancelModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 border border-slate-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fi fi-rr-cross-small text-base text-red-500 leading-none"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">Batalkan Pesanan</p>
                    <p class="text-xs text-slate-500">Pilih alasan pembatalan</p>
                </div>
            </div>
            <div id="orderCancelReasonList" class="space-y-2 mb-4">
                @php
                $cancelReasons = [
                    'Berubah pikiran / tidak jadi membeli',
                    'Salah memilih produk atau varian',
                    'Ingin menggunakan metode pembayaran lain',
                    'Harga terlalu mahal',
                    'Menemukan produk lebih murah di tempat lain',
                    'Alasan lainnya',
                ];
                @endphp
                @foreach($cancelReasons as $reason)
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer has-[:checked]:border-red-400 has-[:checked]:bg-red-50 transition-colors">
                    <input type="radio" name="orderCancelReason" value="{{ $reason }}" class="text-red-500 focus:ring-red-400">
                    <span class="text-sm text-slate-700">{{ $reason }}</span>
                </label>
                @endforeach
                <input type="text" id="orderCancelReasonOther" placeholder="Tulis alasan lainnya..."
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm hidden focus:outline-none focus:border-red-400">
            </div>
            <p id="orderCancelError" class="text-xs text-red-500 mb-2 hidden">Pilih alasan pembatalan terlebih dahulu.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeOrderCancelModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Kembali</button>
                <button type="button" id="orderCancelConfirmBtn" class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-colors">Ya, Batalkan</button>
            </div>
        </div>
    </div>

    <!-- Modal Logout -->
    <div id="logoutModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 border border-slate-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fi fi-rr-exit text-base text-red-500 leading-none"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">Keluar dari akun?</p>
                    <p class="text-sm text-slate-500">Sesi Anda akan diakhiri.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="closeLogout()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-colors">Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <div id="reviewModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Beri Ulasan</h3>
                    <p id="reviewInvoiceText" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>

                        <form id="reviewForm" method="POST" action="{{ route('frontend.profil.reviews.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="transaction_id" id="reviewTransactionId" />
                <input type="hidden" name="transaction_detail_id" id="reviewTransactionDetailId" />
                <input type="hidden" name="rating" id="reviewRatingInput" value="5" />

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Pilih Produk</label>
                    <select id="reviewProductSelect"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400"
                        onchange="onReviewProductChange(this.value)"></select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Rating Bintang</label>
                    <div class="flex items-center gap-2" id="reviewStarsWrap">
                        <button type="button" data-star="1" onclick="setReviewRating(1)" class="review-star text-2xl text-slate-300">&#9733;</button>
                        <button type="button" data-star="2" onclick="setReviewRating(2)" class="review-star text-2xl text-slate-300">&#9733;</button>
                        <button type="button" data-star="3" onclick="setReviewRating(3)" class="review-star text-2xl text-slate-300">&#9733;</button>
                        <button type="button" data-star="4" onclick="setReviewRating(4)" class="review-star text-2xl text-slate-300">&#9733;</button>
                        <button type="button" data-star="5" onclick="setReviewRating(5)" class="review-star text-2xl text-slate-300">&#9733;</button>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Pesan</label>
                    <textarea name="message" id="reviewMessageInput" placeholder="Bagikan pengalaman belanja kamu..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-24"></textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Foto</label>
                    <input type="file" name="photos[]" id="reviewPhotosInput" multiple accept="image/*"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400" />
                    <div id="reviewPhotoPreview" class="mt-2 flex flex-wrap gap-2"></div>
                    <p class="text-xs text-slate-400 mt-1">Maksimal 8 foto, masing-masing maksimal 2MB.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeReviewModal()"
                        class="flex-1 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 rounded-xl transition-colors">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="returnRequestModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Ajukan Return / Refund</h3>
                    <p id="returnInvoiceText" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeReturnRequestModal()" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('frontend.profil.return-requests.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="transaction_id" id="returnTransactionId" />

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Jenis Pengajuan</label>
                    <select name="type" id="returnTypeInput"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400"
                        onchange="updateReturnPreview()">
                        <option value="refund">Refund uang</option>
                        <option value="exchange">Ganti barang</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Produk yang diajukan</label>
                    <div id="returnItemsWrap" class="space-y-3"></div>
                    <p id="returnAmountPreview" class="text-sm font-semibold text-blue-600 mt-3">Estimasi refund: Rp 0</p>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Alasan</label>
                    <textarea name="reason" id="returnReasonInput" required placeholder="Contoh: barang rusak, salah varian, ukuran tidak sesuai..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-24"></textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Catatan Tambahan</label>
                    <textarea name="customer_note" id="returnNoteInput" placeholder="Nomor rekening refund atau catatan penggantian barang"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-400 resize-none h-20"></textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Foto Bukti</label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400" />
                    <p class="text-xs text-slate-400 mt-1">Maksimal 5 foto, masing-masing maksimal 2MB.</p>
                </div>

                <p class="text-xs text-slate-500 bg-blue-50 border border-blue-100 rounded-xl p-3">
                    Pengajuan berlaku maksimal 7 hari sejak pesanan dikirim/selesai. Refund atau penggantian barang diproses manual oleh admin setelah disetujui.
                </p>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeReturnRequestModal()"
                        class="flex-1 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 rounded-xl transition-colors">Kirim Pengajuan</button>
                </div>
            </form>
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
                    'province_id' => $a->province_id,
                    'city_id' => $a->city_id,
                    'district_id' => $a->district_id,
                    'subdistrict_id' => $a->subdistrict_id,
                    'province' => $a->province,
                    'city' => $a->city,
                    'district' => $a->district,
                    'subdistrict' => $a->subdistrict,
                    'postal_code' => $a->postal_code,
                    'destination_id' => $a->destination_id,
                    'address_line' => $a->address_line,
                    'is_primary' => (bool) $a->is_primary,
                ];
            })
            ->values();
        $profileOrdersPayload = isset($transactions)
            ? $transactions
                ->map(function ($tx) {
                    $statusRaw = strtolower((string) $tx->status);
                    $status = match (true) {
                        in_array($statusRaw, ['paid', 'settlement', 'capture']) => 'dibayar',
                        in_array($statusRaw, ['selesai', 'completed', 'delivered']) => 'selesai',
                        in_array($statusRaw, ['kirim', 'shipping', 'shipped']) => 'kirim',
                        in_array($statusRaw, ['process', 'processing']) => 'proses',
                        in_array($statusRaw, ['cancel', 'expire', 'deny', 'failure', 'failed', 'dibatalkan']) => 'batal',
                        in_array($statusRaw, ['menunggu_verifikasi']) => 'menunggu_verifikasi',
                        default => 'menunggu',
                    };
                    $returnEligibleStatuses = ['kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];
                    $returnWindowStart = $tx->shipped_at ?? $tx->updated_at ?? $tx->created_at;
                    $returnDeadline = $returnWindowStart ? $returnWindowStart->copy()->addDays(7)->endOfDay() : null;
                    $canReturn = in_array($statusRaw, $returnEligibleStatuses, true)
                        && $returnDeadline
                        && $returnDeadline->isFuture();

                    $resolveImg = function ($img) {
                        $img = (string) ($img ?? '');
                        if ($img === '') {
                            return 'https://via.placeholder.com/80x80?text=No+Image';
                        }
                        if (
                            str_starts_with($img, 'http://') ||
                            str_starts_with($img, 'https://') ||
                            str_starts_with($img, '//') ||
                            str_starts_with($img, 'data:')
                        ) {
                            return $img;
                        }
                        return asset(ltrim($img, '/'));
                    };
                    return [
                        'transaction_id' => (int) $tx->id,
                        'id' => (string) $tx->invoice_no,
                        'order_id' => (string) $tx->order_id,
                        'date' => optional($tx->created_at)->translatedFormat('d M Y'),
                        'status' => $status,
                        'status_raw' => $tx->status,
                        'payment_method' => (string) ($tx->payment_method ?? '-'),
                        'tracking_number' => (string) ($tx->tracking_number ?? ''),
                        'shipping_cost' => (int) $tx->shipping_cost,
                        'discount_amount' => (int) ($tx->discount_amount ?? 0),
                        'coupon_code' => (string) ($tx->coupon_code ?? ''),
                        'tax_name' => (string) ($tx->tax_name ?? ''),
                        'tax_rate' => (float) ($tx->tax_rate ?? 0),
                        'taxable_amount' => (int) ($tx->taxable_amount ?? 0),
                        'tax_amount' => (int) ($tx->tax_amount ?? 0),
                        'shipping_label' => (string) ($tx->shipping_label ?? ''),
                        'shipping_recipient_name' => (string) ($tx->shipping_recipient_name ?? ''),
                        'shipping_phone' => (string) ($tx->shipping_phone ?? ''),
                        'shipping_address_line' => (string) ($tx->shipping_address_line ?? ''),
                        'shipping_city' => (string) ($tx->shipping_city ?? ''),
                        'shipping_province' => (string) ($tx->shipping_province ?? ''),
                        'shipping_postal_code' => (string) ($tx->shipping_postal_code ?? ''),
                        'can_return' => (bool) $canReturn,
                        'return_deadline' => $returnDeadline ? $returnDeadline->translatedFormat('d M Y') : '',
                        'invoice_url' => route('invoice.show', ['transaction' => $tx->id]),
                        'complete_url' => route('frontend.profil.orders.complete', ['transaction' => $tx->id]),
                        'payment_proof_url' => $tx->payment_proof_path ? asset(ltrim((string) $tx->payment_proof_path, '/')) : '',
                        'payment_admin_note' => (string) ($tx->payment_admin_note ?? ''),
                        'payment_proof_upload_url' => route('manual-payment.proof', ['transaction' => $tx->id]),
                        'status_histories' => $tx->statusHistories
                            ->sortBy('created_at')
                            ->map(fn($h) => [
                                'from_status' => (string) ($h->from_status ?? ''),
                                'to_status' => (string) $h->to_status,
                                'type' => (string) $h->type,
                                'note' => (string) ($h->note ?? ''),
                                'actor' => (string) ($h->user?->name ?? 'System'),
                                'created_at' => optional($h->created_at)->translatedFormat('d M Y H:i'),
                            ])
                            ->values()
                            ->all(),
                        'return_requests' => $tx->returnRequests
                            ->map(fn($rr) => [
                                'request_no' => (string) $rr->request_no,
                                'type' => (string) $rr->type,
                                'status' => (string) $rr->status,
                                'refund_amount' => (int) $rr->refund_amount,
                                'reason' => (string) $rr->reason,
                                'admin_note' => (string) ($rr->admin_note ?? ''),
                                'created_at' => optional($rr->created_at)->translatedFormat('d M Y'),
                            ])
                            ->values()
                            ->all(),
                        'items' => $tx->details
                            ->map(
                                function ($d) use ($resolveImg) {
                                    $usedReturnQty = $d->returnRequestItems
                                        ->filter(fn($ri) => optional($ri->returnRequest)->status !== 'ditolak')
                                        ->sum('quantity');

                                    return [
                                    'detail_id' => (int) $d->id,
                                    'name' => $d->product_name,
                                    'variant' => (string) ($d->variant_name ?? ''),
                                    'qty' => (int) $d->quantity,
                                    'returnable_qty' => max(0, (int) $d->quantity - (int) $usedReturnQty),
                                    'price' => (int) $d->price,
                                    'subtotal' => (int) $d->subtotal,
                                    'image' => $resolveImg($d->image),
                                    'is_reviewed' => $d->productReviews->isNotEmpty(),
                                    ];
                                },
                            )
                            ->values()
                            ->all(),
                        'total' => (int) $tx->grand_total,
                    ];
                })
                ->values()
            : collect();
        $profileWishlistsPayload = isset($wishlists)
            ? $wishlists
                ->map(function ($w) {
                    $product = $w->product;
                    if (!$product) {
                        return null;
                    }

                    $variant = $product->productVariants->first();
                    if (!$variant) {
                        return null;
                    }

                    $image = trim((string) (($variant->image ?? '') ?: ($product->firstAvailableImagePath() ?? '')));
                    $imageUrl = $image === ''
                        ? 'https://via.placeholder.com/300x300?text=No+Image'
                        : (str_starts_with($image, 'http://') ||
                            str_starts_with($image, 'https://') ||
                            str_starts_with($image, '//') ||
                            str_starts_with($image, 'data:')
                            ? $image
                            : asset(ltrim($image, '/')));

                    return [
                        'id' => (int) $w->id,
                        'product_id' => (int) $product->id,
                        'slug' => (string) $product->slug,
                        'name' => (string) $product->name,
                        'price' => (int) $variant->price,
                        'image' => $imageUrl,
                    ];
                })
                ->filter()
                ->values()
            : collect();
    @endphp
    <script>
        const profileUser = @json($profileUserPayload);
        const profileAddresses = @json($profileAddressesPayload);
        const profileOrders = @json($profileOrdersPayload);
        const waitingUrlTemplate = @json(route('frontend.checkout.waiting', ['orderId' => '__ORDER_ID__']));
        const csrfToken = @json(csrf_token());
        const roProvincesUrl = @json(route('frontend.rajaongkir.provinces'));
        const roCitiesUrl = @json(route('frontend.rajaongkir.cities'));
        const roDistrictsUrl = @json(route('frontend.rajaongkir.districts'));
        const roSubdistrictsUrl = @json(route('frontend.rajaongkir.subdistricts'));
        const initialProfileTab = @json(request()->query('tab', 'biodata'));
        const wishlistToggleUrl = @json(route('frontend.wishlist.toggle'));
        const wishlistItemsData = @json($profileWishlistsPayload);

        let roProvinces = [];
        let roCities = [];
        let roDistricts = [];
        let roSubdistricts = [];
        let editingAddressId = null;

        function byField(name) {
            return document.querySelector(`#newAddressForm [data-address-field="${name}"]`);
        }

        function setAddressFormLoading(isLoading) {
            const loading = document.getElementById('addressFormLoading');
            const body = document.getElementById('addressFormBody');
            const submitBtn = document.getElementById('addressFormSubmitBtn');
            if (!loading || !body || !submitBtn) return;
            if (isLoading) {
                loading.classList.remove('hidden');
                body.classList.add('opacity-50', 'pointer-events-none');
                submitBtn.disabled = true;
                return;
            }
            loading.classList.add('hidden');
            body.classList.remove('opacity-50', 'pointer-events-none');
            submitBtn.disabled = false;
        }

        const _comboboxMap = {
            'provinceList': { dropdownId: 'provinceDropdown', inputId: 'provinceInput' },
            'cityList': { dropdownId: 'cityDropdown', inputId: 'cityInput' },
            'districtList': { dropdownId: 'districtDropdown', inputId: 'districtInput' },
            'subdistrictList': { dropdownId: 'subdistrictDropdown', inputId: 'subdistrictInput' },
        };

        function fillDatalist(id, items, toLabel) {
            const info = _comboboxMap[id];
            if (!info) return;
            const dropdown = document.getElementById(info.dropdownId);
            if (!dropdown) return;
            dropdown._items = items;
            dropdown._inputId = info.inputId;
            dropdown._toLabel = toLabel;
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
        }

        function _showCombobox(dropdownId, query) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown || !dropdown._items) return;
            const q = (query || '').toLowerCase().trim();
            const filtered = q
                ? dropdown._items.filter(item => (dropdown._toLabel(item) || '').toLowerCase().includes(q))
                : dropdown._items;
            if (!filtered.length) {
                dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-slate-400 text-center">Tidak ada data</div>';
            } else {
                const toLabel = dropdown._toLabel;
                dropdown.innerHTML = filtered.map((item, i) =>
                    `<div class="combo-opt px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-idx="${i}">${toLabel(item)}</div>`
                ).join('');
                dropdown.querySelectorAll('.combo-opt').forEach((el, i) => {
                    el.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        const input = document.getElementById(dropdown._inputId);
                        if (input) {
                            input.value = toLabel(filtered[i]);
                            input.dispatchEvent(new Event('change'));
                        }
                        dropdown.classList.add('hidden');
                    });
                });
            }
            dropdown.classList.remove('hidden');
        }

        function _setupCombobox(inputId, dropdownId) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            if (!input || !dropdown) return;
            input.addEventListener('focus', () => { if (!input.disabled) _showCombobox(dropdownId, input.value); });
            input.addEventListener('input', () => { if (!input.disabled) _showCombobox(dropdownId, input.value); });
            input.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('hidden'), 200));
            input.addEventListener('keydown', (e) => { if (e.key === 'Escape') dropdown.classList.add('hidden'); });
        }

        function _setComboboxLoading(inputId, isLoading) {
            const input = document.getElementById(inputId);
            const chevronId = inputId.replace('Input', 'Chevron');
            const spinnerId = inputId.replace('Input', 'Spinner');
            const chevron = document.getElementById(chevronId);
            const spinner = document.getElementById(spinnerId);
            if (input) input.disabled = isLoading;
            if (chevron) chevron.classList.toggle('hidden', isLoading);
            if (spinner) spinner.classList.toggle('hidden', !isLoading);
            if (isLoading) {
                const dropdownId = inputId.replace('Input', 'Dropdown');
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) dropdown.classList.add('hidden');
            }
        }

        function _setComboboxEnabled(inputId, enabled) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.disabled = !enabled;
            if (enabled && input.dataset.placeholderEnabled) {
                input.placeholder = input.dataset.placeholderEnabled;
            }
            if (!enabled) {
                const dropdownId = inputId.replace('Input', 'Dropdown');
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) dropdown.classList.add('hidden');
            }
        }

        function resetLocationFields(level) {
            if (level <= 2) {
                roCities = [];
                byField('city_id').value = '';
                byField('city').value = '';
                fillDatalist('cityList', [], () => '');
                _setComboboxLoading('cityInput', false);
                _setComboboxEnabled('cityInput', false);
            }
            if (level <= 3) {
                roDistricts = [];
                byField('district_id').value = '';
                byField('district').value = '';
                fillDatalist('districtList', [], () => '');
                _setComboboxLoading('districtInput', false);
                _setComboboxEnabled('districtInput', false);
            }
            if (level <= 4) {
                roSubdistricts = [];
                byField('subdistrict_id').value = '';
                byField('subdistrict').value = '';
                byField('destination_id').value = '';
                byField('postal_code').value = '';
                fillDatalist('subdistrictList', [], () => '');
                _setComboboxLoading('subdistrictInput', false);
                _setComboboxEnabled('subdistrictInput', false);
            }
        }

        async function loadProvinces() {
            _setComboboxLoading('provinceInput', true);
            try {
                const res = await fetch(roProvincesUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                roProvinces = Array.isArray(json?.data) ? json.data : [];
                fillDatalist('provinceList', roProvinces, (p) => p.name || p.province_name || '');
            } finally {
                _setComboboxLoading('provinceInput', false);
            }
        }

        async function onProvinceChange() {
            const name = (byField('province').value || '').trim().toLowerCase();
            const found = roProvinces.find((p) => String(p.name || p.province_name || '').trim().toLowerCase() === name);
            byField('province_id').value = found ? String(found.id || found.province_id || '') : '';
            resetLocationFields(2);
            if (!found) return;
            _setComboboxLoading('cityInput', true);
            try {
                const res = await fetch(`${roCitiesUrl}?province_id=${encodeURIComponent(byField('province_id').value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                roCities = Array.isArray(json?.data) ? json.data : [];
                fillDatalist('cityList', roCities, (c) => c.name || c.city_name || '');
            } finally {
                _setComboboxLoading('cityInput', false);
                _setComboboxEnabled('cityInput', true);
            }
        }

        async function onCityChange() {
            const name = (byField('city').value || '').trim().toLowerCase();
            const found = roCities.find((c) => String(c.name || c.city_name || '').trim().toLowerCase() === name);
            byField('city_id').value = found ? String(found.id || found.city_id || '') : '';
            resetLocationFields(3);
            if (!found) return;
            _setComboboxLoading('districtInput', true);
            try {
                const res = await fetch(`${roDistrictsUrl}?city_id=${encodeURIComponent(byField('city_id').value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                roDistricts = Array.isArray(json?.data) ? json.data : [];
                fillDatalist('districtList', roDistricts, (d) => d.name || d.district_name || '');
            } finally {
                _setComboboxLoading('districtInput', false);
                _setComboboxEnabled('districtInput', true);
            }
        }

        async function onDistrictChange() {
            const name = (byField('district').value || '').trim().toLowerCase();
            const found = roDistricts.find((d) => String(d.name || d.district_name || '').trim().toLowerCase() === name);
            byField('district_id').value = found ? String(found.id || found.district_id || '') : '';
            resetLocationFields(4);
            if (!found) return;
            _setComboboxLoading('subdistrictInput', true);
            try {
                const res = await fetch(`${roSubdistrictsUrl}?district_id=${encodeURIComponent(byField('district_id').value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                roSubdistricts = Array.isArray(json?.data) ? json.data : [];
                fillDatalist('subdistrictList', roSubdistricts, (s) => s.name || s.subdistrict_name || '');
            } finally {
                _setComboboxLoading('subdistrictInput', false);
                _setComboboxEnabled('subdistrictInput', true);
            }
        }

        function onSubdistrictChange() {
            const name = (byField('subdistrict').value || '').trim().toLowerCase();
            const found = roSubdistricts.find((s) => String(s.name || s.subdistrict_name || '').trim().toLowerCase() ===
                name);
            byField('subdistrict_id').value = found ? String(found.id || found.subdistrict_id || '') : '';
            byField('destination_id').value = found ? String(found.destination_id || found.id || '') : '';
            if (found) {
                byField('postal_code').value = String(found.zip_code || found.postal_code || byField('postal_code').value ||
                    '');
            }
        }

        const orders = Array.isArray(profileOrders) ? profileOrders : [];

        const statusMap = {
            menunggu: {
                label: 'Menunggu Pembayaran',
                class: 'badge-menunggu'
            },
            menunggu_verifikasi: {
                label: 'Menunggu Verifikasi',
                class: 'badge-menunggu'
            },
            selesai: {
                label: 'Selesai',
                class: 'badge-selesai'
            },
            dibayar: {
                label: 'Dibayar',
                class: 'badge-bayar'
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
                const s = statusMap[o.status] || {
                    label: o.status,
                    class: 'badge-menunggu'
                };
                const items = Array.isArray(o.items) ? o.items : [];
                const itemsHtml = items.map(item => `
                    <div class="flex items-center gap-3">
                        <img src="${item.image}" alt="${item.name}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-slate-100"
                             onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 leading-snug">${item.name}${item.variant ? ` <span class="text-slate-400 font-normal">(${item.variant})</span>` : ''}</p>
                            <p class="text-xs text-slate-400">x${item.qty}</p>
                        </div>
                    </div>
                `).join('');
                const returnRequests = Array.isArray(o.return_requests) ? o.return_requests : [];
                const returnRequestsHtml = returnRequests.length ? `
                    <div class="mb-3 space-y-2">
                        ${returnRequests.map((rr) => `
                            <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-slate-600">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-800">${rr.request_no}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-white text-blue-600 font-semibold">${rr.type === 'refund' ? 'Refund uang' : 'Ganti barang'}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-white text-slate-600 font-semibold">${rr.status}</span>
                                    <span class="ml-auto font-semibold text-blue-600">Rp ${Number(rr.refund_amount || 0).toLocaleString('id-ID')}</span>
                                </div>
                                ${rr.admin_note ? `<p class="mt-1 text-slate-500">Catatan admin: ${rr.admin_note}</p>` : ''}
                            </div>
                        `).join('')}
                    </div>
                ` : '';
                return `<div class="border border-slate-200 rounded-2xl p-5 hover:border-slate-300 transition-colors">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
            <div>
              <span class="font-mono font-bold text-slate-800 text-sm">${o.id}</span>
              <span class="text-slate-400 text-xs ml-2">&bull; ${o.date}</span>
            </div>
            <span class="status-badge ${s.class}">${s.label}</span>
          </div>
          <div class="space-y-2 mb-3">${itemsHtml}</div>
          ${returnRequestsHtml}
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-3 border-t border-slate-100">
            <div>
              <span class="text-xs text-slate-500">Total: </span>
              <span class="font-bold text-slate-800">Rp ${o.total.toLocaleString('id-ID')}</span>
              ${o.status === 'kirim' && o.tracking_number ? `<p class="text-xs text-slate-500 mt-1">No. Resi: <span class="font-semibold text-slate-700">${o.tracking_number}</span></p>` : ''}
              ${o.can_return && o.return_deadline ? `<p class="text-xs text-blue-600 mt-1">Batas return/refund: ${o.return_deadline}</p>` : ''}
            </div>
            <div class="flex flex-wrap gap-2">
              ${o.status === 'menunggu' ? `<a href="${waitingUrlTemplate.replace('__ORDER_ID__', o.order_id)}" class="text-xs border border-orange-300 text-orange-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-orange-50 transition-colors flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>Bayar Sekarang</a>` : ''}
              ${o.status === 'menunggu_verifikasi' ? `<a href="${waitingUrlTemplate.replace('__ORDER_ID__', o.order_id)}" class="text-xs border border-orange-300 text-orange-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-orange-50 transition-colors">Upload Bukti</a>` : ''}
              ${o.status === 'menunggu' ? `<button type="button" onclick="openOrderCancelModal('${o.order_id}')" class="text-xs border border-red-300 text-red-500 font-semibold px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">Batalkan</button>` : ''}
              ${Array.isArray(o.items) && o.items.some((it) => !it.is_reviewed) ? '<button onclick="openReviewModal(\'' + o.id + '\')" class="text-xs border border-yellow-300 text-yellow-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-yellow-50 transition-colors">Beri Ulasan</button>' : ''}
              ${o.status === 'kirim' ? `<button onclick="openTrackingModal('${o.id}')" class="text-xs border border-blue-300 text-blue-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">Lacak Pesanan</button>` : ''}
              ${o.status === 'kirim' ? `<form method="POST" action="${o.complete_url}" onsubmit="return confirm('Tandai pesanan ini sudah diterima?')" class="inline"><input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="PATCH"><button type="submit" class="text-xs border border-emerald-300 text-emerald-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-emerald-50 transition-colors">Pesanan Diterima</button></form>` : ''}
              ${o.can_return && items.some((it) => Number(it.returnable_qty || 0) > 0) ? `<button onclick="openReturnRequestModal('${o.id}')" class="text-xs border border-emerald-300 text-emerald-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-emerald-50 transition-colors">Return / Refund</button>` : ''}
              ${o.invoice_url ? `<a href="${o.invoice_url}" target="_blank" class="text-xs border border-indigo-300 text-indigo-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors">Invoice</a>` : ''}
              <button type="button" onclick="openOrderDetailModal('${o.id}')" class="text-xs border border-slate-300 text-slate-600 font-semibold px-3 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">Lihat Detail</button>
            </div>
          </div>
        </div>`;
            }).join('');
        }

        function setReviewRating(star) {
            const rating = Math.max(1, Math.min(5, Number(star || 5)));
            const ratingInput = document.getElementById('reviewRatingInput');
            if (ratingInput) ratingInput.value = String(rating);
            document.querySelectorAll('#reviewStarsWrap .review-star').forEach((btn) => {
                const s = Number(btn.dataset.star || 0);
                btn.classList.toggle('text-yellow-400', s <= rating);
                btn.classList.toggle('text-slate-300', s > rating);
            });
        }

                function onReviewProductChange(detailId) {
            const hidden = document.getElementById('reviewTransactionDetailId');
            if (hidden) hidden.value = String(detailId || '');
        }

        function renderReviewPhotoPreview(files) {
            const wrap = document.getElementById('reviewPhotoPreview');
            if (!wrap) return;
            wrap.innerHTML = '';
            Array.from(files || []).slice(0, 8).forEach((file) => {
                if (!file || !file.type || !file.type.startsWith('image/')) return;
                const img = document.createElement('img');
                img.className = 'w-14 h-14 rounded-lg object-cover border border-slate-200';
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                wrap.appendChild(img);
            });
        }

        function openReviewModal(invoiceNo) {
            const order = orders.find((x) => x.id === invoiceNo);
            if (!order) return;
            const modal = document.getElementById('reviewModal');
            if (!modal) return;
            document.getElementById('reviewTransactionId').value = String(order.transaction_id || '');
            document.getElementById('reviewInvoiceText').textContent = `Invoice: ${order.id}`;
            document.getElementById('reviewMessageInput').value = '';
            document.getElementById('reviewPhotosInput').value = '';

            const select = document.getElementById('reviewProductSelect');
            const items = (Array.isArray(order.items) ? order.items : []).filter((item) => !item.is_reviewed);
            if (!items.length) {
                showToast('Semua produk pada transaksi ini sudah diulas.');
                return;
            }
            select.innerHTML = items.map((item, idx) => {
                const did = Number(item.detail_id || 0);
                const label = `${item.name}${item.variant ? ' (' + item.variant + ')' : ''}`;
                return `<option value="${did}" ${idx === 0 ? 'selected' : ''}>${label}</option>`;
            }).join('');

            const firstDetailId = Number(items[0]?.detail_id || 0);
            document.getElementById('reviewTransactionDetailId').value = firstDetailId ? String(firstDetailId) : '';
            setReviewRating(5);
            renderReviewPhotoPreview([]);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReviewModal() {
            const modal = document.getElementById('reviewModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openReturnRequestModal(invoiceNo) {
            const order = orders.find((x) => x.id === invoiceNo);
            if (!order) return;

            const modal = document.getElementById('returnRequestModal');
            if (!modal) return;

            document.getElementById('returnTransactionId').value = String(order.transaction_id || '');
            document.getElementById('returnInvoiceText').textContent = `Invoice: ${order.id}`;
            document.getElementById('returnReasonInput').value = '';
            document.getElementById('returnNoteInput').value = '';
            document.getElementById('returnTypeInput').value = 'refund';

            const wrap = document.getElementById('returnItemsWrap');
            const items = (Array.isArray(order.items) ? order.items : []).filter((item) => Number(item.returnable_qty || 0) > 0);
            wrap.innerHTML = items.map((item) => {
                const detailId = Number(item.detail_id || 0);
                const maxQty = Number(item.returnable_qty || 0);
                return `
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-3">
                        <img src="${item.image}" alt="${item.name}" class="w-12 h-12 rounded-lg object-cover border border-slate-100" onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 leading-snug">${item.name}${item.variant ? ` <span class="text-xs text-slate-400 font-normal">(${item.variant})</span>` : ''}</p>
                            <p class="text-xs text-slate-500">Rp ${Number(item.price || 0).toLocaleString('id-ID')} &bull; bisa diajukan ${maxQty}</p>
                        </div>
                        <input type="number" min="0" max="${maxQty}" value="0" name="items[${detailId}]"
                            data-return-price="${Number(item.price || 0)}"
                            class="return-qty-input w-20 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400"
                            oninput="updateReturnPreview()" />
                    </div>
                `;
            }).join('');

            updateReturnPreview();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function updateReturnPreview() {
            let total = 0;
            document.querySelectorAll('.return-qty-input').forEach((input) => {
                const qty = Math.max(0, Number(input.value || 0));
                const max = Math.max(0, Number(input.max || 0));
                if (qty > max) input.value = String(max);
                total += Math.min(qty, max) * Number(input.dataset.returnPrice || 0);
            });
            const preview = document.getElementById('returnAmountPreview');
            const type = document.getElementById('returnTypeInput')?.value || 'refund';
            if (preview) {
                preview.textContent = `${type === 'refund' ? 'Estimasi refund' : 'Nilai barang pengganti'}: Rp ${total.toLocaleString('id-ID')}`;
            }
        }

        function closeReturnRequestModal() {
            const modal = document.getElementById('returnRequestModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openOrderDetailModal(invoiceNo) {
            const o = orders.find(x => x.id === invoiceNo);
            if (!o) return;

            document.getElementById('orderDetailInvoice').textContent = o.id;

            const s = statusMap[o.status] || {
                label: o.status,
                class: 'badge-menunggu'
            };

            const infoHtml = `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Informasi Pesanan</h4>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Tanggal</p>
                            <p class="font-medium text-slate-800">${o.date}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Metode Pembayaran</p>
                            <p class="font-medium text-slate-800">${o.payment_method}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Status</p>
                            <span class="status-badge ${s.class}">${s.label}</span>
                        </div>
                        ${o.tracking_number ? `<div>
                                <p class="text-xs text-slate-400 mb-0.5">Nomor Resi</p>
                                <p class="font-medium text-slate-800">${o.tracking_number}</p>
                            </div>` : ''}
                    </div>
                </div>
            `;

            const hasAddress = o.shipping_recipient_name || o.shipping_phone || o.shipping_address_line;
            const addressHtml = hasAddress ? `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Alamat Pengiriman</h4>
                    <div class="bg-slate-50 rounded-xl p-4 space-y-1.5">
                        ${o.shipping_recipient_name ? `<p class="font-semibold text-slate-800 text-sm">${o.shipping_recipient_name}</p>` : ''}
                        ${o.shipping_phone ? `<p class="text-sm text-slate-600 flex items-center gap-1.5"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.62 4.45 2 2 0 0 1 3.6 2.24h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.09 6.09l.95-.95a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/></svg>${o.shipping_phone}</p>` : ''}
                        ${o.shipping_address_line ? `<p class="text-sm text-slate-600">${o.shipping_address_line}${o.shipping_city ? ', ' + o.shipping_city : ''}${o.shipping_province ? ', ' + o.shipping_province : ''}${o.shipping_postal_code ? ' ' + o.shipping_postal_code : ''}</p>` : ''}
                        ${o.shipping_label ? `<span class="inline-block mt-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">${o.shipping_label}</span>` : ''}
                    </div>
                </div>
            ` : '';

            const items = Array.isArray(o.items) ? o.items : [];
            const productsHtml = `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Produk</h4>
                    <div class="space-y-3">
                        ${items.map(d => `
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-lg overflow-hidden shrink-0 bg-slate-100 border border-slate-200">
                                        <img src="${d.image}" alt="${d.name}" class="w-full h-full object-cover"
                                             onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 leading-snug">${d.name}${d.variant ? " <span style='font-size:11px;color:#94a3b8;font-weight:400'>(" + d.variant + ")</span>" : ""}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">${d.qty} Ã— Rp ${Number(d.price || 0).toLocaleString('id-ID')}</p>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-800 shrink-0">Rp ${Number(d.subtotal || 0).toLocaleString('id-ID')}</div>
                                </div>
                            `).join('')}
                    </div>
                </div>
            `;

            const summaryHtml = `
                <div class="bg-slate-50 rounded-xl p-4 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-500">
                        <span>Ongkos Kirim</span>
                        <span>Rp ${Number(o.shipping_cost || 0).toLocaleString('id-ID')}</span>
                    </div>
                    ${Number(o.discount_amount || 0) > 0 ? `<div class="flex justify-between text-emerald-600">
                        <span>Voucher ${o.coupon_code || ''}</span>
                        <span>- Rp ${Number(o.discount_amount || 0).toLocaleString('id-ID')}</span>
                    </div>` : ''}
                    ${Number(o.tax_amount || 0) > 0 ? `<div class="flex justify-between text-slate-500">
                        <span>${o.tax_name || 'PPN'} ${Number(o.tax_rate || 0).toLocaleString('id-ID')}%</span>
                        <span>Rp ${Number(o.tax_amount || 0).toLocaleString('id-ID')}</span>
                    </div>` : ''}
                    <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                        <span class="font-bold text-slate-800">Grand Total</span>
                        <span class="font-bold text-blue-600 text-base">Rp ${Number(o.total || 0).toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;

            const paymentProofHtml = (o.payment_proof_url || o.payment_admin_note) ? `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Bukti Pembayaran</h4>
                    <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600">
                        ${o.payment_proof_url ? `<a href="${o.payment_proof_url}" target="_blank" class="text-blue-600 font-semibold hover:underline">Lihat bukti transfer</a>` : '<p>Belum ada bukti transfer.</p>'}
                        ${o.payment_admin_note ? `<p class="mt-2">Catatan admin: ${o.payment_admin_note}</p>` : ''}
                    </div>
                </div>
            ` : '';

            const histories = Array.isArray(o.status_histories) ? o.status_histories : [];
            const historyHtml = histories.length ? `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Riwayat Status</h4>
                    <div class="space-y-2">
                        ${histories.map((h) => `
                            <div class="rounded-xl border border-slate-100 p-3 text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-800">${h.to_status}</span>
                                    <span class="text-xs text-slate-400">${h.created_at} oleh ${h.actor}</span>
                                </div>
                                ${h.note ? `<p class="text-xs text-slate-500 mt-1">${h.note}</p>` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : '';

            document.getElementById('orderDetailContent').innerHTML = infoHtml + addressHtml + productsHtml + paymentProofHtml + historyHtml + summaryHtml;
            const modal = document.getElementById('orderDetailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeOrderDetailModal() {
            const modal = document.getElementById('orderDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('orderDetailModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeOrderDetailModal();
        });
        document.getElementById('reviewModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeReviewModal();
        });
        document.getElementById('returnRequestModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeReturnRequestModal();
        });
        document.getElementById('reviewPhotosInput')?.addEventListener('change', function(e) {
            renderReviewPhotoPreview(e?.target?.files || []);
        });



        function filterOrder(status) {
            document.querySelectorAll('.order-tab').forEach(t => {
                t.className =
                    'order-tab px-5 py-3 text-sm font-medium text-slate-500 whitespace-nowrap hover:text-slate-700';
            });
            const activeTabButton = Array.from(document.querySelectorAll('.order-tab'))
                .find((btn) => (btn.getAttribute('onclick') || '').includes(`filterOrder('${status}')`));
            if (activeTabButton) {
                activeTabButton.className =
                    'order-tab px-5 py-3 text-sm font-semibold text-blue-600 border-b-2 border-blue-500 whitespace-nowrap';
            }
            renderOrders(status);
        }

        let wishlistItems = Array.isArray(wishlistItemsData) ? [...wishlistItemsData] : [];

        function syncWishlistCount() {
            const count = wishlistItems.length;
            const title = document.getElementById('wishlistTitle');
            const stat = document.getElementById('profileWishlistStat');
            if (title) title.textContent = `Wishlist Saya (${count})`;
            if (stat) stat.textContent = String(count);
        }

        function renderWishlist() {
            const grid = document.getElementById('wishlistGrid');
            if (!grid) return;
            syncWishlistCount();
            if (!wishlistItems.length) {
                grid.innerHTML =
                    '<div class="col-span-2 sm:col-span-3 text-center py-12"><div class="text-5xl mb-3">â™¡</div><p class="text-slate-500">Wishlist kamu masih kosong</p></div>';
                return;
            }

            grid.innerHTML = wishlistItems.map((w) => `
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group hover:border-blue-300 transition-colors">
        <div class="relative overflow-hidden">
          <a href="{{ url('/detail-produk') }}/${w.slug}">
            <img src="${w.image}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300" />
          </a>
          <button onclick="removeWishlist(${w.product_id})" class="absolute top-2 right-2 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center text-red-400 hover:text-red-500 text-xs opacity-0 group-hover:opacity-100 transition-all">âœ•</button>
        </div>
        <div class="p-3">
          <a href="{{ url('/detail-produk') }}/${w.slug}" class="text-xs font-semibold text-slate-800 line-clamp-2 mb-1 block hover:text-blue-600">${w.name}</a>
          <p class="font-bold text-slate-900 text-sm mb-2">Rp ${Number(w.price || 0).toLocaleString('id-ID')}</p>
          <button onclick="removeWishlist(${w.product_id})" class="w-full text-xs bg-blue-50 hover:bg-blue-500 text-blue-600 hover:text-white font-semibold py-1.5 rounded-lg border border-blue-200 hover:border-blue-500 transition-all">Hapus</button>
        </div>
      </div>`).join('');
        }

        async function removeWishlist(productId) {
            const target = wishlistItems.find((w) => Number(w.product_id) === Number(productId));
            const res = await fetch(wishlistToggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_id: Number(productId),
                }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                showToast('Gagal memproses wishlist');
                return;
            }
            if (!json.wished) {
                wishlistItems = wishlistItems.filter((w) => Number(w.product_id) !== Number(productId));
                renderWishlist();
                showToast(`"${target?.name || 'Produk'}" dihapus dari wishlist!`);
                window.dispatchEvent(new Event('wishlist:updated'));
            }
        }

        function openTrackingModal(orderId) {
            const o = profileOrders.find(x => x.id === orderId);
            if (!o) return;

            const resi = o.tracking_number || '-';
            const courier = o.shipping_label || 'Ekspedisi';

            document.getElementById('trackingResi').textContent = 'No. Resi: ' + resi;
            document.getElementById('trackingResiSmall').textContent = resi;
            document.getElementById('trackingCourier').textContent = courier;
            document.getElementById('trackingStatusBadge').textContent = 'Dalam Pengiriman';

            // Dummy timeline â€” ganti dengan API tracking nyata saat tersedia
            const dummySteps = [
                { done: true,  time: o.date,                            label: 'Pesanan Dibuat',           desc: 'Pesanan berhasil dibuat dan menunggu konfirmasi pembayaran.' },
                { done: true,  time: '',                                 label: 'Pembayaran Diterima',      desc: 'Pembayaran telah dikonfirmasi oleh sistem.' },
                { done: true,  time: '',                                 label: 'Pesanan Diproses',         desc: 'Pesanan sedang disiapkan oleh tim gudang.' },
                { done: true,  time: '',                                 label: 'Diserahkan ke Kurir',      desc: `Paket telah diserahkan ke ${courier} dengan nomor resi ${resi}.` },
                { done: false, time: '',                                 label: 'Dalam Perjalanan',         desc: 'Paket sedang dalam perjalanan menuju tujuan.' },
                { done: false, time: '',                                 label: 'Tiba di Kota Tujuan',      desc: `Paket tiba di kota ${o.shipping_city || 'tujuan'}.` },
                { done: false, time: '',                                 label: 'Diterima',                 desc: `Paket berhasil diterima oleh ${o.shipping_recipient_name || 'penerima'}.` },
            ];

            document.getElementById('trackingTimeline').innerHTML = dummySteps.map((step, i) => {
                const isLast = i === dummySteps.length - 1;
                const dotColor  = step.done ? 'bg-blue-500' : 'bg-slate-200';
                const lineColor = step.done ? 'bg-blue-200' : 'bg-slate-100';
                const labelColor = step.done ? 'text-slate-800 font-semibold' : 'text-slate-400';
                const descColor  = step.done ? 'text-slate-500' : 'text-slate-300';
                return `
                <div class="flex gap-3">
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-3.5 h-3.5 rounded-full ${dotColor} mt-0.5 ring-4 ring-white shrink-0 z-10 relative"></div>
                        ${!isLast ? `<div class="w-0.5 flex-1 ${lineColor} my-1"></div>` : ''}
                    </div>
                    <div class="pb-5 min-w-0 flex-1">
                        <p class="text-sm ${labelColor}">${step.label}</p>
                        <p class="text-xs ${descColor} mt-0.5 leading-relaxed">${step.desc}</p>
                        ${step.done && step.time ? `<p class="text-[11px] text-slate-400 mt-1">${step.time}</p>` : ''}
                    </div>
                </div>`;
            }).join('');

            const modal = document.getElementById('trackingModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeTrackingModal() {
            const modal = document.getElementById('trackingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

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

        function chooseProfilePhoto() {
            document.getElementById('profileAvatarFile')?.click();
        }

        function removeProfilePhoto() {
            const fileInput = document.getElementById('profileAvatarFile');
            const removeInput = document.getElementById('profileAvatarRemove');
            const preview = document.getElementById('profileAvatarPreview');
            const initial = document.getElementById('profileAvatarInitial');
            if (fileInput) fileInput.value = '';
            if (removeInput) removeInput.value = '1';
            if (preview) {
                preview.src = '';
                preview.classList.add('hidden');
            }
            if (initial) initial.classList.remove('hidden');
        }

        function saveProfile() {
            const form = document.getElementById('profileForm');
            if (!form) return;
            form.submit();
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
                province_id: found.province_id,
                city_id: found.city_id,
                district_id: found.district_id,
                subdistrict_id: found.subdistrict_id,
                province: found.province,
                city: found.city,
                district: found.district,
                subdistrict: found.subdistrict,
                postal_code: found.postal_code,
                destination_id: found.destination_id,
                address_line: found.address_line,
                is_primary: 1,
            });
        }

        function showNewAddressForm() {
            document.getElementById('newAddressForm').classList.remove('hidden');
            document.getElementById('addressFormTitle').textContent = 'Tambah Alamat Baru';
            document.getElementById('addressFormSubmitBtn').textContent = 'Simpan Alamat';
            editingAddressId = null;
            setAddressFormLoading(false);
            document.querySelectorAll('#newAddressForm [data-address-field]').forEach((field) => {
                if (field.type === 'hidden') return;
                field.value = '';
            });
            byField('label').value = 'Rumah';
            byField('phone_country_code').value = '+62';
            byField('is_primary').value = '0';
            byField('province_id').value = '';
            byField('city_id').value = '';
            byField('district_id').value = '';
            byField('subdistrict_id').value = '';
            byField('destination_id').value = '';
            byField('postal_code').value = '';
            roCities = [];
            roDistricts = [];
            roSubdistricts = [];
            fillDatalist('cityList', [], () => '');
            fillDatalist('districtList', [], () => '');
            fillDatalist('subdistrictList', [], () => '');
            _setComboboxLoading('cityInput', false); _setComboboxEnabled('cityInput', false);
            _setComboboxLoading('districtInput', false); _setComboboxEnabled('districtInput', false);
            _setComboboxLoading('subdistrictInput', false); _setComboboxEnabled('subdistrictInput', false);
            if (!roProvinces.length) loadProvinces();
            else _setComboboxEnabled('provinceInput', true);
        }

        function hideNewAddressForm() {
            document.getElementById('newAddressForm').classList.add('hidden');
            editingAddressId = null;
        }

        function saveNewAddress() {
            onSubdistrictChange();
            const payload = {};
            document.querySelectorAll('#newAddressForm [data-address-field]').forEach((field) => {
                payload[field.dataset.addressField] = field.type === 'checkbox' ? (field.checked ? 1 : 0) : field
                    .value;
            });
            if (editingAddressId) {
                payload._method = 'PUT';
                postForm(`{{ url('/profil/addresses') }}/${editingAddressId}`, payload);
                return;
            }
            postForm("{{ route('frontend.profil.addresses.store') }}", payload);
        }

        async function editAddress(btn) {
            const id = btn?.dataset?.id;
            if (!id) return;
            const found = profileAddresses.find((a) => String(a.id) === String(id));
            if (!found) return;

            editingAddressId = String(id);
            document.getElementById('newAddressForm').classList.remove('hidden');
            document.getElementById('addressFormTitle').textContent = 'Ubah Alamat';
            document.getElementById('addressFormSubmitBtn').textContent = 'Update Alamat';
            setAddressFormLoading(true);

            byField('label').value = found.label || 'Rumah';
            byField('recipient_name').value = found.recipient_name || '';
            byField('phone_country_code').value = found.phone_country_code || '+62';
            byField('phone_number').value = found.phone_number || '';
            byField('province').value = found.province || '';
            byField('city').value = found.city || '';
            byField('district').value = found.district || '';
            byField('subdistrict').value = found.subdistrict || '';
            byField('postal_code').value = found.postal_code || '';
            byField('address_line').value = found.address_line || '';
            byField('is_primary').value = found.is_primary ? '1' : '0';
            byField('province_id').value = found.province_id || '';
            byField('city_id').value = found.city_id || '';
            byField('district_id').value = found.district_id || '';
            byField('subdistrict_id').value = found.subdistrict_id || '';
            byField('destination_id').value = found.destination_id || '';

            try {
                if (!roProvinces.length) await loadProvinces();

                if (found.province_id) {
                    const cityRes = await fetch(`${roCitiesUrl}?province_id=${encodeURIComponent(found.province_id)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const cityJson = await cityRes.json();
                    roCities = Array.isArray(cityJson?.data) ? cityJson.data : [];
                    fillDatalist('cityList', roCities, (c) => c.name || c.city_name || '');
                }
                if (found.city_id) {
                    const distRes = await fetch(`${roDistrictsUrl}?city_id=${encodeURIComponent(found.city_id)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const distJson = await distRes.json();
                    roDistricts = Array.isArray(distJson?.data) ? distJson.data : [];
                    fillDatalist('districtList', roDistricts, (d) => d.name || d.district_name || '');
                }
                if (found.district_id) {
                    const subdistRes = await fetch(
                        `${roSubdistrictsUrl}?district_id=${encodeURIComponent(found.district_id)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                    const subdistJson = await subdistRes.json();
                    roSubdistricts = Array.isArray(subdistJson?.data) ? subdistJson.data : [];
                    fillDatalist('subdistrictList', roSubdistricts, (s) => s.name || s.subdistrict_name || '');
                }
            } finally {
                setAddressFormLoading(false);
            }
        }

        function deleteAddress(btn) {
            const id = btn?.dataset?.id;
            if (!id) return;
            if (!confirm('Hapus alamat ini?')) return;
            postForm(`{{ url('/profil/addresses') }}/${id}`, {
                _method: 'DELETE'
            });
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

        document.getElementById('profileAvatarFile')?.addEventListener('change', function () {
            const removeInput = document.getElementById('profileAvatarRemove');
            const preview = document.getElementById('profileAvatarPreview');
            const initial = document.getElementById('profileAvatarInitial');
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file || !preview) return;
            if (removeInput) removeInput.value = '0';
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            if (initial) initial.classList.add('hidden');
        });

        hydrateProfileFromBackend();
        _setupCombobox('provinceInput', 'provinceDropdown');
        _setupCombobox('cityInput', 'cityDropdown');
        _setupCombobox('districtInput', 'districtDropdown');
        _setupCombobox('subdistrictInput', 'subdistrictDropdown');
        document.getElementById('provinceInput')?.addEventListener('change', onProvinceChange);
        document.getElementById('cityInput')?.addEventListener('change', onCityChange);
        document.getElementById('districtInput')?.addEventListener('change', onDistrictChange);
        document.getElementById('subdistrictInput')?.addEventListener('change', onSubdistrictChange);
        setReviewRating(5);
        renderOrders();
        renderWishlist();
        if (initialProfileTab && ['biodata', 'keamanan', 'alamat', 'pesanan', 'wishlist', 'notif'].includes(
                initialProfileTab)) {
            showTab(initialProfileTab);
        }

        // Navbar mega dropdown
        function toggleCategoryMenu(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('category-dropdown');
            if (!menu) return;
            menu.classList.toggle('hidden');
        }
        const megaCategoryData = {
            'rumah-tangga': [{
                    title: 'Baut',
                    items: ['Baut Hex', 'Baut L', 'Baut Roofing', 'Baut Stainless']
                },
                {
                    title: 'Mur',
                    items: ['Mur Hex', 'Mur Nyloc', 'Mur Flange', 'Mur Kuping']
                },
                {
                    title: 'Sekrup',
                    items: ['Sekrup Kayu', 'Sekrup Gypsum', 'Sekrup SDS', 'Sekrup Mesin']
                },
                {
                    title: 'Ring & Washer',
                    items: ['Ring Plat', 'Ring Per', 'Washer Stainless', 'Washer Galvanis']
                }
            ],
            'fashion-pria': [{
                    title: 'Dynabolt',
                    items: ['Dynabolt M8', 'Dynabolt M10', 'Dynabolt M12', 'Drop In Anchor']
                },
                {
                    title: 'Anchor',
                    items: ['Fischer', 'Chemical Anchor', 'Sleeve Anchor', 'Anchor Bolt']
                },
                {
                    title: 'Tools',
                    items: ['Kunci Pas', 'Kunci L', 'Obeng', 'Tang']
                },
                {
                    title: 'Mata Bor',
                    items: ['Mata Bor Besi', 'Mata Bor Beton', 'Hole Saw', 'Countersink']
                }
            ],
            'fashion-wanita': [{
                    title: 'Paku',
                    items: ['Paku Beton', 'Paku Kayu', 'Paku Rivet', 'Paku Tembak']
                },
                {
                    title: 'Klem',
                    items: ['Klem Pipa', 'U Bolt', 'Clamp Stainless', 'Klem Selang']
                },
                {
                    title: 'Bracket',
                    items: ['Bracket L', 'Bracket Rak', 'Plat Sambung', 'Engsel Besi']
                },
                {
                    title: 'Tools',
                    items: ['Lem Besi', 'Sealant', 'Anti Karat', 'Threadlocker']
                }
            ],
            'elektronik': [{
                    title: 'Baut Mesin',
                    items: ['Baut M4', 'Baut M5', 'Baut M6', 'Baut M8']
                },
                {
                    title: 'Material',
                    items: ['Baja', 'Stainless 304', 'Galvanis', 'Kuningan']
                },
                {
                    title: 'Grade',
                    items: ['Grade 4.8', 'Grade 8.8', 'Grade 10.9', 'Grade 12.9']
                },
                {
                    title: 'Tools',
                    items: ['Box Baut', 'Rak Komponen', 'Label SKU', 'Organizer']
                }
            ],
            'kecantikan': [{
                    title: 'Abrasive',
                    items: ['Mata Gerinda', 'Amplas', 'Cutting Wheel', 'Flap Disc']
                },
                {
                    title: 'Safety',
                    items: ['Sarung Tangan', 'Kacamata Safety', 'Masker Kerja', 'Ear Plug']
                },
                {
                    title: 'Lem & Sealant',
                    items: ['Lem Besi', 'Lem PVC', 'Sealant', 'Epoxy']
                },
                {
                    title: 'Anti Karat',
                    items: ['WD Spray', 'Rust Remover', 'Grease', 'Lubricant']
                }
            ],
            'olahraga': [{
                    title: 'Perkakas Tangan',
                    items: ['Palu', 'Tang', 'Obeng', 'Kunci Inggris']
                },
                {
                    title: 'Perkakas Ukur',
                    items: ['Meteran', 'Jangka Sorong', 'Waterpass', 'Siku Ukur']
                },
                {
                    title: 'Power Tool',
                    items: ['Bor Listrik', 'Gerinda', 'Impact Driver', 'Blower']
                },
                {
                    title: 'Consumable',
                    items: ['Mata Bor', 'Mata Gerinda', 'Amplas', 'Kabel Ties']
                }
            ],
            'ibu-bayi': [{
                    title: 'Klem & Clamp',
                    items: ['Klem Pipa', 'Hose Clamp', 'U Bolt', 'Clamp Stainless']
                },
                {
                    title: 'Bracket',
                    items: ['Bracket L', 'Bracket U', 'Plat Sambung', 'Dudukan Rak']
                },
                {
                    title: 'Rivet',
                    items: ['Paku Rivet', 'Rivet Nut', 'Tang Rivet', 'Blind Rivet']
                },
                {
                    title: 'Accessories',
                    items: ['Cable Tie', 'Fisher', 'Spacer', 'Insert Nut']
                }
            ],
            'makanan-minuman': [{
                    title: 'Chemical',
                    items: ['Threadlocker', 'Sealant', 'Epoxy', 'Cleaner']
                },
                {
                    title: 'Lem',
                    items: ['Lem Besi', 'Lem Kayu', 'Lem PVC', 'Lem Serbaguna']
                },
                {
                    title: 'Pelumas',
                    items: ['Grease', 'Oli Serbaguna', 'Anti Karat', 'Contact Cleaner']
                },
                {
                    title: 'Packing',
                    items: ['Lakban', 'Stretch Film', 'Bubble Wrap', 'Kardus Sparepart']
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

        // Order cancel modal
        const cancelOrderUrlTemplate = @json(route('frontend.checkout.midtrans.cancel', ['orderId' => '__ORDER_ID__']));
        const cancelCsrf = @json(csrf_token());
        let cancelTargetOrderId = null;

        function openOrderCancelModal(orderId) {
            cancelTargetOrderId = orderId;
            document.querySelectorAll('input[name="orderCancelReason"]').forEach(r => r.checked = false);
            document.getElementById('orderCancelReasonOther').classList.add('hidden');
            document.getElementById('orderCancelReasonOther').value = '';
            document.getElementById('orderCancelError').classList.add('hidden');
            const m = document.getElementById('orderCancelModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeOrderCancelModal() {
            cancelTargetOrderId = null;
            const m = document.getElementById('orderCancelModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.querySelectorAll('input[name="orderCancelReason"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const other = document.getElementById('orderCancelReasonOther');
                if (this.value === 'Alasan lainnya') { other.classList.remove('hidden'); other.focus(); }
                else other.classList.add('hidden');
            });
        });

        document.getElementById('orderCancelConfirmBtn')?.addEventListener('click', async function() {
            const selected = document.querySelector('input[name="orderCancelReason"]:checked');
            const errEl = document.getElementById('orderCancelError');
            if (!selected) { errEl.classList.remove('hidden'); return; }
            errEl.classList.add('hidden');

            let reason = selected.value;
            if (reason === 'Alasan lainnya') {
                const other = document.getElementById('orderCancelReasonOther').value.trim();
                reason = other || 'Alasan lainnya';
            }

            this.disabled = true;
            this.textContent = 'Membatalkan...';
            try {
                const url = cancelOrderUrlTemplate.replace('__ORDER_ID__', cancelTargetOrderId);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cancelCsrf },
                    body: JSON.stringify({ cancel_reason: reason }),
                });
                const json = await res.json();
                if (json.ok || json.transaction_status === 'cancel') {
                    closeOrderCancelModal();
                    location.reload();
                }
            } catch(e) {}
            this.disabled = false;
            this.textContent = 'Ya, Batalkan';
        });

        document.getElementById('orderCancelModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeOrderCancelModal();
        });
    </script>
@endsection

