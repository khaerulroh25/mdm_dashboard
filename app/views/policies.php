<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<?php

if (isset($_SESSION['flash_success'])) {
    echo "<div class='alert success'>" . $_SESSION['flash_success'] . "</div>";
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    echo "<div class='alert error'>" . $_SESSION['flash_error'] . "</div>";
    unset($_SESSION['flash_error']);
}
?>



<div class="form-container">
    <h2>Buat Policy Baru</h2>
    <form method="post" action="index.php?page=policy_create">
    <label>Nama Policy</label>
    <input type="text" name="name" required placeholder="policy-default">
    <small>Contoh: <code>policy-default</code></small>
    <br>
    <button type="submit" class="btn">
        💾 Simpan Policy
    </button>
</form>
</div>



<?php include __DIR__ . '/layout/footer.php'; ?>