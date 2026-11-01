<?php

namespace App\Services;

use App\Models\EnrollmentToken;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StandaloneEnrollmentService
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Create an enrollment token and return provisioning bundle + QR code.
     */
    public function createEnrollment(
        string $policyId = 'Mode_kiosk',
        ?int $branchId = null,
        ?array $ownerData = null,
        int $ttlSeconds = 604800, // 7 days default
        bool $isOneTime = true,
        ?string $wifiSsid = null,
        ?string $wifiPassword = null
    ): array {
        $tokenString = EnrollmentToken::generateUniqueToken();
        $expiresAt = now()->addSeconds($ttlSeconds);

        $enrollmentToken = EnrollmentToken::create([
            'token'         => $tokenString,
            'policy_id'     => $policyId,
            'branch_id'     => $branchId,
            'owner_data'    => $ownerData,
            'is_one_time'   => $isOneTime,
            'is_used'       => false,
            'expires_at'    => $expiresAt,
            'created_by'    => Auth::id(),
        ]);

        $baseUrl = config('app.url', 'http://127.0.0.1:8000');
        // When testing with Android Emulator, 10.0.2.2 connects to host computer's localhost
        $apkDownloadUrl = env('DPC_APK_URL', $baseUrl . '/downloads/dpc-debug.apk');
        $apkChecksum = env('DPC_APK_CHECKSUM', '');
        $dpcReceiverComponent = env('DPC_ADMIN_COMPONENT', 'com.amanin.mdm.dpc/.receiver.MdmAdminReceiver');
        $apiUrl = env('DPC_API_URL', $baseUrl . '/api/v1/dpc');

        // Android Enterprise Standard Provisioning JSON
        $provisioningPayload = [
            'android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION' => $apkDownloadUrl,
            'android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME'           => $dpcReceiverComponent,
            'android.app.extra.PROVISIONING_LEAVE_ALL_SYSTEM_APPS_ENABLED'          => true,
            'android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE' => [
                'server_url'       => $apiUrl,
                'enrollment_token' => $tokenString,
                'policy_id'        => $policyId,
            ],
        ];

        if (!empty($apkChecksum)) {
            $provisioningPayload['android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_CHECKSUM'] = $apkChecksum;
        }

        if (!empty($wifiSsid)) {
            $provisioningPayload['android.app.extra.PROVISIONING_WIFI_SSID'] = $wifiSsid;
            if (!empty($wifiPassword)) {
                $provisioningPayload['android.app.extra.PROVISIONING_WIFI_PASSWORD'] = $wifiPassword;
                $provisioningPayload['android.app.extra.PROVISIONING_WIFI_SECURITY_TYPE'] = 'WPA';
            } else {
                $provisioningPayload['android.app.extra.PROVISIONING_WIFI_SECURITY_TYPE'] = 'NONE';
            }
        }

        $jsonPayload = json_encode($provisioningPayload, JSON_UNESCAPED_SLASHES);
        $base64Qr = $this->qrCodeService->generateBase64Qr($jsonPayload, 320, 10);

        return [
            'token'                => $enrollmentToken,
            'token_string'         => $tokenString,
            'qr_payload'           => $jsonPayload,
            'base64_qr'            => $base64Qr,
            'expires_at'           => $expiresAt,
            'provisioning_payload' => $provisioningPayload,
        ];
    }
}
