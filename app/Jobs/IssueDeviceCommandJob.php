<?php

namespace App\Jobs;

use Exception;
use App\Models\DeviceCommand;
use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IssueDeviceCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    protected int $deviceCommandId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $deviceCommandId)
    {
        $this->deviceCommandId = $deviceCommandId;
    }

    /**
     * Execute the job.
     * In standalone MDM mode, commands are stored in the database as PENDING
     * and delivered to the native Android DPC client via heartbeat / API polling.
     */
    public function handle(): void
    {
        $command = DeviceCommand::find($this->deviceCommandId);

        if (!$command) {
            Log::error("IssueDeviceCommandJob: DeviceCommand ID {$this->deviceCommandId} not found.");
            return;
        }

        Log::info("Command {$command->command_type} (ID: {$command->id}) ready for standalone DPC delivery to {$command->device_id}");
    }
}
