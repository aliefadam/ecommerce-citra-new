@php
    $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;
@endphp

@include('errors.layout', [
    'status' => $status,
    'title' => 'Permintaan tidak dapat diproses',
    'description' => 'Periksa kembali alamat atau data yang dikirim, lalu coba kembali.',
])
