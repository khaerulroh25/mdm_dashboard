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
            if (!Schema::hasColumn('policies', 'usb_debugging_disabled')) {
                $table->boolean('usb_debugging_disabled')->default(true)->after('safe_boot_disabled');
            }
            if (!Schema::hasColumn('policies', 'usb_file_transfer_disabled')) {
                $table->boolean('usb_file_transfer_disabled')->default(false)->after('usb_debugging_disabled');
            }
        });

        // Default enable usb_debugging_disabled for all active policies except 'Lunas'
        DB::table('policies')
            ->where('policy_id', '!=', 'Lunas')
            ->update(['usb_debugging_disabled' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            if (Schema::hasColumn('policies', 'usb_file_transfer_disabled')) {
                $table->dropColumn('usb_file_transfer_disabled');
            }
            if (Schema::hasColumn('policies', 'usb_debugging_disabled')) {
                $table->dropColumn('usb_debugging_disabled');
            }
        });
    }
};
