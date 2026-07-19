<?php

namespace App\Http\Controllers;

use App\Models\AdminCompanyAssignment;
use App\Models\AdminRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index()
    {
        $adminUsers = User::query()
            ->with(['adminRole', 'companyAssignments'])
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhereNotNull('admin_role_id')
                    ->orWhereHas('companyAssignments');
            })
            ->orderByDesc('role')
            ->orderBy('name')
            ->get();

        return view('backend.admin-users.index', [
            'adminUsers' => $adminUsers,
        ]);
    }

    public function create()
    {
        return view('backend.admin-users.create', [
            'adminUser' => new User(),
            'roles' => AdminRole::query()->orderBy('name')->get(),
            'companies' => Company::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'companyOverrides' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAdminUser($request);

        $adminUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['account_type'] === 'super_admin' ? 'admin' : 'staff',
            'admin_role_id' => $validated['account_type'] === 'super_admin' ? null : $validated['admin_role_id'],
        ]);

        $this->syncGlobalCompanyAssignment($adminUser, $validated);
        $this->syncCompanyOverrides($adminUser, $validated);

        return redirect()->route('admin-users.index')->with('success', 'Admin user created successfully.');
    }

    public function edit(User $adminUser)
    {
        abort_unless($adminUser->canAccessAdminPanel(), 404);

        return view('backend.admin-users.edit', [
            'adminUser' => $adminUser->load('adminRole'),
            'roles' => AdminRole::query()->orderBy('name')->get(),
            'companies' => Company::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'companyOverrides' => $adminUser->companyAssignments()->whereNotNull('company_id')->pluck('admin_role_id', 'company_id')->all(),
        ]);
    }

    public function update(Request $request, User $adminUser)
    {
        abort_unless($adminUser->canAccessAdminPanel(), 404);

        $validated = $this->validateAdminUser($request, $adminUser);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['account_type'] === 'super_admin' ? 'admin' : 'staff',
            'admin_role_id' => $validated['account_type'] === 'super_admin' ? null : $validated['admin_role_id'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $adminUser->update($payload);
        $this->syncGlobalCompanyAssignment($adminUser, $validated);
        $this->syncCompanyOverrides($adminUser, $validated);

        return redirect()->route('admin-users.index')->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $adminUser)
    {
        abort_unless($adminUser->canAccessAdminPanel(), 404);

        if ((int) $adminUser->id === (int) auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if (strtolower((string) $adminUser->role) === 'admin') {
            return back()->with('error', 'Super admin accounts cannot be deleted from this page.');
        }

        $adminUser->delete();

        return redirect()->route('admin-users.index')->with('success', 'Admin user deleted successfully.');
    }

    private function validateAdminUser(Request $request, ?User $adminUser = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($adminUser?->id)],
            'account_type' => ['required', Rule::in(['super_admin', 'staff'])],
            'admin_role_id' => ['nullable', 'integer', Rule::exists('admin_roles', 'id')],
            'password' => [$adminUser ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'company_role_overrides' => ['nullable', 'array'],
            'company_role_overrides.*' => ['nullable', 'integer', Rule::exists('admin_roles', 'id')],
        ]);

        if (($validated['account_type'] ?? null) === 'staff') {
            $hasGlobalRole = !empty($validated['admin_role_id']);
            $hasAnyOverride = collect($validated['company_role_overrides'] ?? [])->filter()->isNotEmpty();

            if (!$hasGlobalRole && !$hasAnyOverride) {
                throw ValidationException::withMessages([
                    'admin_role_id' => 'Pilih role default, atau minimal satu override role per perusahaan di bawah.',
                ]);
            }
        }

        return $validated;
    }

    /**
     * "Role" di form ini adalah role default/global staff (assignment company_id=NULL) --
     * berlaku untuk semua perusahaan kecuali di-override lewat companyRoleOverrides (lihat
     * syncCompanyOverrides). Kalau dikosongkan, staff TIDAK punya akses global -- aksesnya
     * dibatasi hanya ke perusahaan yang punya override eksplisit (lihat syncCompanyOverrides),
     * inilah cara membuat "admin PT A+B tapi bukan PT C" (docs/prd-multi-company-foundation.md §3).
     */
    private function syncGlobalCompanyAssignment(User $adminUser, array $validated): void
    {
        if ($validated['account_type'] === 'super_admin' || empty($validated['admin_role_id'])) {
            AdminCompanyAssignment::query()
                ->where('user_id', $adminUser->id)
                ->whereNull('company_id')
                ->delete();

            return;
        }

        AdminCompanyAssignment::query()->updateOrCreate(
            ['user_id' => $adminUser->id, 'company_id' => null],
            ['admin_role_id' => $validated['admin_role_id']]
        );
    }

    /**
     * Assignment company-spesifik menggantikan role global saat staff bekerja di perusahaan itu
     * (lihat User::permissionsForCompany()). Super admin tidak butuh override sama sekali karena
     * role-nya sudah bypass semua permission check.
     */
    private function syncCompanyOverrides(User $adminUser, array $validated): void
    {
        if ($validated['account_type'] === 'super_admin') {
            AdminCompanyAssignment::query()
                ->where('user_id', $adminUser->id)
                ->whereNotNull('company_id')
                ->delete();

            return;
        }

        $overrides = (array) ($validated['company_role_overrides'] ?? []);

        foreach (Company::query()->pluck('id') as $companyId) {
            $roleId = $overrides[$companyId] ?? null;

            if (empty($roleId)) {
                AdminCompanyAssignment::query()
                    ->where('user_id', $adminUser->id)
                    ->where('company_id', $companyId)
                    ->delete();

                continue;
            }

            AdminCompanyAssignment::query()->updateOrCreate(
                ['user_id' => $adminUser->id, 'company_id' => $companyId],
                ['admin_role_id' => (int) $roleId]
            );
        }
    }
}
