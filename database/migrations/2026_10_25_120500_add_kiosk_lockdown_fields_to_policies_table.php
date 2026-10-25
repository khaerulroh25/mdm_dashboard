<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->boolean('kiosk_status_bar_disabled')->default(false)->after('safe_boot_disabled');
            $table->boolean('kiosk_settings_blocked')->default(false)->after('kiosk_status_bar_disabled');
            $table->boolean('kiosk_navigation_disabled')->default(false)->after('kiosk_settings_blocked');
            $table->boolean('screen_capture_disabled')->default(false)->after('kiosk_navigation_disabled');
            $table->boolean('keyguard_disabled')->default(false)->after('screen_capture_disabled');
        });

        // Seed existing Gagal_bayar policy with ultra lockdown enabled
        DB::table('policies')
            ->where('policy_id', 'Gagal_bayar')
            ->update([
                'kiosk_status_bar_disabled' => true,
                'kiosk_settings_blocked'    => true,
                'kiosk_navigation_disabled' => true,
                'screen_capture_disabled'   => true,
                'keyguard_disabled'         => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn([
                'kiosk_status_bar_disabled',
                'kiosk_settings_blocked',
                'kiosk_navigation_disabled',
                'screen_capture_disabled',
                'keyguard_disabled',
            ]);
        });
    }
};
