<?php

namespace App\Support;

/**
 * FAQ & kebijakan toko untuk IBOQ. Jawaban disimpan di StoreSetting
 * (key "boqi_faq_<topik>") dan direvisi admin dari Setting > IBOQ / FAQ.
 *
 * Placeholder yang diganti otomatis: {whatsapp}, {url_daftar}, {url_lupa_password},
 * {url_lacak_pesanan}, {url_flash_sale}, {url_blog}.
 */
class BoqiFaq
{
    public const PREFIX = 'boqi_faq_';

    public const MAX_LENGTH = 1500;

    public static function topics(): array
    {
        return [
            'cod' => [
                'label' => 'COD (Bayar di Tempat)',
                'pattern' => '/\b(cod|bayar di tempat|bayar ditempat|cash on delivery)(?:nya|kan)?\b/iu',
                'default' => 'Saat ini pembayaran COD (bayar di tempat) belum tersedia. Pembayaran dapat dilakukan melalui Virtual Account, QRIS, atau transfer manual.',
            ],
            'pembayaran_lain' => [
                'label' => 'E-wallet, Cicilan, Paylater & Kartu Kredit',
                'pattern' => '/\b(cicil|cicilan|paylater|pay later|kartu kredit|credit card|kredivo|akulaku|gopay|ovo|dana|shopeepay|e-?wallet|tempo)(?:nya|kan)?\b/iu',
                'default' => 'Pembayaran e-wallet (GoPay, OVO, DANA, ShopeePay, dan lainnya) bisa memakai QRIS: pilih QRIS saat checkout lalu scan kodenya dari aplikasi e-wallet Anda. Cicilan, paylater, dan kartu kredit saat ini belum tersedia.',
            ],
            'retur' => [
                'label' => 'Retur & Refund',
                'pattern' => '/\b(retur|return|refund|pengembalian|kembalikan|tukar|rusak|cacat|salah kirim|komplain|complain)(?:nya|kan)?\b/iu',
                'default' => 'Return/refund dapat diajukan maksimal 7 hari setelah pesanan diterima melalui menu Profil > Pesanan Saya > Ajukan Return, dengan menyertakan alasan dan foto kondisi barang. Lama proses refund mengikuti hasil pengecekan tim kami. Butuh bantuan cepat? Hubungi CS WhatsApp: {whatsapp}',
            ],
            'garansi' => [
                'label' => 'Garansi',
                'pattern' => '/\b(garansi|warranty|jaminan)(?:nya|kan)?\b/iu',
                'default' => 'Ketentuan garansi dapat berbeda untuk tiap produk. Silakan cek deskripsi produk atau hubungi CS WhatsApp {whatsapp} untuk memastikan garansi produk yang Anda butuhkan.',
            ],
            'pembatalan' => [
                'label' => 'Pembatalan Pesanan',
                'pattern' => '/\b(batal|batalkan|membatalkan|pembatalan|cancel)(?:nya|kan)?\b/iu',
                'default' => 'Pesanan yang belum dibayar dapat dibatalkan dari halaman menunggu pembayaran, dan akan batal otomatis bila melewati batas waktu pembayaran. Untuk pesanan yang sudah dibayar, hubungi CS WhatsApp {whatsapp} dengan menyertakan nomor invoice.',
            ],
            'restock' => [
                'label' => 'Restock & Inden',
                'pattern' => '/\b(restock|re-stock|restok|stok kosong|stok habis|kapan ada|kapan tersedia|kapan ready|inden|pre ?order)(?:nya|kan)?\b/iu',
                'default' => 'Jadwal restock belum dapat dipastikan. Simpan produk ke wishlist atau hubungi CS WhatsApp {whatsapp} untuk menanyakan ketersediaan atau inden.',
            ],
            'lacak' => [
                'label' => 'Status & Lacak Pesanan',
                'pattern' => '/\b(lacak|tracking|resi|sampai mana|status pesanan|pesanan saya|cek pesanan|order saya|invoice)(?:nya|kan)?\b/iu',
                'default' => 'Status pesanan dan nomor resi dapat dicek di menu Profil > Pesanan setelah login, atau melalui halaman Lacak Pesanan: {url_lacak_pesanan}',
            ],
            'pengiriman' => [
                'label' => 'Pengiriman, Kurir & Ongkir',
                'pattern' => '/\b(kurir|ekspedisi|pengiriman|dikirim|kirim|ongkir|ongkos|jne|sicepat|j&t|jnt|estimasi|lama pengiriman|lama kirim|lama sampai|berapa lama sampai|berapa lama dikirim|kapan sampai|kapan dikirim)(?:nya|kan)?\b/iu',
                'default' => 'Pengiriman memakai kurir JNE, SiCepat, dan J&T. Pilihan layanan dan ongkir muncul otomatis di halaman checkout sesuai alamat tujuan dan berat pesanan. Estimasi tiba mengikuti layanan kurir yang dipilih (tertulis di pilihan kurir saat checkout).',
            ],
            'toko' => [
                'label' => 'Alamat, Jam Operasional & Ambil Sendiri',
                'pattern' => '/\b(alamat|lokasi|di ?mana|ambil sendiri|ambil langsung|pickup|pick up|datang ke toko|jam buka|jam operasional|jam kerja|buka jam|tutup jam|hari libur|showroom|gudang|offline)(?:nya|kan)?\b/iu',
                'default' => 'Untuk alamat toko, jam operasional, dan pengambilan barang langsung (ambil sendiri), silakan hubungi CS WhatsApp kami: {whatsapp}',
            ],
            'grosir' => [
                'label' => 'Grosir, Proyek & Penawaran Harga',
                'pattern' => '/\b(grosir|partai|jumlah besar|proyek|project|penawaran|quotation|rfq|harga khusus|nego|b2b|kontrak|distributor|reseller|tender)(?:nya|kan)?\b/iu',
                'default' => 'Kami melayani pembelian partai besar dan kebutuhan proyek. Kirim daftar kebutuhan (nama produk, ukuran, jumlah) ke CS WhatsApp {whatsapp}, tim sales akan membuatkan penawaran harga (quotation).',
            ],
            'pajak' => [
                'label' => 'Faktur Pajak / PPN',
                'pattern' => '/\b(faktur|pajak|npwp|ppn|e-?faktur|invoice pt)(?:nya|kan)?\b/iu',
                'default' => 'Faktur pajak dapat diajukan dari halaman detail pesanan (menu Profil > Pesanan) setelah pembayaran lunas, dengan melengkapi data NPWP perusahaan Anda.',
            ],
            'minimal_order' => [
                'label' => 'Minimal Order',
                'pattern' => '/\b(minimal|minimum|min\.?)\s+(order|pembelian|beli|belanja|pesan|qty)\b|\bmoq\b/iu',
                'default' => 'Minimal pembelian mengikuti ketentuan pada halaman masing-masing produk.',
            ],
            'akun' => [
                'label' => 'Akun, Daftar & Lupa Password',
                'pattern' => '/\b(daftar|registrasi|register|buat akun|akun|login|log in|masuk akun|lupa password|lupa kata sandi|lupa sandi|reset password|ganti password)(?:nya|kan)?\b/iu',
                'default' => 'Daftar akun baru di {url_daftar}. Lupa password? Atur ulang melalui {url_lupa_password} dengan email yang terdaftar.',
            ],
            'kontak' => [
                'label' => 'Kontak CS',
                'pattern' => '/\b(cs|customer service|whatsapp|wa|kontak|contact|hubungi|telepon|telpon|nomor admin|no admin|email toko)(?:nya|kan)?\b/iu',
                'default' => 'Hubungi CS kami melalui WhatsApp: {whatsapp}',
            ],
            'promo' => [
                'label' => 'Promo, Voucher & Kupon',
                'pattern' => '/\b(promo|diskon|discount|voucher|kupon|coupon|potongan|flash ?sale|cashback)(?:nya|kan)?\b/iu',
                'default' => 'Kode voucher/kupon dapat dimasukkan di halaman checkout. Promo yang sedang berjalan dapat dilihat di halaman Flash Sale: {url_flash_sale}',
            ],
            'tambahan' => [
                'label' => 'FAQ Tambahan (bebas)',
                'pattern' => null,
                'default' => '',
            ],
        ];
    }

    public static function key(string $topic): string
    {
        return self::PREFIX.$topic;
    }

    /** Default StoreSetting untuk seluruh topik FAQ. */
    public static function defaults(): array
    {
        $defaults = [];
        foreach (self::topics() as $topic => $definition) {
            $defaults[self::key($topic)] = $definition['default'];
        }

        return $defaults;
    }

    /** Jawaban per topik (placeholder sudah diganti); topik kosong dilewati. */
    public static function entries(array $settings): array
    {
        $entries = [];
        foreach (self::topics() as $topic => $definition) {
            $answer = trim((string) ($settings[self::key($topic)] ?? $definition['default']));
            if ($answer !== '') {
                $entries[$topic] = [
                    'label' => $definition['label'],
                    'answer' => self::fill($answer, $settings),
                ];
            }
        }

        return $entries;
    }

    /**
     * Topik FAQ yang paling cocok dengan pertanyaan (urutan definisi = prioritas).
     *
     * @return array{topic: string, label: string, answer: string}|null
     */
    public static function match(string $message, array $settings): ?array
    {
        $entries = self::entries($settings);
        foreach (self::topics() as $topic => $definition) {
            if ($definition['pattern'] === null || ! isset($entries[$topic])) {
                continue;
            }

            if (preg_match($definition['pattern'], $message) === 1) {
                return ['topic' => $topic, ...$entries[$topic]];
            }
        }

        return null;
    }

    /** Pertanyaan menyentuh salah satu topik layanan toko (meski jawabannya dikosongkan admin). */
    public static function mentionsTopic(string $message): bool
    {
        foreach (self::topics() as $definition) {
            if ($definition['pattern'] !== null && preg_match($definition['pattern'], $message) === 1) {
                return true;
            }
        }

        return false;
    }

    public static function fill(string $text, array $settings): string
    {
        $whatsApp = trim((string) ($settings['social_whatsapp'] ?? ''));

        return strtr($text, [
            '{whatsapp}' => $whatsApp !== '' ? $whatsApp : 'CS kami',
            '{url_daftar}' => route('register'),
            '{url_lupa_password}' => route('password.request'),
            '{url_lacak_pesanan}' => route('frontend.order-tracking.index'),
            '{url_flash_sale}' => route('frontend.flash-sale'),
            '{url_blog}' => route('frontend.blog.index'),
        ]);
    }
}
