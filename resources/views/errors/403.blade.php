@include('errors.layout', [
    'status' => 403,
    'title' => 'Akses tidak diizinkan',
    'description' => 'Anda tidak memiliki izin untuk membuka halaman ini. Jika menurut Anda ini keliru, hubungi administrator.',
])
