<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // pindahin ke sini, jadi global

$page = $_GET['page'] ?? 'dashboard';

//Cek apakah user sudah login (kecuali halaman login & loginaction)
$publicPages = ['login', 'login_action'];
if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}
switch ($page) {
    case 'devices':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();
        if (isset($_GET['detail'])) {
            $controller->show($_GET['detail']);
        } else {
            $controller->index();
        }
        break;
    case 'enroll':
        require __DIR__ . '/app/controllers/enrollmentController.php';
        $controller = new EnrollmentController();
        $controller->index();
        break;

    case 'policy_create':
        require __DIR__ . '/app/controllers/policyController.php';
        $controller = new PolicyController();
        $controller->create();
        break;

        //Tambahan untuk konfigurasi policies
    case 'konfigurasi':
        require __DIR__ . '/app/controllers/konfigurasiController.php';
        $controller = new KonfigurasiController();
        $controller->index();
        break;

    case 'konfigurasi_update':
        require __DIR__ . '/app/controllers/konfigurasiController.php';
        $controller = new KonfigurasiController();
        $controller->update();
        break;

    case 'konfigurasi_delete':
        require __DIR__ . '/app/controllers/konfigurasiController.php';
        $controller = new KonfigurasiController();
        $controller->delete();
        break;

    case 'konfigurasi_detail':
        require __DIR__ . '/app/controllers/konfigurasiController.php';
        $controller = new KonfigurasiController();
        $controller->detail();
        break;

    case 'pemilik_add':
        require __DIR__ . '/app/controllers/pemilikController.php';
        $controller = new PemilikController();
        $controller->create(); // nanti tampilkan form add pemilik
        break;

    case 'pemilik_store':
        require __DIR__ . '/app/controllers/pemilikController.php';
        $controller = new PemilikController();
        $controller->store($_POST); // simpan data
        break;
        
    case 'pemilik_edit':
        require __DIR__ . '/app/controllers/pemilikController.php';
        $controller = new PemilikController();
        $controller->edit(); // nanti tampilkan form add pemilik
        break;
    case 'pemilik_edits':
        require __DIR__ . '/app/controllers/pemilikController.php';
        $controller = new PemilikController();
        $controller->edits($_POST); // simpan data
        break;
    
    case 'update_device_policy':
        require __DIR__ . '/app/controllers/policyController.php';
        $controller = new PolicyController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId = $_POST['device_id'] ?? null;
            $newPolicy = $_POST['policy_id'] ?? null;

            if ($deviceId && $newPolicy) {
                $result = $controller->updatePolicy($deviceId, $newPolicy);

                if ($result) {
                    $_SESSION['success'] = "✅ Policy perangkat berhasil diubah.";
                } else {
                    $_SESSION['error'] = "❌ Gagal mengubah policy perangkat.";
                }

                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            }
        }
        break;

    case 'device_info':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();
        $deviceId = $_GET['device'] ?? null;
        $controller->info($deviceId);
        break;

    case 'device_unenroll':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();
        $controller->infoUnenroll();
        break;

    case 'reset_pin':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId = $_POST['device_id'] ?? null;
            $newPin   = $_POST['new_pin'] ?? null;

            if ($deviceId) {
    $result = $controller->resetPin($deviceId, $newPin ?: null);

    if ($result) {
        $_SESSION['success'] = $newPin 
            ? "PIN perangkat berhasil direset ke $newPin."
            : "PIN perangkat berhasil dihapus (kosong).";
    } else {
        $_SESSION['error'] = "Gagal mereset PIN perangkat.";
    }
}

                // redirect balik ke halaman device_info
                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            } else {
                $_SESSION['error'] = "Device ID atau PIN baru tidak boleh kosong.";
                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            }
        
        break;
    
    case 'install_app':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId     = $_POST['device_id'] ?? null;
            $packageName  = $_POST['package_name'] ?? null;
            $installType  = $_POST['install_type'] ?? 'required';


            if ($deviceId && $packageName) {
                $result = $controller->installApp($deviceId, $packageName, $installType);

                if ($result) {
                    $_SESSION['success'] = "✅ Aplikasi <b>{$packageName}</b> berhasil dipasang di device.";
                } else {
                    $_SESSION['error'] = "❌ Gagal memasang aplikasi <b>{$packageName}</b>.";
                }

                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            }
        }
        break;

    case 'lock_device':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId = $_POST['device_id'] ?? null;

            if ($deviceId) {
                $result = $controller->lock($deviceId);

                if ($result) {
                    $_SESSION['success'] = "Perangkat berhasil dikunci.";
                } else {
                    $_SESSION['error'] = "Gagal mengunci perangkat.";
                }

                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            } else {
                $_SESSION['error'] = "Device ID tidak boleh kosong.";
                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            }
        }
        break;

    case 'reboot_device':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId = $_POST['device_id'] ?? null;

            if ($deviceId) {
                $result = $controller->reboot($deviceId);

                if ($result) {
                    $_SESSION['success'] = "Perangkat berhasil dinyalakan ulang.";
                } else {
                    $_SESSION['error'] = "Gagal menyalakan ulang perangkat.";
                }

                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            } else {
                $_SESSION['error'] = "Device ID tidak boleh kosong.";
                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            }
        }
        break;

    case 'unenroll_device':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId = $_POST['device_id'] ?? null;

            if ($deviceId) {
                $result = $controller->unenroll($deviceId);

                if ($result) {
                    $_SESSION['success'] = "Perangkat berhasil diunenroll.";
                    header("Location: index.php?page=devices");
                    exit;
                } else {
                    $_SESSION['error'] = "Gagal unenroll.";
                    header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                    exit;
                }
            }
        }
        break;
    
  case 'lockDevice':
    require __DIR__ . '/app/controllers/deviceController.php';
    $controller = new DeviceController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $deviceId = $_POST['device_id'] ?? null;
        $message  = $_POST['lost_message'] ?? "Perangkat hilang, hubungi admin";
        $phone    = $_POST['lost_phone'] ?? "+6281234567890";

        if ($deviceId) {
            $result = $controller->lockDevice($deviceId, $message, $phone);

            $_SESSION['success'] = $result ? "Device Telah Di Lock." : "Gagal Lock Device";
            header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
            exit;
        }
    }
    break;
        //unlock device with stop lost mode
    case 'unlock':
        require __DIR__ . '/app/controllers/deviceController.php';
        $controller = new DeviceController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceId = $_POST['device_id'] ?? null;

            if ($deviceId) {
                $result = $controller->unlock($deviceId);

                if ($result) {
                    $_SESSION['success'] = "Perangkat berhasil diUnlock.";
                } else {
                    $_SESSION['error'] = "Gagal Unlock perangkat.";
                }

                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            } else {
                $_SESSION['error'] = "Device ID tidak boleh kosong.";
                header("Location: index.php?page=device_info&device=" . urlencode($deviceId));
                exit;
            }
        }
        break;

    case 'googleplay_manage':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $controller->index();
        break;
        
     case 'app_search':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $controller->view();
        break;

    case 'googleplay_search':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $keyword = $_GET['q'] ?? '';
        $controller->search($keyword);
        break;
        
    case 'googleplay_added':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $controller->appsAdded($_POST);
        break;


    case 'googleplay_add_manual':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $controller->store($_POST);
        break;

    case 'googleplay_add_to_policy':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $controller->addToPolicy($_POST);
        break;

    case 'googleplay_delete':
        require_once __DIR__ . '/app/controllers/googlePlayController.php';
        $controller = new GooglePlayController();
        $id = $_POST['id'] ?? $_GET['id'] ?? null; // ambil dari POST atau GET
        if ($id) {
            $controller->delete($id);
        }
        break;

    case 'dashboard':
        require __DIR__ . '/app/controllers/dashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'login':
        require __DIR__ . '/app/controllers/authController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'login_action':
        require __DIR__ . '/app/controllers/authController.php';
        $controller = new AuthController();
        $controller->loginAction($_POST);
        break;

    case 'logout':
        require __DIR__ . '/app/controllers/authController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'user_list':
        require __DIR__ . '/app/controllers/userController.php';
        $controller = new UserController();
        $controller->index();
        break;

    case 'user_add':
        require __DIR__ . '/app/controllers/userController.php';
        $controller = new UserController();
        $controller->addForm(); // tampilkan form tambah user
        break;

    case 'user_store':
        require __DIR__ . '/app/controllers/userController.php';
        $controller = new UserController();
        $controller->addAction($_POST); // simpan user baru
        break;
    case 'user_edit':
        require __DIR__ . '/app/controllers/userController.php';
        $controller = new UserController();
        $controller->editForm($_GET['id']);
        break;
    case 'user_update':
        require __DIR__ . '/app/controllers/userController.php';
        $controller = new UserController();
        $controller->update($_POST);
        break;
    case 'user_delete':
        require __DIR__ . '/app/controllers/userController.php';
        $controller = new UserController();
        $controller->delete($_GET['id']); // hapus user
        break;

    default:
        // arahkan ke dashboard supaya variabelnya disiapkan controller
        header('Location: index.php?page=dashboard');
        exit;
}
