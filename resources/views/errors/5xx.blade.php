@include('errors.layout', [
    'status' => 500,
    'title' => 'Terjadi kendala pada sistem',
    'description' => 'Permintaan Anda belum dapat diproses. Silakan coba kembali beberapa saat lagi.',
    'showRetry' => true,
])
