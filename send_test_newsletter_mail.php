<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Mail::raw(
    'Halo, ini test email dari fitur newsletter Citra Ecommerce.',
    function ($message) {
        $message->to('denyprasetyo41@gmail.com')
            ->subject('Test Email Newsletter - Citra Ecommerce');
    }
);

echo "MAIL_SENT\n";
