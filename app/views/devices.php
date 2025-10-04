<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Pemilik.php';
require_once __DIR__ . '/../helpers/deviceHelper.php';
$database = new Database();
$db = $database->getConnection();
$pemilikModel = new Pemilik($db);

// Ambil semua pemilik & buat mapping berdasarkan device
$pemilikData = [];
$stmt = $pemilikModel->getAll();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pemilikData[$row['device']] = [
        'nama' => $row['nama'],
        'id_anggota' => $row['id_anggota']
    ];
}

?>

<h1 class="mb-4">Devices</h1>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <table id="userTable" class="table table-striped table-hover">
     <thead class="table-primary">
  <tr>
    <th>No</th>
    <th>Id Anggota</th>
    <th>Nama Pemilik</th>
    <th>Model</th>
    <th>Status</th>
    <th>Action</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($devices->getDevices())): ?>
    <?php $no = 1;
      foreach ($devices->getDevices() as $d): ?>
      <?php
          $modelCode = $d->getHardwareInfo()->getModel() ?? '-';
          $modelName = DeviceHelper::mapModel($modelCode);
          ?>
      <tr>
        <td><?= $no++ ?></td>
        <td>
          <?= isset($pemilikData[$d->getName()])
                    ? htmlspecialchars($pemilikData[$d->getName()]['id_anggota'])
                    : '<span class="text-muted">Belum ada</span>' ?>
        </td>
        <td>
          <?= isset($pemilikData[$d->getName()])
                    ? htmlspecialchars($pemilikData[$d->getName()]['nama'])
                    : '<span class="text-muted">Belum ada</span>' ?>
        </td>
        <td><?= htmlspecialchars($modelName) ?></td>
        <td>
          <?php if ($d->getState() == "ACTIVE"): ?>
            <span class="badge bg-success">Active</span>
          <?php else: ?>
            <span class="badge bg-secondary"><?= $d->getState() ?></span>
          <?php endif; ?>
        </td>
        <td>
          <a href="index.php?page=device_info&device=<?= urlencode($d->getName()) ?>" class="btn btn-xs btn-outline-primary">View</a>
          <a href="index.php?page=pemilik_add&device=<?= urlencode($d->getName()) ?>" class="btn btn-xs btn-success">Add</a>
          <a href="index.php?page=pemilik_edit&device=<?= urlencode($d->getName()) ?>" class="btn btn-xs btn-warning">Edit</a>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="6" class="text-center text-muted">No devices enrolled yet.</td>
    </tr>
  <?php endif; ?>
</tbody>

    </table>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>