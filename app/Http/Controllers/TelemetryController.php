<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class TelemetryController extends Controller
{
    private const AGENT_TOKEN = 'amanin_mdm_loc_2026_secret';

    /**
     * Receive real-time GPS coordinates from the Amanin Location Agent APK.
     * Endpoint: POST /api/telemetry/location
     */
    public function reportLocation(Request $request)
    {
        $token = $request->header('X-MDM-Token') ?? $request->input('token');
        if ($token !== self::AGENT_TOKEN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized telemetry token.',
            ], 401);
        }

        $validated = $request->validate([
            'latitude'       => ['required', 'numeric', 'between:-90,90'],
            'longitude'      => ['required', 'numeric', 'between:-180,180'],
            'accuracy'       => ['nullable', 'numeric', 'min:0'],
            'android_id'     => ['nullable', 'string', 'max:100'],
            'device_code'    => ['nullable', 'string', 'max:150'],
            'phone_numbers'  => ['nullable', 'array'],
            'hardware_model' => ['nullable', 'string', 'max:150'],
            'brand'          => ['nullable', 'string', 'max:100'],
            'battery_level'  => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        // Ignore only Google Play automated pre-launch crawler bot (Mountain View, CA: ~37.42, -122.08)
        if (abs($lat - 37.42) < 0.2 && abs($lng - (-122.08)) < 0.2) {
            return response()->json([
                'success' => false,
                'matched' => false,
                'message' => 'Koordinat automated Google Play test lab diabaikan.',
            ], 422);
        }

        $androidId     = !empty($validated['android_id']) ? trim($validated['android_id']) : null;
        $deviceCode    = !empty($validated['device_code']) ? trim($validated['device_code']) : null;
        $phoneNumbers  = $validated['phone_numbers'] ?? [];
        $hardwareModel = !empty($validated['hardware_model']) ? trim($validated['hardware_model']) : null;
        $batteryLevel  = isset($validated['battery_level']) ? (float) $validated['battery_level'] : null;

        $device = $this->resolveDevice($androidId, $deviceCode, $phoneNumbers, $hardwareModel, $batteryLevel);

        if (!$device) {
            return response()->json([
                'success'    => false,
                'matched'    => false,
                'needs_code' => true,
                'message'    => 'Perangkat belum teridentifikasi otomatis. Masukkan ID Pengguna, No. IMEI, atau Kode Perangkat (1x saja).',
            ], 404);
        }

        $updateData = [
            'latitude'            => (float) $validated['latitude'],
            'longitude'           => (float) $validated['longitude'],
            'location_accuracy'   => isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
            'location_updated_at' => now(),
            'last_status_report'  => now(),
        ];

        if ($androidId && $device->agent_android_id !== $androidId) {
            $updateData['agent_android_id'] = $androidId;
        }

        // Also update battery_level in raw_device_data if sent by the agent
        if (isset($validated['battery_level']) && $validated['battery_level'] !== null) {
            $raw = $device->raw_device_data ?? [];
            $events = $raw['powerManagementEvents'] ?? [];
            $events[] = [
                'eventType'    => 'BATTERY_LEVEL_COLLECTED',
                'batteryLevel' => (float) $validated['battery_level'],
                'createTime'   => now()->toIso8601ZuluString(),
            ];
            // Keep last 20 events
            $raw['powerManagementEvents'] = array_slice($events, -20);
            $updateData['raw_device_data'] = $raw;
        }

        $device->update($updateData);
        $device->loadMissing(['owner', 'branch']);

        $isLocked = in_array($device->policy_id, ['Gagal_bayar', 'lock_device'], true);

        return response()->json([
            'success'        => true,
            'matched'        => true,
            'device_id'      => $device->short_id,
            'owner_name'     => $device->owner->nama ?? 'Nasabah Terdaftar',
            'member_id'      => $device->owner->id_anggota ?? '-',
            'model'          => $device->human_model_name,
            'policy_id'      => $device->policy_id ?? 'Mode_kiosk',
            'is_locked'      => $isLocked,
            'branch_name'    => $device->branch?->name ?? 'Cabang Rengasdengklok',
            'branch_phone'   => $device->branch?->contact_phone ?: '0821-1895-4552',
            'branch_address' => $device->branch?->address ?? 'Rengasdengklok, Karawang',
            'updated_at'     => now()->timezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB',
        ]);
    }

    /**
     * Resolve which Device record matches the incoming telemetry payload.
     */
    protected function resolveDevice(
        ?string $androidId,
        ?string $deviceCode,
        array $phoneNumbers,
        ?string $hardwareModel,
        ?float $batteryLevel = null
    ): ?Device {
        // 1. Match by explicit device_code if provided by user/managed config
        if (!empty($deviceCode)) {
            $byCode = Device::with('owner')
                ->where('state', '!=', 'DELETED')
                ->where(function ($q) use ($deviceCode) {
                    $q->where('device_id', $deviceCode)
                      ->orWhere('device_id', 'like', "%/{$deviceCode}")
                      ->orWhere('device_id', 'like', "%{$deviceCode}")
                      ->orWhere('imei', $deviceCode)
                      ->orWhere('serial_number', $deviceCode)
                      ->orWhereHas('owner', function ($oq) use ($deviceCode) {
                          $oq->where('id_anggota', $deviceCode)
                             ->orWhere('telepon', $deviceCode)
                             ->orWhere('nama', $deviceCode);
                      });
                })
                ->first();

            if ($byCode) {
                if (!empty($byCode->hardware_model) && !empty($hardwareModel) && strcasecmp($byCode->hardware_model, $hardwareModel) !== 0) {
                    // Prevent a different phone model (e.g. personal test phone) from overwriting an enrolled device
                    return null;
                }
                return $byCode;
            }
        }

        // 2. Match by previously bound agent_android_id
        if (!empty($androidId)) {
            $byAndroidId = Device::with('owner')
                ->where('state', '!=', 'DELETED')
                ->where('agent_android_id', $androidId)
                ->first();

            if ($byAndroidId) {
                if (empty($byAndroidId->hardware_model) || empty($hardwareModel) || strcasecmp($byAndroidId->hardware_model, $hardwareModel) === 0) {
                    return $byAndroidId;
                }
            }
        }

        // 3. Match by SIM card phone numbers detected on the device
        $normalizedPhones = [];
        foreach ($phoneNumbers as $p) {
            $loc = Device::formatLocalPhone((string) $p);
            if (!empty($loc)) {
                $normalizedPhones[] = $loc;
            }
        }

        if (!empty($normalizedPhones)) {
            $activeDevices = Device::with('owner')->where('state', '!=', 'DELETED')->get();
            foreach ($activeDevices as $dev) {
                if (!empty($dev->hardware_model) && !empty($hardwareModel) && strcasecmp($dev->hardware_model, $hardwareModel) !== 0) {
                    continue;
                }
                $devSimPhones = $dev->detected_phone_numbers;
                $ownerPhone = $dev->owner ? Device::formatLocalPhone($dev->owner->telepon) : null;

                foreach ($normalizedPhones as $np) {
                    if (in_array($np, $devSimPhones, true) || ($ownerPhone && $ownerPhone === $np)) {
                        return $dev;
                    }
                }
            }
        }

        // 4. Match by hardware_model among unbound active devices
        if (!empty($hardwareModel)) {
            $unboundMatches = Device::with('owner')
                ->where('state', '!=', 'DELETED')
                ->where('hardware_model', $hardwareModel)
                ->whereNull('agent_android_id')
                ->orderByDesc('last_status_report')
                ->orderByDesc('updated_at')
                ->get();

            if ($unboundMatches->count() === 1) {
                return $unboundMatches->first();
            }

            if ($unboundMatches->count() > 1) {
                // Prefer device with matching battery level (±3%) if available
                if ($batteryLevel !== null) {
                    foreach ($unboundMatches as $candidate) {
                        if ($candidate->battery_level !== null && abs($candidate->battery_level - $batteryLevel) <= 3) {
                            return $candidate;
                        }
                    }
                }

                // Fallback to the most recently synced unbound device of this hardware model
                return $unboundMatches->first();
            }
        }

        return null;
    }

    /**
     * Download the pre-built Amanin Location Agent APK file.
     */
    public function downloadApk()
    {
        $path = public_path('apk/amanin-location-agent.apk');
        if (!file_exists($path)) {
            abort(404, 'File APK belum tersedia.');
        }

        return response()->download($path, 'amanin-location-agent.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
