<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EnrollmentToken;
use App\Models\Policy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EnrollmentWebTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'admin', 'username' => 'test_admin']);
    }

    public function test_admin_can_view_enrollment_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/enrollment');

        $response->assertStatus(200);
        $response->assertSee('Buat QR Code Pendaftaran Perangkat');
        $response->assertSee('In-House MDM');
        $response->assertSee('Google Cloud AMAPI');
    }

    public function test_admin_can_generate_standalone_enrollment_qr(): void
    {
        $response = $this->actingAs($this->admin)->post('/enrollment/generate', [
            'engine'     => 'standalone',
            'policy_id'  => 'Mode_kiosk',
            'owner_name' => 'Budi Santoso',
            'id_anggota' => 'EMP-888',
            'wifi_ssid'  => 'Office_WiFi',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Lembar QR Code Pendaftaran Perangkat');
        $response->assertSee('In-House MDM (Custom DPC)');
        $response->assertSee('Budi Santoso');
        $response->assertSee('EMP-888');
        $response->assertSee('Perintah Simulasi di Emulator');

        $this->assertDatabaseHas('enrollment_tokens', [
            'policy_id' => 'Mode_kiosk',
            'is_used' => false,
        ]);
    }

    public function test_admin_can_view_existing_token(): void
    {
        $token = EnrollmentToken::create([
            'token' => EnrollmentToken::generateUniqueToken(),
            'policy_id' => 'Mode_kiosk',
            'expires_at' => now()->addDays(7),
            'is_one_time' => true,
            'is_used' => false,
        ]);

        $response = $this->actingAs($this->admin)->get("/enrollment/tokens/{$token->id}");

        $response->assertStatus(200);
        $response->assertSee($token->token);
    }

    public function test_admin_can_revoke_token(): void
    {
        $token = EnrollmentToken::create([
            'token' => EnrollmentToken::generateUniqueToken(),
            'policy_id' => 'Mode_kiosk',
            'expires_at' => now()->addDays(7),
            'is_one_time' => true,
            'is_used' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete("/enrollment/tokens/{$token->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollment_tokens', [
            'id' => $token->id,
            'is_used' => true,
        ]);
    }
}
