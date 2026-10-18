<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeviceSyncService;

class SyncDevicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mdm:sync-devices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all devices from Google Android Management API (AMAPI) to local database';

    /**
     * Execute the console command.
     */
    public function handle(DeviceSyncService $syncService): int
    {
        $this->info('Starting AMAPI device synchronization...');

        $result = $syncService->syncAll();

        if ($result['success']) {
            $policiesSynced = $result['policies_synced'] ?? 0;
            $this->info("Successfully synchronized {$result['synced_count']} devices and {$policiesSynced} policies into local database.");
            if (!empty($result['errors'])) {
                $this->warn('Encountered non-fatal warnings:');
                foreach ($result['errors'] as $err) {
                    $this->warn("- {$err}");
                }
            }
            return Command::SUCCESS;
        } else {
            $this->error("Device sync failed: " . ($result['message'] ?? 'Unknown error'));
            return Command::FAILURE;
        }
    }
}
