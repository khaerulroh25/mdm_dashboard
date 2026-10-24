<?php

namespace App\Services;

use Exception;
use Google\Client;
use Google\Service\AndroidManagement;
use Google\Service\AndroidManagement\Command;
use Google\Service\AndroidManagement\Policy;
use Google\Service\AndroidManagement\EnrollmentToken;
use Google\Service\AndroidManagement\ApplicationPolicy;
use Google\Service\AndroidManagement\WebToken;
use Illuminate\Support\Facades\Log;

class AndroidManagementService
{
    protected AndroidManagement $service;
    protected string $enterpriseId;

    public function __construct()
    {
        $credentialsPath = config('amapi.credentials_path');
        $this->enterpriseId = config('amapi.enterprise_id');

        if (!file_exists($credentialsPath)) {
            throw new Exception("AMAPI Service Account credentials file not found at: {$credentialsPath}");
        }

        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope(AndroidManagement::ANDROIDMANAGEMENT);

        $this->service = new AndroidManagement($client);
    }

    /**
     * Get the raw Google AndroidManagement service instance.
     */
    public function getService(): AndroidManagement
    {
        return $this->service;
    }

    public function getEnterpriseId(): string
    {
        return $this->enterpriseId;
    }

    /**
     * Normalize device resource name.
     */
    public function formatDeviceName(string $deviceId): string
    {
        if (str_starts_with($deviceId, 'enterprises/')) {
            return $deviceId;
        }
        return "{$this->enterpriseId}/devices/{$deviceId}";
    }

    /**
     * Normalize policy resource name.
     */
    public function formatPolicyName(string $policyId): string
    {
        if (str_starts_with($policyId, 'enterprises/')) {
            return $policyId;
        }
        return "{$this->enterpriseId}/policies/{$policyId}";
    }

    // =========================================================================
    // DEVICE FLEET MANAGEMENT
    // =========================================================================

    /**
     * List all devices under the enterprise.
     */
    public function listDevices(int $pageSize = 100, ?string $pageToken = null)
    {
        $optParams = ['pageSize' => $pageSize];
        if ($pageToken) {
            $optParams['pageToken'] = $pageToken;
        }

        return $this->service->enterprises_devices->listEnterprisesDevices(
            $this->enterpriseId,
            $optParams
        );
    }

    /**
     * Get detailed information for a single device.
     */
    public function getDevice(string $deviceId)
    {
        $deviceName = $this->formatDeviceName($deviceId);
        return $this->service->enterprises_devices->get($deviceName);
    }

    /**
     * Update the assigned policy on a specific device.
     */
    public function patchDevicePolicy(string $deviceId, string $policyId)
    {
        $deviceName = $this->formatDeviceName($deviceId);
        $policyName = $this->formatPolicyName($policyId);

        $device = $this->getDevice($deviceName);
        $device->setPolicyName($policyName);

        return $this->service->enterprises_devices->patch($deviceName, $device);
    }

    /**
     * Unenroll / delete a device from management.
     */
    public function unenrollDevice(string $deviceId): bool
    {
        try {
            $deviceName = $this->formatDeviceName($deviceId);
            $this->service->enterprises_devices->delete($deviceName);
            return true;
        } catch (Exception $e) {
            Log::error("AMAPI unenrollDevice error for {$deviceId}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // REMOTE COMMAND EXECUTION
    // =========================================================================

    /**
     * Issue remote command to a device.
     * Supported types: LOCK, REBOOT, RESET_PASSWORD, START_LOST_MODE, STOP_LOST_MODE
     */
    public function issueCommand(string $deviceId, string $type, array $params = []): Command
    {
        $deviceName = $this->formatDeviceName($deviceId);
        $commandData = ['type' => strtoupper($type)];

        switch ($commandData['type']) {
            case 'START_LOST_MODE':
                $lostParams = [];
                if (!empty($params['lostMessage'])) {
                    $lostParams['lostMessage'] = [
                        'defaultMessage' => $params['lostMessage']
                    ];
                }
                if (!empty($params['lostPhoneNumber'])) {
                    $lostParams['lostPhoneNumber'] = [
                        'defaultMessage' => $params['lostPhoneNumber']
                    ];
                }
                if (!empty($params['lostEmailAddress'])) {
                    $lostParams['lostEmailAddress'] = $params['lostEmailAddress'];
                }
                if (!empty($params['lostStreetAddress'])) {
                    $lostParams['lostStreetAddress'] = [
                        'defaultMessage' => $params['lostStreetAddress']
                    ];
                }
                $commandData['startLostModeParams'] = $lostParams;
                break;

            case 'STOP_LOST_MODE':
                $commandData['stopLostModeParams'] = new \stdClass();
                break;

            case 'RESET_PASSWORD':
                $commandData['newPassword'] = $params['newPassword'] ?? '';
                $commandData['resetPasswordFlags'] = $params['resetPasswordFlags'] ?? ['LOCK_NOW', 'REQUIRE_ENTRY'];
                break;

            case 'LOCK':
            case 'REBOOT':
                // No extra parameters required
                break;

            default:
                throw new Exception("Unsupported command type: {$type}");
        }

        $command = new Command($commandData);
        return $this->service->enterprises_devices->issueCommand($deviceName, $command);
    }

    // =========================================================================
    // POLICY MANAGEMENT
    // =========================================================================

    /**
     * List all policies in enterprise.
     */
    public function listPolicies(int $pageSize = 100)
    {
        return $this->service->enterprises_policies->listEnterprisesPolicies(
            $this->enterpriseId,
            ['pageSize' => $pageSize]
        );
    }

    /**
     * Get single policy definition.
     */
    public function getPolicy(string $policyId): Policy
    {
        $policyName = $this->formatPolicyName($policyId);
        return $this->service->enterprises_policies->get($policyName);
    }

    /**
     * Default status reporting settings so IMEI, battery, and hardware telemetry are always collected.
     */
    public function defaultStatusReportingSettings(): array
    {
        return [
            'softwareInfoEnabled' => true,
            'networkInfoEnabled' => true,
            'hardwareStatusEnabled' => true,
            'deviceSettingsEnabled' => true,
            'memoryInfoEnabled' => true,
            'powerManagementEventsEnabled' => true,
            'displayInfoEnabled' => true,
        ];
    }

    /**
     * Enable statusReportingSettings on an existing policy using updateMask
     * so no other policy fields (kiosk apps, playStoreMode, security overrides) are modified.
     */
    public function enableStatusReportingOnPolicy(string $policyId): Policy
    {
        $policyName = $this->formatPolicyName($policyId);
        $policy = new Policy([
            'statusReportingSettings' => $this->defaultStatusReportingSettings(),
        ]);

        return $this->service->enterprises_policies->patch($policyName, $policy, [
            'updateMask' => 'statusReportingSettings',
        ]);
    }

    /**
     * Create or patch an existing policy with array config while preserving existing applications
     * and ensuring statusReportingSettings is always active.
     */
    public function createOrPatchPolicy(string $policyId, array $policyData): Policy
    {
        $policyName = $this->formatPolicyName($policyId);

        $policyData['statusReportingSettings'] = array_merge(
            $this->defaultStatusReportingSettings(),
            $policyData['statusReportingSettings'] ?? []
        );

        if (!array_key_exists('applications', $policyData)) {
            try {
                $existing = $this->getPolicy($policyId);
                $existingApps = $existing->getApplications();
                if (!empty($existingApps)) {
                    $policyData['applications'] = json_decode(json_encode($existingApps), true);
                }
            } catch (Exception $e) {
                // Policy does not exist yet on AMAPI; safe to proceed
            }
        }

        $policy = new Policy($policyData);

        return $this->service->enterprises_policies->patch($policyName, $policy);
    }

    /**
     * Delete policy from AMAPI.
     */
    public function deletePolicy(string $policyId): bool
    {
        try {
            $policyName = $this->formatPolicyName($policyId);
            $this->service->enterprises_policies->delete($policyName);
            return true;
        } catch (Exception $e) {
            Log::error("AMAPI deletePolicy error for {$policyId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add or update an app in a policy's application whitelist.
     */
    public function addAppToPolicy(string $policyId, string $packageName, string $installType = 'AVAILABLE'): Policy
    {
        $policy = $this->getPolicy($policyId);
        $applications = $policy->getApplications() ?? [];

        $validInstallTypes = ['FORCE_INSTALLED', 'AVAILABLE', 'KIOSK', 'BLOCKED'];
        $installType = strtoupper($installType);
        if (!in_array($installType, $validInstallTypes)) {
            $installType = 'AVAILABLE';
        }

        $updated = false;
        $newApplications = [];
        foreach ($applications as $app) {
            $pkg = is_array($app) ? ($app['packageName'] ?? '') : $app->getPackageName();
            if ($pkg === $packageName) {
                $newApplications[] = [
                    'packageName' => $packageName,
                    'installType' => $installType,
                    'defaultPermissionPolicy' => 'GRANT',
                ];
                $updated = true;
            } else {
                $newApplications[] = is_array($app) ? $app : json_decode(json_encode($app), true);
            }
        }

        if (!$updated) {
            $newApplications[] = [
                'packageName' => $packageName,
                'installType' => $installType,
                'defaultPermissionPolicy' => 'GRANT',
            ];
        }

        $policy->setApplications($newApplications);
        $policyName = $this->formatPolicyName($policyId);
        return $this->service->enterprises_policies->patch($policyName, $policy, [
            'updateMask' => 'applications',
        ]);
    }

    // =========================================================================
    // ENROLLMENT TOKENS
    // =========================================================================

    /**
     * Create an enrollment token for onboarding devices.
     */
    public function createEnrollmentToken(
        ?string $policyId = null,
        int $durationSeconds = 604800,
        ?string $additionalData = null,
        bool $oneTimeOnly = false
    ): EnrollmentToken {
        $policyName = $policyId ? $this->formatPolicyName($policyId) : $this->formatPolicyName('Mode_kiosk');

        $payload = [
            'policyName' => $policyName,
            'duration' => "{$durationSeconds}s",
        ];

        if (!empty($additionalData)) {
            $payload['additionalData'] = $additionalData;
        }

        if ($oneTimeOnly) {
            $payload['oneTimeOnly'] = true;
        }

        $tokenConfig = new EnrollmentToken($payload);

        return $this->service->enterprises_enrollmentTokens->create(
            $this->enterpriseId,
            $tokenConfig
        );
    }

    // =========================================================================
    // MANAGED GOOGLE PLAY & APP METADATA (OFFICIAL)
    // =========================================================================

    /**
     * Create an official Google AMAPI Web Token for embedding Managed Google Play iframe.
     * Note: $parentFrameUrl must be an HTTPS URL.
     */
    public function createWebToken(string $parentFrameUrl, array $features = ['PLAY_SEARCH', 'PRIVATE_APPS', 'WEB_APPS', 'STORE_BUILDER']): WebToken
    {
        $token = new WebToken();
        $token->setParentFrameUrl($parentFrameUrl);
        $token->setEnabledFeatures($features);
        $token->setPermissions(['APPROVE_APPS']);

        return $this->service->enterprises_webTokens->create(
            $this->enterpriseId,
            $token
        );
    }

    /**
     * Fetch official application metadata directly from Google Android Management API.
     */
    public function getApplication(string $packageName)
    {
        try {
            $name = "{$this->enterpriseId}/applications/{$packageName}";
            return $this->service->enterprises_applications->get($name);
        } catch (\Throwable $e) {
            Log::warning("AMAPI getApplication failed for {$packageName}: " . $e->getMessage());
            return null;
        }
    }
}
