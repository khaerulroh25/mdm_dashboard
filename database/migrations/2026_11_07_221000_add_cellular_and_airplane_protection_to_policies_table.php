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
            if (!Schema::hasColumn('policies', 'mobile_networks_config_disabled')) {
                $table->boolean('mobile_networks_config_disabled')->default(true)->after('usb_file_transfer_disabled');
            }
            if (!Schema::hasColumn('policies', 'airplane_mode_disabled')) {
                $table->boolean('airplane_mode_disabled')->default(true)->after('mobile_networks_config_disabled');
            }
        });

        // Set default enabled for active policies except 'Lunas'
        DB::table('policies')
            ->where('policy_id', '!=', 'Lunas')
            ->update([
                'mobile_networks_config_disabled' => true,
                'airplane_mode_disabled' => true,
            ]);

        DB::table('policies')
            ->where('policy_id', 'Lunas')
            ->update([
                'mobile_networks_config_disabled' => false,
                'airplane_mode_disabled' => false,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            if (Schema::hasColumn('policies', 'airplane_mode_disabled')) {
                $table->dropColumn('airplane_mode_disabled');
            }
            if (Schema::hasColumn('policies', 'mobile_networks_config_disabled')) {
                $table->dropColumn('mobile_networks_config_disabled');
            }
        });
    }
};
