<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Policy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MdmDashboardTest extends TestCase
{
    use DatabaseTransactions;
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
        $response->assertSee('Amanin');
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
        $devicesResponse->assertSee('Manajemen Perangkat');

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
        $admin = User::where('username', 'superadmin')->first();
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

        $profileDevice = Device::where('state', 'ACTIVE')->first();
        if ($profileDevice) {
            $raw = $profileDevice->raw_device_data ?? [];
            $raw['managementMode'] = 'PROFILE_OWNER';
            $raw['ownership'] = 'PERSONALLY_OWNED';
            $profileDevice->update([
                'raw_device_data' => $raw,
                'enrolled_at' => now()->addHour(),
            ]);
        }

        $response = $this->get(route('devices.index'));
        $response->assertStatus(200);
        $response->assertSee('Work Profile');
        $response->assertSee('Fully Managed');
    }

    public function test_device_battery_and_online_telemetry_accessors_and_view(): void
    {
        $deviceWithBattery = new Device([
            'last_status_report' => now()->subHours(2),
            'raw_device_data' => [
                'powerManagementEvents' => [
                    ['eventType' => 'BATTERY_LEVEL_COLLECTED', 'batteryLevel' => 84.5],
                ],
            ],
        ]);

        $this->assertEquals(84.5, $deviceWithBattery->battery_level);
        $this->assertTrue($deviceWithBattery->is_online);
        $this->assertEquals('Aktif Online', $deviceWithBattery->online_status_label);

        $offlineDevice = new Device([
            'last_status_report' => now()->subDays(3),
        ]);
        $this->assertFalse($offlineDevice->is_online);

        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator);

        $device = Device::first();
        $response = $this->get(route('devices.show', $device->id));
        $response->assertStatus(200);
        $response->assertSeeText('Status Keaktifan');
        $response->assertSeeText('Status Daya & Baterai', false);
        $response->assertSeeText('Mode Lokasi & GPS', false);
    }

    public function test_policy_play_store_mode_support_and_views(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->actingAs($admin);

        $policy = Policy::where('policy_id', 'Mode_kiosk')->first();
        $this->assertEquals('BLACKLIST', $policy->play_store_mode);
        $this->assertTrue($policy->allow_untrusted_apps);

        $response = $this->get(route('policies.index'));
        $response->assertStatus(200);
        $response->assertSee('Google Play');
        $response->assertSee('Buka Penuh (Publik)');
        $response->assertSee('Allow APK');

        $response = $this->get(route('policies.edit', $policy->id));
        $response->assertStatus(200);
        $response->assertSee('Buka Penuh Google Play Publik (BLACKLIST)');
        $response->assertSee('Izinkan Instalasi APK Luar (Sideloading)');
    }

    public function test_google_play_search_view_and_tabs(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('apps.search'));
        $response->assertStatus(200);
        $response->assertSee('Managed Google Play');
        $response->assertSee('Google Play Store Enterprise');
    }

    public function test_google_play_lookup_and_store(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->actingAs($admin);

        // Store an app with auto-fetch metadata
        $response = $this->post(route('apps.store'), [
            'package_name' => 'com.google.android.youtube',
            'app_name'     => 'YouTube',
        ]);
        $response->assertRedirect();

        $app = \App\Models\App::where('package_name', 'com.google.android.youtube')->first();
        $this->assertNotNull($app);
        $this->assertEquals('YouTube', $app->app_name);
    }

    public function test_device_index_separates_active_and_deleted_tabs(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->actingAs($admin);

        // Active tab (default) should show Perangkat Aktif
        $response = $this->get(route('devices.index'));
        $response->assertStatus(200);
        $response->assertSee('Perangkat Aktif');
        $response->assertSee('Riwayat Unenroll / Dihapus');

        // Deleted tab should show unenrolled devices
        $responseDeleted = $this->get(route('devices.index', ['tab' => 'deleted']));
        $responseDeleted->assertStatus(200);
        $responseDeleted->assertSee('Riwayat Perangkat Di-unenroll / Dihapus');
    }

    public function test_dashboard_only_shows_active_devices_ordered_by_newest(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Perangkat Aktif Terbaru');

        // Verify that deleted device is NOT in recent devices list on dashboard
        $deletedDevice = Device::where('state', 'DELETED')->first();
        if ($deletedDevice) {
            $response->assertDontSee($deletedDevice->device_id);
        }
    }

    public function test_custom_user_permissions_and_superadmin_full_access(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->actingAs($admin);

        // 1. Superadmin has all permissions regardless of custom permissions column
        foreach (User::allPermissionKeys() as $permKey) {
            $this->assertTrue($admin->hasPermission($permKey));
        }

        // 1b. Verify admin_rdk is configured as branch operator with Operasional Penuh Cabang permissions
        $adminRdk = User::where('username', 'admin_rdk')->first();
        $this->assertNotNull($adminRdk);
        $this->assertEquals('operator', $adminRdk->role);
        $this->assertTrue($adminRdk->hasPermission('devices.delete'));
        $this->assertTrue($adminRdk->hasPermission('enrollment.manage'));
        $this->assertFalse($adminRdk->hasPermission('policies.manage'));
        $this->assertFalse($adminRdk->hasPermission('users.manage'));
        $this->assertFalse($adminRdk->hasPermission('audit_logs.view'));

        // 2. Superadmin creates a custom operator with only devices.view and audit_logs.view
        $response = $this->post(route('users.store'), [
            'username'              => 'custom_staff',
            'name'                  => 'Staf Khusus',
            'email'                 => 'staf@amanin.com',
            'password'              => 'secret123',
            'role'                  => 'operator',
            'permissions_submitted' => '1',
            'permissions'           => ['devices.view', 'audit_logs.view'],
        ]);
        $response->assertRedirect(route('users.index'));

        $customUser = User::where('username', 'custom_staff')->first();
        $this->assertNotNull($customUser);
        $this->assertTrue($customUser->hasPermission('devices.view'));
        $this->assertTrue($customUser->hasPermission('audit_logs.view'));
        $this->assertFalse($customUser->hasPermission('enrollment.manage'));
        $this->assertFalse($customUser->hasPermission('policies.view'));

        // 3. Verify route enforcement for the custom user
        $this->actingAs($customUser);

        $this->get(route('devices.index'))->assertStatus(200);
        $this->get(route('audit-logs.index'))->assertStatus(200);
        $this->get(route('enrollment.index'))->assertStatus(403);
        $this->get(route('policies.index'))->assertStatus(403);
    }

    public function test_master_branches_crud_and_multi_branch_device_isolation(): void
    {
        $superadmin = User::where('username', 'superadmin')->first();
        $adminRdk = User::where('username', 'admin_rdk')->first();

        // 1. Verify RDK branch exists and admin_rdk is scoped to it
        $rdkBranch = \App\Models\Branch::where('code', 'RDK')->first();
        $this->assertNotNull($rdkBranch);
        $this->assertEquals($rdkBranch->id, $adminRdk->branch_id);
        $this->assertTrue($adminRdk->isBranchScoped());
        $this->assertFalse($superadmin->isBranchScoped());

        // 2. Superadmin can access Master Cabang and create a new Branch B (with automatic branch code generation)
        $this->actingAs($superadmin);
        $this->get(route('branches.index'))->assertStatus(200)->assertSee('Cabang Rengasdengklok');

        $this->post(route('branches.store'), [
            'name'          => 'Cabang Karawang Barat',
            'contact_phone' => '0812-9999-8888',
            'address'       => 'Jl. Tuparev Karawang',
            'is_active'     => 1,
        ])->assertRedirect(route('branches.index'));

        $branchB = \App\Models\Branch::where('name', 'Cabang Karawang Barat')->first();
        $this->assertNotNull($branchB);
        $this->assertEquals('KRW-B', $branchB->code);

        // 3. Superadmin creates a branch operator for Branch B
        $this->post(route('users.store'), [
            'username'              => 'admin_cbg_b',
            'name'                  => 'Admin Cabang B',
            'password'              => 'secret123',
            'role'                  => 'operator',
            'branch_id'             => $branchB->id,
            'permissions_submitted' => '1',
            'permissions'           => ['devices.view', 'devices.command', 'devices.edit'],
        ])->assertRedirect(route('users.index'));

        $adminBranchB = User::where('username', 'admin_cbg_b')->first();
        $this->assertEquals($branchB->id, $adminBranchB->branch_id);

        // 4. Create a device assigned to Branch B and pick an existing device in RDK
        $rdkDevice = Device::where('branch_id', $rdkBranch->id)->where('state', 'ACTIVE')->first();
        $branchBDevice = Device::create([
            'branch_id'      => $branchB->id,
            'device_id'      => 'enterprises/LC041be11h/devices/branchb99999',
            'hardware_model' => 'V2434',
            'marketing_name' => 'Vivo Y29 Cabang B',
            'state'          => 'ACTIVE',
            'policy_id'      => 'default',
            'enrolled_at'    => now()->addHours(2),
        ]);

        // 5. Verify Branch RDK admin can access RDK device, but gets 403 on Branch B device
        $this->actingAs($adminRdk);
        $this->get(route('branches.index'))->assertStatus(403);
        $this->get(route('devices.show', $rdkDevice->id))->assertStatus(200);
        $this->get(route('devices.show', $branchBDevice->id))->assertStatus(403);
        $this->get(route('devices.index'))->assertStatus(200)->assertDontSee('Vivo Y29 Cabang B');

        // 6. Verify Branch B admin can access Branch B device, but gets 403 on RDK device
        $this->actingAs($adminBranchB);
        $this->get(route('devices.show', $branchBDevice->id))->assertStatus(200)->assertSee('Vivo Y29 Cabang B');
        $this->get(route('devices.show', $rdkDevice->id))->assertStatus(403);
        $this->get(route('devices.index'))->assertStatus(200)->assertSee('Vivo Y29 Cabang B');

        // 7. Superadmin can see both and transfer Branch B device to RDK
        $this->actingAs($superadmin);
        $this->post(route('devices.branch', $branchBDevice->id), [
            'branch_id' => $rdkBranch->id,
        ])->assertRedirect();

        $this->assertEquals($rdkBranch->id, $branchBDevice->fresh()->branch_id);
    }
}
