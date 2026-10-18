<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index()->comment('AMAPI Resource Name');
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('command_type')->index()->comment('LOCK, REBOOT, RESET_PASSWORD, START_LOST_MODE, STOP_LOST_MODE');
            $table->json('command_params')->nullable();
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAILED'])->default('PENDING')->index();
            $table->string('amapi_command_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
