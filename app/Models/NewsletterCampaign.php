<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'subject',
        'message',
        'cta_label',
        'cta_url',
        'hero_image',
        'test_email',
        'status',
        'recipient_count',
        'scheduled_at',
        'sent_at',
        'last_error',
        'created_by',
        'sent_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
