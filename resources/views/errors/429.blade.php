@include('errors.layout', [
    'status' => 429,
    'title' => 'Terlalu banyak permintaan',
    'description' => 'Kami menerima terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar, lalu coba kembali.',
    'showRetry' => true,
])
