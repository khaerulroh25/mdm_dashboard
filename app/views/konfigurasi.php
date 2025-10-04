<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<div class="container mt-4">
    <div class="form-container">
        <h4 class="mb-3">⚙️ Konfigurasi Policy</h4>

        <!-- Flash message -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert success"><?= $_SESSION['flash_success'];
            unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert error"><?= $_SESSION['flash_error'];
            unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_info'])): ?>
            <div class="alert info"><?= $_SESSION['flash_info'];
            unset($_SESSION['flash_info']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=konfigurasi_action" class="form-container">
            <!-- Dropdown pilih policy -->
        <div class="mb-3">
         <label for="policy_name" class="form-label">Pilih Policy</label>
            <select name="policy_name" id="policy_name" class="form-select" required>
              <option value="">-- Pilih Policy --</option>
                  <?php if (!empty($policies)): ?>
                  <?php foreach ($policies as $policy): ?>
                      <option value="<?= htmlspecialchars($policy->getName()) ?>">
                    <?= basename($policy->getName()) ?>
                     </option>
                 <?php endforeach; ?>
                <?php endif; ?>
         </select>
        </div>

            <!-- Checklist konfigurasi -->
            <div class="form-check">
                <input type="hidden" name="cameraDisabled" value="false">
                <input type="checkbox" name="cameraDisabled" value="true"> Nonaktifkan Kamera
            </div>

            <div class="form-check">
                <input type="hidden" name="wifiConfigDisabled" value="false">
                <input type="checkbox" name="wifiConfigDisabled" value="true"> Nonaktifkan Konfigurasi Wi-Fi
            </div>

            <div class="form-check">
                <input type="hidden" name="factoryResetDisabled" value="false">
                <input type="checkbox" name="factoryResetDisabled" value="true"> Nonaktifkan Factory Reset
            </div>

            <div class="form-check">
                <input type="hidden" name="locationMode" value="">
                <input type="checkbox" name="locationMode" value="HIGH_ACCURACY"> Aktifkan Lokasi Akurasi Tinggi
            </div>

            <div class="form-check">
                <input type="hidden" name="installAppsDisabled" value="false">
                <input type="checkbox" name="installAppsDisabled" value="true"> Nonaktifkan Install Aplikasi
            </div>

            <div class="form-check">
                <input type="hidden" name="safeBootDisabled" value="false">
                <input type="checkbox" name="safeBootDisabled" value="true"> Nonaktifkan recovery Mode
            </div>

            <!-- Tombol -->
           
          <div class="mt-3 d-flex justify-content-between align-items-center" style="gap:10px;">
         <!-- Kiri -->
          <div class="d-flex" style="gap:10px;">
              <button type="submit" formaction="index.php?page=konfigurasi_detail" class="btn btn-secondary">🔍 Detail</button>
              <button type="submit" formaction="index.php?page=konfigurasi_update" class="btn btn-primary">💾 Update</button>
          </div>
         <button type="submit" formaction="index.php?page=konfigurasi_delete" class="btn btn-danger">🗑 Hapus</button>
        </div>  
        </form>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
