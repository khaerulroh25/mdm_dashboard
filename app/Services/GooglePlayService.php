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
            try {
                $results = @$this->gplay->search($keyword, 100);
                $apps = [];

                foreach ($results as $app) {
                    if ($app) {
                        $apps[] = [
                            'app_name'     => $app->getName() ?? 'Unknown',
                            'package_name' => $app->getId() ?? '',
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
     */
    public function getAppInfo(string $packageName): ?array
    {
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
                ];
            } catch (Exception $e) {
                Log::warning("GooglePlayService getAppInfo error for '{$packageName}': " . $e->getMessage());
                return null;
            }
        });
    }
}
