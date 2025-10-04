<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<div class="content">
    <h2 class="page-title">Generate Enrollment QR</h2>

    <div class="card">
        <form method="POST" action="index.php?page=enroll">
            <div class="mb-3">
                <label for="policy" class="form-label">Pilih Policy</label>
                <select name="policy" id="policy" class="form-select" required>
                    <?php foreach ($policies as $p): ?>
                        <?php $id = basename($p->getName()); ?>
                        <option value="<?= htmlspecialchars($id) ?>">
                            <?= htmlspecialchars($id) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Generate QR</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
