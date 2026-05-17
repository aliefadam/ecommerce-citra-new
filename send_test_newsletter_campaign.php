<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Mail::to('denyprasetyo41@gmail.com')->send(
    new App\Mail\NewsletterCampaign(
        'Diskon Spesial Menantimu - Buruan Cek Promo Terbaru',
        "Halo,\n\nPromo spesial dari Citra Ecommerce sedang berlangsung sekarang juga.\n\nKami sudah siapkan penawaran terbaik untuk kamu dengan pilihan produk unggulan dan harga yang lebih hemat dari biasanya. Kalau kamu sedang cari momen yang pas untuk belanja, ini waktunya.\n\nKenapa wajib cek sekarang?\n- ada promo menarik untuk produk pilihan\n- stok dan periode promo terbatas\n- kesempatan belanja lebih hemat sebelum kehabisan\n\nJangan tunggu terlalu lama, karena promo bisa berakhir kapan saja.\n\nKlik tombol di bawah ini dan langsung lihat promo yang sedang aktif sekarang.\n\nSalam,\nTim Citra Ecommerce",
        'Lihat Promo Sekarang',
        'https://boq.dokterkoding.my.id/flash-sale',
        null
    )
);

echo "NEWSLETTER_SENT\n";
