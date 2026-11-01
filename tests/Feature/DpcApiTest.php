<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\EnrollmentToken;
use App\Models\Policy;
use App\Services\StandaloneEnrollmentService;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DpcApiTest extends TestCase
{
    use DatabaseTransactions;
    public function test_can_create_standalone_enrollment_and_qr_code()
    {
        $service = app(StandaloneEnrollmentService::class);
        $result = $service->createEnrollment(
            policyId: 'Mode_kiosk',
            ownerData: ['nama' => 'Test User', 'id_anggota' => 'EMP-001']
        );

        $this->assertNotEmpty($result['token_string']);
        $this->assertNotEmpty($result['base64_qr']);
        $this->assertStringStartsWith('data:image/png;base64,', $result['base64_qr']);
        $this->assertDatabaseHas('enrollment_tokens', [
            'token' => $result['token_string'],
            'is_used' => false,
        ]);
    }

    public function test_dpc_device_can_enroll_with_valid_token()
    {
        $token = EnrollmentToken::create([
            'token' => EnrollmentToken::generateUniqueToken(),
            'policy_id' => 'Mode_kiosk',
            'owner_data' => ['nama' => 'John Doe', 'id_anggota' => 'JD-123'],
            'is_one_time' => true,
            'is_used' => false,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/v1/dpc/enroll', [
            'enrollment_token' => $token->token,
            'brand' => 'Google',
            'hardware_model' => 'Pixel 6',
            'manufacturer' => 'Google',
            'serial_number' => 'TESTSERIAL12345',
            'android_version' => '13',
            'dpc_version' => '1.0.0',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'device_id',
                    'device_token',
                    'policy_id',
                    'policy',
                ]
            ]);

        $this->assertDatabaseHas('devices', [
            'serial_number' => 'TESTSERIAL12345',
            'is_online' => true,
        ]);

        $this->assertDatabaseHas('enrollment_tokens', [
            'id' => $token->id,
            'is_used' => true,
        ]);

        $this->assertDatabaseHas('device_owners', [
            'nama' => 'John Doe',
            'id_anggota' => 'JD-123',
        ]);
    }

    public function test_dpc_enrollment_fails_with_invalid_token()
    {
        $response = $this->postJson('/api/v1/dpc/enroll', [
            'enrollment_token' => 'INVALID-TOKEN',
            'hardware_model' => 'Pixel 6',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_dpc_can_send_heartbeat_and_fetch_policy()
    {
        $device = Device::create([
            'device_id' => 'MDM-TEST-001',
            'device_token' => 'sample_secure_bearer_token_1234567890',
            'state' => 'ACTIVE',
            'policy_id' => 'Mode_kiosk',
            'is_online' => true,
        ]);

        // Heartbeat
        $hbResponse = $this->withHeader('Authorization', 'Bearer sample_secure_bearer_token_1234567890')
            ->postJson('/api/v1/dpc/heartbeat', [
                'battery_level' => 88,
                'battery_status' => 'CHARGING',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]);

        $hbResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('devices', [
            'device_id' => 'MDM-TEST-001',
            'battery_level' => 88,
            'battery_status' => 'CHARGING',
        ]);

        // Fetch Policy
        $policyResponse = $this->withHeader('Authorization', 'Bearer sample_secure_bearer_token_1234567890')
            ->getJson('/api/v1/dpc/policy');

        $policyResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'policy_id',
                'policy' => [
                    'policy_id',
                    'restrictions',
                    'frp' => [
                        'enabled',
                        'accounts',
                    ],
                ]
            ]);
    }

    public function test_dpc_can_acknowledge_commands()
    {
        $device = Device::create([
            'device_id' => 'MDM-TEST-002',
            'device_token' => 'sample_bearer_token_cmd_test',
            'state' => 'ACTIVE',
            'policy_id' => 'Mode_kiosk',
        ]);

        $cmd = DeviceCommand::create([
            'device_id' => 'MDM-TEST-002',
            'command_type' => 'LOCK',
            'status' => 'PENDING',
        ]);

        $ackResponse = $this->withHeader('Authorization', 'Bearer sample_bearer_token_cmd_test')
            ->postJson("/api/v1/dpc/commands/{$cmd->id}/ack", [
                'status' => 'SUCCESS',
            ]);

        $ackResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('device_commands', [
            'id' => $cmd->id,
            'status' => 'SUCCESS',
        ]);
    }
}
