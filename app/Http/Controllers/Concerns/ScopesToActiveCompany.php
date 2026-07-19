<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;

/**
 * Dipakai controller Master Data (Products, Coupons, Flash Sale, Store Location) supaya CRUD-nya
 * selalu beroperasi dalam konteks perusahaan aktif di company switcher -- baik untuk memfilter list
 * maupun menolak akses record milik perusahaan lain lewat manipulasi URL/ID. Lihat
 * docs/prd-multi-company-foundation.md §3.
 */
trait ScopesToActiveCompany
{
    protected function activeCompanyId(): int
    {
        return (int) (User::activeCompanyId() ?? 0);
    }

    protected function guardCompanyOwnership(?int $modelCompanyId): void
    {
        abort_unless($modelCompanyId === $this->activeCompanyId(), 404);
    }
}
