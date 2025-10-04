<?php

require_once __DIR__ . '/../services/googleAMAPI.php';

class DeviceController
{
    private $amapi;
    public function __construct()
    {
        $this->amapi = new GoogleAMAPI();
    }

    public function index()
    {
        $amapi = new GoogleAMAPI();
        $devices = $amapi->listDevices();
        include __DIR__ . '/../views/devices.php';
    }

    public function show($deviceName)
    {
        $amapi = new GoogleAMAPI();
        $device = $amapi->getDevice($deviceName);
        include __DIR__ . '/../views/device_detail.php';
    }
    // detail device
    public function info($deviceId)
    {
        $amapi = new GoogleAMAPI();
        if (!$deviceId) {
            die("Device not found.");
        }

        $device = $amapi->getDevice($deviceId);
        include __DIR__ . '/../views/deviceInfo.php';
    }
    // 🔐 Reset PIN
    public function resetPin($deviceId, $newPin)
    {
        $amapi = new GoogleAMAPI();
        return $amapi->issueCommand(
            $deviceId,
            'RESET_PASSWORD',
            [
                'newPassword' => $newPin,
                'resetPasswordFlags' => ['LOCK_NOW', 'REQUIRE_ENTRY']
            ]
        );
    }

    // Lock Device
    public function sleep($deviceId)
    {
        $amapi = new GoogleAMAPI();
        return $amapi->issueCommand($deviceId, 'LOCK');
    }

    // Reboot Device
    public function reboot($deviceId)
    {
        $amapi = new GoogleAMAPI();
        return $amapi->issueCommand($deviceId, 'REBOOT');
    }

    //  Unenroll
    public function unenroll($deviceId)
    {
        $amapi = new GoogleAMAPI();
        return $amapi->unenrollDevice($deviceId);
    }
    // 🔎 Start Lost Mode/lock
    public function lockDevice($deviceId, $message = null)
    {
        $amapi = new GoogleAMAPI();
        $params = [
            'lostMessage' => $message
        ];
        return $amapi->issueCommand($deviceId, 'START_LOST_MODE', $params);
    }
    //unlock device
    public function unlock($deviceId)
    {
        $amapi = new GoogleAMAPI();
        return $amapi->issueCommand($deviceId, 'STOP_LOST_MODE');
    }

    // Install app ke device
    public function installApp($deviceId, $packageName, $installType = 'REQUIRED')
    {
        try {
            $device = $this->amapi->getDevice($deviceId);

            // Pastikan device sudah ada policy
            $policyNameFull = $device->getPolicyName();
            if (!$policyNameFull) {
                $_SESSION['error'] = "❌ Device belum punya policy, tidak bisa install aplikasi.";
                return false;
            }

            // Ambil policy lama
            $policy = $this->amapi->getPolicy($policyNameFull);
            $applications = $policy->getApplications() ?? [];

            // Valid installType sesuai Android Management API
            $validInstallTypes = ['FORCE_INSTALLED', 'AVAILABLE', 'KIOSK'];
            $installType = strtoupper($installType);
            if (!in_array($installType, $validInstallTypes)) {
                $installType = 'AVAILABLE'; // default
            }

            // Cek duplikat aplikasi
            $alreadyExists = false;
            foreach ($applications as $app) {
                // pastikan array/object bisa diakses
                $pkg = is_array($app) ? $app['packageName'] : $app->packageName;
                if ($pkg === $packageName) {
                    $alreadyExists = true;
                    break;
                }
            }

            // Tambahkan aplikasi kalau belum ada
            if (!$alreadyExists) {
                $applications[] = [
                    "packageName" => $packageName,
                    "installType" => $installType
                ];
            }

            // Update policy
            $policy->setApplications($applications);
            $this->amapi->updatePolicy($policyNameFull, $policy);

            $_SESSION['success'] = "✅ App berhasil ditambahkan ke policy ($installType).";
            return true;

        } catch (\Exception $e) {
            error_log("Install app error: " . $e->getMessage());
            $_SESSION['error'] = "❌ Install app error: " . $e->getMessage();
            return false;
        }
    }


}
