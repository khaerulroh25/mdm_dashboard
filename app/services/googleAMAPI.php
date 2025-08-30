<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Client;
use Google\Service\AndroidManagement;

class GoogleAMAPI
{
    private $service;
    private $enterpriseId;

    public function __construct()
    {
        $config = include __DIR__ . '/../../config/config.php';
        $client = new Client();
        $client->setAuthConfig($config['credentials_path']);
        $client->addScope(AndroidManagement::ANDROIDMANAGEMENT);

        $this->service = new AndroidManagement($client);
        $this->enterpriseId = $config['enterprise_id'];
    }

    // 🔹 List devices
    public function listDevices()
    {
        return $this->service->enterprises_devices
            ->listEnterprisesDevices($this->enterpriseId);
    }

    // 🔹 Get device detail
    public function getDevice($deviceName)
    {
        return $this->service->enterprises_devices
            ->get($deviceName);
    }

    // 🔹 List policies
    public function listPolicies()
    {
        return $this->service->enterprises_policies
            ->listEnterprisesPolicies($this->enterpriseId);
    }

    // 🔹 Get first policy name
    public function getFirstPolicyName()
    {
        $policies = $this->listPolicies();

        if (!empty($policies->getPolicies())) {
            return $policies->getPolicies()[0]->getName();
        }
        return "{$this->enterpriseId}/policies/default";
    }

    // 🔹 Create enrollment token
    public function createEnrollmentToken()
    {
        $policyName = $this->getFirstPolicyName();

        $enrollmentToken = new Google\Service\AndroidManagement\EnrollmentToken([
            'policyName' => $policyName,
            'duration' => '604800s' // 7 hari
        ]);

        return $this->service->enterprises_enrollmentTokens->create(
            $this->enterpriseId,
            $enrollmentToken
        );
    }

    // 🔹 Create policy
    public function createPolicy($policyId, $data)
    {
        $policy = new Google\Service\AndroidManagement\Policy($data);

        return $this->service->enterprises_policies->patch(
            "{$this->enterpriseId}/policies/{$policyId}",
            $policy
        );
    }

    // 🔹 Delete policy
    public function deletePolicy($policyId)
    {
        return $this->service->enterprises_policies->delete(
            "{$this->enterpriseId}/policies/{$policyId}"
        );
    }

    // 🔹 Get policy
    public function getPolicy($policyId)
    {
        $policyName = $this->enterpriseId . '/policies/' . $policyId;
        return $this->service->enterprises_policies->get($policyName);
    }

    // 🔹 Update policy (FIXED → merge array, ga pakai setter manual)
    public function updatePolicy($policyId, $policyData)
    {
        $policyName = $this->enterpriseId . '/policies/' . $policyId;

        // Ambil policy lama
        $existingPolicy = $this->getPolicy($policyId);
        $existingArray = json_decode(json_encode($existingPolicy->toSimpleObject()), true);

        // Merge: overwrite hanya jika value != null
        foreach ($policyData as $key => $value) {
            if ($value !== null) {
                $existingArray[$key] = $value;
            }
        }

        // Buat object Policy baru
        $policy = new AndroidManagement\Policy($existingArray);

        // Patch ke Google
        return $this->service->enterprises_policies->patch(
            $policyName,
            $policy
        );
    }

    // issueCommand
    public function issueCommand($deviceId, $type, $params = [])
    {
        $commandData = ['type' => $type];

        if ($type === 'START_LOST_MODE') {
            $commandData['startLostModeParams'] = [];

            if (!empty($params['lostMessage'])) {
                $commandData['startLostModeParams']['lostMessage'] = [
                    'defaultMessage' => $params['lostMessage']
                ];
            }
        }

        if ($type === 'STOP_LOST_MODE') {
            // WAJIB ada walaupun kosong
            $commandData['stopLostModeParams'] = new \stdClass();
        }

        $command = new Google\Service\AndroidManagement\Command($commandData);

        return $this->service->enterprises_devices->issueCommand($deviceId, $command);
    }



    // 🔹 Unenroll device
    public function unenrollDevice($deviceName)
    {
        try {
            $this->service->enterprises_devices->delete($deviceName);
            return true; // return true kalau sukses
        } catch (Exception $e) {
            return false; // return false kalau gagal
        }
    }
}
