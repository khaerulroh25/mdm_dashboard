<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\DeviceOwner;
use App\Models\DeviceCommand;
use App\Models\AuditLog;
use App\Services\AndroidManagementService;
use Illuminate\Support\Facades\DB;

class PruneUnenrolledCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mdm:prune-unenrolled {--dry-run : Hanya tampilkan daftar data yang tidak terdaftar tanpa menghapus} {--force : Eksekusi penghapusan tanpa konfirmasi prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus data pengguna (device owners) dan perangkat lama yang sudah tidak terdaftar di Google AMAPI (sudah di-unenroll)';

    /**
     * Execute the console command.
     */
    public function handle(AndroidManagementService $amapi): int
    {
        $this->info('Mengambil daftar perangkat aktif dari Google Android Management API (AMAPI)...');

        try {
            $amapiDevices = $amapi->listDevices();
        } catch (\Throwable $e) {
            $this->error('Gagal menghubungi Google AMAPI: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $liveDeviceNames = [];
        foreach ($amapiDevices as $dev) {
            $name = $dev->getName();
            $liveDeviceNames[] = $name;
            // Also add relative device id e.g. 3181ce87efb031ea
            $parts = explode('/', $name);
            $liveDeviceNames[] = end($parts);
        }

        $this->info('Perangkat aktif di Google AMAPI: ' . count($amapiDevices));

        // 1. Identify unenrolled devices in local DB
        $dbDevices = Device::all();
        $unenrolledDevices = $dbDevices->filter(function ($dev) use ($liveDeviceNames) {
            return !in_array($dev->device_id, $liveDeviceNames);
        });

        // 2. Identify orphaned or unenrolled device owners
        $dbOwners = DeviceOwner::all();
        $unenrolledOwners = $dbOwners->filter(function ($owner) use ($liveDeviceNames) {
            if (!$owner->device_id) {
                return true;
            }
            return !in_array($owner->device_id, $liveDeviceNames);
        });

        if ($unenrolledDevices->isEmpty() && $unenrolledOwners->isEmpty()) {
            $this->info('Tidak ditemukan data pengguna atau perangkat yang unenrolled. Seluruh database sudah sinkron dengan AMAPI.');
            return Command::SUCCESS;
        }

        $this->warn('Ditemukan ' . $unenrolledOwners->count() . ' data pengguna (owners) dan ' . $unenrolledDevices->count() . ' perangkat yang sudah tidak terdaftar di AMAPI:');

        $this->newLine();
        $this->table(
            ['Owner ID', 'Nama Pengguna', 'ID Anggota', 'Device ID Terkait'],
            $unenrolledOwners->map(fn($o) => [
                $o->id,
                $o->nama,
                $o->id_anggota,
                $o->device_id ?? '(Tidak Ada)'
            ])
        );

        $this->newLine();
        $this->table(
            ['Device ID DB', 'AMAPI Device Name / ID', 'Model', 'Status DB'],
            $unenrolledDevices->map(fn($d) => [
                $d->id,
                $d->device_id,
                $d->hardware_model ?? '-',
                $d->state
            ])
        );

        if ($this->option('dry-run')) {
            $this->info('Mode --dry-run aktif. Tidak ada data yang dihapus.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin menghapus seluruh data pengguna dan perangkat unenrolled di atas secara permanen?')) {
                $this->info('Operasi dibatalkan.');
                return Command::SUCCESS;
            }
        }

        $this->info('Memproses penghapusan data secara aman dalam database transaction...');

        DB::beginTransaction();
        try {
            $deletedOwnerCount = 0;
            $deletedDeviceCount = 0;

            // Delete owners
            foreach ($unenrolledOwners as $owner) {
                $owner->delete();
                $deletedOwnerCount++;
            }

            // Delete associated commands and devices
            foreach ($unenrolledDevices as $device) {
                DeviceCommand::where('device_id', $device->id)->delete();
                $device->delete();
                $deletedDeviceCount++;
            }

            AuditLog::log(
                'PRUNE_UNENROLLED',
                'System',
                null,
                [
                    'deleted_owners_count' => $deletedOwnerCount,
                    'deleted_devices_count' => $deletedDeviceCount,
                    'deleted_owner_ids' => $unenrolledOwners->pluck('id')->toArray(),
                    'deleted_device_ids' => $unenrolledDevices->pluck('device_id')->toArray(),
                ]
            );

            DB::commit();

            $this->info("Berhasil membersihkan database:");
            $this->line("- {$deletedOwnerCount} data pengguna (device owners) telah dihapus.");
            $this->line("- {$deletedDeviceCount} perangkat unenrolled telah dihapus.");
            $this->line("- Riwayat audit trail telah dicatat.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal menghapus data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
