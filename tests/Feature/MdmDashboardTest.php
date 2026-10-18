<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Policy;
use Tests\TestCase;

class MdmDashboardTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/devices');
        $response->assertRedirect('/login');

        $response = $this->get('/policies');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MDM Core');
        $response->assertSee('Masuk ke Dashboard');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'operator',
            'password' => 'operator123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'operator',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_authenticated_operator_can_view_pages(): void
    {
        $operator = User::where('username', 'operator')->first();
        if (!$operator) {
            $operator = User::factory()->create(['role' => 'operator']);
        }

        $this->actingAs($operator);

        // Dashboard
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard');

        // Devices
        $devicesResponse = $this->get('/devices');
        $devicesResponse->assertStatus(200);
        $devicesResponse->assertSee('Manajemen Armada Perangkat');

        // Policies
        $policiesResponse = $this->get('/policies');
        $policiesResponse->assertStatus(200);
        $policiesResponse->assertSee('Daftar Kebijakan Enterprise');

        // Apps
        $appsResponse = $this->get('/apps');
        $appsResponse->assertStatus(200);
        $appsResponse->assertSee('Katalog Aplikasi Android');

        // Enrollment
        $enrollmentResponse = $this->get('/enrollment');
        $enrollmentResponse->assertStatus(200);
        $enrollmentResponse->assertSee('Buat QR Code Pendaftaran Perangkat');
    }

    public function test_rbac_operator_cannot_access_audit_logs(): void
    {
        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator);

        $response = $this->get('/audit-logs');
        $response->assertStatus(403);
    }

    public function test_auditor_can_view_audit_logs(): void
    {
        $auditor = User::where('username', 'auditor')->first();
        $this->actingAs($auditor);

        $response = $this->get('/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('Riwayat Aktivitas');
    }

    public function test_rbac_operator_cannot_access_user_management(): void
    {
        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator);

        $response = $this->get('/users');
        $response->assertStatus(403);
    }

    public function test_rbac_superadmin_can_access_user_management(): void
    {
        $admin = User::where('username', 'admin_rdk')->first();
        $this->actingAs($admin);

        $response = $this->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Daftar Pengguna');
    }

    public function test_operator_can_rename_device_marketing_model_name(): void
    {
        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator);

        $device = Device::first();

        $response = $this->post(route('devices.rename-model', $device->id), [
            'marketing_name' => 'Vivo Y29 Special Edition',
            'apply_to_all_same_model' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'marketing_name' => 'Vivo Y29 Special Edition',
        ]);
    }

    public function test_device_android_version_resolves_and_displays_correctly(): void
    {
        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator);

        $device = Device::first();
        $this->assertNotNull($device->android_version);
        $this->assertNotEquals('N/A', $device->android_version);

        // Verify device detail page displays the real Android version
        $response = $this->get(route('devices.show', $device->id));
        $response->assertStatus(200);
        $response->assertSee('Android ' . $device->android_version);
    }

    public function test_device_imei_and_api_level_mapping(): void
    {
        $this->assertEquals('16', Device::mapApiLevelToVersion(36));
        $this->assertEquals('15', Device::mapApiLevelToVersion(35));
        $this->assertEquals('14', Device::mapApiLevelToVersion(34));
        $this->assertEquals('13', Device::mapApiLevelToVersion(33));

        $device = new Device([
            'network_info' => ['imei' => '861234567890123'],
        ]);
        $this->assertEquals('861234567890123', $device->imei);
    }

    public function test_device_management_mode_accessors_and_view(): void
    {
        $deviceOwner = new Device([
            'raw_device_data' => ['managementMode' => 'DEVICE_OWNER', 'ownership' => 'COMPANY_OWNED'],
        ]);
        $this->assertFalse($deviceOwner->is_work_profile);
        $this->assertEquals('Fully Managed (Perusahaan)', $deviceOwner->management_mode_label);

        $profileOwner = new Device([
            'raw_device_data' => ['managementMode' => 'PROFILE_OWNER', 'ownership' => 'PERSONALLY_OWNED'],
        ]);
        $this->assertTrue($profileOwner->is_work_profile);
        $this->assertEquals('Work Profile (Pribadi)', $profileOwner->management_mode_label);

        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator);

        $response = $this->get(route('devices.index'));
        $response->assertStatus(200);
        $response->assertSee('Work Profile');
        $response->assertSee('Fully Managed');
    }
}
