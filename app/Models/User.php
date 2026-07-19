<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'admin_role_id',
    'first_name',
    'last_name',
    'username',
    'gender',
    'phone_country_code',
    'phone_number',
    'birth_date',
    'social_url',
    'bio',
    'google_id',
    'avatar',
    'point_balance',
    'lifetime_points',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function taxProfiles(): HasMany
    {
        return $this->hasMany(UserTaxProfile::class);
    }

    public function requestedTaxInvoices(): HasMany
    {
        return $this->hasMany(TransactionTaxInvoice::class, 'requested_by_user_id');
    }

    public function uploadedTaxInvoices(): HasMany
    {
        return $this->hasMany(TransactionTaxInvoice::class, 'uploaded_by_admin_id');
    }

    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function companyAssignments(): HasMany
    {
        return $this->hasMany(AdminCompanyAssignment::class);
    }

    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class);
    }

    /**
     * admin_role_id bisa NULL untuk staff yang aksesnya murni lewat company-specific override
     * (tanpa role global) -- jadi keberadaan admin_company_assignments dicek juga, bukan cuma
     * kolom admin_role_id. Lihat docs/prd-multi-company-foundation.md §3.
     */
    public function canAccessAdminPanel(): bool
    {
        if (strtolower((string) $this->role) === 'admin' || ! is_null($this->admin_role_id)) {
            return true;
        }

        return $this->companyAssignments()->exists();
    }

    /**
     * $companyId null berarti "perusahaan aktif di session" (company switcher). Assignment dengan
     * company_id NULL berlaku untuk semua perusahaan (hasil backfill single-role lama), jadi tetap
     * cocok walau $companyId tidak diisi -- ini yang menjaga perilaku tidak berubah selama hanya
     * ada satu perusahaan (lihat docs/prd-multi-company-foundation.md §3).
     */
    public function hasAdminPermission(string $permission, ?int $companyId = null): bool
    {
        if (strtolower((string) $this->role) === 'admin') {
            return true;
        }

        $companyId = $companyId ?? static::activeCompanyId();
        $permissions = $this->permissionsForCompany($companyId);

        if (in_array($permission, $permissions, true)) {
            return true;
        }

        foreach ($this->legacyPermissionAliases() as $legacyPermission => $expandedPermissions) {
            if (in_array($legacyPermission, $permissions, true) && in_array($permission, $expandedPermissions, true)) {
                return true;
            }
        }

        return false;
    }

    public static function activeCompanyId(): ?int
    {
        $companyId = session('admin_active_company_id');

        return $companyId ? (int) $companyId : null;
    }

    /**
     * Assignment company-spesifik menggantikan (bukan digabung dengan) role global -- supaya
     * "role berbeda per perusahaan" (lihat docs/prd-multi-company-foundation.md §3) benar-benar
     * berarti berbeda, bukan union permission dari keduanya. Assignment company_id=NULL (role
     * global/default) dipakai kalau tidak ada override untuk company yang diminta. Kalau user
     * belum punya assignment sama sekali, fallback ke users.admin_role_id langsung -- menjaga
     * kompatibilitas untuk kode/test/seeder yang membuat staff user tanpa lewat
     * AdminUserController.
     */
    private function permissionsForCompany(?int $companyId): array
    {
        if ($this->companyAssignments->isEmpty()) {
            return $this->adminRole?->permissions ?? [];
        }

        $specific = $this->companyAssignments->first(fn (AdminCompanyAssignment $assignment) => $assignment->company_id === $companyId);
        if ($specific) {
            return $specific->adminRole?->permissions ?? [];
        }

        $global = $this->companyAssignments->first(fn (AdminCompanyAssignment $assignment) => $assignment->company_id === null);

        return $global?->adminRole?->permissions ?? [];
    }

    private function legacyPermissionAliases(): array
    {
        return [
            'view_dashboard' => ['dashboard.index'],
            'view_reports' => [
                'reports.index',
                'reports.owner',
                'reports.sales',
                'reports.sales.export',
                'reports.stock',
                'reports.products',
                'reports.payments',
                'reports.customers',
                'reports.promos',
                'reports.returns',
            ],
            'manage_orders' => [
                'transactions.index',
                'transactions.create',
                'transactions.show',
                'transactions.edit',
                'transactions.verify_payment',
                'tax_invoices.index',
                'tax_invoices.show',
                'tax_invoices.process',
                'tax_invoices.reject',
                'tax_invoices.upload',
                'tax_invoices.send',
                'return_requests.index',
                'return_requests.edit',
            ],
            'manage_product_reviews' => [
                'product_reviews.index',
                'product_reviews.edit',
                'product_reviews.delete',
            ],
            'manage_catalog' => [
                'products.index',
                'products.create',
                'products.edit',
                'products.delete',
                'products.import',
                'categories.index',
                'categories.create',
                'categories.edit',
                'categories.delete',
                'variants.index',
                'variants.create',
                'variants.edit',
                'variants.delete',
                'stock.index',
                'stock.edit',
                'flash_sales.index',
                'flash_sales.create',
                'flash_sales.edit',
                'flash_sales.delete',
                'coupons.index',
                'coupons.create',
                'coupons.edit',
                'coupons.delete',
            ],
            'manage_banners' => [
                'banners.index',
                'banners.create',
                'banners.edit',
                'banners.delete',
            ],
            'manage_store_settings' => [
                'store_settings.index',
                'store_settings.edit',
                'newsletter.index',
                'newsletter.send',
                'newsletter.delete',
                'promo_pages.index',
                'promo_pages.create',
                'promo_pages.edit',
                'promo_pages.delete',
                'content_pages.index',
                'content_pages.create',
                'content_pages.edit',
                'content_pages.delete',
            ],
            'view_customers' => ['customers.index'],
            'manage_membership_tiers' => [
                'member_tiers.index',
                'member_tiers.create',
                'member_tiers.edit',
                'member_tiers.delete',
            ],
            'manage_admin_users' => [
                'admin_users.index',
                'admin_users.create',
                'admin_users.edit',
                'admin_users.delete',
            ],
            'manage_roles_permissions' => [
                'admin_roles.index',
                'admin_roles.create',
                'admin_roles.edit',
                'admin_roles.delete',
            ],
        ];
    }
}
