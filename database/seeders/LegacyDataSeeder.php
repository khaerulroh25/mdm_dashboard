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

        // 3b. Sync production device ownership records (updated Oct 2026)
        $productionOwners = [
            ['device_id' => 'enterprises/LC041be11h/devices/3cdeb00f44d89165', 'nama' => 'DINA-G.SALAK', 'id_anggota' => '0', 'telepon' => null, 'departemen' => null, 'catatan' => null],
            ['device_id' => 'enterprises/LC041be11h/devices/38fafe235105ff83', 'nama' => 'EVI-KOALA', 'id_anggota' => '120725050020', 'telepon' => '085693588930', 'departemen' => null, 'catatan' => null],
            ['device_id' => 'enterprises/LC041be11h/devices/39d9bf30f74256af', 'nama' => 'TATI-TEUKU UMAR', 'id_anggota' => '120725080019', 'telepon' => null, 'departemen' => null, 'catatan' => null],
            ['device_id' => 'enterprises/LC041be11h/devices/3b7586c07e6688eb', 'nama' => 'MARLINA-IMAM BONJOL', 'id_anggota' => '120724120019', 'telepon' => null, 'departemen' => null, 'catatan' => null],
            ['device_id' => 'enterprises/LC041be11h/devices/3f5c526d8876526d', 'nama' => 'Khaerul', 'id_anggota' => '20181001911', 'telepon' => null, 'departemen' => null, 'catatan' => null],
            ['device_id' => 'enterprises/LC041be11h/devices/34a7f4178c6e5649', 'nama' => 'ANIH PATIMURA', 'id_anggota' => '12072412006303', 'telepon' => '085710383968', 'departemen' => '0', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3c9baf77dfd591d5', 'nama' => 'IDA AGEN IIS', 'id_anggota' => '12072604000201', 'telepon' => '085880692343', 'departemen' => '0', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3885e5d527148314', 'nama' => 'DIAN MARLINA AGEN IIS', 'id_anggota' => '12072603000101', 'telepon' => '083875290489', 'departemen' => '0', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/38244358c6151a24', 'nama' => 'NURJANAH ANGGREK', 'id_anggota' => '12122309008906', 'telepon' => '08581700559', 'departemen' => '0', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3181ce87efb031ea', 'nama' => 'WIDAWATI PAUS', 'id_anggota' => '12122402007806', 'telepon' => '085883355471', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/34998c9ecbb20192', 'nama' => 'TARYONO KENANGA', 'id_anggota' => '12072511004401', 'telepon' => '0895428537487', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/36d98a74cabf8401', 'nama' => 'WINDA AGEN IIS', 'id_anggota' => '12072501002302', 'telepon' => '085810773287', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/39caf2d3a4a25458', 'nama' => 'YANTI CUT NYAK DIEN', 'id_anggota' => '12072507004001', 'telepon' => '085788654120', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/34365f9a09030d23', 'nama' => 'SUKAESIH ANTASARI', 'id_anggota' => '12072501007405', 'telepon' => '085695358592', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/32d803d2393414ae', 'nama' => 'ANGGITA KUTAI', 'id_anggota' => '12072605000502', 'telepon' => '08972278738', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3b8b73bc8750cfa8', 'nama' => 'SITI ROMLAH GN SALAK', 'id_anggota' => '12072411003804', 'telepon' => '081617130072', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/37d05d5ee049905e', 'nama' => 'NINA AGEN IIS', 'id_anggota' => '12072607000901', 'telepon' => '085714941993', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/355b1347dd3544ac', 'nama' => 'MUMUN AGEN IIS', 'id_anggota' => '12072506003902', 'telepon' => '085215059517', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/32a4230b046837c3', 'nama' => 'LINA TARUMANEGARA', 'id_anggota' => '12072605000301', 'telepon' => '083823195644', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3fa1c160cdec57ad', 'nama' => 'ENCIH GN PUTRI', 'id_anggota' => '12072411004303', 'telepon' => '085639678521', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3cc1bd2ac4047cd6', 'nama' => 'AYAH AGEN IIS', 'id_anggota' => '12072502003202', 'telepon' => '08593159766', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/34a908caad2d435b', 'nama' => 'NOPI GN PUTRI', 'id_anggota' => '12072411002503', 'telepon' => '083873468779', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/32a3b2af203a5f96', 'nama' => 'IRATNA RP KAPUAS', 'id_anggota' => '12072511000902', 'telepon' => '085714270038', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/32c280eaf54f8ce8', 'nama' => 'ACEM SINGOSARI', 'id_anggota' => '12072510010602', 'telepon' => '083845995452', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3667c7ff9e03cac5', 'nama' => 'VELA JAGUNG', 'id_anggota' => '12072505002901', 'telepon' => '082115740640', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3d6975f79e30488d', 'nama' => 'NURJANAH CISADANE', 'id_anggota' => '12072510007402', 'telepon' => '085773233281', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3730f46347f64287', 'nama' => 'SARIPATUL ANTASARI', 'id_anggota' => '12072501007903', 'telepon' => '082123624106', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3c3f26d42765c6ef', 'nama' => 'DELSI AGEN IIS', 'id_anggota' => '12072511002901', 'telepon' => '085694057767', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/394ffe7e42567151', 'nama' => 'SITI AISAH TALAM', 'id_anggota' => '12122311003106', 'telepon' => '085691840938', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
            ['device_id' => 'enterprises/LC041be11h/devices/3cbd0871e4480f33', 'nama' => 'CHASIAH BANTEN', 'id_anggota' => '12122304001705', 'telepon' => '08560031787', 'departemen' => 'RENGASDENGKLOK', 'catatan' => '0'],
        ];

        foreach ($productionOwners as $owner) {
            DeviceOwner::updateOrCreate(
                ['device_id' => $owner['device_id']],
                $owner
            );
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
