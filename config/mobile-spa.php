<?php

return [
    'enabled' => (bool) env('MOBILE_SPA_ENABLED', false),
    'auto_redirect' => (bool) env('MOBILE_SPA_AUTO_REDIRECT', false),
    'pilot' => (bool) env('MOBILE_SPA_PILOT', true),
    'analytics_enabled' => (bool) env('MOBILE_SPA_ANALYTICS_ENABLED', false),
    'analytics_retention_days' => max(1, (int) env('MOBILE_SPA_ANALYTICS_RETENTION_DAYS', 90)),
    'checkout_rate_limit' => max(1, (int) env('MOBILE_SPA_CHECKOUT_RATE_LIMIT', 30)),
    'version' => env('MOBILE_SPA_VERSION', 'rollout-6'),
];
