# 🛒 Citra E-Commerce

Citra E-Commerce adalah aplikasi toko online berbasis **Laravel 13** dengan fitur lengkap mulai dari manajemen produk, keranjang belanja, checkout, pembayaran online via **Midtrans**, hingga kalkulasi ongkos kirim otomatis via **RajaOngkir**.

## 📋 Daftar Isi

- [Tech Stack](#-tech-stack)
- [Fitur](#-fitur)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Struktur Project](#-struktur-project)
- [Database Seeder](#-database-seeder)
- [Lisensi](#-lisensi)

## 🚀 Tech Stack

| Layer            | Teknologi                         |
| ---------------- | --------------------------------- |
| **Framework**    | Laravel 13 (PHP 8.3)              |
| **Frontend**     | Blade Templates + Tailwind CSS 4  |
| **Build Tool**   | Vite 8                            |
| **Database**     | MySQL                             |
| **Payment**      | Midtrans (Sandbox / Production)   |
| **Shipping**     | RajaOngkir API                    |
| **Auth**         | Laravel Auth + Google OAuth       |
| **Mail**         | SMTP (Gmail)                      |
| **Queue**        | Database Driver                   |

## ✨ Fitur

### 🛍️ Frontend (Customer)

- **Homepage** — Banner slider, flash sale, produk terbaru
- **Pencarian & Kategori** — Cari produk dan jelajahi berdasarkan kategori
- **Detail Produk** — Galeri gambar, varian produk, review pelanggan
- **Flash Sale** — Produk diskon dengan batas waktu
- **Keranjang Belanja** — Tambah, ubah jumlah, hapus item
- **Wishlist** — Simpan produk favorit
- **Checkout** — Pilih alamat, kurir, dan metode pembayaran
- **Ongkos Kirim** — Kalkulasi otomatis via RajaOngkir (JNE, SiCepat, J&T)
- **Pembayaran Online** — Integrasi Midtrans (transfer bank, e-wallet, dll)
- **Profil User** — Edit biodata, ubah password, kelola alamat
- **Riwayat Transaksi** — Lihat status pesanan & beri review produk
- **Notifikasi** — Pemberitahuan status pesanan real-time
- **Login Google** — Autentikasi cepat via Google OAuth
- **Lupa Password** — Reset password via email

### ⚙️ Backend (Admin Panel)

- **Dashboard** — Statistik penjualan & grafik
- **Manajemen Produk** — CRUD produk dengan varian & gambar
- **Manajemen Kategori** — Kategori utama & sub-kategori
- **Manajemen Varian** — Opsi varian produk (ukuran, warna, dll)
- **Manajemen Stok** — Riwayat pergerakan stok (stock movement)
- **Flash Sale** — Buat & kelola event flash sale
- **Manajemen Banner** — Atur banner homepage
- **Manajemen Transaksi** — Proses & kirim pesanan
- **Lokasi Toko** — Pengaturan alamat asal pengiriman
- **Manajemen User** — Daftar user terdaftar
- **Ganti Password Admin** — Keamanan akun admin

## 💻 Persyaratan Sistem

- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL
- Laragon / XAMPP / Herd (atau web server lainnya)

## 📦 Instalasi

1. **Clone repository**

   ```bash
   git clone https://github.com/username/citra-ecommerce-new.git
   cd citra-ecommerce-new
   ```

2. **Install dependensi PHP**

   ```bash
   composer install
   ```

3. **Install dependensi JavaScript**

   ```bash
   npm install
   ```

4. **Salin file environment**

   ```bash
   cp .env.example .env
   ```

5. **Generate application key**

   ```bash
   php artisan key:generate
   ```

6. **Konfigurasi database** di file `.env` (lihat [Konfigurasi Environment](#-konfigurasi-environment))

7. **Jalankan migrasi & seeder**

   ```bash
   php artisan migrate --seed
   ```

8. **Buat symlink storage**

   ```bash
   php artisan storage:link
   ```

## 🔧 Konfigurasi Environment

Sesuaikan variabel berikut di file `.env`:

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=citra-ecommerce-new
DB_USERNAME=root
DB_PASSWORD=
```

### Google OAuth

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://your-domain.com/auth/google/callback
```

### RajaOngkir

```env
RAJAONGKIR_API_KEY=your-rajaongkir-api-key
RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api/v1
RAJAONGKIR_ORIGIN_ID=12345
RAJAONGKIR_COURIERS=jne:sicepat:jnt
CHECKOUT_DEFAULT_ITEM_WEIGHT=1
```

### Midtrans

```env
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_IS_PRODUCTION=false
```

### Mail (SMTP Gmail)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Citra Ecommerce"
```

> **Catatan:** Untuk Gmail, gunakan [App Password](https://support.google.com/accounts/answer/185833) bukan password akun biasa.

## ▶️ Menjalankan Aplikasi

### Cara Cepat (Semua service sekaligus)

```bash
composer dev
```

Perintah ini menjalankan secara bersamaan:
- `php artisan serve` — Server Laravel
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log viewer
- `npm run dev` — Vite dev server

### Cara Manual

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Vite dev server
npm run dev

# Terminal 3 — Queue worker (opsional, untuk email & notifikasi)
php artisan queue:listen
```

Akses aplikasi di: **http://localhost:8000**

## 📁 Struktur Project

```
citra-ecommerce-new/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # 23 controller (Auth, Frontend, Backend, dll)
│   │   └── Middleware/         # AdminOnly middleware
│   ├── Mail/                   # InvoiceOrder mailable
│   ├── Models/                 # 19 Eloquent model
│   ├── Providers/              # Service providers
│   └── Services/               # RajaOngkirService
├── database/
│   ├── migrations/             # 33 file migrasi
│   └── seeders/                # 10 seeder (User, Product, Category, dll)
├── resources/
│   ├── css/                    # Stylesheet
│   ├── js/                     # JavaScript
│   └── views/
│       ├── auth/               # Halaman login, register, reset password
│       ├── backend/            # Admin panel views
│       ├── emails/             # Template email invoice
│       ├── frontend/           # Halaman customer-facing
│       ├── layouts/            # Layout utama
│       └── partials/           # Komponen reusable
├── routes/
│   └── web.php                 # Definisi semua route
├── public/                     # Asset publik
├── composer.json               # Dependensi PHP
├── package.json                # Dependensi JavaScript
└── vite.config.js              # Konfigurasi Vite + Tailwind
```

## 🌱 Database Seeder

Jalankan seeder untuk mengisi data awal:

```bash
php artisan db:seed
```

Seeder yang tersedia:

| Seeder                  | Keterangan                     |
| ----------------------- | ------------------------------ |
| `UserSeeder`            | Akun admin & user demo         |
| `AddressSeeder`         | Alamat contoh                  |
| `MainCategorySeeder`    | Kategori utama                 |
| `CategoryDetailSeeder`  | Sub-kategori                   |
| `VariantSeeder`         | Varian produk (ukuran, warna)  |
| `ProductSeeder`         | Produk contoh                  |
| `BannerSeeder`          | Banner homepage                |
| `StoreLocationSeeder`   | Lokasi toko asal pengiriman    |

## 📄 Lisensi

Project ini dibuat untuk keperluan pembelajaran dan pengembangan.
