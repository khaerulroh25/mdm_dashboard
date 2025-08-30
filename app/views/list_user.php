<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>



<div class="container">
        <h3 class="mb-3">👤 Daftar User</h3>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'];
            unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'];
            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <a href="index.php?page=user_add" class="btn btn-primary mb-3">+ Tambah User</a>

        <table id="userTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
foreach ($users as $u): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $u['username'] ?></td>
                    <td><?= $u['role'] ?></td>
              <td class="text-end">
                <a href="index.php?page=user_edit&id=<?= $u['id'] ?>" class="text-warning me-2" title="Edit">
                 <i class="bi bi-pencil-square"></i>
                </a>
                <a href="index.php?page=user_delete&id=<?= $u['id'] ?>" class="text-danger" onclick="return confirm('Yakin hapus user ini?')" title="Hapus">
                 <i class="bi bi-trash"></i>
                </a>
                </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include __DIR__ . '/layout/footer.php'; ?>