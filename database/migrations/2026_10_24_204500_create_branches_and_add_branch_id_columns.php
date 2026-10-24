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
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 150);
                $table->string('contact_phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('branches')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('devices', 'branch_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('device_id')
                    ->constrained('branches')
                    ->nullOnDelete();
            });
        }

        // Seed initial Branch: Cabang Rengasdengklok (RDK) & migrate existing devices + admin_rdk
        $now = now();
        $rdkBranch = DB::table('branches')->where('code', 'RDK')->first();
        if (!$rdkBranch) {
            $rdkId = DB::table('branches')->insertGetId([
                'code'          => 'RDK',
                'name'          => 'Cabang Rengasdengklok',
                'contact_phone' => '0858-1406-4274',
                'address'       => 'Rengasdengklok, Karawang',
                'is_active'     => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        } else {
            $rdkId = $rdkBranch->id;
        }

        // Assign all existing devices to RDK branch
        DB::table('devices')->whereNull('branch_id')->update(['branch_id' => $rdkId]);

        // Normalize departemen in device_owners to RENGASDENGKLOK
        DB::table('device_owners')
            ->whereNull('departemen')
            ->orWhere('departemen', '0')
            ->orWhere('departemen', '')
            ->update(['departemen' => 'RENGASDENGKLOK']);

        // Assign admin_rdk user to RDK branch
        DB::table('users')
            ->where('username', 'admin_rdk')
            ->update(['branch_id' => $rdkId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('devices', 'branch_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        Schema::dropIfExists('branches');
    }
};
