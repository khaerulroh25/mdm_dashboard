<?php

require_once __DIR__ . '/../services/googleAMAPI.php';

class DeviceController
{
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



}
