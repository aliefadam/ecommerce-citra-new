<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAnalyticsEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'event_name',
        'surface',
        'app_version',
        'route_name',
        'session_hash',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];
}
