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
        Schema::create('policy_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->onDelete('cascade');
            $table->foreignId('app_id')->constrained('apps')->onDelete('cascade');
            $table->enum('install_type', ['FORCE_INSTALLED', 'AVAILABLE', 'KIOSK'])->default('AVAILABLE');
            $table->boolean('auto_update')->default(true);
            $table->timestamps();

            $table->unique(['policy_id', 'app_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_applications');
    }
};
