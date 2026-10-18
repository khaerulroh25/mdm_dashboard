<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DeviceOwner;
use App\Models\App;
use App\Models\Policy;
use App\Models\Device;

class LegacyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting migration of legacy data...');

        // 1. Seed Default Admin User
        $admin = User::firstOrCreate(
            ['username' => 'admin_rdk'],
            [
                'name' => 'Administrator',
                'email' => 'admin@mdm.local',
                // Keep existing bcrypt password ($2y$10$7j7ECY.nwWYxox7LBphGAOf6ps3DBwURh3viiMalN/eLxEY9SEDuC) or fallback demo
                'password' => '$2y$10$7j7ECY.nwWYxox7LBphGAOf6ps3DBwURh3viiMalN/eLxEY9SEDuC',
                'role' => 'admin',
            ]
        );
        $this->command->info("Seeded User: {$admin->username}");

        // Create standard Operator and Auditor for testing RBAC
        User::firstOrCreate(
            ['username' => 'operator'],
            [
                'name' => 'Operator Lapangan',
                'email' => 'operator@mdm.local',
                'password' => Hash::make('operator123'),
                'role' => 'operator',
            ]
        );

        User::firstOrCreate(
            ['username' => 'auditor'],
            [
                'name' => 'Auditor MDM',
                'email' => 'auditor@mdm.local',
                'password' => Hash::make('auditor123'),
                'role' => 'auditor',
            ]
        );

        // 2. Seed Default Policy Placeholder
        Policy::firstOrCreate(
            ['policy_id' => 'default'],
            [
                'name' => 'Default Policy Enterprise',
                'description' => 'Policy standar untuk seluruh perangkat aktif organisasi.',
                'camera_disabled' => false,
                'wifi_config_disabled' => false,
                'factory_reset_disabled' => true,
                'install_apps_disabled' => false,
                'safe_boot_disabled' => true,
                'location_mode' => 'HIGH_ACCURACY',
            ]
        );

        // 3. Parse and Seed Owners and Apps from legacy_v1/config/Db_mdm.sql
        $sqlPath = base_path('legacy_v1/config/Db_mdm.sql');
        if (!file_exists($sqlPath)) {
            $this->command->warn("SQL file not found at: {$sqlPath}");
            return;
        }

        $sqlContent = file_get_contents($sqlPath);

        // 3a. Extract `pemilik` rows
        // Pattern: (9, 'enterprises/LC041be11h/devices/37db67c3e1be3423', 'DEDE ZUNAEDI - AGEN IIS', '0', '085814064274', '2025-08-19 11:09:48')
        if (preg_match('/INSERT INTO `pemilik`.*?VALUES\s*(.*?);/s', $sqlContent, $pemilikMatch)) {
            $valuesBlock = $pemilikMatch[1];
            preg_match_all('/\((.*?)\)(?:,|$)/s', $valuesBlock, $rows);

            $ownerCount = 0;
            foreach ($rows[1] as $row) {
                $cols = str_getcsv($row, ",", "'");
                if (count($cols) >= 5) {
                    $device = trim($cols[1]);
                    $nama = trim($cols[2]);
                    $idAnggota = trim($cols[3]);
                    $telepon = trim($cols[4]);

                    DeviceOwner::updateOrCreate(
                        ['nama' => $nama, 'id_anggota' => $idAnggota],
                        [
                            'device_id' => $device ?: null,
                            'telepon' => $telepon ?: null,
                        ]
                    );

                    // Also seed stub device in devices table so relationship works immediately
                    if ($device) {
                        Device::firstOrCreate(
                            ['device_id' => $device],
                            [
                                'state' => 'ACTIVE',
                                'policy_id' => 'default',
                                'enrolled_at' => now(),
                            ]
                        );
                    }

                    $ownerCount++;
                }
            }
            $this->command->info("Seeded {$ownerCount} device owners.");
        }

        // 3b. Extract `apps` rows
        // Pattern: (1, 'TikTok Lite - Hemat Data', 'com.zhiliaoapp.musically.go', '2025-09-13 13:04:56')
        if (preg_match('/INSERT INTO `apps`.*?VALUES\s*(.*?);/s', $sqlContent, $appsMatch)) {
            $valuesBlock = $appsMatch[1];
            preg_match_all('/\((.*?)\)(?:,|$)/s', $valuesBlock, $rows);

            $appsBatch = [];
            $now = now();
            foreach ($rows[1] as $row) {
                $cols = str_getcsv($row, ",", "'");
                if (count($cols) >= 3) {
                    $appName = trim($cols[1]);
                    $pkgName = trim($cols[2]);

                    $appsBatch[] = [
                        'app_name' => $appName,
                        'package_name' => $pkgName,
                        'icon_url' => null,
                        'developer' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Insert in chunks of 500 for high performance
            foreach (array_chunk($appsBatch, 500) as $chunk) {
                App::upsert($chunk, ['package_name'], ['app_name', 'updated_at']);
            }
            $totalApps = count($appsBatch);
            $this->command->info("Seeded {$totalApps} apps from legacy database.");
        }

        $this->command->info('Legacy data migration completed successfully!');
    }
}
