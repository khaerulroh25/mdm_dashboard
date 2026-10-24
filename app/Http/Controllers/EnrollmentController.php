<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\Branch;
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
        $policies = Policy::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('enrollment.index', compact('policies', 'branches'));
    }

    /**
     * Generate an enrollment token and QR code for onboarding devices.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'policy_id' => ['required', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $user = auth()->user();
        $branchId = $user->isBranchScoped() ? $user->branch_id : ($validated['branch_id'] ?? null);
        $branch = $branchId ? Branch::find($branchId) : null;

        $additionalData = $branch ? json_encode([
            'branch_id'   => $branch->id,
            'branch_code' => $branch->code,
            'branch_name' => $branch->name,
        ]) : null;

        try {
            $token = $this->amapi->createEnrollmentToken($validated['policy_id'], 604800, $additionalData);

            $qrPayload = $token->getQrCode();
            $base64Image = $this->qrService->generateBase64Qr($qrPayload, 320, 10);
            $expiration = $token->getExpirationTimestamp();
            $tokenName = $token->getName();
            $tokenValue = $token->getValue();

            AuditLog::log('GENERATE_ENROLLMENT_TOKEN', 'Policy', $validated['policy_id'], [
                'token_name'  => $tokenName,
                'expiration'  => $expiration,
                'branch_id'   => $branch?->id,
                'branch_code' => $branch?->code,
            ]);

            $policy = Policy::where('policy_id', $validated['policy_id'])->first();

            return view('enrollment.show', compact(
                'base64Image',
                'token',
                'tokenValue',
                'expiration',
                'policy',
                'branch',
                'qrPayload'
            ));

        } catch (Exception $e) {
            return back()->with('error', "Gagal membuat enrollment token AMAPI: " . $e->getMessage());
        }
    }
}
