<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
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
     * Search apps live from Google Play Store or local catalog.
     */
    public function search(Request $request)
    {
        $keyword = $request->input('q', '');
        $results = [];

        if (!empty($keyword)) {
            $results = $this->playService->search($keyword, 30);
        }

        $policies = Policy::all();

        return view('apps.search', compact('results', 'keyword', 'policies'));
    }

    /**
     * Store an application into catalog and optionally attach to policy.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'app_name'     => ['required', 'string', 'max:255'],
            'icon_url'     => ['nullable', 'string'],
            'developer'    => ['nullable', 'string'],
            'policy_id'    => ['nullable', 'string'],
            'install_type' => ['nullable', 'string', 'in:FORCE_INSTALLED,AVAILABLE,KIOSK'],
        ]);

        $app = App::updateOrCreate(
            ['package_name' => $validated['package_name']],
            [
                'app_name'  => $validated['app_name'],
                'icon_url'  => $validated['icon_url'] ?? null,
                'developer' => $validated['developer'] ?? null,
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
                return back()->with('error', "Aplikasi tersimpan ke katalog, namun gagal didaftarkan ke policy AMAPI: " . $e->getMessage());
            }
        }

        AuditLog::log('ADD_CATALOG_APP', 'App', $app->package_name, $validated);

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
