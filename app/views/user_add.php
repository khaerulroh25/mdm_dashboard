<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<div class="form-container">
    <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success'];
        unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'];
    unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>
<h2>Tambah User Baru</h2>
<form method="POST" action="index.php?page=user_store" class="card p-3 shadow-sm" style="max-width:400px;">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Role</label>
    <select name="role" class="form-control">
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
  </div>
  <button class="btn btn-primary">Simpan</button>
</form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>