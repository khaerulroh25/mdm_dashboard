<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Device;
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
        $query = Device::with(['owner', 'policy']);

        // Search Keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hardware_model', 'like', "%{$search}%")
                  ->orWhere('marketing_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('imei', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('nama', 'like', "%{$search}%")
                         ->orWhere('id_anggota', 'like', "%{$search}%")
                         ->orWhere('telepon', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by State
        if ($state = $request->input('state')) {
            $query->where('state', $state);
        }

        // Filter by Policy
        if ($policy = $request->input('policy_id')) {
            $query->where('policy_id', $policy);
        }

        $devices = $query->latest('last_status_report')->paginate(15)->withQueryString();
        $policies = Policy::select('id', 'name', 'policy_id')->get();

        return view('devices.index', compact('devices', 'policies'));
    }

    /**
     * Display detailed device technical specs and command history.
     */
    public function show($id)
    {
        $device = Device::with(['owner', 'policy.applications', 'commands.issuedBy'])->findOrFail($id);
        $allPolicies = Policy::select('id', 'name', 'policy_id')->get();

        return view('devices.show', compact('device', 'allPolicies'));
    }

    /**
     * Issue remote command via asynchronous queue job.
     */
    public function issueCommand(Request $request, $id)
    {
        $device = Device::findOrFail($id);

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
            $params['lostMessage'] = $validated['lost_message'] ?? 'Perangkat ini terkunci karena status tagihan/kehilangan. Hubungi Admin.';
            $params['lostPhoneNumber'] = $validated['lost_phone'] ?? '0858-1406-4274';
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
     * Unenroll / Wipe device.
     */
    public function unenroll(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        try {
            $this->amapi->unenrollDevice($device->device_id);

            $device->update(['state' => 'DELETED']);

            AuditLog::log('UNENROLL_DEVICE', 'Device', $device->device_id);

            return redirect()->route('devices.index')
                ->with('success', "Perangkat <strong>{$device->device_id}</strong> berhasil di-unenroll dan dihapus dari pengelolaan.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal unenroll perangkat: " . $e->getMessage());
        }
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
