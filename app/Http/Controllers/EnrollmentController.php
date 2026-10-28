<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\Branch;
use App\Models\Enterprise;
use App\Models\AuditLog;
use App\Services\AndroidManagementService;
use App\Services\QrCodeService;

class EnrollmentController extends Controller
{
    protected AndroidManagementService $amapi;
    protected QrCodeService $qrService;

    public function __construct(AndroidManagementService $amapi, QrCodeService $qrService)
    {
        $this->amapi = $amapi;
        $this->qrService = $qrService;
    }

    /**
     * Show enrollment policy selection form.
     */
    public function index()
    {
        $user = auth()->user();
        $branches = Branch::with('enterprise')->where('is_active', true)->orderBy('name')->get();
        $enterprises = Enterprise::orderByDesc('is_active')->orderBy('name')->get();
        $activeEnterprise = Enterprise::getActiveEnterprise();

        $selectedBranch = null;
        $defaultPolicy = 'Mode_kiosk';

        if ($user->isBranchScoped() && $user->branch) {
            $selectedBranch = $user->branch->load('enterprise');
            $defaultPolicy = $selectedBranch->getEffectivePolicyId();
        }

        $policies = Policy::where('policy_id', '!=', 'Lunas')->get();

        return view('enrollment.index', compact('policies', 'branches', 'enterprises', 'selectedBranch', 'activeEnterprise', 'defaultPolicy'));
    }

    /**
     * Generate an enrollment token and QR code for onboarding devices.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'policy_id'     => ['nullable', 'string'],
            'enterprise_id' => ['nullable', 'string'],
            'branch_id'     => ['nullable', 'exists:branches,id'],
            'owner_name'    => ['nullable', 'string', 'max:150'],
            'id_anggota'    => ['nullable', 'string', 'max:100', 'required_with:owner_name'],
            'owner_phone'   => ['nullable', 'string', 'max:50'],
            'owner_notes'   => ['nullable', 'string', 'max:250'],
        ], [
            'id_anggota.required_with' => 'ID Pengguna / Nomor Identitas wajib diisi apabila Nama Calon Pemilik diisi.',
        ]);

        $user = auth()->user();
        $branchId = $user->isBranchScoped() ? $user->branch_id : ($validated['branch_id'] ?? null);
        $branch = $branchId ? Branch::with('enterprise')->find($branchId) : null;

        // Determine target enterprise ID
        $targetEnterpriseId = null;
        if ($branch) {
            $targetEnterpriseId = $branch->getEffectiveEnterpriseId();
        } elseif (!empty($validated['enterprise_id'])) {
            $targetEnterpriseId = $validated['enterprise_id'];
        }

        // Determine effective policy ID
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

        $tokenMeta = [];
        if ($branch) {
            $tokenMeta['branch_id'] = $branch->id;
            $tokenMeta['branch_code'] = $branch->code;
            $tokenMeta['branch_name'] = $branch->name;
        }
        if ($ownerPreview) {
            $tokenMeta['owner'] = $ownerPreview;
        }

        $additionalData = !empty($tokenMeta) ? json_encode($tokenMeta) : null;
        $oneTimeOnly = ($ownerPreview !== null);

        try {
            $token = $this->amapi->createEnrollmentToken(
                $policyId,
                604800,
                $additionalData,
                $oneTimeOnly,
                $targetEnterpriseId
            );

            $qrPayload = $token->getQrCode();
            $base64Image = $this->qrService->generateBase64Qr($qrPayload, 320, 10);
            $expiration = $token->getExpirationTimestamp();
            $tokenName = $token->getName();
            $tokenValue = $token->getValue();

            // Find target enterprise model for display
            $targetEnterpriseRecord = Enterprise::where('enterprise_id', $targetEnterpriseId)->first()
                ?? Enterprise::getActiveEnterprise();

            AuditLog::log('GENERATE_ENROLLMENT_TOKEN', 'Policy', $policyId, [
                'token_name'    => $tokenName,
                'enterprise_id' => $targetEnterpriseId,
                'expiration'    => $expiration,
                'branch_id'     => $branch?->id,
                'branch_code'   => $branch?->code,
                'owner_preset'  => $ownerPreview,
                'one_time'      => $oneTimeOnly,
            ]);

            $policy = Policy::where('policy_id', $policyId)->first();

            return view('enrollment.show', compact(
                'base64Image',
                'token',
                'tokenValue',
                'expiration',
                'policy',
                'branch',
                'ownerPreview',
                'oneTimeOnly',
                'qrPayload',
                'targetEnterpriseRecord'
            ));

        } catch (Exception $e) {
            return back()->with('error', "Gagal membuat enrollment token AMAPI: " . $e->getMessage());
        }
    }
}
