<?php

return [
    'readiness_token' => env('OPS_READINESS_TOKEN', ''),
    'backup_disk' => env('OPS_BACKUP_DISK', 'local'),
    'backup_directory' => trim((string) env('OPS_BACKUP_DIRECTORY', 'backups'), '/'),
    'backup_password' => env('OPS_BACKUP_PASSWORD', ''),
    'mysqldump_binary' => env('OPS_MYSQLDUMP_BINARY', 'mysqldump'),
    'backup_retention_days' => (int) env('OPS_BACKUP_RETENTION_DAYS', 30),
    'backup_max_age_hours' => (int) env('OPS_BACKUP_MAX_AGE_HOURS', 26),
    'disk_warning_percent' => (int) env('OPS_DISK_WARNING_PERCENT', 80),
    'disk_critical_percent' => (int) env('OPS_DISK_CRITICAL_PERCENT', 90),
    'queue_oldest_minutes' => (int) env('OPS_QUEUE_OLDEST_MINUTES', 10),
];
