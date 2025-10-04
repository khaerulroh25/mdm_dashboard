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

    <h2>Edit User</h2>
    <form method="POST" action="index.php?page=user_update" class="card p-3 shadow-sm" style="max-width:400px;">
        <!-- ID User wajib dikirim -->
        <input type="hidden" name="id" value="<?= $user['id']; ?>">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
