<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceModel;
use Illuminate\Support\Facades\Log;

class DeviceNameService
{
    /**
     * Built-in dictionary of verified Android models (from Google Play Certified Devices dataset).
     *
     * @var array<string, array{brand: string, name: string}>
     */
    protected static array $dictionary = [
        // Vivo Models
        'V2434'         => ['brand' => 'Vivo', 'name' => 'Vivo Y29 5G'],
        'V2419'         => ['brand' => 'Vivo', 'name' => 'Vivo Y19s'],
        'V2455'         => ['brand' => 'Vivo', 'name' => 'Vivo Y400'],
        'V2507'         => ['brand' => 'Vivo', 'name' => 'Vivo Y03t'],
        'V2520'         => ['brand' => 'Vivo', 'name' => 'Vivo Y21d'],
        'V2556'         => ['brand' => 'Vivo', 'name' => 'Vivo Y05'],
        'V2440'         => ['brand' => 'Vivo', 'name' => 'Vivo V50 Lite 5G'],
        'V2443'         => ['brand' => 'Vivo', 'name' => 'Vivo Y37t'],
        'V2521'         => ['brand' => 'Vivo', 'name' => 'Vivo Y31 5G'],
        'V2506'         => ['brand' => 'Vivo', 'name' => 'Vivo Y400 5G'],

        // Samsung Models
        'SM-A556E'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A55 5G'],
        'SM-A065F'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A06'],
        'SM-A166P'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A16 5G'],
        'SM-A176B'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A17 5G'],
        'SM-A155F'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A15'],
        'SM-A256E'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A25 5G'],
        'SM-A356E'      => ['brand' => 'Samsung', 'name' => 'Samsung Galaxy A35 5G'],

        // OPPO Models
        'CPH2641'       => ['brand' => 'OPPO', 'name' => 'OPPO Reno12 F 5G'],
        'CPH2725'       => ['brand' => 'OPPO', 'name' => 'OPPO A3x'],
        'CPH2773'       => ['brand' => 'OPPO', 'name' => 'OPPO Reno12 F 4G'],
        'CPH2579'       => ['brand' => 'OPPO', 'name' => 'OPPO A60'],
        'CPH2639'       => ['brand' => 'OPPO', 'name' => 'OPPO A3 Pro 5G'],

        // Infinix Models
        'Infinix X6885' => ['brand' => 'Infinix', 'name' => 'Infinix HOT 50 Pro'],
        'X6885'         => ['brand' => 'Infinix', 'name' => 'Infinix HOT 50 Pro'],
        'Infinix X6882' => ['brand' => 'Infinix', 'name' => 'Infinix HOT 50'],
        'X6882'         => ['brand' => 'Infinix', 'name' => 'Infinix HOT 50'],
        'Infinix X6881' => ['brand' => 'Infinix', 'name' => 'Infinix SMART 9'],
        'X6881'         => ['brand' => 'Infinix', 'name' => 'Infinix SMART 9'],

        // Xiaomi / Redmi
        '23129RAA4G'    => ['brand' => 'Xiaomi', 'name' => 'Redmi Note 13 4G'],
        '2312DRA50G'    => ['brand' => 'Xiaomi', 'name' => 'Redmi Note 13 Pro 5G'],
        '23108RN04Y'    => ['brand' => 'Xiaomi', 'name' => 'Redmi 13C'],
    ];

    /**
     * Resolve consumer marketing name from brand & model code.
     */
    public function resolve(?string $brand, ?string $modelCode): ?string
    {
        if (empty($modelCode)) {
            return null;
        }

        $trimmedModel = trim($modelCode);

        // 1. Check local DB table first (allows admin customization/override)
        $cached = DeviceModel::where('model_code', $trimmedModel)->first();
        if ($cached) {
            return $cached->marketing_name;
        }

        // 2. Check built-in dictionary
        if (isset(self::$dictionary[$trimmedModel])) {
            $info = self::$dictionary[$trimmedModel];
            // Auto-persist into DeviceModel table for future edits & fast querying
            try {
                DeviceModel::create([
                    'brand' => $info['brand'] ?? $brand,
                    'model_code' => $trimmedModel,
                    'marketing_name' => $info['name'],
                ]);
            } catch (\Throwable $e) {
                Log::debug("DeviceModel duplicate race condition ignored: " . $e->getMessage());
            }

            return $info['name'];
        }

        // 3. Check partial dictionary match (e.g. without prefix)
        foreach (self::$dictionary as $code => $info) {
            if (strcasecmp($code, $trimmedModel) === 0 || str_ends_with(strtoupper($trimmedModel), strtoupper($code))) {
                return $info['name'];
            }
        }

        // 4. Check Google Play 39,000+ Device Catalog dataset (Offline & Ultra Fast)
        $catalogResult = $this->lookupCatalogFile($trimmedModel, $brand);
        if ($catalogResult) {
            return $catalogResult;
        }

        // 5. Fallback: Online Search / Live Query
        $onlineResult = $this->lookupOnlineApi($trimmedModel, $brand);
        if ($onlineResult) {
            return $onlineResult;
        }

        return null;
    }

    /**
     * Search 39,000+ Google Play Certified Devices catalog.
     */
    protected function lookupCatalogFile(string $modelCode, ?string $brand): ?string
    {
        $path = storage_path('app/device_catalog.json');
        if (!file_exists($path)) {
            return null;
        }

        static $catalog = null;
        if ($catalog === null) {
            $json = file_get_contents($path);
            $catalog = json_decode($json, true) ?: [];
        }

        foreach ($catalog as $item) {
            if (isset($item['model']) && strcasecmp(trim($item['model']), $modelCode) === 0) {
                $resolvedBrand = $item['brand'] ?? $brand ?? '';
                $name = $item['name'] ?? $modelCode;
                
                $fullName = (!empty($resolvedBrand) && !str_starts_with(strtolower($name), strtolower($resolvedBrand)))
                    ? trim($resolvedBrand . ' ' . $name)
                    : $name;

                try {
                    DeviceModel::updateOrCreate(
                        ['model_code' => $modelCode],
                        [
                            'brand' => $resolvedBrand,
                            'marketing_name' => $fullName,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::debug("DeviceModel cache save: " . $e->getMessage());
                }

                return $fullName;
            }
        }

        return null;
    }

    /**
     * Fallback online query when model is not yet in local catalog.
     */
    protected function lookupOnlineApi(string $modelCode, ?string $brand): ?string
    {
        try {
            // Check Google Play public supported devices CSV stream
            $url = "http://storage.googleapis.com/play_public/supported_devices.csv";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_RANGE, '0-1048576'); // First 1MB or full
            $content = curl_exec($ch);
            curl_close($ch);

            if ($content && str_contains($content, $modelCode)) {
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (str_contains($line, $modelCode)) {
                        $parts = str_getcsv($line);
                        if (count($parts) >= 2) {
                            $brandName = trim($parts[0] ?? $brand ?? '');
                            $mName = trim($parts[1] ?? '');
                            $fullName = (!empty($brandName) && !str_starts_with(strtolower($mName), strtolower($brandName)))
                                ? trim($brandName . ' ' . $mName)
                                : $mName;

                            if (!empty($fullName)) {
                                DeviceModel::updateOrCreate(
                                    ['model_code' => $modelCode],
                                    ['brand' => $brandName, 'marketing_name' => $fullName]
                                );
                                return $fullName;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("lookupOnlineApi failed for {$modelCode}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Update or create a custom marketing name mapping.
     */
    public function setCustomName(string $modelCode, string $marketingName, ?string $brand = null): DeviceModel
    {
        $deviceModel = DeviceModel::updateOrCreate(
            ['model_code' => trim($modelCode)],
            [
                'marketing_name' => trim($marketingName),
                'brand' => $brand ? trim($brand) : null,
            ]
        );

        // Update all existing devices with this model code
        Device::where('hardware_model', $modelCode)
            ->update(['marketing_name' => trim($marketingName)]);

        return $deviceModel;
    }

    /**
     * Sync marketing names for all devices currently in local database.
     */
    public function syncAllExistingDevices(): int
    {
        $updated = 0;
        $devices = Device::all();

        foreach ($devices as $device) {
            $resolved = $this->resolve($device->brand, $device->hardware_model);
            if ($resolved && $device->marketing_name !== $resolved) {
                $device->marketing_name = $resolved;
                $device->save();
                $updated++;
            }
        }

        return $updated;
    }
}
