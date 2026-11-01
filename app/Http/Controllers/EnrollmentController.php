<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\Branch;
use App\Models\AuditLog;
use App\Models\EnrollmentToken;
use App\Services\QrCodeService;
use App\Services\StandaloneEnrollmentService;

class EnrollmentController extends Controller
{
    protected QrCodeService $qrService;
    protected StandaloneEnrollmentService $standaloneService;

    public function __construct(
        QrCodeService $qrService,
        StandaloneEnrollmentService $standaloneService
    ) {
        $this->qrService = $qrService;
        $this->standaloneService = $standaloneService;
    }

    /**
     * Show enrollment policy selection form.
     */
    public function index()
    {
        $user = auth()->user();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $selectedBranch = null;
        $defaultPolicy = 'Mode_kiosk';

        if ($user->isBranchScoped() && $user->branch) {
            $selectedBranch = $user->branch;
            $defaultPolicy = $selectedBranch->getEffectivePolicyId();
        }

        $policies = Policy::where('policy_id', '!=', 'Lunas')->get();

        // Fetch recent standalone tokens for tracking history
        $recentTokens = EnrollmentToken::with(['branch', 'creator', 'usedByDevice'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('enrollment.index', compact(
            'policies',
            'branches',
            'selectedBranch',
            'defaultPolicy',
            'recentTokens'
        ));
    }

    /**
     * Generate an enrollment token and QR code for onboarding devices.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'policy_id'     => ['nullable', 'string'],
            'branch_id'     => ['nullable', 'exists:branches,id'],
            'owner_name'    => ['nullable', 'string', 'max:150'],
            'id_anggota'    => ['nullable', 'string', 'max:100', 'required_with:owner_name'],
            'owner_phone'   => ['nullable', 'string', 'max:50'],
            'owner_notes'   => ['nullable', 'string', 'max:250'],
            'wifi_ssid'     => ['nullable', 'string', 'max:100'],
            'wifi_password' => ['nullable', 'string', 'max:100'],
        ], [
            'id_anggota.required_with' => 'ID Pengguna / Nomor Identitas wajib diisi apabila Nama Calon Pemilik diisi.',
        ]);

        $user = auth()->user();
        $branchId = $user->isBranchScoped() ? $user->branch_id : ($validated['branch_id'] ?? null);
        $branch = $branchId ? Branch::find($branchId) : null;

        $policyId = !empty($validated['policy_id'])
            ? $validated['policy_id']
            : ($branch ? $branch->getEffectivePolicyId() : 'Mode_kiosk');

        $ownerPreview = null;
        if (!empty($validated['owner_name'])) {
            $ownerPreview = [
                'nama'       => trim($validated['owner_name']),
                'id_anggota' => trim($validated['id_anggota']),
                'telepon'    => !empty($validated['owner_phone']) ? trim($validated['owner_phone']) : null,
                'catatan'    => !empty($validated['owner_notes']) ? trim($validated['owner_notes']) : null,
            ];
        }

        $oneTimeOnly = ($ownerPreview !== null);
        $engine = 'standalone';

        try {
            $enrollmentData = $this->standaloneService->createEnrollment(
                policyId: $policyId,
                branchId: $branch?->id,
                ownerData: $ownerPreview,
                ttlSeconds: 604800, // 7 days
                isOneTime: $oneTimeOnly,
                wifiSsid: $validated['wifi_ssid'] ?? null,
                wifiPassword: $validated['wifi_password'] ?? null
            );

            $token = $enrollmentData['token'];
            $tokenValue = $enrollmentData['token_string'];
            $base64Image = $enrollmentData['base64_qr'];
            $qrPayload = $enrollmentData['qr_payload'];
            $provisioningPayload = $enrollmentData['provisioning_payload'];
            $expiration = $enrollmentData['expires_at'];

            AuditLog::log('GENERATE_STANDALONE_ENROLLMENT_TOKEN', 'EnrollmentToken', (string) $token->id, [
                'token'        => $tokenValue,
                'policy_id'    => $policyId,
                'branch_id'    => $branch?->id,
                'branch_code'  => $branch?->code,
                'owner_preset' => $ownerPreview,
                'one_time'     => $oneTimeOnly,
            ]);

            $policy = Policy::where('policy_id', $policyId)->first();
            $targetEnterpriseRecord = null;

            return view('enrollment.show', compact(
                'engine',
                'base64Image',
                'token',
                'tokenValue',
                'expiration',
                'policy',
                'branch',
                'ownerPreview',
                'oneTimeOnly',
                'qrPayload',
                'provisioningPayload',
                'targetEnterpriseRecord'
            ));
        } catch (Exception $e) {
            return back()->with('error', "Gagal membuat enrollment token: " . $e->getMessage());
        }
    }

    /**
     * Show/reprint an existing enrollment token.
     */
    public function showToken(int $id)
    {
        $token = EnrollmentToken::with(['branch', 'usedByDevice'])->findOrFail($id);

        $baseUrl = config('app.url', 'http://127.0.0.1:8000');
        $apkDownloadUrl = env('DPC_APK_URL', $baseUrl . '/downloads/dpc-debug.apk');
        $dpcReceiverComponent = env('DPC_ADMIN_COMPONENT', 'com.amanin.mdm.dpc/.receiver.MdmAdminReceiver');
        $apiUrl = env('DPC_API_URL', $baseUrl . '/api/v1/dpc');

        $provisioningPayload = [
            'android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION' => $apkDownloadUrl,
            'android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME'           => $dpcReceiverComponent,
            'android.app.extra.PROVISIONING_LEAVE_ALL_SYSTEM_APPS_ENABLED'          => true,
            'android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE' => [
                'server_url'       => $apiUrl,
                'enrollment_token' => $token->token,
                'policy_id'        => $token->policy_id,
            ],
        ];

        $qrPayload = json_encode($provisioningPayload, JSON_UNESCAPED_SLASHES);
        $base64Image = $this->qrService->generateBase64Qr($qrPayload, 320, 10);

        $engine = 'standalone';
        $tokenValue = $token->token;
        $expiration = $token->expires_at;
        $policy = Policy::where('policy_id', $token->policy_id)->first();
        $branch = $token->branch;
        $ownerPreview = $token->owner_data;
        $oneTimeOnly = $token->is_one_time;
        $targetEnterpriseRecord = null;

        return view('enrollment.show', compact(
            'engine',
            'base64Image',
            'token',
            'tokenValue',
            'expiration',
            'policy',
            'branch',
            'ownerPreview',
            'oneTimeOnly',
            'qrPayload',
            'provisioningPayload',
            'targetEnterpriseRecord'
        ));
    }

    /**
     * Revoke / cancel an active enrollment token.
     */
    public function revokeToken(int $id)
    {
        $token = EnrollmentToken::findOrFail($id);
        $token->update([
            'is_used'    => true,
            'expires_at' => now()->subMinute(),
        ]);

        AuditLog::log('REVOKE_ENROLLMENT_TOKEN', 'EnrollmentToken', (string) $token->id, [
            'token' => $token->token,
        ]);

        return back()->with('success', "Token {$token->token} berhasil dibatalkan.");
    }
}
