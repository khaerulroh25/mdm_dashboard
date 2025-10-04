<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>


<div class="container">
       <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <h2>Google Play Manage</h2>

    <!-- Form Add ke Policy -->
    <form method="POST" action="index.php?page=googleplay_add_to_policy">
        <label>Pilih Policy:</label>
        <select name="policy_name" class="form-control" required>
            <option value="">-- Pilih Policy --</option>
            <?php foreach ($policies as $policy): ?>
                <option value="<?= basename($policy->getName()) ?>">
                 <?= basename($policy->getName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <hr>

       <h4>Daftar Aplikasi</h4>
<div class="app-list-container">
    <!-- Select All checkbox -->
    <div class="mb-2">
        <input type="checkbox" id="selectAll"> <strong>Pilih Semua / Hapus Semua</strong>
    </div>

    <?php foreach ($apps as $app): ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>
                <input type="checkbox" class="app-checkbox" name="apps[]" value="<?= htmlspecialchars($app['package_name']) ?>">
                <?= htmlspecialchars($app['app_name']) ?> (<?= htmlspecialchars($app['package_name']) ?>)
            </span>

            <a href="index.php?page=googleplay_delete&id=<?= $app['id'] ?>"
               onclick="return confirm('Hapus aplikasi ini?')"
               class="text-danger"
               style="text-decoration:none; cursor:pointer;">
                Hapus
            </a>
        </div>
    <?php endforeach; ?>
</div>





<div class="d-flex justify-content-between mt-3">
    <div>
        <button type="submit" class="btn btn-success" name="action" value="add">Add ke Policy</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#addAppModal" class="btn btn-primary">Tambahkan Aplikasi Manual</button>
</form>


    </div>
</div>
    <hr>
    <!-- Modal -->
    <div class="modal fade" id="addAppModal">
        <div class="modal-dialog">
            <form method="POST" action="index.php?page=googleplay_add_manual">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Aplikasi Manual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label>Nama Aplikasi</label>
                        <input type="text" name="name" class="form-control" required>
                        <label>Package Name</label>
                        <input type="text" name="package_name" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>