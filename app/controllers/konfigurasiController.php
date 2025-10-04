<?php

require_once __DIR__ . '/../services/googleAMAPI.php';

class KonfigurasiController
{
    private $amapi;

    public function __construct()
    {
        $this->amapi = new GoogleAMAPI();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Halaman utama konfigurasi
    public function index()
    {
        $policies = $this->amapi->listPolicies();
        include __DIR__ . '/../views/konfigurasi.php';
    }

    // Update konfigurasi policy (merge dengan policy lama)
    public function update()
    {
        $googleAMAPI = new GoogleAMAPI();

        if (!empty($_POST['policy_name'])) {
            $policyFullName = $_POST['policy_name'];
            $policyId = basename($policyFullName);

            // Mapping dari checkbox
            $policyData = [
            "cameraDisabled"       => ($_POST['cameraDisabled'] === "true"),
            "wifiConfigDisabled"   => ($_POST['wifiConfigDisabled'] === "true"),
            "factoryResetDisabled" => ($_POST['factoryResetDisabled'] === "true"),
            "installAppsDisabled"  => ($_POST['installAppsDisabled'] === "true"),
            "safeBootDisabled"     => ($_POST['safeBootDisabled'] === "true"),
            "locationMode"         => !empty($_POST['locationMode']) ? "HIGH_ACCURACY" : null,
            ];

            try {
                $googleAMAPI->updatePolicy($policyId, $policyData);
                $_SESSION['flash_success'] = "Policy $policyId berhasil diupdate ✅";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Gagal update policy: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "Pilih policy dulu sebelum update.";
        }
        header("Location: index.php?page=konfigurasi");
        exit;
    }


    // Hapus Policy
    public function delete()
    {
        $googleAMAPI = new GoogleAMAPI();

        if (!empty($_POST['policy_name'])) {
            // ambil nama policy dari form
            $policyFullName = $_POST['policy_name'];
            $policyId = basename($policyFullName); // ambil ID terakhir dari string

            try {
                $googleAMAPI->deletePolicy($policyId);
                $_SESSION['flash_success'] = "Policy <b>$policyId</b> berhasil dihapus.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Gagal hapus policy: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "Pilih policy dulu sebelum hapus.";
        }

        header("Location: index.php?page=konfigurasi");
        exit;
    }
    //Menampilkan Poolicy yang aktiv
    public function detail()
    {
        $googleAMAPI = new GoogleAMAPI();

        if (!empty($_POST['policy_name'])) {
            $policyFullName = $_POST['policy_name'];
            $policyId = basename($policyFullName);

            try {
                $policy = $googleAMAPI->getPolicy($policyId);

                // kirim ke view
                include __DIR__ . '/../views/konfigurasi_detail.php';
                return;
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Gagal ambil detail policy: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "Pilih policy dulu untuk lihat detail.";
        }

        header("Location: index.php?page=policy_konfigurasi");
        exit;
    }


}
