<?php

namespace App\Models\Concerns;

use App\Models\Company;

/**
 * Selama hanya ada satu perusahaan aktif (Fase 1), record yang dibuat tanpa company_id eksplisit
 * otomatis mengikuti perusahaan pertama -- menjaga "tanpa perubahan perilaku" untuk kode/test yang
 * belum company-aware. Fase 2 akan selalu mengisi company_id eksplisit dari konteks aktif, jadi
 * default ini tidak akan terpakai begitu ada lebih dari satu perusahaan.
 */
trait DefaultsToPrimaryCompany
{
    protected static function bootDefaultsToPrimaryCompany(): void
    {
        static::creating(function ($model) {
            if (blank($model->company_id)) {
                $model->company_id = Company::query()->orderBy('id')->value('id');
            }
        });
    }
}
