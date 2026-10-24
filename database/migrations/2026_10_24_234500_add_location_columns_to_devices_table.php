<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('android_version');
            }
            if (!Schema::hasColumn('devices', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('devices', 'location_accuracy')) {
                $table->float('location_accuracy')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('devices', 'location_updated_at')) {
                $table->timestamp('location_updated_at')->nullable()->after('location_accuracy');
            }
            if (!Schema::hasColumn('devices', 'agent_android_id')) {
                $table->string('agent_android_id', 100)->nullable()->index()->after('location_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'location_accuracy',
                'location_updated_at',
                'agent_android_id',
            ]);
        });
    }
};
