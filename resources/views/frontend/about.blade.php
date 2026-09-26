@extends('layouts.user')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . ($appStoreName ?? 'BOQ'))
@section('meta_description', $page->meta_description ?: $page->excerpt)
@section('canonical', $page->public_url)
@section('og_type', 'website')

@section('content')
    @include('partials.navbar-user')

    <main class="about-page">
        <section class="ec-page-hero about-hero">
            <div class="about-hero__grid" aria-hidden="true"></div>
            <div class="ec-container about-hero__layout">
                <div class="relative z-10">
                    <a href="{{ route('frontend.index') }}" class="about-back">
                        <i class="fi fi-rr-arrow-small-left" aria-hidden="true"></i>
                        Beranda
                    </a>
                    <p class="about-kicker">Tentang {{ $appStoreName ?? 'BOQ' }}</p>
                    <h1>Kebutuhan teknik,<br><span>tersusun lebih jelas.</span></h1>
                    <p class="about-hero__lead">{{ $page->excerpt }}</p>
                    <div class="about-actions">
                        <a href="{{ route('frontend.kategori') }}" class="ec-btn ec-btn-primary">
                            Jelajahi Produk
                            <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}" class="ec-btn about-btn-light">Hubungi Kami</a>
                    </div>
                </div>

                <div class="about-blueprint" aria-label="Ringkasan BOQ">
                    <div class="about-blueprint__head">
                        <span>PROJECT SUPPLY / ID</span>
                        <span>01</span>
                    </div>
                    <strong>BOQ</strong>
                    <p>Barang <span></span> Kuantitas <span></span> Spesifikasi</p>
                    <div class="about-blueprint__lines" aria-hidden="true"><i></i><i></i><i></i></div>
                </div>
            </div>
        </section>

        <section class="about-section">
            <div class="ec-container about-story">
                <div class="about-section-label">
                    <span class="ec-section-marker" aria-hidden="true"></span>
                    <p>Apa itu BOQ?</p>
                </div>
                <div class="about-story__content">
                    <h2>Dari daftar kebutuhan menjadi pekerjaan yang terencana.</h2>
                    <div class="about-story__copy">
                        <p><strong>BOQ</strong> adalah singkatan dari <em>Bill of Quantities</em>: daftar terukur yang merangkum jenis pekerjaan, material, spesifikasi, dan jumlah yang dibutuhkan dalam sebuah proyek.</p>
                        <p>Semangatnya sederhana—setiap kebutuhan harus mudah dipahami dan dicari. Karena itu {{ $appStoreName ?? 'BOQ' }} membantu tim lapangan, pengadaan, dan pelaku usaha menemukan produk teknik dengan informasi yang lebih rapi.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-values">
            <div class="ec-container">
                <div class="about-values__heading">
                    <div class="about-section-label">
                        <span class="ec-section-marker" aria-hidden="true"></span>
                        <p>Cara kami membantu</p>
                    </div>
                    <p>Tiga hal mendasar untuk mempermudah proses pengadaan.</p>
                </div>
                <div class="about-values__grid">
                    <article class="ec-surface about-value">
                        <span>01</span>
                        <i class="fi fi-rr-document-signed" aria-hidden="true"></i>
                        <h3>Spesifikasi terbaca</h3>
                        <p>Informasi penting ditampilkan dengan lugas agar produk lebih mudah dicocokkan dengan kebutuhan.</p>
                    </article>
                    <article class="ec-surface about-value">
                        <span>02</span>
                        <i class="fi fi-rr-search-alt" aria-hidden="true"></i>
                        <h3>Pilihan terarah</h3>
                        <p>Kategori dan pencarian memperpendek jarak dari daftar belanja menuju barang yang tepat.</p>
                    </article>
                    <article class="ec-surface about-value">
                        <span>03</span>
                        <i class="fi fi-rr-headset" aria-hidden="true"></i>
                        <h3>Bantuan manusia</h3>
                        <p>Saat detail teknis perlu dipastikan, tim kami siap diajak bicara sebelum pesanan dibuat.</p>
                    </article>
                </div>

                <div class="ec-surface about-cta">
                    <div>
                        <p class="about-kicker">Siap mencari kebutuhan Anda?</p>
                        <h2>Mulai dari satu komponen sampai kebutuhan proyek.</h2>
                    </div>
                    <a href="{{ route('frontend.kategori') }}" class="ec-btn ec-btn-primary">Lihat Katalog <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </section>
    </main>
@endsection

@section('style')
    <style>
        /* ABOUT_THEME_STYLES */
    </style>
@endsection
