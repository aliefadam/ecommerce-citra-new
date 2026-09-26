@extends('layouts.user')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . ($appStoreName ?? 'BOQ'))
@section('meta_description', $page->meta_description ?: $page->excerpt)
@section('canonical', $page->public_url)
@section('og_type', 'website')

@section('content')
    @include('partials.navbar-user')

    <main class=about-boq>
        <section class="about-boq__hero">
            <div class="about-boq__grid" aria-hidden="true"></div>
            <div class="about-boq__wrap about-boq__hero-layout">
                <div class="about-boq__intro">
                    <p class="about-boq__eyebrow"><span>01</span> Tentang kami</p>
                    <h1>Pengadaan teknik,<br><em>tanpa kerumitan.</em></h1>
                    <p class="about-boq__lead">{{ $page->excerpt }}</p>
                    <div class="about-boq__actions">
                        <a href="{{ route('frontend.kategori') }}" class="about-boq__button">Lihat produk <span aria-hidden="true">&rarr;</span></a>
                        <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}" class="about-boq__text-link">Hubungi tim kami</a>
                    </div>
                </div>
                <div class="about-boq__mark" aria-label="BOQ, kebutuhan proyek tersusun jelas">
                    <span class="about-boq__mark-label">Built for the field</span>
                    <strong>BOQ</strong>
                    <p>Barang. Kuantitas.<br>Spesifikasi. Jelas.</p>
                    <span class="about-boq__mark-index">EST. / ID</span>
                </div>
            </div>
        </section>

        <section class="about-boq__statement">
            <div class="about-boq__wrap about-boq__statement-layout">
                <p class="about-boq__section-number">02 / Apa itu BOQ?</p>
                <div>
                    <h2>Daftar kebutuhan yang mengubah rencana menjadi pekerjaan nyata.</h2>
                    <div class="about-boq__copy">
                        <p><strong>BOQ</strong> adalah singkatan dari <em>Bill of Quantities</em>: daftar terukur yang merangkum jenis pekerjaan, material, spesifikasi, dan jumlah yang dibutuhkan dalam sebuah proyek.</p>
                        <p>Bagi kami, semangat itu sederhana: setiap kebutuhan harus mudah dipahami dan dicari. Karena itu {{ $appStoreName ?? 'BOQ' }} hadir untuk membantu tim lapangan, pengadaan, dan pelaku usaha menemukan produk teknik dengan informasi yang lebih rapi.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-boq__principles">
            <div class="about-boq__wrap">
                <div class="about-boq__principles-heading">
                    <p class="about-boq__section-number">03 / Cara kami bekerja</p>
                    <p>Hal mendasar yang kami jaga dalam setiap proses.</p>
                </div>
                <div class="about-boq__principles-grid">
                    <article><span>01</span><h3>Spesifikasi terbaca</h3><p>Informasi penting ditampilkan dengan lugas agar produk lebih mudah dicocokkan dengan kebutuhan.</p></article>
                    <article><span>02</span><h3>Pilihan terarah</h3><p>Kategori dan pencarian membantu memperpendek jarak dari daftar belanja ke barang yang tepat.</p></article>
                    <article><span>03</span><h3>Bantuan manusia</h3><p>Ketika detail teknis perlu dipastikan, tim kami siap diajak bicara sebelum pesanan dibuat.</p></article>
                </div>
            </div>
        </section>

        <section class="about-boq__closing">
            <div class="about-boq__wrap about-boq__closing-layout">
                <p>Mulai dari satu komponen<br>sampai kebutuhan proyek.</p>
                <a href="{{ route('frontend.kategori') }}">Temukan kebutuhannya <span aria-hidden="true">&nearr;</span></a>
            </div>
        </section>
    </main>
@endsection

@section('style')
    <style>
        .about-boq { --ink: #0c1b31; --paper: #f3f0e8; --orange: #f26522; color: var(--ink); background: var(--paper); }
        .about-boq__wrap { width: min(1180px, calc(100% - 2rem)); margin-inline: auto; }
        .about-boq__hero { position: relative; overflow: hidden; min-height: 660px; background: var(--ink); color: #fff; }
        .about-boq__grid { position: absolute; inset: 0; opacity: .13; background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px); background-size: 72px 72px; mask-image: linear-gradient(to right, transparent, #000 35%); }
        .about-boq__hero-layout { position: relative; display: grid; grid-template-columns: minmax(0,1.45fr) minmax(280px,.55fr); gap: clamp(3rem,8vw,8rem); align-items: center; min-height: 660px; padding-block: 5rem; }
        .about-boq__eyebrow, .about-boq__section-number { font-size: .7rem; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; }
        .about-boq__eyebrow { color: #ccd4df; }
        .about-boq__eyebrow span { margin-right: .8rem; color: var(--orange); }
        .about-boq__intro h1 { max-width: 800px; margin-top: 1.5rem; font-family: Georgia,'Times New Roman',serif; font-size: clamp(3.25rem,7.7vw,7rem); font-weight: 400; line-height: .92; letter-spacing: -.055em; }
        .about-boq__intro h1 em { color: #f49a6f; font-weight: 400; }
        .about-boq__lead { max-width: 620px; margin-top: 2rem; color: #c8d0da; font-size: clamp(1rem,1.6vw,1.22rem); line-height: 1.75; }
        .about-boq__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem; margin-top: 2.25rem; }
        .about-boq__button { display: inline-flex; align-items: center; gap: 2rem; padding: .95rem 1.2rem; background: var(--orange); color: #fff; font-size: .85rem; font-weight: 800; }
        .about-boq__button:hover { background: #d95010; }
        .about-boq__text-link { color: #fff; border-bottom: 1px solid #7a8798; padding-bottom: .2rem; font-size: .85rem; font-weight: 700; }

        .about-boq__mark { position: relative; min-height: 330px; padding: 2rem; border: 1px solid rgba(255,255,255,.32); display: flex; flex-direction: column; justify-content: center; }
        .about-boq__mark::before { content: ''; position: absolute; width: 70px; height: 6px; top: -3px; right: 2rem; background: var(--orange); }
        .about-boq__mark-label, .about-boq__mark-index { position: absolute; font-size: .58rem; letter-spacing: .2em; text-transform: uppercase; color: #9ca9b9; }
        .about-boq__mark-label { top: 1.5rem; left: 2rem; }
        .about-boq__mark-index { right: 2rem; bottom: 1.5rem; }
        .about-boq__mark strong { font-family: Georgia,'Times New Roman',serif; font-size: clamp(4rem,8vw,7rem); font-weight: 400; line-height: 1; }
        .about-boq__mark p { margin-top: 1.25rem; padding-left: 3px; color: #bec7d2; font-size: .75rem; line-height: 1.7; letter-spacing: .12em; text-transform: uppercase; }
        .about-boq__statement { padding-block: clamp(5rem,10vw,9rem); }
        .about-boq__statement-layout { display: grid; grid-template-columns: 1fr 3fr; gap: 2rem; }
        .about-boq__section-number { color: #6d7783; }
        .about-boq__statement h2 { max-width: 850px; font-family: Georgia,'Times New Roman',serif; font-size: clamp(2.2rem,5vw,4.7rem); font-weight: 400; line-height: 1.05; letter-spacing: -.04em; }
        .about-boq__copy { display: grid; grid-template-columns: repeat(2,1fr); gap: 2.5rem; max-width: 850px; margin-top: 3.5rem; padding-top: 2rem; border-top: 1px solid #b9b6ae; }
        .about-boq__copy p { color: #46515e; line-height: 1.8; }
        .about-boq__copy strong { color: var(--ink); }

        .about-boq__principles { padding-block: 5rem 6rem; background: #fff; }
        .about-boq__principles-heading { display: flex; justify-content: space-between; gap: 2rem; padding-bottom: 1.5rem; border-bottom: 2px solid var(--ink); }
        .about-boq__principles-heading > p:last-child { max-width: 330px; color: #657080; font-size: .85rem; }
        .about-boq__principles-grid { display: grid; grid-template-columns: repeat(3,1fr); }
        .about-boq__principles article { min-height: 280px; padding: 2.5rem 2rem 2rem 0; border-right: 1px solid #d7dce2; }
        .about-boq__principles article + article { padding-left: 2rem; }
        .about-boq__principles article:last-child { border-right: 0; }
        .about-boq__principles article span { color: var(--orange); font: 400 2rem/1 Georgia,serif; }
        .about-boq__principles h3 { margin-top: 4rem; font-family: Georgia,'Times New Roman',serif; font-size: 1.65rem; font-weight: 400; }
        .about-boq__principles article p { max-width: 290px; margin-top: .8rem; color: #657080; font-size: .9rem; line-height: 1.7; }
        .about-boq__closing { padding-block: clamp(4rem,8vw,7rem); background: var(--orange); color: #fff; }
        .about-boq__closing-layout { display: flex; align-items: end; justify-content: space-between; gap: 2rem; }
        .about-boq__closing p { font-family: Georgia,'Times New Roman',serif; font-size: clamp(2.3rem,5vw,4.8rem); line-height: 1.02; letter-spacing: -.04em; }
        .about-boq__closing a { display: flex; gap: 2rem; min-width: 250px; justify-content: space-between; padding-bottom: .65rem; border-bottom: 1px solid rgba(255,255,255,.65); font-size: .85rem; font-weight: 800; }

        @media (max-width: 767px) {
            .about-boq__hero, .about-boq__hero-layout { min-height: auto; }
            .about-boq__hero-layout { grid-template-columns: 1fr; gap: 3rem; padding-block: 4rem; }
            .about-boq__intro h1 { font-size: clamp(3rem,15vw,4.7rem); }
            .about-boq__mark { min-height: 260px; }
            .about-boq__statement-layout, .about-boq__copy { grid-template-columns: 1fr; }
            .about-boq__statement-layout { gap: 2.5rem; }
            .about-boq__copy { gap: .5rem; margin-top: 2.5rem; }
            .about-boq__principles-heading { display: block; }
            .about-boq__principles-heading > p:last-child { margin-top: 1rem; }
            .about-boq__principles-grid { grid-template-columns: 1fr; }
            .about-boq__principles article, .about-boq__principles article + article { min-height: auto; padding: 2rem 0; border-right: 0; border-bottom: 1px solid #d7dce2; }
            .about-boq__principles article:last-child { border-bottom: 0; }
            .about-boq__principles h3 { margin-top: 2.5rem; }
            .about-boq__closing-layout { display: block; }
            .about-boq__closing a { margin-top: 2.5rem; }
        }
        @media (prefers-reduced-motion: no-preference) {
            .about-boq__intro > *, .about-boq__mark { animation: about-reveal .65s both; }
            .about-boq__intro > :nth-child(2) { animation-delay: .08s; }
            .about-boq__intro > :nth-child(3) { animation-delay: .16s; }
            .about-boq__intro > :nth-child(4), .about-boq__mark { animation-delay: .24s; }
            @keyframes about-reveal { from { opacity: 0; transform: translateY(18px); } }
        }
    </style>
@endsection
