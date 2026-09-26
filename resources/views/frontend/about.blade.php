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
        .about-page { background: var(--ec-warm-50); color: var(--ec-graphite-900); }
        .about-hero { min-height: 36rem; display: flex; align-items: center; }
        .about-hero__grid { position: absolute; inset: 0; opacity: .12; background-image: linear-gradient(rgb(255 255 255 / .45) 1px, transparent 1px), linear-gradient(90deg, rgb(255 255 255 / .45) 1px, transparent 1px); background-size: 56px 56px; mask-image: linear-gradient(90deg, transparent 5%, #000 58%); }
        .about-hero__layout { position: relative; display: grid; grid-template-columns: minmax(0,1.25fr) minmax(18rem,.75fr); align-items: center; gap: clamp(3rem,8vw,7rem); padding-block: clamp(4rem,8vw,7rem); }
        .about-back { display: inline-flex; align-items: center; gap: .45rem; margin-bottom: 2rem; color: #dce8f7; font-size: .75rem; font-weight: 700; }
        .about-back:hover { color: #fff; }
        .about-kicker { color: var(--ec-secondary-500); font-size: .7rem; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
        .about-hero h1 { max-width: 47rem; margin-top: .9rem; color: #fff; font-size: clamp(2.75rem,6vw,5.4rem); font-weight: 800; line-height: .98; letter-spacing: -.045em; }
        .about-hero h1 span { color: #bdd4f2; }
        .about-hero__lead { max-width: 38rem; margin-top: 1.5rem; color: #dce8f7; font-size: clamp(1rem,1.5vw,1.15rem); line-height: 1.75; }
        .about-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .about-btn-light { border-color: rgb(255 255 255 / .38); background: rgb(255 255 255 / .08); color: #fff; }
        .about-btn-light:hover { background: rgb(255 255 255 / .16); }
        .about-blueprint { position: relative; min-height: 18rem; overflow: hidden; border: 1px solid rgb(255 255 255 / .3); border-radius: var(--ec-radius-lg); background: rgb(7 30 61 / .38); padding: 1.5rem; box-shadow: inset 0 1px 0 rgb(255 255 255 / .1); backdrop-filter: blur(4px); }
        .about-blueprint::after { position: absolute; right: -3.5rem; bottom: -3.5rem; width: 10rem; height: 10rem; border: 1px solid rgb(255 255 255 / .18); border-radius: 50%; content: ''; }
        .about-blueprint__head { display: flex; justify-content: space-between; color: #9eb8d8; font-size: .6rem; font-weight: 700; letter-spacing: .16em; }
        .about-blueprint strong { display: block; margin-top: 3.5rem; color: #fff; font-size: clamp(4.5rem,8vw,7rem); font-weight: 800; line-height: .85; letter-spacing: -.06em; }
        .about-blueprint > p { display: flex; align-items: center; gap: .65rem; margin-top: 1.4rem; color: #dce8f7; font-size: .62rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .about-blueprint > p span { width: 3px; height: 3px; border-radius: 50%; background: var(--ec-secondary-500); }
        .about-blueprint__lines { display: grid; gap: .45rem; margin-top: 2.5rem; }
        .about-blueprint__lines i { display: block; height: 1px; background: rgb(255 255 255 / .18); }
        .about-blueprint__lines i:nth-child(2) { width: 72%; }
        .about-blueprint__lines i:nth-child(3) { width: 46%; }

        .about-section { padding-block: clamp(4.5rem,8vw,7.5rem); }
        .about-story { display: grid; grid-template-columns: minmax(12rem,.7fr) minmax(0,2.3fr); gap: clamp(2rem,6vw,6rem); }
        .about-section-label { display: flex; align-items: center; gap: .75rem; height: fit-content; }
        .about-section-label .ec-section-marker { min-height: 1.35rem; }
        .about-section-label p { color: var(--ec-steel-700); font-size: .72rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        .about-story h2 { max-width: 50rem; color: var(--ec-graphite-950); font-size: clamp(2rem,4vw,3.6rem); font-weight: 800; line-height: 1.08; letter-spacing: -.035em; }
        .about-story__copy { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 2rem; max-width: 54rem; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--ec-steel-200); }
        .about-story__copy p { color: var(--ec-steel-700); line-height: 1.8; }
        .about-story__copy strong { color: var(--ec-graphite-900); }
        .about-values { padding-block: clamp(4rem,7vw,6rem); border-top: 1px solid var(--ec-steel-200); background: #fff; }
        .about-values__heading { display: flex; align-items: center; justify-content: space-between; gap: 2rem; }
        .about-values__heading > p { max-width: 22rem; color: var(--ec-steel-700); font-size: .875rem; line-height: 1.6; }
        .about-values__grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 1rem; margin-top: 2rem; }
        .about-value { position: relative; min-height: 18rem; padding: 1.5rem; transition: border-color var(--ec-motion-base), transform var(--ec-motion-base), box-shadow var(--ec-motion-base); }
        .about-value:hover { border-color: var(--ec-steel-300); transform: translateY(-2px); box-shadow: 0 12px 30px rgb(17 24 32 / .07); }
        .about-value > span { position: absolute; top: 1.5rem; right: 1.5rem; color: var(--ec-steel-300); font-size: .7rem; font-weight: 800; }
        .about-value > i { display: grid; width: 2.75rem; height: 2.75rem; place-items: center; border-radius: var(--ec-radius-sm); background: var(--ec-primary-100); color: var(--ec-primary-700); font-size: 1.15rem; }
        .about-value h3 { margin-top: 4rem; color: var(--ec-graphite-950); font-size: 1.15rem; font-weight: 800; }
        .about-value p { margin-top: .75rem; color: var(--ec-steel-700); font-size: .875rem; line-height: 1.7; }
        .about-cta { display: flex; align-items: center; justify-content: space-between; gap: 2rem; margin-top: 1rem; border-color: #c8dafa; background: linear-gradient(110deg,#edf5ff,#fff); padding: clamp(1.5rem,4vw,2.5rem); }
        .about-cta h2 { max-width: 42rem; margin-top: .45rem; color: var(--ec-primary-900); font-size: clamp(1.4rem,3vw,2.25rem); font-weight: 800; line-height: 1.15; letter-spacing: -.025em; }
        .about-cta .ec-btn { flex: 0 0 auto; }

        @media (max-width: 767px) {
            .about-hero { min-height: auto; }
            .about-hero__layout { grid-template-columns: 1fr; gap: 2.5rem; padding-block: 3.5rem; }
            .about-hero h1 { font-size: clamp(2.65rem,13vw,4rem); }
            .about-blueprint { min-height: 15rem; }
            .about-story { grid-template-columns: 1fr; gap: 2.25rem; }
            .about-story__copy { grid-template-columns: 1fr; gap: .75rem; }
            .about-values__heading, .about-cta { align-items: flex-start; flex-direction: column; }
            .about-values__heading { display: flex; }
            .about-values__grid { grid-template-columns: 1fr; }
            .about-value { min-height: 15rem; }
            .about-cta .ec-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: no-preference) {
            .about-hero__layout > * { animation: about-enter .5s both; }
            .about-hero__layout > :last-child { animation-delay: .12s; }
            @keyframes about-enter { from { opacity: 0; transform: translateY(12px); } }
        }
    </style>
@endsection
