<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;

class FrontendContentController extends Controller
{
    public function page(string $slug)
    {
        abort_if($slug === 'tentang-kami', 404);

        $page = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_PAGE)
            ->where('slug', $slug)
            ->first();

        if (! $page) {
            $fallbacks = $this->legalFallbacks();
            abort_unless(isset($fallbacks[$slug]), 404);
            $page = new ContentPage($fallbacks[$slug] + [
                'type' => ContentPage::TYPE_PAGE,
                'slug' => $slug,
                'is_active' => true,
            ]);
        }

        if ($slug === 'pusat-bantuan') {
            return view('frontend.help-center', compact('page'));
        }

        if (app()->isLocale('en')) {
            $localizedPage = trans('information.pages.'.$slug);

            if (is_array($localizedPage)) {
                $page->forceFill(array_intersect_key($localizedPage, array_flip([
                    'title',
                    'excerpt',
                    'meta_title',
                    'meta_description',
                    'content',
                ])));
                $page->meta_title = $localizedPage['meta_title'] ?? $localizedPage['title'] ?? null;
            }
        }

        return view('frontend.content-page', compact('page'));
    }

    /** @return array<string, array{title: string, excerpt: string, content: string, meta_description: string}> */
    private function legalFallbacks(): array
    {
        $appName = (string) config('app.name');

        return [
            'pusat-bantuan' => [
                'title' => 'Pusat Bantuan',
                'excerpt' => 'Panduan ringkas untuk pencarian produk, pemesanan, pembayaran, dan pengiriman.',
                'meta_description' => 'Pusat bantuan pelanggan '.$appName.'.',
                'content' => '<h2>Cara mendapatkan bantuan</h2><p>Cari produk berdasarkan nama, SKU, ukuran, atau material. Periksa varian, satuan jual, stok, dan perusahaan penjual sebelum memasukkan produk ke keranjang.</p><h2>Pesanan dan pengiriman</h2><p>Gunakan halaman Lacak Pesanan untuk memeriksa status. Siapkan nomor pesanan dan data verifikasi yang diminta. Untuk pertanyaan spesifikasi, gunakan kanal WhatsApp resmi yang tercantum di situs.</p>',
            ],
            'cara-belanja' => [
                'title' => 'Cara Belanja',
                'excerpt' => 'Langkah pemesanan produk teknik dari pencarian hingga barang diterima.',
                'meta_description' => 'Panduan cara belanja di '.$appName.'.',
                'content' => '<h2>1. Temukan produk</h2><p>Gunakan pencarian atau kategori, kemudian cocokkan SKU, ukuran, material, varian, dan satuan jual.</p><h2>2. Periksa detail produk</h2><p>Pastikan spesifikasi, stok, harga, dan jumlah sudah sesuai sebelum memasukkan produk ke keranjang.</p><h2>3. Tinjau keranjang</h2><p>Periksa kembali produk dan jumlah sebelum melanjutkan ke checkout.</p><h2>4. Lengkapi checkout</h2><p>Pastikan alamat, layanan pengiriman, dan metode pembayaran sudah benar, lalu buat pesanan.</p><h2>5. Pesanan berhasil dibuat</h2><p>Ikuti instruksi pembayaran yang ditampilkan dan simpan nomor pesanan.</p><h2>6. Lacak pesanan</h2><p>Gunakan email pemesan dan nomor pesanan pada halaman Lacak Pesanan untuk melihat status pembayaran dan pengiriman.</p>',
            ],
            'technical' => [
                'title' => 'Technical',
                'excerpt' => 'Panduan memahami spesifikasi, pemilihan, dan penggunaan produk teknik.',
                'meta_description' => 'Panduan teknis dan spesifikasi produk di '.$appName.'.',
                'content' => '<h2>Informasi teknis produk</h2><p>Gunakan halaman ini sebagai panduan awal untuk memahami ukuran, material, finishing, standar, dan satuan jual produk. Detail yang tersedia dapat berbeda pada setiap kategori dan varian.</p><h2>Sebelum memilih produk</h2><ul><li>Cocokkan dimensi dan spesifikasi dengan kebutuhan aplikasi.</li><li>Periksa material, finishing, standar, dan kondisi lingkungan penggunaan.</li><li>Pastikan varian, jumlah, dan satuan jual sebelum memesan.</li></ul><h2>Butuh konfirmasi?</h2><p>Siapkan nama atau SKU produk beserta kebutuhan aplikasinya, lalu hubungi tim kami melalui Pusat Bantuan untuk mendapatkan arahan lebih lanjut.</p>',
            ],
            'project' => [
                'title' => 'Project',
                'excerpt' => 'Dukungan pengadaan produk teknik untuk kebutuhan proyek dan pembelian volume.',
                'meta_description' => 'Dukungan kebutuhan proyek dan pengadaan produk teknik dari '.$appName.'.',
                'content' => '<h2>Dukungan kebutuhan proyek</h2><p>Kami membantu proses pengadaan produk teknik untuk pekerjaan konstruksi, manufaktur, perawatan, bengkel, dan kebutuhan operasional lainnya.</p><h2>Informasi yang perlu disiapkan</h2><ul><li>Daftar produk atau spesifikasi yang dibutuhkan.</li><li>Jumlah, satuan, dan target waktu pengadaan.</li><li>Lokasi pengiriman serta kebutuhan dokumen pendukung.</li></ul><h2>Proses konsultasi</h2><p>Kirimkan kebutuhan proyek melalui kanal resmi kami. Tim akan membantu mencocokkan produk, memeriksa ketersediaan, dan menyiapkan penawaran sesuai informasi yang diberikan.</p>',
            ],
            'tentang-boq' => [
                'title' => 'Tentang BOQ',
                'excerpt' => 'Kenali Bill of Quantity dan cara menyiapkan kebutuhan pengadaan dengan lebih terstruktur.',
                'meta_description' => 'Informasi Bill of Quantity dan pengadaan proyek di '.$appName.'.',
                'content' => '<h2>Apa itu BOQ?</h2><p>BOQ atau Bill of Quantity adalah daftar terstruktur yang merangkum barang, spesifikasi, satuan, dan jumlah yang dibutuhkan untuk sebuah pekerjaan atau proyek.</p><h2>Mengapa BOQ penting?</h2><ul><li>Memudahkan pemeriksaan kelengkapan kebutuhan.</li><li>Mengurangi risiko perbedaan spesifikasi dan jumlah.</li><li>Membantu proses permintaan harga dan perencanaan pengadaan.</li></ul><h2>Menyiapkan BOQ</h2><p>Cantumkan nama barang, spesifikasi utama, merek bila diwajibkan, satuan, jumlah, serta catatan teknis. Tim kami dapat membantu mencocokkan daftar tersebut dengan produk yang tersedia.</p>',
            ],
            'kebijakan-privasi' => [
                'title' => 'Kebijakan Privasi',
                'excerpt' => 'Ringkasan cara data pelanggan digunakan untuk memproses layanan toko.',
                'meta_description' => 'Kebijakan privasi pelanggan '.$appName.'.',
                'content' => '<h2>Data yang diproses</h2><p>Kami memproses data akun, kontak, alamat, dan transaksi yang diperlukan untuk menyediakan layanan, memenuhi pesanan, mencegah penyalahgunaan, dan memenuhi kewajiban hukum.</p><h2>Penggunaan dan keamanan</h2><p>Data digunakan sesuai tujuan layanan dan hanya dibagikan kepada penyedia pembayaran, pengiriman, atau layanan pendukung sejauh diperlukan. Hubungi kanal dukungan resmi untuk permintaan terkait data pribadi.</p><h2>Pembaruan</h2><p>Versi yang diterbitkan pada URL ini merupakan kebijakan yang berlaku. Konten dapat diperbarui oleh pengelola toko saat kebijakan final tersedia.</p>',
            ],
            'syarat-ketentuan' => [
                'title' => 'Syarat dan Ketentuan',
                'excerpt' => 'Ketentuan dasar penggunaan layanan dan pemesanan produk.',
                'meta_description' => 'Syarat dan ketentuan penggunaan '.$appName.'.',
                'content' => '<h2>Informasi produk</h2><p>Pelanggan bertanggung jawab memeriksa nama, SKU, spesifikasi, varian, satuan, jumlah, dan perusahaan penjual sebelum mengonfirmasi pesanan.</p><h2>Harga dan pembayaran</h2><p>Harga, pajak, diskon, ongkos kirim, dan total yang berlaku ditampilkan pada proses checkout. Pesanan diproses mengikuti status pembayaran dan ketersediaan stok.</p><h2>Pengiriman dan pengembalian</h2><p>Estimasi pengiriman dapat berubah karena operasional kurir. Permintaan pengembalian mengikuti kelayakan dan bukti yang diminta pada alur pesanan.</p>',
            ],
        ];
    }

    public function blog()
    {
        $posts = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_POST)
            ->latest('published_at')
            ->paginate(9);

        return view('frontend.blog-index', compact('posts'));
    }

    public function post(string $slug)
    {
        $page = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_POST)
            ->where('slug', $slug)
            ->firstOrFail();
        $relatedPosts = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_POST)
            ->whereKeyNot($page->id)
            ->latest('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        return view('frontend.content-page', compact('page', 'relatedPosts'));
    }
}
