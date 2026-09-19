<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;
use App\Models\User;

/**
 * Dipakai controller admin company-aware supaya CRUD dan aksi operasional selalu beroperasi dalam
 * konteks perusahaan aktif di company switcher -- baik untuk memfilter list maupun menolak akses
 * record milik perusahaan lain lewat manipulasi URL/ID. Lihat
 * docs/prd-multi-company-foundation.md §3.
 */
trait ScopesToActiveCompany
{
    protected function activeCompanyId(): int
    {
        return (int) (User::activeCompanyId()
            ?? Company::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->value('id')
            ?? 0);
    }

    protected function guardCompanyOwnership(?int $modelCompanyId): void
    {
        abort_unless($modelCompanyId === $this->activeCompanyId(), 404);
    }
}
