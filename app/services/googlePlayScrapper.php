<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nelexa\GPlay\GPlayApps;

class GooglePlayScraper
{
    private $gplay;

    public function __construct()
    {
        $this->gplay = new GPlayApps();
    }

    public function search($keyword, $limit = null)
    {
        // kalau limit null, ambil 100 hasil sebagai batas atas
        $maxFetch = $limit ?? 100;

        $results = $this->gplay->search($keyword, 300, );
        $apps = [];

        foreach ($results as $app) {
            if ($app) {
                $apps[] = [
                    'app_name'     => $app->getName() ?? 'Unknown',
                    'package_name' => $app->getId() ?? '',
                    'icon'         => $app->getIcon() ? $app->getIcon()->getUrl() : '',
                    'developer'    => $app->getDeveloper() ? $app->getDeveloper()->getName() : 'Unknown'
                ];
            }
        }

        // 🔑 Fuzzy sort berdasarkan similarity dengan keyword
        usort($apps, function ($a, $b) use ($keyword) {
            similar_text(strtolower($a['app_name']), strtolower($keyword), $percentA);
            similar_text(strtolower($b['app_name']), strtolower($keyword), $percentB);
            return $percentB <=> $percentA; // urutkan dari paling mirip
        });

        // kalau $limit null, return semua
        return $limit ? array_slice($apps, 0, $limit) : $apps;
    }


}
