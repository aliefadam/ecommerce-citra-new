<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Open Catalog API (docs/prd-company-catalog-api.md) dipanggil dari
    | banyak website katalog eksternal, termasuk langsung dari browser.
    | Karena read-only & publik, semua origin diizinkan untuk path api/*.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'HEAD', 'OPTIONS'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['ETag', 'Retry-After', 'X-RateLimit-Limit', 'X-RateLimit-Remaining'],

    'max_age' => 3600,

    'supports_credentials' => false,

];
