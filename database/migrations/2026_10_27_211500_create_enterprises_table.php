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
        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->string('enterprise_id')->unique();
            $table->string('name');
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
            $table->string('signup_url_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Seed initial enterprise from current system configuration
        DB::table('enterprises')->insert([
            'enterprise_id'   => 'enterprises/LC041be11h',
            'name'            => 'Devicekontrol',
            'admin_email'     => 'devicekontrol450@gmail.com',
            'admin_phone'     => '085814064274',
            'signup_url_name' => null,
            'is_active'       => true,
            'metadata'        => json_encode([
                'enterpriseType' => 'MANAGED_GOOGLE_PLAY_ACCOUNTS_ENTERPRISE',
                'managedGooglePlayAccountsEnterpriseType' => 'CUSTOMER_MANAGED',
                'contactInfo' => [
                    'dataProtectionOfficerEmail' => 'devicekontrol450@gmail.com',
                    'dataProtectionOfficerName'  => 'Khaerul Rohman',
                    'dataProtectionOfficerPhone' => '085814064274',
                ],
            ]),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprises');
    }
};
