@extends('layouts.user')

@section('title', 'Pusat Bantuan - ' . ($appStoreName ?? config('app.name')))
@section('meta_description', $page->meta_description ?: 'Temukan panduan pemesanan, pembayaran, pengiriman, retur, akun, dan kebutuhan proyek di '.$appStoreName.'.')
@section('canonical', $page->public_url)
@section('og_image', asset('imgs/help-center/hero-help-center.webp'))

@php
    $categories = [
        'pemesanan' => ['label' => 'Cara Pemesanan', 'icon' => 'shopping-cart'],
        'pembayaran' => ['label' => 'Pembayaran', 'icon' => 'credit-card'],
        'pengiriman' => ['label' => 'Pengiriman', 'icon' => 'truck-side'],
        'retur' => ['label' => 'Retur & Komplain', 'icon' => 'box-open-full'],
        'akun' => ['label' => 'Akun & Keamanan', 'icon' => 'user'],
        'aplikasi' => ['label' => 'Install Aplikasi', 'icon' => 'download'],
        'produk' => ['label' => 'Produk & Stok', 'icon' => 'box'],
        'penawaran' => ['label' => 'Penawaran Proyek', 'icon' => 'document'],
        'tentang' => ['label' => 'Tentang '.$appStoreName, 'icon' => 'info'],
    ];

    $faqs = [
        ['category' => 'pemesanan', 'question' => 'Bagaimana cara melakukan pemesanan?', 'answer' => 'Cari produk berdasarkan nama, SKU, ukuran, atau material. Pilih varian dan jumlah yang diperlukan, lalu tambahkan ke keranjang. Periksa kembali produk, perusahaan penjual, alamat, dan pilihan pengiriman sebelum melanjutkan checkout.'],
        ['category' => 'pemesanan', 'question' => 'Apa yang perlu diperiksa sebelum checkout?', 'answer' => 'Pastikan SKU, spesifikasi, varian, satuan jual, jumlah, perusahaan penjual, alamat penerima, dan pilihan pengiriman sudah sesuai kebutuhan Anda.'],
        ['category' => 'pembayaran', 'question' => 'Metode pembayaran apa saja yang tersedia?', 'answer' => 'Metode pembayaran yang dapat digunakan ditampilkan pada proses checkout. Pilih salah satu metode yang tersedia dan ikuti instruksi pembayaran pada pesanan Anda.'],
        ['category' => 'pembayaran', 'question' => 'Bagaimana jika pembayaran belum terverifikasi?', 'answer' => 'Periksa kembali status pesanan dan pastikan instruksi pembayaran telah diikuti. Jika Anda menggunakan pembayaran manual, unggah bukti pembayaran pada alur pesanan agar dapat diverifikasi.'],
        ['category' => 'pengiriman', 'question' => 'Bagaimana cara mengecek status pesanan?', 'answer' => 'Buka halaman Lacak Pesanan, lalu masukkan nomor pesanan dan data verifikasi yang diminta. Status terbaru akan ditampilkan setelah data berhasil diverifikasi.'],
        ['category' => 'pengiriman', 'question' => 'Di mana saya dapat melihat nomor resi?', 'answer' => 'Nomor resi akan tersedia pada detail atau pelacakan pesanan setelah pengiriman diproses dan informasi resi telah diterbitkan.'],
        ['category' => 'retur', 'question' => 'Bagaimana proses retur atau komplain produk?', 'answer' => 'Siapkan nomor pesanan, foto produk, dan penjelasan kendala. Hubungi kanal dukungan resmi '.$appStoreName.' agar tim dapat memeriksa kelayakan dan bukti yang diperlukan untuk proses berikutnya.'],
        ['category' => 'akun', 'question' => 'Bagaimana menjaga keamanan akun saya?', 'answer' => 'Gunakan kata sandi yang kuat, jangan membagikan kredensial atau kode verifikasi, dan pastikan Anda mengakses '.$appStoreName.' melalui alamat situs resmi.'],
        [
            'id' => 'install-aplikasi',
            'category' => 'aplikasi',
            'question' => 'Bagaimana cara menginstal aplikasi '.$appStoreName.' di Android atau iPhone?',
            'answer' => $appStoreName.' dapat dipasang dari browser tanpa mengunduh aplikasi melalui Play Store atau App Store.',
            'platforms' => [
                'Android / tablet' => [
                    'Buka situs '.$appStoreName.' menggunakan Google Chrome.',
                    'Tekan Install Aplikasi pada bagian atas halaman. Jika dialog belum muncul, buka menu Chrome lalu pilih Install app atau Tambahkan ke layar utama.',
                    'Tekan Install untuk mengonfirmasi. Ikon aplikasi akan muncul di layar utama perangkat.',
                ],
                'iPhone / iPad' => [
                    'Buka situs '.$appStoreName.' menggunakan Safari.',
                    'Tekan tombol Share atau Bagikan pada toolbar Safari.',
                    'Pilih Add to Home Screen atau Tambahkan ke Layar Utama.',
                    'Tekan Add atau Tambah untuk menyelesaikan instalasi.',
                ],
            ],
        ],
        ['category' => 'produk', 'question' => 'Bagaimana memastikan spesifikasi dan stok produk?', 'answer' => 'Periksa nama, SKU, ukuran, material, varian, satuan jual, dan informasi stok pada halaman produk. Konsultasikan dengan tim '.$appStoreName.' bila spesifikasi teknis masih perlu dipastikan.'],
        ['category' => 'penawaran', 'question' => 'Apakah saya bisa meminta penawaran untuk pembelian dalam jumlah besar?', 'answer' => 'Ya. Gunakan tombol Minta Penawaran atau hubungi tim '.$appStoreName.' melalui kanal resmi untuk menyampaikan daftar produk, jumlah, serta spesifikasi kebutuhan proyek Anda.'],
        ['category' => 'tentang', 'question' => 'Produk apa yang tersedia di '.$appStoreName.'?', 'answer' => $appStoreName.' menyediakan kebutuhan fastener, fitting, perlengkapan teknik, dan industrial supply untuk kebutuhan operasional maupun pengadaan proyek.'],
    ];

    $quoteUrl = !empty($appStoreSettings['social_whatsapp'])
        ? $appStoreSettings['social_whatsapp']
        : '#hubungi-kami';
    $quoteIsExternal = !empty($appStoreSettings['social_whatsapp']);
    $configuredEmail = (string) config('mail.from.address');
    $supportEmail = $configuredEmail !== '' && !str_starts_with(strtolower($configuredEmail), 'noreply@') && !str_ends_with(strtolower($configuredEmail), '@example.com')
        ? $configuredEmail
        : null;
@endphp

@section('content')
    @include('partials.navbar-user')

    <main class="help-center" data-help-center>
        <nav class="help-breadcrumb ec-container" aria-label="Breadcrumb">
            <a href="{{ route('frontend.index') }}"><i class="fi fi-rr-home" aria-hidden="true"></i><span>Beranda</span></a>
            <i class="fi fi-rr-angle-small-right" aria-hidden="true"></i>
            <span aria-current="page">Pusat Bantuan</span>
        </nav>

        <section class="help-hero" aria-labelledby="helpHeroTitle">
            <img src="{{ asset('imgs/help-center/hero-help-center.webp') }}" alt="" width="2048" height="768" fetchpriority="high" aria-hidden="true">
            <div class="help-hero-shade" aria-hidden="true"></div>
            <div class="help-hero-content ec-container">
                <h1 id="helpHeroTitle">Pusat Bantuan</h1>
                <p class="help-hero-kicker">Ada yang bisa kami bantu?</p>
                <p class="help-hero-copy">Temukan jawaban seputar pemesanan, pembayaran, pengiriman, hingga kebutuhan penawaran proyek.</p>
                <form class="help-search" role="search" data-help-search-form>
                    <label class="sr-only" for="helpSearch">Cari pertanyaan atau topik bantuan</label>
                    <i class="fi fi-rr-search" aria-hidden="true"></i>
                    <input id="helpSearch" type="search" placeholder="Cari pertanyaan atau topik bantuan..." autocomplete="off" data-help-search>
                    <button type="submit">Cari</button>
                </form>
            </div>
        </section>

        <section class="help-main ec-container" aria-labelledby="faqHeading">
            <aside class="help-sidebar" aria-labelledby="categoryHeading">
                <h2 id="categoryHeading">Kategori Bantuan</h2>
                <div class="help-mobile-category">
                    <label for="helpCategorySelect">Pilih kategori bantuan</label>
                    <select id="helpCategorySelect" data-help-category-select>
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $key => $category)
                            <option value="{{ $key }}" @selected($key === 'pemesanan')>{{ $category['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="help-category-list" role="list" data-help-category-list>
                    @foreach ($categories as $key => $category)
                        <button type="button" class="help-category-button {{ $key === 'pemesanan' ? 'is-active' : '' }}" data-category="{{ $key }}" aria-pressed="{{ $key === 'pemesanan' ? 'true' : 'false' }}">
                            <i class="fi fi-rr-{{ $category['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $category['label'] }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="help-sidebar-box">
                    <i class="fi fi-rr-comment-alt" aria-hidden="true"></i>
                    <div>
                        <h3>Tidak menemukan jawaban?</h3>
                        <p>Hubungi tim kami untuk bantuan lebih lanjut.</p>
                        <a href="#hubungi-kami">Hubungi Kami <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </aside>

            <div class="help-content">
                <div class="help-section-head">
                    <div>
                        <h2 id="faqHeading">Pertanyaan yang Sering Ditanyakan</h2>
                        <p>Jawaban cepat untuk pertanyaan yang paling sering ditanyakan pelanggan.</p>
                    </div>
                    <label class="sr-only" for="helpDesktopCategory">Filter kategori FAQ</label>
                    <select id="helpDesktopCategory" data-help-category-select>
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $key => $category)
                            <option value="{{ $key }}" @selected($key === 'pemesanan')>{{ $category['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <p class="help-result-summary" aria-live="polite" data-help-result-summary></p>
                <div class="help-faq-list" data-help-faq-list>
                    @foreach ($faqs as $index => $faq)
                        <article id="{{ $faq['id'] ?? 'faq-'.$index }}" class="help-faq-item" data-help-faq data-category="{{ $faq['category'] }}" data-search-text="{{ Illuminate\Support\Str::lower($faq['question'].' '.$faq['answer'].' '.$categories[$faq['category']]['label']) }}">
                            <h3>
                                <button type="button" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faq-panel-{{ $index }}" id="faq-button-{{ $index }}" data-faq-trigger>
                                    <span>{{ $faq['question'] }}</span>
                                    <i class="fi fi-rr-angle-small-down" aria-hidden="true"></i>
                                </button>
                            </h3>
                            <div id="faq-panel-{{ $index }}" role="region" aria-labelledby="faq-button-{{ $index }}" class="help-faq-answer" @if($index !== 0) hidden @endif>
                                <p>{{ $faq['answer'] }}</p>
                                @if (!empty($faq['platforms']))
                                    <div class="help-install-grid">
                                        @foreach ($faq['platforms'] as $platform => $steps)
                                            <section>
                                                <h4>{{ $platform }}</h4>
                                                <ol>
                                                    @foreach ($steps as $step)
                                                        <li>{{ $step }}</li>
                                                    @endforeach
                                                </ol>
                                            </section>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($faq['category'] === 'pengiriman' && str_contains($faq['question'], 'status'))
                                    <a href="{{ route('frontend.order-tracking.index') }}">Buka Lacak Pesanan <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="help-empty" data-help-empty hidden>
                    <i class="fi fi-rr-search" aria-hidden="true"></i>
                    <h3>Jawaban belum ditemukan</h3>
                    <p>Coba kata kunci lain atau pilih Semua Kategori.</p>
                    <button type="button" data-help-reset>Reset pencarian</button>
                </div>

                <section class="help-popular" aria-labelledby="popularHeading">
                    <div class="help-section-head">
                        <div>
                            <h2 id="popularHeading">Topik Populer</h2>
                            <p>Temukan informasi yang Anda butuhkan dengan lebih cepat.</p>
                        </div>
                    </div>
                    <div class="help-topic-grid">
                        <a href="{{ route('frontend.pages.show', 'cara-belanja') }}" class="help-topic-card">
                            <i class="fi fi-rr-shopping-cart" aria-hidden="true"></i><h3>Cara Belanja</h3><p>Panduan lengkap berbelanja dan menyelesaikan pesanan.</p><span>Lihat Panduan <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></span>
                        </a>
                        <button type="button" class="help-topic-card" data-topic-category="pembayaran">
                            <i class="fi fi-rr-credit-card" aria-hidden="true"></i><h3>Pembayaran</h3><p>Metode pembayaran dan proses verifikasi.</p><span>Lihat Panduan <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></span>
                        </button>
                        <a href="{{ route('frontend.order-tracking.index') }}" class="help-topic-card">
                            <i class="fi fi-rr-truck-side" aria-hidden="true"></i><h3>Pengiriman</h3><p>Status, resi, dan pelacakan pesanan Anda.</p><span>Lacak Pesanan <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></span>
                        </a>
                        <a href="{{ $quoteUrl }}" class="help-topic-card" @if($quoteIsExternal) target="_blank" rel="noopener noreferrer" @endif>
                            <i class="fi fi-rr-document" aria-hidden="true"></i><h3>Minta Penawaran</h3><p>Pembelian jumlah besar dan kebutuhan proyek.</p><span>Hubungi Tim <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></span>
                        </a>
                    </div>
                </section>
            </div>
        </section>

        <section class="help-project ec-container" aria-labelledby="projectHeading">
            <img src="{{ asset('imgs/help-center/project-assistance.webp') }}" alt="Fasilitas industri dengan struktur baja dan sistem perpipaan" width="2048" height="864" loading="lazy">
            <div class="help-project-overlay" aria-hidden="true"></div>
            <div class="help-project-copy">
                <i class="fi fi-rr-building" aria-hidden="true"></i>
                <div>
                    <h2 id="projectHeading">Butuh bantuan untuk kebutuhan proyek?</h2>
                    <p>Tim kami siap membantu pembelian dalam jumlah besar dan kebutuhan spesifikasi tertentu untuk proyek Anda.</p>
                    <div class="help-project-actions">
                        <a href="#hubungi-kami" class="is-light"><i class="fi fi-rr-comment-alt" aria-hidden="true"></i> Hubungi Tim {{ $appStoreName }}</a>
                        <a href="{{ $quoteUrl }}" class="is-primary" @if($quoteIsExternal) target="_blank" rel="noopener noreferrer" @endif><i class="fi fi-rr-document" aria-hidden="true"></i> Minta Penawaran</a>
                    </div>
                </div>
            </div>
            <p class="help-project-mark" aria-hidden="true">Your trusted partner<br>for industrial supply</p>
        </section>

        <section id="hubungi-kami" class="help-contact ec-container" aria-labelledby="contactHeading">
            <div class="help-section-head">
                <div>
                    <h2 id="contactHeading">Masih butuh bantuan?</h2>
                    <p>Tim kami siap membantu Anda melalui kanal komunikasi yang tersedia.</p>
                </div>
            </div>
            <div class="help-contact-grid">
                <article class="help-contact-card help-contact-whatsapp">
                    <i class="fi fi-brands-whatsapp" aria-hidden="true"></i>
                    <div><h3>WhatsApp</h3><p>Chat langsung dengan tim kami pada jam operasional.</p></div>
                    @if (!empty($appStoreSettings['social_whatsapp']))
                        <a href="{{ $appStoreSettings['social_whatsapp'] }}" target="_blank" rel="noopener noreferrer">Chat via WhatsApp</a>
                    @else
                        <span class="is-unavailable">Belum tersedia</span>
                    @endif
                </article>
                <article class="help-contact-card">
                    <i class="fi fi-rr-envelope" aria-hidden="true"></i>
                    <div><h3>Email</h3>@if($supportEmail)<p>Kirim pertanyaan Anda ke<br><strong>{{ $supportEmail }}</strong></p>@else<p>Alamat email dukungan belum dikonfigurasi.</p>@endif</div>
                    @if($supportEmail)<a href="mailto:{{ $supportEmail }}">Kirim Email</a>@else<span class="is-unavailable">Belum tersedia</span>@endif
                </article>
                <article class="help-contact-card">
                    <i class="fi fi-rr-headset" aria-hidden="true"></i>
                    <div><h3>Jam Operasional</h3><p>Senin–Sabtu<br><strong>08.00–17.00 WIB</strong></p></div>
                    <a href="#categoryHeading">Lihat Topik Bantuan</a>
                </article>
            </div>
        </section>
    </main>
@endsection

@section('style')
    <link rel="preload" as="image" href="{{ asset('imgs/help-center/hero-help-center.webp') }}" fetchpriority="high">
    <style>
        .help-center{background:#fff;color:#0b1f43}.help-center button,.help-center input,.help-center select{font:inherit}.help-breadcrumb{display:flex;align-items:center;gap:.55rem;min-height:48px;color:#64748b;font-size:.75rem}.help-breadcrumb a{display:inline-flex;align-items:center;gap:.45rem;color:#475569}.help-breadcrumb a:hover{color:#174d91}.help-breadcrumb>i{font-size:.7rem}.help-hero{position:relative;height:272px;overflow:hidden;border-block:1px solid #e5e7eb;background:#eef3f8}.help-hero>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}.help-hero-shade{position:absolute;inset:0;background:linear-gradient(90deg,rgba(247,250,252,.9) 0%,rgba(247,250,252,.76) 48%,rgba(247,250,252,.05) 76%)}.help-hero-content{position:relative;z-index:1;display:flex;height:100%;flex-direction:column;align-items:center;justify-content:center;padding-block:1.4rem;text-align:center}.help-hero h1{font-size:clamp(2rem,4vw,2.75rem);font-weight:800;letter-spacing:-.04em;line-height:1;color:#0b1f43}.help-hero-kicker{margin-top:.25rem;font-size:1.05rem;font-weight:700;color:#0f2856}.help-hero-copy{max-width:38rem;margin-top:.3rem;color:#52627a;font-size:.82rem;line-height:1.55}.help-search{display:flex;width:min(100%,620px);min-height:48px;margin-top:1rem;align-items:center;overflow:hidden;border:1px solid #cbd5e1;border-radius:8px;background:#fff;box-shadow:0 5px 18px rgb(15 48 95 / .08)}.help-search>i{margin-left:1rem;color:#526b91}.help-search input{min-width:0;flex:1;padding:.7rem .85rem;border:0;outline:0;color:#0f172a;font-size:.8rem}.help-search input::placeholder{color:#8190a6}.help-search:focus-within{border-color:#1d5db8;box-shadow:0 0 0 3px #dceafe}.help-search button{align-self:stretch;min-width:88px;border:0;background:#1255ad;color:white;font-size:.8rem;font-weight:700}.help-search button:hover{background:#0a3268}.help-main{display:grid;grid-template-columns:244px minmax(0,1fr);gap:24px;padding-top:24px}.help-sidebar{align-self:start;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;padding:14px 10px}.help-sidebar>h2{padding:0 8px 9px;font-size:.95rem;font-weight:800}.help-category-list{display:grid}.help-category-button{display:flex;min-height:44px;width:100%;align-items:center;gap:.75rem;border-left:3px solid transparent;padding:.55rem .75rem;color:#213555;font-size:.78rem;text-align:left;transition:background-color 160ms,color 160ms,border-color 160ms}.help-category-button i{width:1.2rem;color:#163f78;font-size:1rem}.help-category-button:hover{background:#edf4fd;color:#0d4fa6}.help-category-button.is-active{border-left-color:#1765d1;background:#e5f0ff;color:#0b55ba;font-weight:700}.help-category-button.is-active i{color:#0b62d2}.help-mobile-category{display:none}.help-sidebar-box{display:flex;gap:.8rem;margin:16px 4px 2px;border:1px solid #dce3ec;border-radius:7px;background:#fff;padding:14px 12px}.help-sidebar-box>i{color:#0b62d2;font-size:1.35rem}.help-sidebar-box h3{font-size:.78rem;font-weight:800;line-height:1.35}.help-sidebar-box p{margin-top:.25rem;color:#64748b;font-size:.7rem;line-height:1.5}.help-sidebar-box a{display:inline-flex;align-items:center;gap:.3rem;margin-top:.55rem;color:#0b5fc7;font-size:.72rem;font-weight:700}.help-content{min-width:0}.help-section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}.help-section-head h2{font-size:1.18rem;font-weight:800;letter-spacing:-.02em;color:#0b1f43}.help-section-head p{margin-top:.15rem;color:#64748b;font-size:.76rem;line-height:1.45}.help-section-head select{min-height:40px;min-width:155px;border:1px solid #d5dce2;border-radius:7px;background:#fff;padding:0 2.2rem 0 .75rem;color:#253a5a;font-size:.72rem}.help-result-summary{min-height:18px;margin-top:.25rem;color:#52627a;font-size:.7rem}.help-faq-list{overflow:hidden;border:1px solid #dce3ec;border-radius:8px;background:#fff}.help-faq-item+.help-faq-item{border-top:1px solid #e5e9ef}.help-faq-item h3{margin:0}.help-faq-item h3 button{display:flex;min-height:51px;width:100%;align-items:center;justify-content:space-between;gap:1rem;padding:.7rem 1rem;color:#102449;font-size:.8rem;font-weight:700;text-align:left}.help-faq-item h3 button:hover{background:#f8fafc}.help-faq-item h3 i{flex:0 0 auto;color:#0b55ba;transition:transform 180ms}.help-faq-item h3 button[aria-expanded=true] i{transform:rotate(180deg)}.help-faq-answer{padding:0 3rem 1rem 1rem;color:#596a83;font-size:.75rem;line-height:1.65}.help-faq-answer a{display:inline-flex;align-items:center;gap:.25rem;margin-top:.6rem;color:#0b5fc7;font-weight:700}.help-empty{border:1px dashed #cbd5e1;border-radius:8px;padding:2rem;text-align:center;color:#64748b}.help-empty>i{color:#1d5db8;font-size:1.6rem}.help-empty h3{margin-top:.6rem;color:#172b4d;font-size:.95rem;font-weight:800}.help-empty p{margin-top:.25rem;font-size:.76rem}.help-empty button{margin-top:.85rem;color:#0b5fc7;font-size:.75rem;font-weight:700}.help-popular{padding-top:22px}.help-topic-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}.help-topic-card{display:flex;min-height:160px;flex-direction:column;align-items:flex-start;border:1px solid #dce3ec;border-radius:8px;background:#fff;padding:16px;text-align:left;transition:border-color 160ms,background-color 160ms}.help-topic-card:hover{border-color:#75a8e8;background:#fbfdff}.help-topic-card>i{color:#0b62d2;font-size:1.55rem}.help-topic-card h3{margin-top:.8rem;color:#102449;font-size:.82rem;font-weight:800}.help-topic-card p{margin-top:.2rem;color:#64748b;font-size:.7rem;line-height:1.45}.help-topic-card span{display:inline-flex;align-items:center;gap:.25rem;margin-top:auto;padding-top:.6rem;color:#0b5fc7;font-size:.7rem;font-weight:700}.help-project{position:relative;min-height:188px;margin-top:24px;overflow:hidden;border-radius:8px;background:#082f63;color:white}.help-project>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}.help-project-overlay{position:absolute;inset:0;background:linear-gradient(90deg,#082d5d 0%,rgba(8,45,93,.97) 42%,rgba(8,45,93,.38) 72%,rgba(8,45,93,.08))}.help-project-copy{position:relative;z-index:1;display:flex;max-width:730px;gap:1rem;padding:24px 30px}.help-project-copy>i{margin-top:.25rem;font-size:2rem}.help-project-copy h2{font-size:1.15rem;font-weight:800}.help-project-copy p{max-width:570px;margin-top:.25rem;color:#dce8f7;font-size:.78rem;line-height:1.55}.help-project-actions{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:1rem}.help-project-actions a{display:inline-flex;min-height:40px;align-items:center;justify-content:center;gap:.45rem;border:1px solid transparent;border-radius:6px;padding:.55rem 1rem;font-size:.72rem;font-weight:700}.help-project-actions .is-light{background:#fff;color:#0b55ba}.help-project-actions .is-primary{background:#1265cc;color:#fff}.help-project-mark{position:absolute;right:25px;bottom:25px;z-index:1;border-left:2px solid #fff;padding-left:.75rem;font-size:.58rem;font-weight:700;letter-spacing:.2em;line-height:1.7;text-transform:uppercase}.help-contact{padding-top:24px;padding-bottom:12px}.help-contact-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:12px}.help-contact-card{display:grid;grid-template-columns:48px 1fr;grid-template-rows:1fr auto;gap:0 14px;min-height:152px;border:1px solid #dce3ec;border-radius:8px;padding:16px;background:#fff}.help-contact-card>i{grid-row:1/3;display:grid;width:44px;height:44px;place-items:center;border-radius:50%;background:#eef5ff;color:#0b62d2;font-size:1.45rem}.help-contact-card h3{font-size:.8rem;font-weight:800}.help-contact-card p{margin-top:.2rem;color:#64748b;font-size:.7rem;line-height:1.5}.help-contact-card strong{color:#0b55ba}.help-contact-card>a,.help-contact-card>.is-unavailable{grid-column:2;display:flex;min-height:36px;align-items:center;justify-content:center;border:1px solid #b9d4f7;border-radius:6px;background:#edf5ff;color:#0b55ba;font-size:.7rem;font-weight:700}.help-contact-card>.is-unavailable{border-color:#e2e8f0;background:#f8fafc;color:#94a3b8}.help-contact-whatsapp>i{background:#e9f8ef;color:#168947}.help-contact-whatsapp>a{border-color:#32ad67;background:#fff;color:#168947}.help-contact-whatsapp>a:hover{background:#f0fbf5}
        .help-install-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;margin-top:.85rem}.help-install-grid section{border:1px solid #dce3ec;border-radius:8px;background:#f8fafc;padding:.85rem}.help-install-grid h4{color:#102449;font-size:.76rem;font-weight:800}.help-install-grid ol{margin:.5rem 0 0 1.15rem;list-style:decimal}.help-install-grid li+li{margin-top:.3rem}
        @media(max-width:1023px){.help-main{grid-template-columns:210px minmax(0,1fr);gap:18px}.help-topic-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.help-contact-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.help-contact-card:last-child{grid-column:1/-1}.help-project-mark{display:none}}
        @media(max-width:767px){.help-breadcrumb{min-height:40px}.help-hero{height:258px}.help-hero>img{object-position:68% center}.help-hero-shade{background:rgba(248,250,252,.82)}.help-hero-content{padding-inline:1rem}.help-hero h1{font-size:1.75rem}.help-hero-kicker{font-size:.95rem}.help-hero-copy{max-width:21rem;font-size:.75rem}.help-search{margin-top:.8rem}.help-search button{min-width:72px}.help-main{display:block;padding-top:16px}.help-sidebar{margin-bottom:20px;padding:12px}.help-sidebar>h2{padding-inline:0}.help-category-list{display:none}.help-mobile-category{display:block}.help-mobile-category label{display:block;margin-bottom:.35rem;color:#475569;font-size:.7rem}.help-mobile-category select{width:100%;min-height:44px;border:1px solid #cbd5e1;border-radius:7px;background:white;padding:0 .75rem;color:#183052;font-size:.8rem}.help-sidebar-box{display:none}.help-section-head select{display:none}.help-section-head h2{font-size:1.05rem}.help-result-summary{margin-top:.35rem}.help-faq-item h3 button{min-height:54px;padding:.75rem .85rem;font-size:.78rem}.help-faq-answer{padding:0 2rem .9rem .85rem}.help-install-grid{grid-template-columns:1fr}.help-topic-grid{grid-template-columns:1fr}.help-topic-card{min-height:142px}.help-project{min-height:310px;margin-top:20px}.help-project>img{object-position:70% center}.help-project-overlay{background:linear-gradient(180deg,rgba(8,45,93,.94),rgba(8,45,93,.88))}.help-project-copy{display:block;padding:24px 20px}.help-project-copy>i{display:block;margin:0 0 .8rem}.help-project-actions{display:grid}.help-project-actions a{width:100%}.help-contact{padding-top:22px}.help-contact-grid{grid-template-columns:1fr}.help-contact-card:last-child{grid-column:auto}}
    </style>
@endsection

@section('script')
    <script>
        (() => {
            const root = document.querySelector('[data-help-center]');
            if (!root) return;
            const faqItems = [...root.querySelectorAll('[data-help-faq]')];
            const categoryButtons = [...root.querySelectorAll('[data-category]')];
            const categorySelects = [...root.querySelectorAll('[data-help-category-select]')];
            const searchInput = root.querySelector('[data-help-search]');
            const summary = root.querySelector('[data-help-result-summary]');
            const empty = root.querySelector('[data-help-empty]');
            const faqList = root.querySelector('[data-help-faq-list]');
            const hashId = decodeURIComponent(window.location.hash.slice(1));
            const hashFaq = hashId ? document.getElementById(hashId) : null;
            let activeCategory = new URLSearchParams(window.location.search).get('category') || hashFaq?.dataset.category || 'pemesanan';
            if (activeCategory !== 'all' && !categoryButtons.some(button => button.dataset.category === activeCategory)) activeCategory = 'pemesanan';

            const normalize = value => value.toLocaleLowerCase('id-ID').trim();
            const closeFaq = item => {
                const trigger = item.querySelector('[data-faq-trigger]');
                const panel = item.querySelector('[role="region"]');
                trigger.setAttribute('aria-expanded', 'false');
                panel.hidden = true;
            };
            const openFirstVisible = () => {
                const first = faqItems.find(item => !item.hidden);
                if (!first) return;
                const trigger = first.querySelector('[data-faq-trigger]');
                const panel = first.querySelector('[role="region"]');
                trigger.setAttribute('aria-expanded', 'true');
                panel.hidden = false;
            };
            const render = ({ openFirst = false } = {}) => {
                const query = normalize(searchInput.value);
                let visible = 0;
                faqItems.forEach(item => {
                    const categoryMatch = activeCategory === 'all' || item.dataset.category === activeCategory;
                    const searchMatch = !query || normalize(item.dataset.searchText).includes(query);
                    item.hidden = !(categoryMatch && searchMatch);
                    if (!item.hidden) visible++;
                    else closeFaq(item);
                });
                categoryButtons.forEach(button => {
                    const selected = button.dataset.category === activeCategory;
                    button.classList.toggle('is-active', selected);
                    button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
                categorySelects.forEach(select => select.value = activeCategory);
                summary.textContent = query ? `${visible} jawaban ditemukan untuk “${searchInput.value.trim()}”` : '';
                empty.hidden = visible !== 0;
                faqList.hidden = visible === 0;
                if (openFirst) openFirstVisible();
            };

            root.querySelectorAll('[data-faq-trigger]').forEach(trigger => trigger.addEventListener('click', () => {
                const item = trigger.closest('[data-help-faq]');
                const panel = item.querySelector('[role="region"]');
                const willOpen = trigger.getAttribute('aria-expanded') !== 'true';
                faqItems.forEach(closeFaq);
                trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                panel.hidden = !willOpen;
            }));
            categoryButtons.forEach(button => button.addEventListener('click', () => {
                activeCategory = button.dataset.category;
                render({ openFirst: true });
            }));
            categorySelects.forEach(select => select.addEventListener('change', () => {
                activeCategory = select.value;
                render({ openFirst: true });
            }));
            root.querySelector('[data-help-search-form]').addEventListener('submit', event => {
                event.preventDefault();
                activeCategory = 'all';
                render({ openFirst: true });
                document.getElementById('faqHeading').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            searchInput.addEventListener('input', () => {
                if (searchInput.value.trim()) activeCategory = 'all';
                render();
            });
            root.querySelector('[data-help-reset]').addEventListener('click', () => {
                searchInput.value = '';
                activeCategory = 'all';
                render({ openFirst: true });
                searchInput.focus();
            });
            root.querySelectorAll('[data-topic-category]').forEach(button => button.addEventListener('click', () => {
                activeCategory = button.dataset.topicCategory;
                searchInput.value = '';
                render({ openFirst: true });
                document.getElementById('faqHeading').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }));
            render({ openFirst: true });
            if (hashFaq?.matches('[data-help-faq]')) {
                window.requestAnimationFrame(() => hashFaq.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            }
        })();
    </script>
@endsection
