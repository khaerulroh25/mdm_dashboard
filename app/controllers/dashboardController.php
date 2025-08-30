<?php

require_once __DIR__ . '/../services/googleAMAPI.php';

class DashboardController
{
    private $amapi;

    public function __construct()
    {
        $this->amapi = new GoogleAMAPI();
    }

    public function index()
    {
        // Default value biar aman
        $totalDevices   = 0;
        $activePolicies = 0;
        $pending        = 0;
        $alerts         = 0;

        try {
            // Ambil devices
            $devicesResp = $this->amapi->listDevices();
            $devices     = method_exists($devicesResp, 'getDevices') ? ($devicesResp->getDevices() ?? []) : [];
            $totalDevices = count($devices);

            // Hitung pending & alerts
            foreach ($devices as $d) {
                // Pending enrollment
                if (method_exists($d, 'getEnrollmentState') && $d->getEnrollmentState() === 'ENROLLMENT_IN_PROGRESS') {
                    $pending++;
                }
                // Alerts (non compliant)
                if (method_exists($d, 'getNonComplianceDetails') && !empty($d->getNonComplianceDetails())) {
                    $alerts++;
                }
            }

            // Ambil policies
            $policiesResp   = $this->amapi->listPolicies();
            $policies       = method_exists($policiesResp, 'getPolicies') ? ($policiesResp->getPolicies() ?? []) : [];
            $activePolicies = count($policies);

        } catch (\Throwable $e) {
            // Bisa kirim ke log kalau perlu
            // error_log('Dashboard error: ' . $e->getMessage());
        }

        // Variabel di atas akan tersedia di view
        include __DIR__ . '/../views/dashboard.php';
    }
}
