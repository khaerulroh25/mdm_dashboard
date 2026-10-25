<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\App;
use App\Models\PolicyApplication;
use App\Models\AuditLog;
use App\Services\AndroidManagementService;

class PolicyController extends Controller
{
    protected AndroidManagementService $amapi;

    public function __construct(AndroidManagementService $amapi)
    {
        $this->amapi = $amapi;
    }

    /**
     * Display a listing of enterprise policies.
     */
    public function index()
    {
        $policies = Policy::select([
            'id', 'policy_id', 'name', 'description', 'camera_disabled',
            'factory_reset_disabled', 'wifi_config_disabled', 'install_apps_disabled',
            'allow_untrusted_apps', 'safe_boot_disabled',
            'kiosk_status_bar_disabled', 'kiosk_settings_blocked', 'kiosk_navigation_disabled',
            'screen_capture_disabled', 'keyguard_disabled',
            'location_mode', 'play_store_mode',
            'created_at', 'updated_at'
        ])
        ->where('policy_id', '!=', 'Lunas')
        ->withCount('devices')->latest()->get();

        return view('policies.index', compact('policies'));
    }

    /**
     * Show the form for creating a new policy.
     */
    public function create()
    {
        return view('policies.create');
    }

    /**
     * Store a newly created policy in Google AMAPI and local database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'policy_id'             => ['required', 'string', 'alpha_dash', 'unique:policies,policy_id', 'max:50'],
            'name'                  => ['required', 'string', 'max:150'],
            'description'           => ['nullable', 'string'],
            'camera_disabled'       => ['nullable', 'boolean'],
            'wifi_config_disabled'  => ['nullable', 'boolean'],
            'factory_reset_disabled'=> ['nullable', 'boolean'],
            'install_apps_disabled' => ['nullable', 'boolean'],
            'allow_untrusted_apps'  => ['nullable', 'boolean'],
            'safe_boot_disabled'    => ['nullable', 'boolean'],
            'kiosk_status_bar_disabled' => ['nullable', 'boolean'],
            'kiosk_settings_blocked'    => ['nullable', 'boolean'],
            'kiosk_navigation_disabled' => ['nullable', 'boolean'],
            'screen_capture_disabled'   => ['nullable', 'boolean'],
            'keyguard_disabled'         => ['nullable', 'boolean'],
            'location_mode'         => ['nullable', 'string', 'in:HIGH_ACCURACY,SENSORS_ONLY,BATTERY_SAVING,OFF'],
            'play_store_mode'       => ['nullable', 'string', 'in:WHITELIST,BLACKLIST'],
        ]);

        $playStoreMode = $request->input('play_store_mode', 'WHITELIST');
        $allowUntrusted = $request->boolean('allow_untrusted_apps');
        $kioskStatusBar = $request->boolean('kiosk_status_bar_disabled');
        $kioskSettings  = $request->boolean('kiosk_settings_blocked');
        $kioskNav       = $request->boolean('kiosk_navigation_disabled');
        $screenCapture  = $request->boolean('screen_capture_disabled');
        $keyguard       = $request->boolean('keyguard_disabled');

        $kioskCustomization = null;
        if ($kioskStatusBar || $kioskSettings || $kioskNav) {
            $kioskCustomization = [
                'statusBar'            => $kioskStatusBar ? 'NOTIFICATIONS_AND_SYSTEM_INFO_DISABLED' : 'STATUS_BAR_UNSPECIFIED',
                'deviceSettings'       => $kioskSettings ? 'SETTINGS_ACCESS_BLOCKED' : 'DEVICE_SETTINGS_UNSPECIFIED',
                'systemNavigation'     => $kioskNav ? 'NAVIGATION_DISABLED' : 'SYSTEM_NAVIGATION_UNSPECIFIED',
                'systemErrorWarnings'  => 'ERROR_AND_WARNINGS_MUTED',
            ];
        }

        $frpEmailsRaw = $request->input('frp_admin_emails');
        $frpEmails = [];
        if (!empty($frpEmailsRaw)) {
            $frpEmails = is_array($frpEmailsRaw) ? $frpEmailsRaw : array_filter(array_map('trim', explode(',', $frpEmailsRaw)));
        }
        if (empty($frpEmails)) {
            $frpEmails = ['devicekontrol450@gmail.com'];
        }

        $policyData = [
            'cameraDisabled'       => $request->boolean('camera_disabled'),
            'wifiConfigDisabled'   => $request->boolean('wifi_config_disabled'),
            'factoryResetDisabled' => $request->boolean('factory_reset_disabled'),
            'frpAdminEmails'       => $frpEmails,
            'installAppsDisabled'  => $request->boolean('install_apps_disabled'),
            'safeBootDisabled'     => $request->boolean('safe_boot_disabled'),
            'screenCaptureDisabled'=> $screenCapture,
            'keyguardDisabled'     => $keyguard,
            'locationMode'         => $request->input('location_mode') ?: null,
            'playStoreMode'        => $playStoreMode,
            'modifyAccountsDisabled' => $playStoreMode === 'BLACKLIST' ? false : true,
            'advancedSecurityOverrides' => [
                'untrustedAppsPolicy' => $allowUntrusted ? 'ALLOW_INSTALL_DEVICE_WIDE' : 'DISALLOW_INSTALL',
            ],
            'statusReportingSettings' => [
                'softwareInfoEnabled' => true,
                'networkInfoEnabled'  => true,
                'hardwareStatusEnabled' => true,
                'deviceSettingsEnabled' => true,
                'memoryInfoEnabled'   => true,
                'powerManagementEventsEnabled' => true,
            ],
        ];

        if ($kioskCustomization) {
            $policyData['kioskCustomization'] = $kioskCustomization;
        }

        try {
            $this->amapi->createOrPatchPolicy($validated['policy_id'], $policyData);

            $policy = Policy::create([
                'policy_id'             => $validated['policy_id'],
                'name'                  => $validated['name'],
                'description'           => $validated['description'],
                'camera_disabled'       => $request->boolean('camera_disabled'),
                'wifi_config_disabled'  => $request->boolean('wifi_config_disabled'),
                'factory_reset_disabled'=> $request->boolean('factory_reset_disabled'),
                'install_apps_disabled' => $request->boolean('install_apps_disabled'),
                'allow_untrusted_apps'  => $allowUntrusted,
                'safe_boot_disabled'    => $request->boolean('safe_boot_disabled'),
                'kiosk_status_bar_disabled' => $kioskStatusBar,
                'kiosk_settings_blocked'    => $kioskSettings,
                'kiosk_navigation_disabled' => $kioskNav,
                'screen_capture_disabled'   => $screenCapture,
                'keyguard_disabled'         => $keyguard,
                'location_mode'         => $request->input('location_mode') ?: null,
                'play_store_mode'       => $playStoreMode,
                'raw_policy_json'       => $policyData,
            ]);

            AuditLog::log('CREATE_POLICY', 'Policy', $policy->policy_id, $policyData);

            return redirect()->route('policies.index')
                ->with('success', "Policy <strong>{$policy->name}</strong> berhasil dibuat dan diterapkan ke Google AMAPI.");

        } catch (Exception $e) {
            return back()->withInput()->with('error', "Gagal membuat policy di AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified policy.
     */
    public function edit($id)
    {
        $policy = Policy::with('applications')->findOrFail($id);
        $allApps = App::orderBy('app_name')->take(100)->get();

        return view('policies.edit', compact('policy', 'allApps'));
    }

    /**
     * Update the specified policy in Google AMAPI and local database.
     */
    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'description'           => ['nullable', 'string'],
            'camera_disabled'       => ['nullable', 'boolean'],
            'wifi_config_disabled'  => ['nullable', 'boolean'],
            'factory_reset_disabled'=> ['nullable', 'boolean'],
            'install_apps_disabled' => ['nullable', 'boolean'],
            'allow_untrusted_apps'  => ['nullable', 'boolean'],
            'safe_boot_disabled'    => ['nullable', 'boolean'],
            'kiosk_status_bar_disabled' => ['nullable', 'boolean'],
            'kiosk_settings_blocked'    => ['nullable', 'boolean'],
            'kiosk_navigation_disabled' => ['nullable', 'boolean'],
            'screen_capture_disabled'   => ['nullable', 'boolean'],
            'keyguard_disabled'         => ['nullable', 'boolean'],
            'location_mode'         => ['nullable', 'string', 'in:HIGH_ACCURACY,SENSORS_ONLY,BATTERY_SAVING,OFF'],
            'play_store_mode'       => ['nullable', 'string', 'in:WHITELIST,BLACKLIST'],
        ]);

        $playStoreMode  = $request->input('play_store_mode', 'WHITELIST');
        $allowUntrusted = $request->boolean('allow_untrusted_apps');
        $kioskStatusBar = $request->boolean('kiosk_status_bar_disabled');
        $kioskSettings  = $request->boolean('kiosk_settings_blocked');
        $kioskNav       = $request->boolean('kiosk_navigation_disabled');
        $screenCapture  = $request->boolean('screen_capture_disabled');
        $keyguard       = $request->boolean('keyguard_disabled');

        $kioskCustomization = null;
        if ($kioskStatusBar || $kioskSettings || $kioskNav) {
            $kioskCustomization = [
                'statusBar'            => $kioskStatusBar ? 'NOTIFICATIONS_AND_SYSTEM_INFO_DISABLED' : 'STATUS_BAR_UNSPECIFIED',
                'deviceSettings'       => $kioskSettings ? 'SETTINGS_ACCESS_BLOCKED' : 'DEVICE_SETTINGS_UNSPECIFIED',
                'systemNavigation'     => $kioskNav ? 'NAVIGATION_DISABLED' : 'SYSTEM_NAVIGATION_UNSPECIFIED',
                'systemErrorWarnings'  => 'ERROR_AND_WARNINGS_MUTED',
            ];
        }

        $frpEmailsRaw = $request->input('frp_admin_emails');
        $frpEmails = [];
        if (!empty($frpEmailsRaw)) {
            $frpEmails = is_array($frpEmailsRaw) ? $frpEmailsRaw : array_filter(array_map('trim', explode(',', $frpEmailsRaw)));
        }
        if (empty($frpEmails)) {
            $frpEmails = ['devicekontrol450@gmail.com'];
        }

        $policyData = [
            'cameraDisabled'       => $request->boolean('camera_disabled'),
            'wifiConfigDisabled'   => $request->boolean('wifi_config_disabled'),
            'factoryResetDisabled' => $request->boolean('factory_reset_disabled'),
            'frpAdminEmails'       => $frpEmails,
            'installAppsDisabled'  => $request->boolean('install_apps_disabled'),
            'safeBootDisabled'     => $request->boolean('safe_boot_disabled'),
            'screenCaptureDisabled'=> $screenCapture,
            'keyguardDisabled'     => $keyguard,
            'locationMode'         => $request->input('location_mode') ?: null,
            'playStoreMode'        => $playStoreMode,
            'modifyAccountsDisabled' => $playStoreMode === 'BLACKLIST' ? false : true,
            'advancedSecurityOverrides' => [
                'untrustedAppsPolicy' => $allowUntrusted ? 'ALLOW_INSTALL_DEVICE_WIDE' : 'DISALLOW_INSTALL',
            ],
            'statusReportingSettings' => [
                'softwareInfoEnabled' => true,
                'networkInfoEnabled'  => true,
                'hardwareStatusEnabled' => true,
                'deviceSettingsEnabled' => true,
                'memoryInfoEnabled'   => true,
                'powerManagementEventsEnabled' => true,
            ],
        ];

        if ($kioskCustomization) {
            $policyData['kioskCustomization'] = $kioskCustomization;
        }

        try {
            $this->amapi->createOrPatchPolicy($policy->policy_id, $policyData);

            $policy->update([
                'name'                  => $validated['name'],
                'description'           => $validated['description'],
                'camera_disabled'       => $request->boolean('camera_disabled'),
                'wifi_config_disabled'  => $request->boolean('wifi_config_disabled'),
                'factory_reset_disabled'=> $request->boolean('factory_reset_disabled'),
                'install_apps_disabled' => $request->boolean('install_apps_disabled'),
                'allow_untrusted_apps'  => $allowUntrusted,
                'safe_boot_disabled'    => $request->boolean('safe_boot_disabled'),
                'kiosk_status_bar_disabled' => $kioskStatusBar,
                'kiosk_settings_blocked'    => $kioskSettings,
                'kiosk_navigation_disabled' => $kioskNav,
                'screen_capture_disabled'   => $screenCapture,
                'keyguard_disabled'         => $keyguard,
                'location_mode'         => $request->input('location_mode') ?: null,
                'play_store_mode'       => $playStoreMode,
                'raw_policy_json'       => array_merge($policy->raw_policy_json ?? [], $policyData),
            ]);

            AuditLog::log('UPDATE_POLICY', 'Policy', $policy->policy_id, $policyData);

            return back()->with('success', "Konfigurasi policy <strong>{$policy->name}</strong> berhasil diperbarui.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal memperbarui policy di AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Remove the specified policy from Google AMAPI and database.
     */
    public function destroy($id)
    {
        $policy = Policy::withCount('devices')->findOrFail($id);

        if ($policy->devices_count > 0) {
            return back()->with('error', "Policy ini tidak dapat dihapus karena masih digunakan oleh {$policy->devices_count} perangkat aktif.");
        }

        try {
            $this->amapi->deletePolicy($policy->policy_id);
            $policyId = $policy->policy_id;
            $policy->delete();

            AuditLog::log('DELETE_POLICY', 'Policy', $policyId);

            return redirect()->route('policies.index')
                ->with('success', "Policy <strong>{$policyId}</strong> berhasil dihapus.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal menghapus policy di AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Add or update an app in the policy application list.
     */
    public function addApp(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $validated = $request->validate([
            'package_name' => ['required', 'string'],
            'install_type' => ['required', 'string', 'in:FORCE_INSTALLED,AVAILABLE,KIOSK,BLOCKED'],
        ]);

        try {
            $this->amapi->addAppToPolicy($policy->policy_id, $validated['package_name'], $validated['install_type']);

            // Find or create local app
            $app = App::firstOrCreate(
                ['package_name' => $validated['package_name']],
                ['app_name' => $validated['package_name']]
            );

            // Sync pivot
            PolicyApplication::updateOrCreate(
                ['policy_id' => $policy->id, 'app_id' => $app->id],
                ['install_type' => $validated['install_type']]
            );

            AuditLog::log('POLICY_ADD_APP', 'Policy', $policy->policy_id, [
                'package_name' => $validated['package_name'],
                'install_type' => $validated['install_type'],
            ]);

            return back()->with('success', "Aplikasi <strong>{$validated['package_name']}</strong> ({$validated['install_type']}) berhasil ditambahkan ke policy.");

        } catch (Exception $e) {
            return back()->with('error', "Gagal menambahkan aplikasi ke policy: " . $e->getMessage());
        }
    }
}
