<?php
require_once __DIR__ . '/../services/googleAMAPI.php';

class EnrollmentController {
    private $google;

    public function __construct() {
        $this->google = new GoogleAMAPI();
    }

    public function index() {
        $token = $this->google->createEnrollmentToken();

        // Ambil tanggal expired dari token
        $expiration = $token['expirationTimestamp'] ?? null;

        include __DIR__ . '/../views/enrollment_qr.php';
    }
}
