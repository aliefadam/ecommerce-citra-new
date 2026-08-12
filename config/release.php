<?php

return [
    'feature_freeze' => filter_var(env('RELEASE_FEATURE_FREEZE', false), FILTER_VALIDATE_BOOLEAN),
    'owner' => env('RELEASE_OWNER', ''),
    'approved_at' => env('RELEASE_APPROVED_AT', ''),
    'initial_backup_path' => env('RELEASE_INITIAL_BACKUP_PATH', ''),
    'restore_drill_at' => env('RELEASE_RESTORE_DRILL_AT', ''),
    'load_test_report' => env('RELEASE_LOAD_TEST_REPORT', ''),
    'alert_tested_at' => env('RELEASE_ALERT_TESTED_AT', ''),
    'production_smoke_at' => env('RELEASE_PRODUCTION_SMOKE_AT', ''),
    'midtrans_certified_at' => env('RELEASE_MIDTRANS_CERTIFIED_AT', ''),
    'rajaongkir_certified_at' => env('RELEASE_RAJAONGKIR_CERTIFIED_AT', ''),
    'email_certified_at' => env('RELEASE_EMAIL_CERTIFIED_AT', ''),
    'whatsapp_required' => filter_var(env('RELEASE_WHATSAPP_REQUIRED', false), FILTER_VALIDATE_BOOLEAN),
    'whatsapp_certified_at' => env('RELEASE_WHATSAPP_CERTIFIED_AT', ''),
];
