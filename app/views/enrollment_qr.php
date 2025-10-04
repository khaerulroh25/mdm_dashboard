<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<?php require_once __DIR__ . '/../helpers/qrHelper.php'; ?>

<div class="content">
    <h2 class="page-title mb-4">Enrollment QR Code</h2>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body text-center p-4">

            <?php if (!empty($token['qrCode'])): ?>
                <p class="mb-3">Scan QR berikut untuk enroll device:</p>

                <div class="d-flex justify-content-center mb-4">
                    <div class="p-3 bg-white rounded-3 shadow-sm">
                        <img src="<?php echo QrHelper::generate($token['qrCode']); ?>" 
                             alt="Enrollment QR Code" 
                             class="img-fluid" 
                             style="max-width: 300px;">
                    </div>
                </div>

                <p class="fw-bold mb-1">
                    Policy Applied: <span class="text-muted"><?= htmlspecialchars($token['policyName']); ?></span>
                </p>

                <?php if (!empty($expiration)): ?>
                    <p class="fw-bold">
                        Berlaku sampai: <span class="text-muted"><?= date('Y-m-d H:i:s', strtotime($expiration)) ?> UTC</span>
                    </p>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-danger">
                    ⚠️ Gagal membuat enrollment token.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
