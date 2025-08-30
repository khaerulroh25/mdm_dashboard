<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<?php require_once __DIR__ . '/../helpers/qrHelper.php'; ?>

<div class="content">
    <h2 class="page-title">Enrollment QR Code</h2>

    <div class="card">
        <?php if (!empty($token['qrCode'])): ?>
            <p>Scan QR berikut untuk enroll device:</p>
            
            <div class="qr-box">
                <img src="<?php echo QrHelper::generate($token['qrCode']); ?>" alt="Enrollment QR Code">
            </div>

            <div class="info">
                <p><strong>Policy Applied:</strong> <?php echo htmlspecialchars($token['policyName']); ?></p>
                <?php if (!empty($expiration)): ?>
                <p><strong>Berlaku sampai:</strong> <?= date('Y-m-d H:i:s', strtotime($expiration)) ?> UTC</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="error">⚠️ Gagal membuat enrollment token.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
