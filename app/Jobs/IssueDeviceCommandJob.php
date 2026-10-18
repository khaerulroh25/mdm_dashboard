<?php

namespace App\Jobs;

use Exception;
use App\Models\DeviceCommand;
use App\Models\AuditLog;
use App\Services\AndroidManagementService;
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
     */
    public function handle(AndroidManagementService $amapi): void
    {
        $command = DeviceCommand::find($this->deviceCommandId);

        if (!$command) {
            Log::error("IssueDeviceCommandJob: DeviceCommand ID {$this->deviceCommandId} not found.");
            return;
        }

        try {
            $response = $amapi->issueCommand(
                $command->device_id,
                $command->command_type,
                $command->command_params ?? []
            );

            $amapiCommandId = method_exists($response, 'getName') ? $response->getName() : null;

            $command->update([
                'status' => 'SUCCESS',
                'amapi_command_id' => $amapiCommandId,
                'executed_at' => now(),
            ]);

            // Record in audit log
            AuditLog::create([
                'user_id' => $command->issued_by_user_id,
                'action' => "COMMAND_{$command->command_type}",
                'target_type' => 'Device',
                'target_id' => $command->device_id,
                'new_values' => [
                    'status' => 'SUCCESS',
                    'command_type' => $command->command_type,
                    'amapi_command_id' => $amapiCommandId,
                ],
            ]);

            Log::info("Command {$command->command_type} sent successfully to {$command->device_id}");

        } catch (Exception $e) {
            Log::error("Command {$command->command_type} failed for {$command->device_id}: " . $e->getMessage());

            $command->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
                'executed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $command->issued_by_user_id,
                'action' => "COMMAND_{$command->command_type}_FAILED",
                'target_type' => 'Device',
                'target_id' => $command->device_id,
                'new_values' => [
                    'status' => 'FAILED',
                    'error' => $e->getMessage(),
                ],
            ]);

            // Re-throw if within retry attempts
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }
}
