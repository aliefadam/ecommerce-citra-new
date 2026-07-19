<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class CompanyScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $companies = Company::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        if (!$user || $companies->isEmpty()) {
            return $next($request);
        }

        $isSuperAdmin = strtolower((string) $user->role) === 'admin';
        $allowedCompanyIds = $isSuperAdmin ? null : $this->allowedCompanyIdsFor($user);

        if (!$isSuperAdmin && $allowedCompanyIds !== null && $allowedCompanyIds->isEmpty()) {
            return redirect()->route('frontend.index')->with('error', 'Akun Anda belum ditugaskan ke perusahaan mana pun.');
        }

        $isAllowed = fn (Company $company) => $allowedCompanyIds === null || $allowedCompanyIds->contains($company->id);
        $accessibleCompanies = $companies->filter($isAllowed)->values();

        $activeId = (int) session('admin_active_company_id', 0);
        $activeCompany = $accessibleCompanies->firstWhere('id', $activeId) ?? $accessibleCompanies->first();

        if ($activeCompany && $activeCompany->id !== $activeId) {
            session(['admin_active_company_id' => $activeCompany->id]);
        }

        $request->attributes->set('accessible_companies', $accessibleCompanies);
        view()->share('activeCompany', $activeCompany);
        view()->share('accessibleCompanies', $accessibleCompanies);

        return $next($request);
    }

    /**
     * Null berarti akses semua perusahaan. Berlaku untuk assignment company_id=NULL (hasil
     * backfill role tunggal lama) maupun user yang sama sekali belum punya baris assignment --
     * kasus terakhir ini fallback ke users.admin_role_id langsung (lihat User::permissionsForCompany()
     * dan docs/prd-multi-company-foundation.md §3), supaya staff yang dibuat tanpa lewat
     * AdminUserController tidak ter-lock out dari seluruh admin panel.
     */
    private function allowedCompanyIdsFor(User $user): ?Collection
    {
        $assignments = $user->companyAssignments;

        if ($assignments->isEmpty() || $assignments->contains(fn ($assignment) => $assignment->company_id === null)) {
            return null;
        }

        return $assignments->pluck('company_id')->filter()->unique()->values();
    }
}
