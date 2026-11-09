<?php

return [
    'enabled' => (bool) env('MOBILE_SPA_ENABLED', false),
    'pilot' => (bool) env('MOBILE_SPA_PILOT', true),
    'version' => env('MOBILE_SPA_VERSION', 'catalog-2'),
];
