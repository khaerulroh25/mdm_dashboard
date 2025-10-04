<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<h2>Google Play App Search</h2>
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
    
    <p>Cari aplikasi di Google Play Store dan tambahkan ke database</p>
    <hr>
<form method="GET" action="index.php">
    <input type="hidden" name="page" value="googleplay_search">
    <div class="input-group mb-3">
        <input type="text" name="q" class="form-control"
               placeholder="Cari aplikasi di Play Store..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button class="btn btn-outline-primary" type="submit">Cari</button>
    </div>
    </form>

   <?php if (!empty($results)): ?>
    <h4>Hasil Pencarian</h4>
    <div class="container">
        <div class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
            <?php foreach ($results as $result): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="<?= $result['icon'] ?>" width="40" class="me-2">
                        <div>
                            <strong><?= htmlspecialchars($result['app_name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($result['package_name']) ?></small>
                        </div>
                    </div>
                    <form method="POST" action="index.php?page=googleplay_added" class="mb-0">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($result['app_name']) ?>">
                        <input type="hidden" name="package_name" value="<?= htmlspecialchars($result['package_name']) ?>">
                        <button type="submit" class="btn btn-sm btn-success">Tambahkan</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

 
<?php include __DIR__ . '/layout/footer.php'; ?>