<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\AuditLog;

class BranchController extends Controller
{
    /**
     * Display a listing of master branches.
     */
    public function index()
    {
        $branches = Branch::withCount([
            'devices',
            'devices as active_devices_count' => function ($q) {
                $q->where('state', '!=', 'DELETED');
            },
            'users',
        ])->orderBy('name')->paginate(15);

        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'          => ['required', 'string', 'alpha_dash', 'max:30', 'unique:branches,code'],
            'name'          => ['required', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address'       => ['nullable', 'string', 'max:500'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $branch = Branch::create([
            'code'          => strtoupper(trim($validated['code'])),
            'name'          => trim($validated['name']),
            'contact_phone' => $validated['contact_phone'] ?? null,
            'address'       => $validated['address'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        AuditLog::log('CREATE_BRANCH', 'Branch', (string)$branch->id, [
            'code' => $branch->code,
            'name' => $branch->name,
        ]);

        return redirect()->route('branches.index')
            ->with('success', "Cabang <strong>{$branch->name} ({$branch->code})</strong> berhasil ditambahkan!");
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit($id)
    {
        $branch = Branch::withCount([
            'devices',
            'devices as active_devices_count' => function ($q) {
                $q->where('state', '!=', 'DELETED');
            },
            'users',
        ])->findOrFail($id);

        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'code'          => ['required', 'string', 'alpha_dash', 'max:30', 'unique:branches,code,' . $branch->id],
            'name'          => ['required', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address'       => ['nullable', 'string', 'max:500'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $branch->update([
            'code'          => strtoupper(trim($validated['code'])),
            'name'          => trim($validated['name']),
            'contact_phone' => $validated['contact_phone'] ?? null,
            'address'       => $validated['address'] ?? null,
            'is_active'     => $request->boolean('is_active', false),
        ]);

        AuditLog::log('UPDATE_BRANCH', 'Branch', (string)$branch->id, [
            'code'      => $branch->code,
            'name'      => $branch->name,
            'is_active' => $branch->is_active,
        ]);

        return redirect()->route('branches.index')
            ->with('success', "Data cabang <strong>{$branch->name} ({$branch->code})</strong> berhasil diperbarui!");
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy($id)
    {
        $branch = Branch::withCount(['devices', 'users'])->findOrFail($id);

        if ($branch->devices_count > 0 || $branch->users_count > 0) {
            return back()->with(
                'error',
                "Cabang <strong>{$branch->name}</strong> tidak dapat dihapus karena masih memiliki <strong>{$branch->devices_count} perangkat</strong> dan <strong>{$branch->users_count} pengguna</strong> yang tertaut. Pindahkan perangkat/pengguna terlebih dahulu."
            );
        }

        $branchName = "{$branch->name} ({$branch->code})";
        $branch->delete();

        AuditLog::log('DELETE_BRANCH', 'Branch', (string)$id, ['branch' => $branchName]);

        return redirect()->route('branches.index')
            ->with('success', "Cabang <strong>{$branchName}</strong> berhasil dihapus.");
    }
}
