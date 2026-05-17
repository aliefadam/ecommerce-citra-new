<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;

class AboutUsContentSeeder extends Seeder
{
    public function run(): void
    {
        ContentPage::query()->updateOrCreate(
            ['slug' => 'tentang-kami'],
            [
                'type' => ContentPage::TYPE_PAGE,
                'title' => 'Tentang Kami',
                'excerpt' => 'PT CITRA ABADI TEKNIK INDONESIA adalah partner profesional untuk kebutuhan baut, mur, fastener, anchor, dan perlengkapan teknik industri.',
                'content' => <<<'HTML'
<p><strong>PT CITRA ABADI TEKNIK INDONESIA</strong> hadir sebagai penyedia kebutuhan teknik yang membantu bengkel, proyek, kontraktor, dan industri mendapatkan produk yang tepat dengan proses belanja yang lebih mudah.</p>

<p>Kami memahami bahwa setiap pekerjaan profesional membutuhkan komponen yang presisi, stok yang dapat diandalkan, serta pelayanan yang responsif. Karena itu, kami terus mengembangkan katalog produk mulai dari baut, mur, fastener, anchor, hingga perkakas teknik untuk mendukung berbagai kebutuhan lapangan.</p>

<h2>Professional Partner For Your Professional Results</h2>
<p>Bagi kami, produk teknik bukan sekadar barang. Setiap item yang dipilih pelanggan memiliki peran penting dalam hasil akhir pekerjaan. Kami berkomitmen menjadi partner yang membantu pelanggan memilih produk dengan informasi yang jelas, harga yang kompetitif, dan pengalaman belanja yang praktis.</p>

<h2>Fokus Kami</h2>
<ul>
    <li>Menyediakan produk teknik yang relevan untuk kebutuhan proyek, bengkel, dan industri.</li>
    <li>Memberikan informasi produk yang jelas agar pelanggan lebih mudah mengambil keputusan.</li>
    <li>Menjaga proses pemesanan tetap sederhana, cepat, dan aman.</li>
    <li>Membangun layanan yang responsif untuk pertanyaan produk, stok, dan pengiriman.</li>
</ul>

<h2>Mengapa Memilih Kami?</h2>
<p>Kami menggabungkan pengalaman di kebutuhan teknik dengan sistem belanja online yang dibuat untuk mempermudah pelanggan. Dari pencarian produk, pemilihan varian, checkout, hingga proses pengiriman, semua dirancang agar pelanggan dapat fokus pada pekerjaan utamanya.</p>

<p>Jika Anda membutuhkan bantuan memilih produk atau ingin berkonsultasi sebelum membeli, tim kami siap membantu melalui kanal komunikasi resmi yang tersedia di website.</p>
HTML,
                'hero_image' => null,
                'meta_title' => 'Tentang Kami - PT CITRA ABADI TEKNIK INDONESIA',
                'meta_description' => 'Tentang PT CITRA ABADI TEKNIK INDONESIA, partner profesional untuk kebutuhan baut, mur, fastener, anchor, dan perlengkapan teknik industri.',
                'is_active' => true,
                'published_at' => now(),
                'created_by' => null,
            ]
        );
    }
}
