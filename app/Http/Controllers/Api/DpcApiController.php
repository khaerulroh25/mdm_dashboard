<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceOwner;
use App\Models\EnrollmentToken;
use App\Models\Policy;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DpcApiController extends Controller
{
    /**
     * Handle device initial enrollment from Android DPC client.
     */
    public function enroll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enrollment_token'  => ['required', 'string'],
            'device_id'         => ['nullable', 'string', 'max:255'],
            'brand'             => ['nullable', 'string', 'max:255'],
            'hardware_model'    => ['nullable', 'string', 'max:255'],
            'marketing_name'    => ['nullable', 'string', 'max:255'],
            'manufacturer'      => ['nullable', 'string', 'max:255'],
            'serial_number'     => ['nullable', 'string', 'max:255'],
            'imei'              => ['nullable', 'string', 'max:255'],
            'android_version'   => ['nullable', 'string', 'max:50'],
            'agent_android_id'  => ['nullable', 'string', 'max:100'],
            'dpc_version'       => ['nullable', 'string', 'max:50'],
            'network_info'      => ['nullable', 'array'],
            'memory_info'       => ['nullable', 'array'],
        ]);

        $enrollmentToken = EnrollmentToken::where('token', $validated['enrollment_token'])->first();

        if (!$enrollmentToken || !$enrollmentToken->isValid()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token pendaftaran tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find existing device by serial / IMEI / agent ID or create fresh ID
            $deviceId = $validated['device_id'] ?? null;
            $existingDevice = null;

            if ($deviceId) {
                $existingDevice = Device::where('device_id', $deviceId)->first();
            }

            if (!$existingDevice && !empty($validated['serial_number'])) {
                $existingDevice = Device::where('serial_number', $validated['serial_number'])->first();
            }

            if (!$existingDevice && !empty($validated['agent_android_id'])) {
                $existingDevice = Device::where('agent_android_id', $validated['agent_android_id'])->first();
            }

            $finalDeviceId = $existingDevice ? $existingDevice->device_id : ($deviceId ?: 'MDM-' . strtoupper(Str::random(10)));
            $newDeviceToken = bin2hex(random_bytes(32)); // 64-char secure bearer token

            $policyId = $enrollmentToken->policy_id ?: 'Mode_kiosk';
            $branchId = $enrollmentToken->branch_id ?: ($existingDevice ? $existingDevice->branch_id : null);

            $deviceData = [
                'branch_id'           => $branchId,
                'device_id'           => $finalDeviceId,
                'device_token'        => $newDeviceToken,
                'hardware_model'      => $validated['hardware_model'] ?? null,
                'marketing_name'      => $validated['marketing_name'] ?? null,
                'brand'               => $validated['brand'] ?? null,
                'manufacturer'        => $validated['manufacturer'] ?? null,
                'serial_number'       => $validated['serial_number'] ?? null,
                'imei'                => $validated['imei'] ?? null,
                'android_version'     => $validated['android_version'] ?? null,
                'agent_android_id'    => $validated['agent_android_id'] ?? null,
                'dpc_version'         => $validated['dpc_version'] ?? '1.0.0',
                'state'               => 'ACTIVE',
                'policy_id'           => $policyId,
                'is_online'           => true,
                'network_info'        => $validated['network_info'] ?? null,
                'memory_info'         => $validated['memory_info'] ?? null,
                'enrollment_token_id' => $enrollmentToken->id,
                'last_status_report'  => now(),
                'enrolled_at'         => now(),
            ];

            if ($existingDevice) {
                $existingDevice->update($deviceData);
                $device = $existingDevice;
            } else {
                $device = Device::create($deviceData);
            }

            // Bind owner data if pre-allocated in token
            if (!empty($enrollmentToken->owner_data)) {
                $owner = $enrollmentToken->owner_data;
                DeviceOwner::updateOrCreate(
                    ['device_id' => $device->device_id],
                    [
                        'nama'        => $owner['nama'] ?? 'Belum Dinamai',
                        'id_anggota'  => $owner['id_anggota'] ?? '-',
                        'telepon'     => $owner['telepon'] ?? null,
                        'catatan'     => $owner['catatan'] ?? 'Terdaftar otomatis via Standalone DPC Token',
                    ]
                );
            }

            // Mark token as used
            $enrollmentToken->markAsUsed($device->id);

            // Audit log
            AuditLog::log(
                'DEVICE_ENROLLED_STANDALONE',
                'Device',
                $device->device_id,
                ['device_id' => $device->device_id, 'token' => $enrollmentToken->token]
            );

            DB::commit();

            // Format policy payload for DPC
            $policyConfig = $this->formatPolicyPayload($policyId);

            return response()->json([
                'status'  => 'success',
                'message' => 'Perangkat berhasil terdaftar ke sistem MDM.',
                'data'    => [
                    'device_id'    => $device->device_id,
                    'device_token' => $newDeviceToken,
                    'policy_id'    => $policyId,
                    'policy'       => $policyConfig,
                    'server_time'  => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses pendaftaran perangkat: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve active policy for authenticated device.
     */
    public function getPolicy(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('dpc_device');
        $policyConfig = $this->formatPolicyPayload($device->policy_id);

        return response()->json([
            'status'    => 'success',
            'device_id' => $device->device_id,
            'policy_id' => $device->policy_id,
            'policy'    => $policyConfig,
        ]);
    }

    /**
     * Periodic status report & heartbeat from DPC client.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('dpc_device');

        $validated = $request->validate([
            'battery_level'     => ['nullable', 'integer', 'min:0', 'max:100'],
            'battery_status'    => ['nullable', 'string', 'max:50'],
            'latitude'          => ['nullable', 'numeric'],
            'longitude'         => ['nullable', 'numeric'],
            'location_accuracy' => ['nullable', 'numeric'],
            'network_info'      => ['nullable', 'array'],
            'memory_info'       => ['nullable', 'array'],
            'dpc_version'       => ['nullable', 'string', 'max:50'],
        ]);

        $updateData = [
            'is_online'          => true,
            'last_status_report' => now(),
        ];

        if (isset($validated['battery_level'])) {
            $updateData['battery_level'] = $validated['battery_level'];
        }
        if (isset($validated['battery_status'])) {
            $updateData['battery_status'] = $validated['battery_status'];
        }
        if (isset($validated['dpc_version'])) {
            $updateData['dpc_version'] = $validated['dpc_version'];
        }
        if (isset($validated['network_info'])) {
            $updateData['network_info'] = $validated['network_info'];
        }
        if (isset($validated['memory_info'])) {
            $updateData['memory_info'] = $validated['memory_info'];
        }
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $updateData['latitude'] = $validated['latitude'];
            $updateData['longitude'] = $validated['longitude'];
            $updateData['location_accuracy'] = $validated['location_accuracy'] ?? null;
            $updateData['location_updated_at'] = now();
        }

        $device->update($updateData);

        // Fetch pending commands
        $pendingCommands = DeviceCommand::where('device_id', $device->device_id)
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'command_type', 'command_params', 'created_at']);

        return response()->json([
            'status'           => 'success',
            'policy_id'        => $device->policy_id,
            'server_time'      => now()->toIso8601String(),
            'pending_commands' => $pendingCommands,
        ]);
    }

    /**
     * Get pending commands for device.
     */
    public function getPendingCommands(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('dpc_device');

        $commands = DeviceCommand::where('device_id', $device->device_id)
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status'   => 'success',
            'commands' => $commands,
        ]);
    }

    /**
     * Acknowledge execution result of a command.
     */
    public function acknowledgeCommand(Request $request, int $id): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('dpc_device');

        $validated = $request->validate([
            'status'        => ['required', 'in:SUCCESS,FAILED'],
            'error_message' => ['nullable', 'string'],
        ]);

        $command = DeviceCommand::where('id', $id)
            ->where('device_id', $device->device_id)
            ->first();

        if (!$command) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Command tidak ditemukan.',
            ], 404);
        }

        $command->update([
            'status'        => $validated['status'],
            'error_message' => $validated['error_message'] ?? null,
            'executed_at'   => now(),
        ]);

        AuditLog::log(
            'DPC_COMMAND_ACKNOWLEDGED',
            'DeviceCommand',
            (string) $command->id,
            ['status' => $command->status, 'command_type' => $command->command_type]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Status perintah berhasil diperbarui.',
        ]);
    }

    /**
     * Format a Policy record into structured JSON payload for Android DevicePolicyManager.
     */
    protected function formatPolicyPayload(?string $policyId): array
    {
        $policy = Policy::where('policy_id', $policyId)->with('applications')->first();

        if (!$policy) {
            return [
                'policy_id' => $policyId ?? 'DEFAULT',
                'restrictions' => [],
                'kiosk' => ['enabled' => false],
                'applications' => [],
            ];
        }

        $kioskApp = null;
        $allowedApps = [];

        foreach ($policy->applications as $app) {
            $pivotType = $app->pivot->install_type ?? 'AVAILABLE';
            $appEntry = [
                'package_name' => $app->package_name,
                'app_name'     => $app->app_name,
                'install_type' => $pivotType,
                'auto_update'  => (bool) ($app->pivot->auto_update ?? true),
            ];

            if ($pivotType === 'KIOSK') {
                $kioskApp = $app->package_name;
            }

            $allowedApps[] = $appEntry;
        }

        return [
            'policy_id'   => $policy->policy_id,
            'name'        => $policy->name,
            'description' => $policy->description,
            'restrictions' => [
                'disallow_camera'          => (bool) $policy->camera_disabled,
                'disallow_config_wifi'     => (bool) $policy->wifi_config_disabled,
                'disallow_factory_reset'   => (bool) $policy->factory_reset_disabled,
                'disallow_install_apps'    => (bool) $policy->install_apps_disabled,
                'allow_untrusted_apps'     => (bool) $policy->allow_untrusted_apps,
                'disallow_safe_boot'       => (bool) $policy->safe_boot_disabled,
                'disallow_screen_capture'  => (bool) $policy->screen_capture_disabled,
                'disallow_keyguard'        => (bool) $policy->keyguard_disabled,
                'disallow_airplane_mode'   => true, // Cegah pengguna mengaktifkan mode pesawat
                'gps_always_on'            => true, // Paksa GPS selalu ON & cegah pengguna mematikan lokasi
            ],
            'kiosk' => [
                'enabled'                  => ($kioskApp !== null),
                'kiosk_package'            => $kioskApp,
                'status_bar_disabled'      => (bool) $policy->kiosk_status_bar_disabled,
                'settings_blocked'         => (bool) $policy->kiosk_settings_blocked,
                'navigation_disabled'      => (bool) $policy->kiosk_navigation_disabled,
            ],
            'frp' => [
                'enabled'                  => (bool) $policy->factory_reset_disabled,
                'accounts'                 => (array) ($policy->frp_admin_emails ?: ['devicekontrol450@gmail.com']),
            ],
            'applications' => $allowedApps,
        ];
    }
}
