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

        return view('frontend.content-page', compact('page'));
    }

    /** @return array<string, array{title: string, excerpt: string, content: string, meta_description: string}> */
    private function legalFallbacks(): array
    {
        return [
            'pusat-bantuan' => [
                'title' => 'Pusat Bantuan',
                'excerpt' => 'Panduan ringkas untuk pencarian produk, pemesanan, pembayaran, dan pengiriman.',
                'meta_description' => 'Pusat bantuan pelanggan Ecommerce Citra.',
                'content' => '<h2>Cara mendapatkan bantuan</h2><p>Cari produk berdasarkan nama, SKU, ukuran, atau material. Periksa varian, satuan jual, stok, dan perusahaan penjual sebelum memasukkan produk ke keranjang.</p><h2>Pesanan dan pengiriman</h2><p>Gunakan halaman Lacak Pesanan untuk memeriksa status. Siapkan nomor pesanan dan data verifikasi yang diminta. Untuk pertanyaan spesifikasi, gunakan kanal WhatsApp resmi yang tercantum di situs.</p>',
            ],
            'cara-belanja' => [
                'title' => 'Cara Belanja',
                'excerpt' => 'Langkah pemesanan produk teknik dari pencarian hingga barang diterima.',
                'meta_description' => 'Panduan cara belanja di Ecommerce Citra.',
                'content' => '<h2>1. Temukan produk</h2><p>Gunakan pencarian atau kategori, kemudian cocokkan SKU, ukuran, material, varian, dan satuan jual.</p><h2>2. Periksa keranjang</h2><p>Pastikan jumlah, perusahaan penjual, alamat, dan pilihan pengiriman sudah benar.</p><h2>3. Selesaikan pembayaran</h2><p>Pilih metode yang tersedia dan ikuti instruksi pembayaran. Simpan nomor pesanan untuk pelacakan dan bantuan.</p>',
            ],
            'kebijakan-privasi' => [
                'title' => 'Kebijakan Privasi',
                'excerpt' => 'Ringkasan cara data pelanggan digunakan untuk memproses layanan toko.',
                'meta_description' => 'Kebijakan privasi pelanggan Ecommerce Citra.',
                'content' => '<h2>Data yang diproses</h2><p>Kami memproses data akun, kontak, alamat, dan transaksi yang diperlukan untuk menyediakan layanan, memenuhi pesanan, mencegah penyalahgunaan, dan memenuhi kewajiban hukum.</p><h2>Penggunaan dan keamanan</h2><p>Data digunakan sesuai tujuan layanan dan hanya dibagikan kepada penyedia pembayaran, pengiriman, atau layanan pendukung sejauh diperlukan. Hubungi kanal dukungan resmi untuk permintaan terkait data pribadi.</p><h2>Pembaruan</h2><p>Versi yang diterbitkan pada URL ini merupakan kebijakan yang berlaku. Konten dapat diperbarui oleh pengelola toko saat kebijakan final tersedia.</p>',
            ],
            'syarat-ketentuan' => [
                'title' => 'Syarat dan Ketentuan',
                'excerpt' => 'Ketentuan dasar penggunaan layanan dan pemesanan produk.',
                'meta_description' => 'Syarat dan ketentuan penggunaan Ecommerce Citra.',
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
