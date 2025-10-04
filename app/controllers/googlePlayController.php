<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/aplikasi.php';
require_once __DIR__ . '/../services/googleAMAPI.php';
require_once __DIR__ . '/../services/googlePlayScrapper.php';

class GooglePlayController
{
    private $appModel;
    private $googleApi;
    private $googlePlayScraper;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->appModel = new Aplikasi($db);
        $this->googleApi = new GoogleAMAPI();
        $this->googlePlayScraper = new GooglePlayScraper();
    }

    // halaman utama
    public function index()
    {
        $apps = $this->appModel->getAll();
        $policies = $this->googleApi->listPolicies();
        include __DIR__ . '/../views/google_play_manage.php';
    }
    public function view()
    {
        include __DIR__ . '/../views/google_play_search.php';
    }
    // pencarian aplikasi di google play
    public function search($keyword)
    {
        $results = [];

        if (!empty($keyword)) {
            $results = $this->googlePlayScraper->search($keyword);
        }

        // ambil data apps yang sudah ada di DB juga
        $apps = $this->appModel->getAll();

        // ambil daftar policy
        $policies = $this->googleApi->listPolicies();

        include __DIR__ . '/../views/google_play_search.php';
    }

    public function appsAdded($data)
    {
        if (!empty($data['name']) && !empty($data['package_name'])) {
            $this->appModel->create($data['name'], $data['package_name']);
            $_SESSION['success'] = "Aplikasi berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Nama & package tidak boleh kosong.";
        }
        header("Location: index.php?page=app_search");
        exit;
    }


    // simpan aplikasi manual ke DB
    public function store($data)
    {
        if (!empty($data['name']) && !empty($data['package_name'])) {
            $this->appModel->create($data['name'], $data['package_name']);
            $_SESSION['success'] = "Aplikasi berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Nama & package tidak boleh kosong.";
        }
        header("Location: index.php?page=googleplay_manage");
        exit;
    }

    //tambahkan aplikasi ke policy
    public function addToPolicy($data)
    {
        $policyId     = $data['policy_name'] ?? null; // pastikan ini cuma ID (bukan full path)
        $selectedApps = $data['apps'] ?? [];

        if (!$policyId || empty($selectedApps)) {
            $_SESSION['error'] = "Pilih policy dan aplikasi terlebih dahulu.";
            header("Location: index.php?page=googleplay_manage");
            exit;
        }

        try {
            // ambil policy existing
            $policy = $this->googleApi->getPolicy($policyId);
            $existingApps = $policy->getApplications() ?? [];

            // merge aplikasi baru
            $existingAppsMap = [];
            foreach ($existingApps as $app) {
                $existingAppsMap[$app->getPackageName()] = $app;
            }

            foreach ($selectedApps as $pkg) {
                if (!isset($existingAppsMap[$pkg])) {
                    $existingAppsMap[$pkg] = new Google\Service\AndroidManagement\ApplicationPolicy([
                        'packageName' => $pkg,
                        'installType' => 'AVAILABLE'
                    ]);
                }
            }

            // kirim kembali sebagai array
            $policyData = [
                'applications' => array_values($existingAppsMap)
            ];
            $this->googleApi->updatePolicy($policyId, $policyData);


            $_SESSION['success'] = "Aplikasi berhasil ditambahkan ke policy.";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Gagal update policy: " . $e->getMessage();
        }

        header("Location: index.php?page=googleplay_manage");
        exit;
    }



    // hapus aplikasi dari DB
    public function delete($id)
    {
        if ($id) {
            if ($this->appModel->delete($id)) {
                $_SESSION['success'] = "Aplikasi berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus aplikasi.";
            }
        } else {
            $_SESSION['error'] = "Tidak ada aplikasi yang dipilih.";
        }

        header("Location: index.php?page=googleplay_manage");
        exit;
    }
}
