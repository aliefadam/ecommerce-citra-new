<?php

namespace App\Http\Controllers;

use App\Models\AdminRole;
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
            ->with('adminRole')
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhereNotNull('admin_role_id');
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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAdminUser($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['account_type'] === 'super_admin' ? 'admin' : 'staff',
            'admin_role_id' => $validated['account_type'] === 'super_admin' ? null : $validated['admin_role_id'],
        ]);

        return redirect()->route('admin-users.index')->with('success', 'Admin user created successfully.');
    }

    public function edit(User $adminUser)
    {
        abort_unless($adminUser->canAccessAdminPanel(), 404);

        return view('backend.admin-users.edit', [
            'adminUser' => $adminUser->load('adminRole'),
            'roles' => AdminRole::query()->orderBy('name')->get(),
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
        ]);

        if (($validated['account_type'] ?? null) === 'staff' && empty($validated['admin_role_id'])) {
            throw ValidationException::withMessages([
                'admin_role_id' => 'Please choose a role for staff accounts.',
            ]);
        }

        return $validated;
    }
}
