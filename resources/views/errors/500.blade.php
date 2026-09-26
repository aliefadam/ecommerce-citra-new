@include('errors.layout', [
    'status' => 500,
    'title' => 'Terjadi kendala pada sistem',
    'description' => 'Permintaan Anda belum dapat diproses. Tim kami dapat menanganinya tanpa Anda perlu mengubah apa pun.',
    'showRetry' => true,
])
