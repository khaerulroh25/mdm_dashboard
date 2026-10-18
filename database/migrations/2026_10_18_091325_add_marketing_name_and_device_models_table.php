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
        Schema::table('devices', function (Blueprint $table) {
            $table->string('marketing_name')->nullable()->after('hardware_model')->comment('Consumer marketing name e.g. Vivo Y29 5G');
        });

        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->nullable();
            $table->string('model_code')->unique()->index()->comment('Hardware code from AMAPI e.g. V2434, SM-A556E');
            $table->string('marketing_name')->comment('Human friendly name e.g. Vivo Y29 5G');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_models');

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('marketing_name');
        });
    }
};
