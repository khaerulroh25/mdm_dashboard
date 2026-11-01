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
        // 1. Create enrollment_tokens table for independent provisioning
        Schema::create('enrollment_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique()->index();
            $table->string('policy_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('enterprise_id')->nullable()->index();
            $table->json('owner_data')->nullable(); // Snapshot of owner pre-allocation
            $table->boolean('is_one_time')->default(true);
            $table->boolean('is_used')->default(false)->index();
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('used_by_device_id')->nullable()->index();
            $table->timestamp('expires_at')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // 2. Add columns to devices table for standalone DPC management
        Schema::table('devices', function (Blueprint $table) {
            $table->string('device_token', 64)->nullable()->unique()->after('device_id');
            $table->unsignedTinyInteger('battery_level')->nullable()->after('android_version');
            $table->string('battery_status', 50)->nullable()->after('battery_level');
            $table->boolean('is_online')->default(false)->index()->after('battery_status');
            $table->string('dpc_version', 50)->nullable()->after('is_online');
            $table->unsignedBigInteger('enrollment_token_id')->nullable()->after('dpc_version');

            $table->foreign('enrollment_token_id')->references('id')->on('enrollment_tokens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['enrollment_token_id']);
            $table->dropColumn([
                'device_token',
                'battery_level',
                'battery_status',
                'is_online',
                'dpc_version',
                'enrollment_token_id',
            ]);
        });

        Schema::dropIfExists('enrollment_tokens');
    }
};
