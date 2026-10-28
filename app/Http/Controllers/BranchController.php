<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Enterprise;
use App\Models\Policy;
use App\Models\AuditLog;

class BranchController extends Controller
{
    /**
     * Display a listing of master branches.
     */
    public function index()
    {
        $branches = Branch::with(['enterprise'])->withCount([
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
        $enterprises = Enterprise::orderByDesc('is_active')->orderBy('name')->get();
        $policies = Policy::where('policy_id', '!=', 'Lunas')->get();

        return view('branches.create', compact('enterprises', 'policies'));
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'              => ['nullable', 'string', 'alpha_dash', 'max:30', 'unique:branches,code'],
            'name'              => ['required', 'string', 'max:150'],
            'contact_phone'     => ['nullable', 'string', 'max:50'],
            'address'           => ['nullable', 'string', 'max:500'],
            'enterprise_id'     => ['nullable', 'exists:enterprises,id'],
            'default_policy_id' => ['nullable', 'string', 'max:100'],
            'is_active'         => ['nullable', 'boolean'],
        ]);

        $code = !empty($validated['code'])
            ? strtoupper(trim($validated['code']))
            : Branch::generateCode($validated['name']);

        $branch = Branch::create([
            'code'              => $code,
            'name'              => trim($validated['name']),
            'contact_phone'     => $validated['contact_phone'] ?? null,
            'address'           => $validated['address'] ?? null,
            'enterprise_id'     => $validated['enterprise_id'] ?? null,
            'default_policy_id' => $validated['default_policy_id'] ?? 'Mode_kiosk',
            'is_active'         => $request->boolean('is_active', true),
        ]);

        AuditLog::log('CREATE_BRANCH', 'Branch', (string)$branch->id, [
            'code'              => $branch->code,
            'name'              => $branch->name,
            'enterprise_id'     => $branch->enterprise_id,
            'default_policy_id' => $branch->default_policy_id,
        ]);

        return redirect()->route('branches.index')
            ->with('success', "Cabang <strong>{$branch->name}</strong> berhasil dibuat dengan kode otomatis <strong>{$branch->code}</strong>!");
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit($id)
    {
        $branch = Branch::with(['enterprise'])->withCount([
            'devices',
            'devices as active_devices_count' => function ($q) {
                $q->where('state', '!=', 'DELETED');
            },
            'users',
        ])->findOrFail($id);

        $enterprises = Enterprise::orderByDesc('is_active')->orderBy('name')->get();
        $policies = Policy::where('policy_id', '!=', 'Lunas')->get();

        return view('branches.edit', compact('branch', 'enterprises', 'policies'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:150'],
            'contact_phone'     => ['nullable', 'string', 'max:50'],
            'address'           => ['nullable', 'string', 'max:500'],
            'enterprise_id'     => ['nullable', 'exists:enterprises,id'],
            'default_policy_id' => ['nullable', 'string', 'max:100'],
            'is_active'         => ['nullable', 'boolean'],
        ]);

        $branch->update([
            'name'              => trim($validated['name']),
            'contact_phone'     => $validated['contact_phone'] ?? null,
            'address'           => $validated['address'] ?? null,
            'enterprise_id'     => $validated['enterprise_id'] ?? null,
            'default_policy_id' => $validated['default_policy_id'] ?? 'Mode_kiosk',
            'is_active'         => $request->boolean('is_active', false),
        ]);

        AuditLog::log('UPDATE_BRANCH', 'Branch', (string)$branch->id, [
            'code'              => $branch->code,
            'name'              => $branch->name,
            'enterprise_id'     => $branch->enterprise_id,
            'default_policy_id' => $branch->default_policy_id,
            'is_active'         => $branch->is_active,
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
