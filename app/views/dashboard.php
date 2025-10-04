<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h1 class="mb-4">Dashboard</h1>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title">Total Devices</h5>
        <p class="fs-4 fw-bold text-primary"><?= isset($totalDevices) ? $totalDevices : 0 ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title">Active Policies</h5>
        <p class="fs-4 fw-bold text-success"><?= isset($activePolicies) ? $activePolicies : 0 ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title">Pending</h5>
        <p class="fs-4 fw-bold text-warning"><?= isset($pending) ? $pending : 0 ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title">Alerts</h5>
        <p class="fs-4 fw-bold text-danger"><?= isset($alerts) ? $alerts : 0 ?></p>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
