<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AuditLog;

class UserController extends Controller
{
    /**
     * Display a listing of dashboard users.
     */
    public function index()
    {
        $users = User::latest()->paginate(15);
        $totalPermissionCount = count(User::allPermissionKeys());
        return view('users.index', compact('users', 'totalPermissionCount'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $permissionGroups = User::PERMISSION_GROUPS;
        $rolePresets = User::ROLE_PRESETS;
        return view('users.create', compact('permissionGroups', 'rolePresets'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validKeys = implode(',', User::allPermissionKeys());

        $validated = $request->validate([
            'username'      => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'name'          => ['required', 'string', 'max:150'],
            'email'         => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:6'],
            'role'          => ['required', 'string', 'in:admin,operator,auditor'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . $validKeys],
        ]);

        if ($validated['role'] === 'admin') {
            $permissions = User::allPermissionKeys();
        } elseif ($request->has('permissions_submitted')) {
            $permissions = array_values(array_unique($validated['permissions'] ?? []));
        } else {
            $permissions = User::defaultPermissionsForRole($validated['role']);
        }

        $user = User::create([
            'username'    => $validated['username'],
            'name'        => $validated['name'],
            'email'       => $validated['email'] ?? null,
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'permissions' => $permissions,
        ]);

        AuditLog::log('CREATE_USER', 'User', (string)$user->id, [
            'username'    => $user->username,
            'role'        => $user->role,
            'permissions' => $user->getEffectivePermissions(),
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna <strong>{$user->username}</strong> ({$user->role}) berhasil ditambahkan beserta konfigurasi hak aksesnya!");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $permissionGroups = User::PERMISSION_GROUPS;
        $rolePresets = User::ROLE_PRESETS;
        $effectivePermissions = $user->getEffectivePermissions();

        return view('users.edit', compact('user', 'permissionGroups', 'rolePresets', 'effectivePermissions'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validKeys = implode(',', User::allPermissionKeys());

        $validated = $request->validate([
            'username'      => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username,' . $user->id],
            'name'          => ['required', 'string', 'max:150'],
            'email'         => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'password'      => ['nullable', 'string', 'min:6'],
            'role'          => ['required', 'string', 'in:admin,operator,auditor'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . $validKeys],
        ]);

        // Prevent demoting oneself while logged in
        $newRole = ($user->id === auth()->id()) ? $user->role : $validated['role'];

        if ($newRole === 'admin') {
            $permissions = User::allPermissionKeys();
        } elseif ($request->has('permissions_submitted')) {
            $permissions = array_values(array_unique($validated['permissions'] ?? []));
        } else {
            $permissions = $user->permissions ?? User::defaultPermissionsForRole($newRole);
        }

        $updateData = [
            'username'    => $validated['username'],
            'name'        => $validated['name'],
            'email'       => $validated['email'] ?? null,
            'role'        => $newRole,
            'permissions' => $permissions,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditLog::log('UPDATE_USER', 'User', (string)$user->id, [
            'username'    => $user->username,
            'role'        => $user->role,
            'permissions' => $user->getEffectivePermissions(),
        ]);

        return redirect()->route('users.index')
            ->with('success', "Data pengguna & permission <strong>{$user->username}</strong> berhasil diperbarui!");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        $username = $user->username;
        $user->delete();

        AuditLog::log('DELETE_USER', 'User', (string)$id, ['username' => $username]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna <strong>{$username}</strong> berhasil dihapus.");
    }
}
