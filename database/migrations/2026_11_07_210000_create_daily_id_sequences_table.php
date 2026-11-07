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
        if (!Schema::hasTable('daily_id_sequences')) {
            Schema::create('daily_id_sequences', function (Blueprint $table) {
                $table->id();
                $table->string('date_key', 8)->unique()->comment('Format: YYYYMMDD, e.g. 20261107');
                $table->unsignedInteger('last_sequence')->default(0)->comment('Last allocated sequence number for the day');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_id_sequences');
    }
};
