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
        Schema::create('device_owners', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable()->index()->comment('Links to devices.device_id');
            $table->string('nama')->index();
            $table->string('id_anggota')->index();
            $table->string('telepon')->nullable();
            $table->string('departemen')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_owners');
    }
};
