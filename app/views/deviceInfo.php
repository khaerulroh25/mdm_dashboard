<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../helpers/deviceHelper.php';
require_once __DIR__ . '/../services/googleAMAPI.php';
require_once __DIR__ . '/../models/aplikasi.php';

$deviceId = $_GET['device'] ?? null;
if (!$deviceId) {
    die("Device not found.");
}
$database = new Database();
$db = $database->getConnection();
$appModel = new Aplikasi($db);
$apps = $appModel->getAll();

// Ambil detail device dari API
$amapi = new GoogleAMAPI();
$device = $amapi->getDevice($deviceId);

$modelCode = $device->getHardwareInfo()->getModel() ?? '-';
$modelName = DeviceHelper::mapModel($modelCode);

// Ambil semua policy
$policiesResponse = $amapi->listPolicies();
$allPolicies = $policiesResponse->getPolicies();


// Ambil policy aktif
$policyNameFull = $device->getPolicyName();
$policyId = $policyNameFull ? basename($policyNameFull) : '-';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>


<h1 class="mb-4">Device Info</h1>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <!-- Install Aplikasi -->
 <div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Detail Device</h5>
  <a href="#" 
     class="text-success" 
     title="Install App"
     data-bs-toggle="modal" 
     data-bs-target="#installAppModal">
    <i class="bi bi-box-arrow-in-down fs-5"></i>
  </a>
</div>
    <table class="table table-bordered">
      <tr><th>Device ID</th><td><?= htmlspecialchars($device->getName()) ?></td></tr>
      <tr><th>Model</th><td><?= htmlspecialchars($modelName) ?></td></tr>
      <tr><th>Status</th><td><?= htmlspecialchars($device->getState()) ?></td></tr>
      <tr><th>Last Sync</th><td><?= htmlspecialchars($device->getLastStatusReportTime() ?? '-') ?></td></tr>
      <tr><th>Policy Aktif</th><td><?= htmlspecialchars($policyId) ?>  
         <a href="#" 
         class="text-warning me-2" 
         title="Edit" 
         data-bs-toggle="modal" 
         data-bs-target="#editPolicyModal">
        <i class="bi bi-pencil-square"></i>
      </a>
      </td>
    </tr>
    </table>

    <h5 class="mt-4">Actions</h5>
    <div class="d-flex flex-wrap gap-2">
      <!-- Reset PIN pakai modal -->
      <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetPinModal">Reset PIN</button>

      <!-- Lock Device -->
      <!-- <form method="post" action="index.php?page=lock_device" class="d-inline">
        <input type="hidden" name="device_id" value="">
        <button type="submit" class="btn btn-secondary btn-sm">Sleep</button>
      </form> -->

      <!-- Reboot Device -->
      <form method="post" action="index.php?page=reboot_device" class="d-inline">
        <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">
        <button type="submit" class="btn btn-info btn-sm">Reboot Device</button>
      </form>

      <!-- Unenroll Device -->
      <form method="post" action="index.php?page=unenroll_device" class="d-inline">
        <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">
        <button type="submit" class="btn btn-danger btn-sm"
                onclick="return confirm('Yakin ingin unenroll device ini?')">Unenroll Device</button>
      </form>
      <!-- Tombol Start Lost Mode pakai modal -->
<button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#lostModeModal">
  Lock
</button>
<!-- Stop Lost Mode -->
<form method="post" action="index.php?page=unlock" class="d-inline">
  <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">
  <button type="submit" class="btn btn-outline-dark btn-sm">Unlock</button>
</form>

    </div>
  </div>
</div>

<!-- Modal Reset PIN -->
<div class="modal fade" id="resetPinModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="index.php?page=reset_pin">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reset PIN Device</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">
          <div class="mb-3">
            <label for="new_pin">PIN Baru</label>
            <input type="password" id="new_pin" name="new_pin" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Reset PIN</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Start Lost Mode -->
<div class="modal fade" id="lostModeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="index.php?page=lockDevice">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">
          <div class="mb-3">
            <label for="lost_message" class="form-label">Pesan</label>
            <textarea id="lost_message" name="lost_message" class="form-control" rows="3" required>⚠️ Pembayaran anda tertungggak silahkan Hubungi admin.</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-dark">Lock</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Policy -->
<div class="modal fade" id="editPolicyModal" tabindex="-1" aria-labelledby="editPolicyLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="index.php?page=update_device_policy">
        <div class="modal-header">
          <h5 class="modal-title" id="editPolicyLabel">Ubah Policy Device</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">

          <div class="mb-3">
            <label for="policySelect" class="form-label">Pilih Policy</label>
            <select class="form-select" id="policySelect" name="policy_id" required>
              <option value="" disabled selected>-- Pilih Policy --</option>
              <?php foreach ($allPolicies as $policy): ?>
              <?php $policyShortId = basename($policy->getName());?>
                  <option value="<?= htmlspecialchars($policyShortId) ?>"
                      <?= $policyShortId === $policyId ? 'selected' : '' ?>>
                      <?= htmlspecialchars($policyShortId) ?>
                  </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal Install App -->
<div class="modal fade" id="installAppModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Lebih besar supaya nyaman -->
    <form method="post" action="index.php?page=install_app">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Install Aplikasi ke Device</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="device_id" value="<?= htmlspecialchars($device->getName()) ?>">

          <!-- Pilih Aplikasi -->
         <div class="mb-3">
      <label for="package_name" class="form-label fw-bold">Pilih Aplikasi</label>
        <select id="package_name" name="package_name" 
                class="selectpicker" 
                data-live-search="true"
                data-width="100%"
                title="-- Pilih Aplikasi --"
                data-style="btn-outline-secondary bg-white text-dark border"
                required>
          <?php if (!empty($apps)): ?>
            <?php foreach ($apps as $app): ?>
              <option value="<?= htmlspecialchars($app['package_name']) ?>">
                <?= htmlspecialchars($app['app_name']) ?> (<?= htmlspecialchars($app['package_name']) ?>)
              </option>
            <?php endforeach; ?>
          <?php else: ?>
            <option disabled>Belum ada aplikasi di database</option>
          <?php endif; ?>
        </select>
      </div>
          <!-- Pilih Jenis Install -->
          <div class="mb-3">
            <label for="install_type" class="form-label fw-bold">Jenis Install</label>
            <select id="install_type" name="install_type" class="form-select" required>
              <option value="" disabled selected>-- Pilih Jenis --</option>
              <option value="KIOSK">Kiosk Mode</option>
              <option value="AVAILABLE">Available (Hanya tersedia di Play Store)</option>
              <option value="FORCE_INSTALLED">Install (Wajib terpasang)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Install</button>
        </div>
      </div>
    </form>
  </div>
</div>




<?php include __DIR__ . '/layout/footer.php'; ?>
