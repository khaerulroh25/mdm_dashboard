<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h1 class="mb-4">Tambah Pemilik</h1>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <form method="POST" action="index.php?page=pemilik_store">
      <input type="hidden" name="device" value="<?= htmlspecialchars($_GET['device'] ?? '') ?>">

      <div class="mb-3">
        <label for="nama" class="form-label">Nama Pemilik</label>
        <input type="text" name="nama" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">ID Anggota</label>
        <input type="text" name="id_anggota" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="telepon" class="form-label">Telepon</label>
        <input type="text" name="telepon" class="form-control">
      </div>

      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="index.php?page=devices" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
