<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<?php require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Pemilik.php';
?>

<?php
$database = new Database();
$db = $database->getConnection();
$pemiliks = new Pemilik($db);

$device = $_GET['device'] ?? null;
$data = null;

if ($device) {
    $data = $pemiliks->getById($device);
}

if (!$data) {
    die("Data pemilik tidak ditemukan!");
}
?>



<h1 class="mb-4">Edit Pemilik</h1>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <form method="POST" action="index.php?page=pemilik_edits">
      <input type="hidden" name="device" value="<?= htmlspecialchars($_GET['device'] ?? '') ?>">

      <div class="mb-3">
        <label for="nama" class="form-label">Nama Pemilik</label>
        <input type="text" name="nama" class="form-control" value ="<?= $data['nama']; ?>" required>
      </div>

      <div class="mb-3">
        <label for="id_anggota" class="form-label">ID Anggota</label>
        <input type="text" name="id_anggota" class="form-control" value ="<?= $data['id_anggota']; ?>" required>
      </div>

      <div class="mb-3">
        <label for="telepon" class="form-label">Telepon</label>
        <input type="text" name="telepon" class="form-control" value ="<?= $data['telepon']; ?>">
      </div>

      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="index.php?page=devices" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
