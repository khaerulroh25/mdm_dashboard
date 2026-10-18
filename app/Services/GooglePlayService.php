<?php

namespace App\Services;

use Exception;
use Nelexa\GPlay\GPlayApps;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GooglePlayService
{
    protected GPlayApps $gplay;
    protected AndroidManagementService $amapi;

    public function __construct(AndroidManagementService $amapi)
    {
        $this->gplay = new GPlayApps();
        $this->amapi = $amapi;
    }

    /**
     * Search apps on Google Play Store with fuzzy similarity sorting.
     */
    public function search(string $keyword, int $limit = 30): array
    {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return [];
        }

        $cacheKey = 'gplay_search_' . md5(strtolower($keyword));

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($keyword, $limit) {
            $apps = [];

            // If keyword looks like a package name (e.g. com.whatsapp), resolve via official AMAPI first
            if (str_contains($keyword, '.')) {
                try {
                    $directApp = $this->getAppInfo($keyword);
                    if ($directApp) {
                        $apps[] = $directApp;
                    }
                } catch (\Throwable $e) {
                    Log::info("Direct package lookup error: " . $e->getMessage());
                }
            }

            try {
                $results = @$this->gplay->search($keyword, 100);

                foreach ($results as $app) {
                    if ($app) {
                        $pkg = $app->getId() ?? '';
                        if (!empty($apps) && $apps[0]['package_name'] === $pkg) {
                            continue; // Avoid duplicate
                        }
                        $apps[] = [
                            'app_name'     => $app->getName() ?? 'Unknown',
                            'package_name' => $pkg,
                            'icon'         => $app->getIcon() ? $app->getIcon()->getUrl() : null,
                            'developer'    => $app->getDeveloper() ? $app->getDeveloper()->getName() : 'Unknown',
                        ];
                    }
                }

                if (!empty($apps)) {
                    // Fuzzy sort by keyword similarity
                    usort($apps, function ($a, $b) use ($keyword) {
                        similar_text(strtolower($a['app_name']), strtolower($keyword), $pctA);
                        similar_text(strtolower($b['app_name']), strtolower($keyword), $pctB);
                        return $pctB <=> $pctA;
                    });
                }
            } catch (\Throwable $e) {
                Log::warning("GooglePlayService scraper error for '{$keyword}': " . $e->getMessage());
                $apps = [];
            }

            // Fallback to local database catalog if Play Store returned empty or failed
            if (empty($apps)) {
                $localApps = \App\Models\App::where('app_name', 'like', "%{$keyword}%")
                    ->orWhere('package_name', 'like', "%{$keyword}%")
                    ->take($limit)
                    ->get();

                foreach ($localApps as $lApp) {
                    $apps[] = [
                        'app_name'     => $lApp->app_name,
                        'package_name' => $lApp->package_name,
                        'icon'         => $lApp->icon_url,
                        'developer'    => $lApp->developer ?? 'Local Catalog',
                    ];
                }
            }

            return array_slice($apps, 0, $limit);
        });
    }

    /**
     * Get detailed metadata for a specific package.
     * Uses official Google AMAPI first, then falls back to scraper.
     */
    public function getAppInfo(string $packageName): ?array
    {
        $packageName = trim($packageName);
        if (empty($packageName)) {
            return null;
        }

        $cacheKey = 'gplay_app_' . md5($packageName);

        return Cache::remember($cacheKey, now()->addDays(1), function () use ($packageName) {
            // 1. Try official Google Android Management API first
            try {
                $amapiApp = $this->amapi->getApplication($packageName);
                if ($amapiApp && $amapiApp->getTitle()) {
                    return [
                        'app_name'     => $amapiApp->getTitle(),
                        'package_name' => $packageName,
                        'icon'         => $amapiApp->getIconUrl() ?: $amapiApp->getSmallIconUrl(),
                        'developer'    => $amapiApp->getAuthor() ?: 'Google Play Store',
                        'description'  => $amapiApp->getFullDescription() ?: $amapiApp->getDescription() ?: '',
                        'is_official'  => true,
                    ];
                }
            } catch (\Throwable $e) {
                Log::info("AMAPI official getApplication fallback for {$packageName}: " . $e->getMessage());
            }

            // 2. Scraper fallback
            try {
                $app = $this->gplay->getAppInfo($packageName);
                return [
                    'app_name'     => $app->getName(),
                    'package_name' => $app->getId(),
                    'icon'         => $app->getIcon() ? $app->getIcon()->getUrl() : null,
                    'developer'    => $app->getDeveloper() ? $app->getDeveloper()->getName() : null,
                    'description'  => $app->getDescription(),
                    'is_official'  => false,
                ];
            } catch (Exception $e) {
                Log::warning("GooglePlayService getAppInfo error for '{$packageName}': " . $e->getMessage());
                return null;
            }
        });
    }
}
