<?php

require_once __DIR__ . '/../services/googleAMAPI.php';

class EnrollmentController
{
    private $google;

    public function __construct()
    {
        $this->google = new GoogleAMAPI();
    }

    public function index()
    {
        $policies = $this->google->listPolicies()->getPolicies();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $policyId = $_POST['policy'] ?? null;

            // ✅ panggil dengan policy pilihan user
            $token = $this->google->createEnrollmentToken($policyId);
            $expiration = $token['expirationTimestamp'] ?? null;

            include __DIR__ . '/../views/enrollment_qr.php';
        } else {
            include __DIR__ . '/../views/enrollment_form.php';
        }
    }

}
