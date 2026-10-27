<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Branch;
use App\Models\Policy;
use App\Models\DeviceCommand;
use App\Models\AuditLog;
use App\Services\AndroidManagementService;
use App\Services\DeviceSyncService;
use App\Jobs\IssueDeviceCommandJob;

class DeviceController extends Controller
{
    protected AndroidManagementService $amapi;
    protected DeviceSyncService $syncService;

    public function __construct(AndroidManagementService $amapi, DeviceSyncService $syncService)
    {
        $this->amapi = $amapi;
        $this->syncService = $syncService;
    }

    /**
     * Display a listing of devices with search and filters.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentTab = $request->input('tab', 'active');
        $query = Device::forUser($user)->with(['owner', 'policy', 'branch']);

        // Filter by Branch (for HQ / Superadmin)
        $selectedBranchId = $user->isBranchScoped() ? $user->branch_id : $request->input('branch_id');
        if (!$user->isBranchScoped() && !empty($selectedBranchId)) {
            $query->where('branch_id', $selectedBranchId);
        }

        // Base count query respecting branch scope/filter
        $countBaseQuery = Device::forUser($user);
        if (!$user->isBranchScoped() && !empty($selectedBranchId)) {
            $countBaseQuery->where('branch_id', $selectedBranchId);
        }

        // Tab Filter: active (default, excludes DELETED & Lunas), lunas (only Lunas, not DELETED), deleted (only DELETED, admin only), or all
        if ($currentTab === 'deleted') {
            if (!$user->isAdmin()) {
                abort(403, 'Akses riwayat arsip unenroll hanya untuk Super Admin.');
            }
            $query->where('state', 'DELETED');
        } elseif ($currentTab === 'lunas') {
            $query->where('state', '!=', 'DELETED')->where('policy_id', 'Lunas');
        } elseif ($currentTab === 'all') {
            if (!$user->isAdmin()) {
                $query->where('state', '!=', 'DELETED');
            }
        } else {
            $currentTab = 'active';
            $query->where('state', '!=', 'DELETED')->where('policy_id', '!=', 'Lunas');
        }

        // Search Keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hardware_model', 'like', "%{$search}%")
                  ->orWhere('marketing_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('imei', 'like', "%{$search}%")
                  ->orWhere('network_info', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('nama', 'like', "%{$search}%")
                         ->orWhere('id_anggota', 'like', "%{$search}%")
                         ->orWhere('telepon', 'like', "%{$search}%")
                         ->orWhere('departemen', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by State within tab
        if ($state = $request->input('state')) {
            $query->where('state', $state);
        }

        // Filter by Policy
        if ($policy = $request->input('policy_id')) {
            $query->where('policy_id', $policy);
        }

        $devices = $query->orderByRaw('COALESCE(enrolled_at, created_at) DESC')
                         ->orderByDesc('id')
                         ->paginate(15)
                         ->withQueryString();
        $policies = Policy::select('id', 'name', 'policy_id')->where('policy_id', '!=', 'Lunas')->get();
        $branches = Branch::orderBy('name')->get();

        $activeCount = (clone $countBaseQuery)->where('state', '!=', 'DELETED')->where('policy_id', '!=', 'Lunas')->count();
        $lunasCount = (clone $countBaseQuery)->where('state', '!=', 'DELETED')->where('policy_id', 'Lunas')->count();
        $deletedCount = (clone $countBaseQuery)->where('state', 'DELETED')->count();

        return view('devices.index', compact(
            'devices',
            'policies',
            'branches',
            'currentTab',
            'activeCount',
            'lunasCount',
            'deletedCount',
            'selectedBranchId'
        ));
    }

    /**
     * Display detailed device technical specs and command history.
     */
    public function show($id)
    {
        $device = Device::with(['owner', 'branch', 'policy.applications', 'commands.issuedBy'])->findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        $allPolicies = Policy::select('id', 'name', 'policy_id')->where('policy_id', '!=', 'Lunas')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('devices.show', compact('device', 'allPolicies', 'branches'));
    }

    /**
     * Issue remote command via asynchronous queue job.
     */
    public function issueCommand(Request $request, $id)
    {
        $device = Device::with('branch')->findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        $validated = $request->validate([
            'command_type' => ['required', 'string', 'in:LOCK,REBOOT,RESET_PASSWORD,START_LOST_MODE,STOP_LOST_MODE'],
            'new_password' => ['nullable', 'string', 'required_if:command_type,RESET_PASSWORD'],
            'lost_message' => ['nullable', 'string'],
            'lost_phone'   => ['nullable', 'string'],
        ]);

        $commandType = $validated['command_type'];
        $params = [];

        if ($commandType === 'RESET_PASSWORD') {
            $params['newPassword'] = $validated['new_password'];
            $params['resetPasswordFlags'] = ['LOCK_NOW', 'REQUIRE_ENTRY'];
        } elseif ($commandType === 'START_LOST_MODE') {
            $defaultPhone = $device->branch?->contact_phone ?: '0821-1895-4552';
            $params['lostMessage'] = $validated['lost_message'] ?? 'Perangkat ini terkunci karena status tagihan/kehilangan. Hubungi Admin.';
            $params['lostPhoneNumber'] = $validated['lost_phone'] ?? $defaultPhone;
        }

        // Create command record in database
        $command = DeviceCommand::create([
            'device_id' => $device->device_id,
            'issued_by_user_id' => auth()->id(),
            'command_type' => $commandType,
            'command_params' => $params,
            'status' => 'PENDING',
        ]);

        // Dispatch background job
        IssueDeviceCommandJob::dispatch($command->id);

        AuditLog::log("DISPATCH_{$commandType}", 'Device', $device->device_id, $params);

        return back()->with('success', "Perintah <strong>{$commandType}</strong> berhasil dijadwalkan dan sedang dikirim ke perangkat.");
    }

    /**
     * Switch device policy.
     */
    public function updatePolicy(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        $validated = $request->validate([
            'policy_id' => ['required', 'string'],
        ]);

        try {
            $this->amapi->patchDevicePolicy($device->device_id, $validated['policy_id']);

            $device->update(['policy_id' => $validated['policy_id']]);

            AuditLog::log('UPDATE_DEVICE_POLICY', 'Device', $device->device_id, ['new_policy' => $validated['policy_id']]);

            return back()->with('success', "Policy perangkat berhasil diubah menjadi <strong>{$validated['policy_id']}</strong>.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal memperbarui policy di Google AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Lock device into Gagal_bayar policy.
     */
    public function lockDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        if ($device->state === 'DELETED') {
            return back()->with('error', 'Perangkat yang sudah di-unenroll tidak dapat dikunci.');
        }

        if ($device->policy_id === 'Lunas') {
            return back()->with('error', 'Perangkat berstatus Lunas tidak dapat dikunci.');
        }

        try {
            $this->amapi->patchDevicePolicy($device->device_id, 'Gagal_bayar');

            $device->update(['policy_id' => 'Gagal_bayar']);

            AuditLog::log('LOCK_DEVICE_GAGAL_BAYAR', 'Device', $device->device_id, [
                'policy'    => 'Gagal_bayar',
                'locked_by' => auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email,
                'branch'    => $device->branch?->name ?? 'Pusat',
            ]);

            return redirect()->route('devices.show', $device->id)
                ->with('warning', "Perangkat berhasil <strong>Dikunci (Gagal Bayar)</strong>. Layar perangkat telah dibatasi ke mode peringatan tagihan.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal mengunci perangkat di Google AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Unlock device back to Mode_kiosk policy.
     */
    public function unlockDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        if ($device->state === 'DELETED') {
            return back()->with('error', 'Perangkat yang sudah di-unenroll tidak dapat diubah.');
        }

        try {
            $this->amapi->patchDevicePolicy($device->device_id, 'Mode_kiosk');

            $device->update(['policy_id' => 'Mode_kiosk']);

            AuditLog::log('UNLOCK_DEVICE_KIOSK', 'Device', $device->device_id, [
                'policy'      => 'Mode_kiosk',
                'unlocked_by' => auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email,
                'branch'      => $device->branch?->name ?? 'Pusat',
            ]);

            return redirect()->route('devices.show', $device->id)
                ->with('success', "Kunci perangkat berhasil <strong>Dibuka</strong>. Kebijakan perangkat telah dikembalikan ke <strong>Mode Kiosk Normal</strong>.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal membuka kunci perangkat di Google AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Transfer device to another branch (Superadmin / HQ only).
     */
    public function updateBranch(Request $request, $id)
    {
        if (auth()->user()->isBranchScoped()) {
            abort(403, 'Akun cabang tidak dapat memindahkan perangkat antar cabang.');
        }

        $device = Device::with('owner')->findOrFail($id);

        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $newBranch = !empty($validated['branch_id']) ? Branch::find($validated['branch_id']) : null;
        $device->update(['branch_id' => $newBranch?->id]);

        if ($device->owner && $newBranch) {
            $device->owner->update(['departemen' => strtoupper($newBranch->name)]);
        }

        AuditLog::log('TRANSFER_DEVICE_BRANCH', 'Device', $device->device_id, [
            'branch_id'   => $newBranch?->id,
            'branch_name' => $newBranch?->name ?? 'Pusat / Tanpa Cabang',
        ]);

        return back()->with('success', 'Penempatan cabang perangkat berhasil diperbarui menjadi <strong>' . ($newBranch?->name ?? 'Pusat / Tanpa Cabang') . '</strong>.');
    }

    /**
     * Release device upon loan completion (Policy Lunas).
     * Disables MDM restrictions, unbinds tracker, and lifts enterprise FRP without wiping user data.
     */
    public function releaseDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        if ($device->state === 'DELETED') {
            return back()->with('error', 'Perangkat yang sudah di-unenroll tidak dapat dilepaskan ke status Lunas.');
        }

        try {
            // Apply Lunas policy in Google AMAPI
            $this->amapi->patchDevicePolicy($device->device_id, 'Lunas');

            $device->update([
                'policy_id' => 'Lunas',
            ]);

            AuditLog::log('RELEASE_DEVICE_LUNAS', 'Device', $device->device_id, [
                'policy'      => 'Lunas',
                'released_by' => auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email,
                'branch'      => $device->branch?->name ?? 'Pusat',
            ]);

            return redirect()->route('devices.show', $device->id)
                ->with('success', "Perangkat nasabah berhasil dilepaskan dengan status <strong>Lunas</strong>. Semua pembatasan operasional dan pelacakan telah dinonaktifkan tanpa menghapus data ponsel.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal melepaskan perangkat ke policy Lunas di Google AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Unenroll / Wipe device (Superadmin Only).
     */
    public function unenroll(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Aksi unenroll hanya dapat dilakukan oleh Super Admin.');
        }

        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        try {
            $this->amapi->unenrollDevice($device->device_id);

            $device->update(['state' => 'DELETED']);

            AuditLog::log('UNENROLL_DEVICE', 'Device', $device->device_id);

            return redirect()->route('devices.index', ['tab' => 'deleted'])
                ->with('success', "Perangkat <strong>{$device->device_id}</strong> berhasil di-unenroll dan dipindahkan ke riwayat arsip.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal unenroll perangkat: " . $e->getMessage());
        }
    }

    /**
     * Permanently remove unenrolled device record from database.
     */
    public function destroyPermanent($id)
    {
        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        if ($device->state !== 'DELETED') {
            return back()->with('error', 'Hanya perangkat yang sudah berstatus DELETED / di-unenroll yang dapat dihapus permanen.');
        }

        $deviceId = $device->device_id;
        $hardwareModel = $device->human_model_name;
        $device->delete();

        AuditLog::log('PERMANENT_DELETE_DEVICE', 'Device', $deviceId, [
            'hardware_model' => $hardwareModel
        ]);

        return redirect()->route('devices.index', ['tab' => 'deleted'])
            ->with('success', "Data riwayat perangkat <strong>{$deviceId}</strong> berhasil dihapus permanen dari database.");
    }

    /**
     * Trigger manual synchronization from Google AMAPI.
     */
    public function sync()
    {
        $result = $this->syncService->syncAll();

        if ($result['success']) {
            $count = $result['synced_count'];
            $polCount = $result['policies_synced'] ?? 0;
            return back()->with('success', "Sinkronisasi berhasil! <strong>{$count}</strong> perangkat dan <strong>{$polCount}</strong> policy telah diperbarui dari Google AMAPI.");
        } else {
            return back()->with('error', "Sinkronisasi gagal: " . ($result['message'] ?? 'Terjadi kesalahan'));
        }
    }

    /**
     * Update marketing name / alias for a device model.
     */
    public function updateMarketingName(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        if (!$device->canBeAccessedBy()) {
            abort(403, 'Anda tidak memiliki akses ke perangkat dari cabang lain.');
        }

        $validated = $request->validate([
            'marketing_name' => ['required', 'string', 'max:100'],
            'apply_to_all_same_model' => ['nullable', 'boolean'],
        ]);

        $device->marketing_name = $validated['marketing_name'];
        $device->save();

        if ($request->boolean('apply_to_all_same_model') && $device->hardware_model) {
            app(\App\Services\DeviceNameService::class)->setCustomName(
                $device->hardware_model,
                $validated['marketing_name'],
                $device->brand
            );
        }

        AuditLog::log('UPDATE_DEVICE_MODEL_NAME', 'Device', (string)$device->id, [
            'marketing_name' => $device->marketing_name,
            'hardware_model' => $device->hardware_model,
        ]);

        return back()->with('success', "Nama model komersial berhasil disimpan sebagai <strong>{$device->marketing_name}</strong>.");
    }
}
