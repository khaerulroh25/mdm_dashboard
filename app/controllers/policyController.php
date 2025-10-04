<?php

require_once __DIR__ . '/../services/googleAMAPI.php';

class PolicyController
{
    private $amapi;

    public function __construct()
    {
        $this->amapi = new GoogleAMAPI();
    }

    // List semua policy
    public function index()
    {
        $policies = $this->amapi->listPolicies();
        include __DIR__ . '/../views/policies.php';
    }

    // Buat policy baru
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? null;
            $policyData = []; // bisa ambil dari form

            try {
                $this->amapi->createPolicy($name, $policyData);
                $_SESSION['flash_success'] = "✅ Policy <b>{$name}</b> berhasil dibuat!";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "❌ Gagal membuat policy: " . $e->getMessage();
            }

            header("Location: index.php?page=policy_create");
            exit;
        } else {
            include __DIR__ . '/../views/policies.php';
        }
    }

    // Lihat detail policy
    public function detail($policyId)
    {
        $policy = $this->amapi->getPolicy($policyId);
        include __DIR__ . '/../views/policy_detail.php';
    }
    // Hapus policy
    public function delete($policyId)
    {
        try {
            $this->amapi->deletePolicy($policyId);
            $_SESSION['flash_success'] = "✅ Policy berhasil dihapus!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "❌ Gagal hapus policy: " . $e->getMessage();
        }

        header("Location: index.php?page=policies");
        exit;
    }

    // Update policy device
    public function updatePolicy($deviceId, $policyId)
    {
        try {
            $updated = $this->amapi->updateDevicePolicy($deviceId, $policyId);
            return $updated ? true : false;
        } catch (Exception $e) {
            error_log("Update policy error: " . $e->getMessage());
            return false;
        }
    }


}
