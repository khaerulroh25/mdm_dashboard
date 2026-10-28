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
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'enterprise_id')) {
                $table->foreignId('enterprise_id')
                    ->nullable()
                    ->after('address')
                    ->constrained('enterprises')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('branches', 'default_policy_id')) {
                $table->string('default_policy_id', 100)
                    ->default('Mode_kiosk')
                    ->after('enterprise_id');
            }
        });

        // Seed existing branches to link to first/active enterprise
        $activeEnt = DB::table('enterprises')->where('is_active', true)->first()
            ?? DB::table('enterprises')->first();

        if ($activeEnt) {
            DB::table('branches')
                ->whereNull('enterprise_id')
                ->update([
                    'enterprise_id'     => $activeEnt->id,
                    'default_policy_id' => 'Mode_kiosk',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'enterprise_id')) {
                $table->dropForeign(['enterprise_id']);
                $table->dropColumn('enterprise_id');
            }
            if (Schema::hasColumn('branches', 'default_policy_id')) {
                $table->dropColumn('default_policy_id');
            }
        });
    }
};
