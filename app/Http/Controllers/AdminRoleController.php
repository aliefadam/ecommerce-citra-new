<?php

namespace App\Http\Controllers;

use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = AdminRole::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('backend.admin-roles.index', [
            'roles' => $roles,
            'permissionGroups' => config('admin_permissions.groups', []),
        ]);
    }

    public function create()
    {
        return view('backend.admin-roles.create', [
            'role' => new AdminRole(),
            'permissionGroups' => config('admin_permissions.groups', []),
            'selectedPermissions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        AdminRole::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'permissions' => array_values($validated['permissions'] ?? []),
            'is_system' => false,
        ]);

        return redirect()->route('admin-roles.index')->with('success', 'Admin role created successfully.');
    }

    public function edit(AdminRole $adminRole)
    {
        return view('backend.admin-roles.edit', [
            'role' => $adminRole,
            'permissionGroups' => config('admin_permissions.groups', []),
            'selectedPermissions' => $adminRole->permissions ?? [],
        ]);
    }

    public function update(Request $request, AdminRole $adminRole)
    {
        $validated = $this->validateRole($request, $adminRole);

        $adminRole->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'permissions' => array_values($validated['permissions'] ?? []),
        ]);

        return redirect()->route('admin-roles.index')->with('success', 'Admin role updated successfully.');
    }

    public function destroy(AdminRole $adminRole)
    {
        if ($adminRole->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($adminRole->users()->exists()) {
            return back()->with('error', 'This role is still assigned to one or more admin users.');
        }

        $adminRole->delete();

        return redirect()->route('admin-roles.index')->with('success', 'Admin role deleted successfully.');
    }

    private function validateRole(Request $request, ?AdminRole $adminRole = null): array
    {
        $availablePermissions = $this->permissionKeys();

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('admin_roles', 'name')->ignore($adminRole?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($availablePermissions)],
        ]);
    }

    private function permissionKeys(): array
    {
        return collect(config('admin_permissions.groups', []))
            ->pluck('permissions')
            ->map(fn ($items) => array_keys($items))
            ->flatten()
            ->values()
            ->all();
    }
}
