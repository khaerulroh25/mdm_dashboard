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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique()->index()->comment('AMAPI Resource Name, e.g. enterprises/.../devices/...');
            $table->string('hardware_model')->nullable()->index();
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('android_version')->nullable();
            $table->string('state')->default('ACTIVE')->index()->comment('ACTIVE, PROVISIONING, DISABLED, DELETED');
            $table->string('policy_id')->nullable()->index();
            $table->json('network_info')->nullable();
            $table->json('memory_info')->nullable();
            $table->json('raw_device_data')->nullable();
            $table->timestamp('last_status_report')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
