<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Enterprise;
use App\Models\AuditLog;
use App\Services\AndroidManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EnterpriseController extends Controller
{
    protected AndroidManagementService $amapi;

    public function __construct(AndroidManagementService $amapi)
    {
        $this->amapi = $amapi;
    }

    /**
     * Display Enterprise management & Google Cloud integration settings.
     */
    public function index()
    {
        $enterprises = Enterprise::orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $activeEnterprise = Enterprise::getActiveEnterprise();
        $projectId = $this->amapi->getProjectId();
        $serviceAccountEmail = $this->amapi->getServiceAccountEmail();
        $credentialsPath = $this->amapi->getCredentialsPath();
        $hasCredentials = file_exists($credentialsPath);

        // Run live connection test if credentials exist
        $connectionTest = $hasCredentials ? $this->amapi->testConnection() : [
            'success' => false,
            'message' => 'File kredensial Service Account belum terpasang.',
        ];

        return view('enterprises.index', compact(
            'enterprises',
            'activeEnterprise',
            'projectId',
            'serviceAccountEmail',
            'credentialsPath',
            'hasCredentials',
            'connectionTest'
        ));
    }

    /**
     * Initiate automated Google Managed Play signup URL flow.
     */
    public function initiateSignup(Request $request)
    {
        try {
            $callbackUrl = route('enterprises.callback');
            $signupData = $this->amapi->createSignupUrl($callbackUrl);

            // Store signup URL name in session to match callback
            session(['amapi_signup_url_name' => $signupData['name']]);

            AuditLog::log('INITIATE_ENTERPRISE_SIGNUP', 'Enterprise', null, [
                'signup_url_name' => $signupData['name'],
                'callback_url'    => $callbackUrl,
            ]);

            return redirect()->away($signupData['url']);

        } catch (Exception $e) {
            return back()->with('error', "Gagal memulai pendaftaran Enterprise di Google AMAPI: " . $e->getMessage());
        }
    }

    /**
     * Handle callback from Google Play signup with enterpriseToken.
     */
    public function callback(Request $request)
    {
        $enterpriseToken = $request->query('enterpriseToken');
        $signupUrlName = session('amapi_signup_url_name');

        if (!$enterpriseToken) {
            return redirect()->route('enterprises.index')
                ->with('error', 'Token pendaftaran Enterprise tidak diterima dari Google.');
        }

        if (!$signupUrlName) {
            return redirect()->route('enterprises.index')
                ->with('error', 'Sesi pendaftaran kedaluwarsa. Silakan ulangi pendaftaran Enterprise baru.');
        }

        try {
            $googleEnterprise = $this->amapi->completeEnterpriseSignup($signupUrlName, $enterpriseToken);

            $enterpriseId = $googleEnterprise->getName();
            $displayName = $googleEnterprise->getEnterpriseDisplayName() ?: 'Enterprise Baru';
            $contactEmail = $googleEnterprise->getContactInfo()?->getDataProtectionOfficerEmail();
            $contactPhone = $googleEnterprise->getContactInfo()?->getDataProtectionOfficerPhone();

            // Set as active if it's the first or user wants it
            $hasExisting = Enterprise::exists();

            $enterprise = Enterprise::updateOrCreate(
                ['enterprise_id' => $enterpriseId],
                [
                    'name'            => $displayName,
                    'admin_email'     => $contactEmail,
                    'admin_phone'     => $contactPhone,
                    'signup_url_name' => $signupUrlName,
                    'is_active'       => !$hasExisting, // Active if first
                    'metadata'        => [
                        'enterpriseType' => $googleEnterprise->getEnterpriseType(),
                        'managedGooglePlayAccountsEnterpriseType' => $googleEnterprise->getManagedGooglePlayAccountsEnterpriseType(),
                    ],
                ]
            );

            session()->forget('amapi_signup_url_name');

            AuditLog::log('CREATE_ENTERPRISE_SUCCESS', 'Enterprise', $enterprise->enterprise_id, [
                'name'        => $enterprise->name,
                'admin_email' => $enterprise->admin_email,
            ]);

            return redirect()->route('enterprises.index')
                ->with('success', "Enterprise <strong>{$enterprise->name}</strong> ({$enterprise->enterprise_id}) berhasil didaftarkan secara otomatis melalui Google AMAPI!");

        } catch (Exception $e) {
            return redirect()->route('enterprises.index')
                ->with('error', "Gagal menyelesaikan pendaftaran Enterprise: " . $e->getMessage());
        }
    }

    /**
     * Switch the active enterprise.
     */
    public function activate($id)
    {
        $enterprise = Enterprise::findOrFail($id);

        Enterprise::query()->update(['is_active' => false]);
        $enterprise->update(['is_active' => true]);

        AuditLog::log('SWITCH_ACTIVE_ENTERPRISE', 'Enterprise', $enterprise->enterprise_id, [
            'name' => $enterprise->name,
        ]);

        return redirect()->route('enterprises.index')
            ->with('success', "Enterprise aktif berhasil diubah menjadi <strong>{$enterprise->name}</strong> ({$enterprise->enterprise_id}).");
    }

    /**
     * Test AMAPI connection.
     */
    public function testConnection(Request $request)
    {
        $result = $this->amapi->testConnection();

        if ($result['success']) {
            return back()->with('success', "<strong>Uji Koneksi Berhasil!</strong> Terhubung ke Google AMAPI (Project: <code>{$result['project_id']}</code>, Enterprise: <code>{$result['enterprise_id']}</code>, Display: {$result['display_name']}).");
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Upload or update Master Service Account JSON file.
     */
    public function uploadCredentials(Request $request)
    {
        $request->validate([
            'credentials_file' => ['required', 'file', 'max:2048'],
        ]);

        $file = $request->file('credentials_file');
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (!is_array($json) || empty($json['client_email']) || empty($json['private_key']) || empty($json['project_id'])) {
            return back()->with('error', 'Format file JSON tidak valid. Pastikan file adalah Service Account Key resmi dari Google Cloud (berisi project_id, client_email, dan private_key).');
        }

        try {
            $targetPath = $this->amapi->getCredentialsPath();
            $dir = dirname($targetPath);

            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            File::put($targetPath, $content);

            AuditLog::log('UPDATE_AMAPI_SERVICE_ACCOUNT', 'System', null, [
                'project_id'   => $json['project_id'],
                'client_email' => $json['client_email'],
            ]);

            return back()->with('success', "File kredensial Service Account berhasil diperbarui untuk Google Cloud Project <strong>{$json['project_id']}</strong> ({$json['client_email']}).");

        } catch (Exception $e) {
            return back()->with('error', "Gagal menyimpan file kredensial: " . $e->getMessage());
        }
    }
}
