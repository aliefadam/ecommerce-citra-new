<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;

class StarterBlogContentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'panduan-memilih-baut-untuk-kebutuhan-proyek',
                'title' => 'Panduan Memilih Baut untuk Kebutuhan Proyek',
                'excerpt' => 'Kenali ukuran, material, dan jenis baut agar pilihan produk lebih sesuai dengan kebutuhan pekerjaan di lapangan.',
                'content' => <<<'HTML'
<p>Memilih baut terlihat sederhana, tetapi keputusan kecil ini bisa memengaruhi kekuatan, keamanan, dan kerapian hasil pekerjaan. Setiap proyek memiliki kebutuhan berbeda, mulai dari sambungan ringan, konstruksi, mesin, hingga instalasi outdoor.</p>

<h2>Mulai dari ukuran dan drat</h2>
<p>Ukuran baut biasanya mengacu pada diameter, panjang, dan jenis drat. Pastikan diameter baut sesuai dengan lubang atau komponen pasangannya. Panjang baut juga perlu cukup untuk mengunci material tanpa menyisakan terlalu banyak ulir yang tidak terpakai.</p>

<h2>Perhatikan material</h2>
<p>Baut baja umum digunakan untuk kebutuhan mekanik dan konstruksi ringan. Untuk area lembap atau outdoor, stainless steel sering menjadi pilihan karena lebih tahan korosi. Jika pekerjaan membutuhkan kekuatan lebih tinggi, perhatikan grade atau kelas kekuatan baut.</p>

<h2>Sesuaikan kepala baut</h2>
<ul>
    <li><strong>Hex bolt</strong> cocok untuk sambungan yang membutuhkan torsi kuat.</li>
    <li><strong>Socket head bolt</strong> cocok untuk area sempit dan tampilan lebih rapi.</li>
    <li><strong>Counter sunk bolt</strong> cocok ketika kepala baut perlu rata dengan permukaan.</li>
</ul>

<blockquote>Pilihan baut yang tepat membantu pekerjaan lebih aman, presisi, dan mudah dirawat.</blockquote>

<p>Jika ragu, siapkan informasi ukuran, material yang disambung, lokasi penggunaan, dan beban kerja. Informasi ini akan sangat membantu saat konsultasi produk.</p>
HTML,
            ],
            [
                'slug' => 'perbedaan-stainless-steel-dan-baja-karbon-pada-fastener',
                'title' => 'Perbedaan Stainless Steel dan Baja Karbon pada Fastener',
                'excerpt' => 'Stainless steel dan baja karbon punya karakter berbeda. Pahami keunggulannya sebelum memilih fastener untuk pekerjaanmu.',
                'content' => <<<'HTML'
<p>Fastener seperti baut, mur, ring, dan sekrup hadir dalam berbagai material. Dua pilihan yang sering ditemui adalah stainless steel dan baja karbon. Keduanya sama-sama berguna, tetapi punya karakter yang berbeda.</p>

<h2>Stainless steel</h2>
<p>Stainless steel dikenal karena ketahanan terhadap karat. Material ini cocok untuk area outdoor, lingkungan lembap, area dekat air, atau aplikasi yang membutuhkan tampilan lebih bersih.</p>

<h2>Baja karbon</h2>
<p>Baja karbon biasanya dipilih karena kuat, ekonomis, dan tersedia dalam banyak grade. Untuk kebutuhan struktur, mesin, atau sambungan dengan beban tertentu, baja karbon dengan grade yang tepat bisa menjadi pilihan efektif.</p>

<h2>Kapan memilih masing-masing?</h2>
<ul>
    <li>Pilih stainless steel jika prioritasnya adalah ketahanan korosi.</li>
    <li>Pilih baja karbon jika prioritasnya adalah kekuatan, biaya, dan ketersediaan ukuran.</li>
    <li>Gunakan pelapisan seperti zinc plated jika baja karbon dipakai di area yang butuh perlindungan tambahan.</li>
</ul>

<p>Material terbaik bukan selalu yang paling mahal, tetapi yang paling sesuai dengan lingkungan kerja dan fungsi sambungannya.</p>
HTML,
            ],
            [
                'slug' => 'mengenal-anchor-dan-fungsinya-untuk-instalasi',
                'title' => 'Mengenal Anchor dan Fungsinya untuk Instalasi',
                'excerpt' => 'Anchor membantu pemasangan ke beton atau dinding menjadi lebih kuat. Kenali jenis dan cara memilihnya.',
                'content' => <<<'HTML'
<p>Anchor digunakan untuk mengikat benda ke media seperti beton, bata, atau dinding. Produk ini banyak dipakai untuk pemasangan bracket, rangka, mesin, railing, hingga kebutuhan konstruksi ringan.</p>

<h2>Fungsi utama anchor</h2>
<p>Anchor bekerja dengan menciptakan cengkeraman di dalam media pasang. Saat dikencangkan, anchor akan mengunci dan membantu menahan beban agar komponen tidak mudah lepas.</p>

<h2>Jenis anchor yang umum</h2>
<ul>
    <li><strong>Dynabolt</strong> banyak digunakan untuk pemasangan pada beton.</li>
    <li><strong>Chemical anchor</strong> cocok untuk aplikasi yang membutuhkan daya rekat tinggi.</li>
    <li><strong>Fischer atau nylon anchor</strong> cocok untuk kebutuhan ringan seperti bracket kecil atau aksesoris.</li>
</ul>

<h2>Hal yang perlu dicek</h2>
<p>Perhatikan jenis media, diameter lubang bor, kedalaman tanam, dan beban yang akan ditahan. Kesalahan ukuran lubang atau kedalaman pemasangan bisa mengurangi kekuatan anchor.</p>

<blockquote>Anchor yang kuat dimulai dari pemilihan jenis yang tepat dan pemasangan yang presisi.</blockquote>
HTML,
            ],
            [
                'slug' => 'cara-membaca-ukuran-baut-mur-dan-ring',
                'title' => 'Cara Membaca Ukuran Baut, Mur, dan Ring',
                'excerpt' => 'Memahami kode ukuran fastener akan membuat proses pembelian lebih cepat dan mengurangi risiko salah pilih.',
                'content' => <<<'HTML'
<p>Ukuran fastener biasanya ditulis dengan kombinasi huruf dan angka. Bagi yang belum terbiasa, kode seperti M8 x 40 atau M10 bisa terasa membingungkan. Padahal, membaca ukuran ini cukup sederhana.</p>

<h2>Arti kode M pada baut</h2>
<p>Huruf M berarti metric. Angka setelah M menunjukkan diameter baut dalam milimeter. Contohnya, M8 berarti diameter baut sekitar 8 mm.</p>

<h2>Panjang baut</h2>
<p>Pada kode M8 x 40, angka 40 menunjukkan panjang baut dalam milimeter. Panjang ini umumnya diukur dari bawah kepala baut sampai ujung baut, kecuali untuk tipe kepala tertentu seperti countersunk.</p>

<h2>Ukuran mur dan ring</h2>
<p>Mur harus mengikuti diameter dan jenis drat baut. Ring juga dipilih berdasarkan diameter baut, tetapi perhatikan diameter luar dan ketebalannya jika digunakan untuk distribusi tekanan.</p>

<ul>
    <li>Baut M8 dipasangkan dengan mur M8.</li>
    <li>Ring M8 digunakan untuk baut diameter M8.</li>
    <li>Panjang baut dipilih berdasarkan total tebal material yang disambung.</li>
</ul>

<p>Jika ingin lebih aman, bawa contoh lama atau catat diameter, panjang, dan kebutuhan material sebelum membeli.</p>
HTML,
            ],
            [
                'slug' => 'tips-menyimpan-fastener-agar-tetap-rapi-dan-tidak-berkarat',
                'title' => 'Tips Menyimpan Fastener agar Tetap Rapi dan Tidak Berkarat',
                'excerpt' => 'Penyimpanan yang baik membantu stok baut, mur, ring, dan sekrup tetap mudah dicari serta lebih awet.',
                'content' => <<<'HTML'
<p>Fastener sering dibeli dalam jumlah banyak dan berbagai ukuran. Tanpa penyimpanan yang rapi, stok mudah tercampur, sulit dicari, bahkan berisiko berkarat jika terkena kelembapan.</p>

<h2>Pisahkan berdasarkan ukuran</h2>
<p>Gunakan kotak partisi atau wadah kecil untuk memisahkan ukuran. Label seperti M6, M8, M10, atau panjang tertentu akan membantu proses pencarian lebih cepat.</p>

<h2>Jaga dari kelembapan</h2>
<p>Simpan fastener di tempat kering. Untuk stok yang jarang digunakan, gunakan kemasan tertutup dan tambahkan silica gel jika diperlukan. Hindari menyimpan langsung di lantai atau area yang mudah terkena air.</p>

<h2>Gunakan sistem stok sederhana</h2>
<ul>
    <li>Catat item yang sering digunakan.</li>
    <li>Pisahkan stok baru dan stok lama.</li>
    <li>Tentukan batas minimum agar tidak kehabisan saat dibutuhkan.</li>
</ul>

<p>Penyimpanan yang baik bukan hanya membuat area kerja lebih rapi, tetapi juga mengurangi pembelian dobel dan memudahkan kontrol stok.</p>
HTML,
            ],
        ];

        foreach ($posts as $index => $post) {
            ContentPage::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'type' => ContentPage::TYPE_POST,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'hero_image' => null,
                    'meta_title' => $post['title'] . ' - Blog',
                    'meta_description' => $post['excerpt'],
                    'is_active' => true,
                    'published_at' => now()->subDays(count($posts) - $index),
                    'created_by' => null,
                ]
            );
        }
    }
}
