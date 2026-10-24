<?php

namespace App\Services;

use Exception;
use App\Models\Device;
use App\Models\DeviceOwner;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class DeviceSyncService
{
    protected AndroidManagementService $amapi;

    public function __construct(AndroidManagementService $amapi)
    {
        $this->amapi = $amapi;
    }

    /**
     * Synchronize all devices from Google AMAPI to the local database.
     */
    public function syncAll(): array
    {
        $syncedCount = 0;
        $errors = [];
        $pageToken = null;

        try {
            do {
                $response = $this->amapi->listDevices(100, $pageToken);
                $devices = $response->getDevices() ?? [];

                foreach ($devices as $gDevice) {
                    try {
                        $this->syncSingleDevice($gDevice);
                        $syncedCount++;
                    } catch (Exception $e) {
                        $deviceId = method_exists($gDevice, 'getName') ? $gDevice->getName() : 'Unknown';
                        $errors[] = "Failed syncing {$deviceId}: " . $e->getMessage();
                        Log::warning("DeviceSyncService item error: " . $e->getMessage());
                    }
                }

                $pageToken = method_exists($response, 'getNextPageToken') ? $response->getNextPageToken() : null;
            } while ($pageToken);

            // Also sync policies
            $policiesSynced = $this->syncPolicies();

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'policies_synced' => $policiesSynced,
                'errors' => $errors,
            ];

        } catch (Exception $e) {
            Log::error("DeviceSyncService syncAll fatal error: " . $e->getMessage());
            return [
                'success' => false,
                'synced_count' => $syncedCount,
                'message' => $e->getMessage(),
                'errors' => $errors,
            ];
        }
    }

    /**
     * Parse and upsert a single Google AMAPI device object into the database.
     */
    public function syncSingleDevice($gDevice): Device
    {
        $deviceId = $gDevice->getName();
        $hardwareInfo = method_exists($gDevice, 'getHardwareInfo') ? $gDevice->getHardwareInfo() : null;
        $softwareInfo = method_exists($gDevice, 'getSoftwareInfo') ? $gDevice->getSoftwareInfo() : null;
        $networkInfo = method_exists($gDevice, 'getNetworkInfo') ? $gDevice->getNetworkInfo() : null;
        $memoryInfo = method_exists($gDevice, 'getMemoryInfo') ? $gDevice->getMemoryInfo() : null;

        $model = $hardwareInfo ? $hardwareInfo->getModel() : null;
        $brand = $hardwareInfo ? $hardwareInfo->getBrand() : null;
        $manufacturer = $hardwareInfo ? $hardwareInfo->getManufacturer() : null;
        $serial = $hardwareInfo ? $hardwareInfo->getSerialNumber() : null;
        $androidVersion = $softwareInfo ? $softwareInfo->getAndroidVersion() : null;
        if (empty($androidVersion)) {
            $apiLevel = method_exists($gDevice, 'getApiLevel') ? $gDevice->getApiLevel() : ($gDevice['apiLevel'] ?? null);
            $androidVersion = Device::mapApiLevelToVersion($apiLevel ? (int)$apiLevel : null);
        }

        $policyName = $gDevice->getPolicyName() ?? $gDevice->getAppliedPolicyName() ?? null;
        $policyId = $policyName ? basename($policyName) : null;

        $lastReport = $gDevice->getLastStatusReportTime() ? date('Y-m-d H:i:s', strtotime($gDevice->getLastStatusReportTime())) : null;
        $enrollmentTime = $gDevice->getEnrollmentTime() ? date('Y-m-d H:i:s', strtotime($gDevice->getEnrollmentTime())) : null;

        $networkInfoArray = $networkInfo ? json_decode(json_encode($networkInfo), true) : null;
        $rawDeviceArray = json_decode(json_encode($gDevice), true);

        $imei = null;
        if (!empty($networkInfoArray['imei'])) {
            $imei = $networkInfoArray['imei'];
        } elseif (!empty($rawDeviceArray['networkInfo']['imei'])) {
            $imei = $rawDeviceArray['networkInfo']['imei'];
        } elseif (!empty($networkInfoArray['telephonyInfos']) && is_array($networkInfoArray['telephonyInfos'])) {
            foreach ($networkInfoArray['telephonyInfos'] as $sim) {
                if (!empty($sim['imei'])) {
                    $imei = $sim['imei'];
                    break;
                }
            }
        }

        $existingDevice = Device::where('device_id', $deviceId)->first();
        $marketingName = $existingDevice?->marketing_name ?: app(DeviceNameService::class)->resolve($brand, $model);

        // Parse AMAPI enrollmentTokenData (contains branch & optional pre-assigned owner metadata)
        $decodedTokenData = null;
        $tokenDataRaw = method_exists($gDevice, 'getEnrollmentTokenData')
            ? $gDevice->getEnrollmentTokenData()
            : ($rawDeviceArray['enrollmentTokenData'] ?? null);

        if (!empty($tokenDataRaw) && is_string($tokenDataRaw)) {
            $parsed = json_decode($tokenDataRaw, true);
            if (is_array($parsed)) {
                $decodedTokenData = $parsed;
            }
        }

        // Resolve branch_id from existing device or AMAPI enrollmentTokenData
        $branchId = $existingDevice?->branch_id;
        $resolvedBranch = null;
        if (!$branchId && is_array($decodedTokenData)) {
            if (!empty($decodedTokenData['branch_id'])) {
                $resolvedBranch = Branch::find($decodedTokenData['branch_id']);
                $branchId = $resolvedBranch?->id;
            }
            if (!$branchId && !empty($decodedTokenData['branch_code'])) {
                $resolvedBranch = Branch::where('code', $decodedTokenData['branch_code'])->first();
                $branchId = $resolvedBranch?->id;
            }
        }

        // Fallback: if synced by a branch-scoped user and still unassigned
        if (!$branchId && auth()->check() && auth()->user()->isBranchScoped()) {
            $branchId = auth()->user()->branch_id;
        }

        if ($branchId && !$resolvedBranch) {
            $resolvedBranch = Branch::find($branchId);
        }

        $device = Device::updateOrCreate(
            ['device_id' => $deviceId],
            [
                'branch_id' => $branchId,
                'hardware_model' => $model,
                'marketing_name' => $marketingName,
                'brand' => $brand,
                'manufacturer' => $manufacturer,
                'serial_number' => $serial,
                'imei' => $imei ?? $existingDevice?->imei,
                'android_version' => $androidVersion,
                'state' => $gDevice->getState() ?? 'ACTIVE',
                'policy_id' => $policyId,
                'network_info' => $networkInfoArray,
                'memory_info' => $memoryInfo ? json_decode(json_encode($memoryInfo), true) : null,
                'raw_device_data' => $rawDeviceArray,
                'last_status_report' => $lastReport,
                'enrolled_at' => $enrollmentTime ?? now(),
            ]
        );

        // Auto-link DeviceOwner if pre-assigned during Enrollment QR generation and device has no owner yet
        $ownerData = $decodedTokenData['owner'] ?? null;
        if (is_array($ownerData) && !empty($ownerData['nama']) && !empty($ownerData['id_anggota'])) {
            $hasExistingOwner = DeviceOwner::where('device_id', $deviceId)->exists();
            if (!$hasExistingOwner) {
                $departemen = $resolvedBranch
                    ? strtoupper($resolvedBranch->name)
                    : ($decodedTokenData['branch_name'] ?? null);

                DeviceOwner::create([
                    'device_id'  => $deviceId,
                    'nama'       => $ownerData['nama'],
                    'id_anggota' => $ownerData['id_anggota'],
                    'telepon'    => $ownerData['telepon'] ?? null,
                    'departemen' => $departemen,
                    'catatan'    => $ownerData['catatan'] ?? null,
                ]);
            }
        }

        return $device;
    }

    /**
     * Synchronize all enterprise policies from Google AMAPI into local policies table.
     */
    public function syncPolicies(): int
    {
        $policyCount = 0;
        try {
            $policiesResp = $this->amapi->listPolicies();
            $policies = $policiesResp->getPolicies() ?? [];

            foreach ($policies as $gPolicy) {
                $policyName = $gPolicy->getName();
                $policyId = basename($policyName);

                \App\Models\Policy::updateOrCreate(
                    ['policy_id' => $policyId],
                    [
                        'name' => ucwords(str_replace('_', ' ', $policyId)),
                        'camera_disabled' => (bool)$gPolicy->getCameraDisabled(),
                        'wifi_config_disabled' => (bool)$gPolicy->getWifiConfigDisabled(),
                        'factory_reset_disabled' => (bool)$gPolicy->getFactoryResetDisabled(),
                        'install_apps_disabled' => (bool)$gPolicy->getInstallAppsDisabled(),
                        'safe_boot_disabled' => (bool)$gPolicy->getSafeBootDisabled(),
                        'location_mode' => $gPolicy->getLocationMode(),
                        'raw_policy_json' => json_decode(json_encode($gPolicy), true),
                    ]
                );
                $policyCount++;
            }
        } catch (Exception $e) {
            Log::warning("DeviceSyncService syncPolicies error: " . $e->getMessage());
        }

        return $policyCount;
    }
}
