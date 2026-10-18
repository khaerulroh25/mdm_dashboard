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
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'string', 'in:admin,operator,auditor'],
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'name'     => $validated['name'],
            'email'    => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        AuditLog::log('CREATE_USER', 'User', (string)$user->id, [
            'username' => $user->username,
            'role' => $user->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna <strong>{$user->username}</strong> ({$user->role}) berhasil ditambahkan!");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username,' . $user->id],
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role'     => ['required', 'string', 'in:admin,operator,auditor'],
        ]);

        $updateData = [
            'username' => $validated['username'],
            'name'     => $validated['name'],
            'email'    => $validated['email'] ?? null,
            'role'     => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditLog::log('UPDATE_USER', 'User', (string)$user->id, [
            'username' => $user->username,
            'role' => $user->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', "Data pengguna <strong>{$user->username}</strong> berhasil diperbarui!");
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
