<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Policy;
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
        return view('enrollment.index', compact('policies'));
    }

    /**
     * Generate an enrollment token and QR code for onboarding devices.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'policy_id' => ['required', 'string'],
        ]);

        try {
            $token = $this->amapi->createEnrollmentToken($validated['policy_id']);

            $qrPayload = $token->getQrCode();
            $base64Image = $this->qrService->generateBase64Qr($qrPayload, 320, 10);
            $expiration = $token->getExpirationTimestamp();
            $tokenName = $token->getName();
            $tokenValue = $token->getValue();

            AuditLog::log('GENERATE_ENROLLMENT_TOKEN', 'Policy', $validated['policy_id'], [
                'token_name' => $tokenName,
                'expiration' => $expiration,
            ]);

            $policy = Policy::where('policy_id', $validated['policy_id'])->first();

            return view('enrollment.show', compact(
                'base64Image',
                'token',
                'tokenValue',
                'expiration',
                'policy',
                'qrPayload'
            ));

        } catch (Exception $e) {
            return back()->with('error', "Gagal membuat enrollment token AMAPI: " . $e->getMessage());
        }
    }
}
