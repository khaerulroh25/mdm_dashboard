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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_id')->unique()->index()->comment('AMAPI policy ID, e.g. default, kiosk, policy_sales');
            $table->string('name')->comment('User friendly label');
            $table->text('description')->nullable();
            $table->boolean('camera_disabled')->default(false);
            $table->boolean('wifi_config_disabled')->default(false);
            $table->boolean('factory_reset_disabled')->default(false);
            $table->boolean('install_apps_disabled')->default(false);
            $table->boolean('safe_boot_disabled')->default(false);
            $table->string('location_mode')->nullable()->comment('HIGH_ACCURACY, SENSORS_ONLY, BATTERY_SAVING, OFF');
            $table->json('raw_policy_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
