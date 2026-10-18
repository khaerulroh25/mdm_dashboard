<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\App;
use App\Models\Policy;
use App\Models\AuditLog;
use App\Services\GooglePlayService;
use App\Services\AndroidManagementService;

class GooglePlayController extends Controller
{
    protected GooglePlayService $playService;
    protected AndroidManagementService $amapi;

    public function __construct(GooglePlayService $playService, AndroidManagementService $amapi)
    {
        $this->playService = $playService;
        $this->amapi = $amapi;
    }

    /**
     * Display a listing of catalog apps in database.
     */
    public function index(Request $request)
    {
        $query = App::withCount('policies');

        if ($search = $request->input('search')) {
            $query->where('app_name', 'like', "%{$search}%")
                  ->orWhere('package_name', 'like', "%{$search}%");
        }

        $apps = $query->latest()->paginate(20)->withQueryString();
        $policies = Policy::all();

        return view('apps.index', compact('apps', 'policies'));
    }

    /**
     * Search apps live from Google Play Store or Managed Google Play iframe.
     */
    public function search(Request $request)
    {
        $keyword = $request->input('q', '');
        $results = [];

        if (!empty($keyword)) {
            $results = $this->playService->search($keyword, 30);
        }

        $policies = Policy::all();

        // 1. Determine Parent Frame URL for Google Managed Play Iframe
        $customParent = config('amapi.parent_frame_url');
        $parentUrl = $customParent;
        
        if (!$parentUrl) {
            $isHttps = $request->isSecure() || $request->header('X-Forwarded-Proto') === 'https' || str_starts_with(config('app.url'), 'https://');
            if ($isHttps) {
                $parentUrl = $request->url();
            }
        }

        $webToken = null;
        $webTokenError = null;

        // 2. If parentUrl is HTTPS, request official Web Token from AMAPI
        if ($parentUrl && str_starts_with($parentUrl, 'https://')) {
            try {
                $tokenObj = $this->amapi->createWebToken($parentUrl);
                $webToken = $tokenObj->getValue();
            } catch (\Throwable $e) {
                Log::warning("AMAPI createWebToken failed: " . $e->getMessage());
                $webTokenError = $e->getMessage();
            }
        }

        return view('apps.search', compact('results', 'keyword', 'policies', 'webToken', 'webTokenError', 'parentUrl'));
    }

    /**
     * Official metadata lookup for an app package directly via Google AMAPI.
     */
    public function lookup(Request $request)
    {
        $packageName = trim($request->input('package_name', ''));
        if (empty($packageName)) {
            return response()->json(['success' => false, 'message' => 'Package name is required'], 422);
        }

        $app = $this->playService->getAppInfo($packageName);
        if (!$app) {
            return response()->json([
                'success' => false,
                'message' => "Aplikasi dengan package '{$packageName}' tidak ditemukan di Google Play."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'app'     => $app,
        ]);
    }

    /**
     * Store an application into catalog and optionally attach to policy.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'app_name'     => ['nullable', 'string', 'max:255'],
            'icon_url'     => ['nullable', 'string'],
            'developer'    => ['nullable', 'string'],
            'policy_id'    => ['nullable', 'string'],
            'install_type' => ['nullable', 'string', 'in:FORCE_INSTALLED,AVAILABLE,KIOSK'],
        ]);

        $packageName = trim($validated['package_name']);
        $appName     = $validated['app_name'] ?? null;
        $iconUrl     = $validated['icon_url'] ?? null;
        $developer   = $validated['developer'] ?? null;

        // Auto-fetch official metadata if name or icon is missing
        if (empty($appName) || empty($iconUrl)) {
            $info = $this->playService->getAppInfo($packageName);
            if ($info) {
                $appName   = $appName ?: ($info['app_name'] ?? $packageName);
                $iconUrl   = $iconUrl ?: ($info['icon'] ?? null);
                $developer = $developer ?: ($info['developer'] ?? null);
            } else {
                $appName = $appName ?: $packageName;
            }
        }

        $app = App::updateOrCreate(
            ['package_name' => $packageName],
            [
                'app_name'  => $appName,
                'icon_url'  => $iconUrl,
                'developer' => $developer,
            ]
        );

        // Optionally attach to policy
        if (!empty($validated['policy_id'])) {
            $installType = $validated['install_type'] ?? 'AVAILABLE';
            try {
                $this->amapi->addAppToPolicy($validated['policy_id'], $app->package_name, $installType);
                $policy = Policy::where('policy_id', $validated['policy_id'])->first();
                if ($policy) {
                    $policy->applications()->syncWithoutDetaching([
                        $app->id => ['install_type' => $installType]
                    ]);
                }
            } catch (Exception $e) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Aplikasi tersimpan ke katalog, namun gagal didaftarkan ke policy AMAPI: " . $e->getMessage()
                    ], 500);
                }
                return back()->with('error', "Aplikasi tersimpan ke katalog, namun gagal didaftarkan ke policy AMAPI: " . $e->getMessage());
            }
        }

        AuditLog::log('ADD_CATALOG_APP', 'App', $app->package_name, $validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Aplikasi {$app->app_name} berhasil ditambahkan!",
                'app'     => $app,
            ]);
        }

        return back()->with('success', "Aplikasi <strong>{$app->app_name}</strong> berhasil ditambahkan ke katalog!");
    }

    /**
     * Delete application from local catalog.
     */
    public function destroy($id)
    {
        $app = App::findOrFail($id);
        $name = $app->app_name;
        $app->delete();

        AuditLog::log('DELETE_CATALOG_APP', 'App', (string)$id, ['app_name' => $name]);

        return back()->with('success', "Aplikasi <strong>{$name}</strong> berhasil dihapus dari katalog.");
    }
}
