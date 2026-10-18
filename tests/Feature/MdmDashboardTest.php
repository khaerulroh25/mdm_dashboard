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
}
