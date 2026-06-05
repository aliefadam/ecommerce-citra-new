<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionTaxInvoice extends Model
{
    public const STATUS_NOT_REQUESTED = 'not_requested';

    public const STATUS_REQUESTED = 'requested';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_SENT = 'sent';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NOT_REQUESTED,
        self::STATUS_REQUESTED,
        self::STATUS_PROCESSING,
        self::STATUS_ISSUED,
        self::STATUS_SENT,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'transaction_id',
        'requested_by_user_id',
        'status',
        'taxpayer_name',
        'taxpayer_number',
        'taxpayer_address',
        'taxpayer_email',
        'customer_note',
        'admin_note',
        'tax_invoice_number',
        'tax_invoice_date',
        'tax_invoice_file_path',
        'uploaded_by_admin_id',
        'requested_at',
        'processing_at',
        'issued_at',
        'sent_at',
        'last_downloaded_at',
        'email_failed_at',
        'email_failure_reason',
        'rejected_at',
        'rejected_reason',
    ];

    protected $casts = [
        'tax_invoice_date' => 'date',
        'requested_at' => 'datetime',
        'processing_at' => 'datetime',
        'issued_at' => 'datetime',
        'sent_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'email_failed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function uploadedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_admin_id');
    }

    public function getMaskedTaxpayerNumberAttribute(): string
    {
        return self::maskTaxpayerNumber($this->taxpayer_number);
    }

    public function setTaxpayerNumberAttribute(?string $value): void
    {
        $this->attributes['taxpayer_number'] = self::normalizeTaxpayerNumber($value);
    }

    public static function normalizeTaxpayerNumber(?string $taxpayerNumber): string
    {
        return preg_replace('/\D+/', '', (string) $taxpayerNumber) ?: '';
    }

    public static function maskTaxpayerNumber(?string $taxpayerNumber): string
    {
        $digits = self::normalizeTaxpayerNumber($taxpayerNumber);

        if ($digits === '') {
            return '-';
        }

        if (strlen($digits) <= 8) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 4)
            .str_repeat('*', strlen($digits) - 8)
            .substr($digits, -4);
    }
}
