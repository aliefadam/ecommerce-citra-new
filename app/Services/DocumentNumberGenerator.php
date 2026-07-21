<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberGenerator
{
    /**
     * Generate a document number scoped to a company, formatted as
     * {company.invoice_prefix}-{typeCode}-{YmdHis}-{4-digit sequence}.
     *
     * The sequence counts existing rows of $modelClass for the same company
     * created today, with a row lock to stay safe under concurrent requests.
     * Must be called from within a DB::transaction() so the lock takes effect.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function generate(string $modelClass, string $typeCode, int $companyId): string
    {
        $prefix = Company::query()->whereKey($companyId)->value('invoice_prefix');

        $sequence = ((int) $modelClass::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->lockForUpdate()
            ->count()) + 1;

        return sprintf(
            '%s-%s-%s-%s',
            $prefix,
            $typeCode,
            now()->format('YmdHis'),
            str_pad((string) $sequence, 4, '0', STR_PAD_LEFT)
        );
    }
}
