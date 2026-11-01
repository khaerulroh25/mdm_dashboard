<?php

namespace App\Services;

use Exception;
use Nelexa\GPlay\GPlayApps;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GooglePlayService
{
    protected GPlayApps $gplay;

    public function __construct()
    {
        $this->gplay = new GPlayApps();
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

            // If keyword looks like a package name (e.g. com.whatsapp), resolve via scraper first
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
                            continue;
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
     * Get detailed metadata for a specific package via scraper.
     */
    public function getAppInfo(string $packageName): ?array
    {
        $packageName = trim($packageName);
        if (empty($packageName)) {
            return null;
        }

        $cacheKey = 'gplay_app_' . md5($packageName);

        return Cache::remember($cacheKey, now()->addDays(1), function () use ($packageName) {
            try {
                $app = $this->gplay->getAppInfo($packageName);
                return [
                    'app_name'     => $app->getName(),
                    'package_name' => $app->getId(),
                    'icon'         => $app->getIcon() ? $app->getIcon()->getUrl() : null,
                    'developer'    => $app->getDeveloper() ? $app->getDeveloper()->getName() : null,
                    'description'  => $app->getDescription(),
                    'is_official'  => true,
                ];
            } catch (Exception $e) {
                Log::warning("GooglePlayService getAppInfo error for '{$packageName}': " . $e->getMessage());

                // Fallback to local database catalog
                $local = \App\Models\App::where('package_name', $packageName)->first();
                if ($local) {
                    return [
                        'app_name'     => $local->app_name,
                        'package_name' => $local->package_name,
                        'icon'         => $local->icon_url,
                        'developer'    => $local->developer ?? 'Local Catalog',
                        'description'  => '',
                        'is_official'  => false,
                    ];
                }

                return null;
            }
        });
    }
}
