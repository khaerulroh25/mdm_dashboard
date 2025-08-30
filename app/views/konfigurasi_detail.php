<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<div class="container mt-4">
    <div class="card p-3 shadow-sm">
        <h4 class="mb-3">📋 Detail Policy</h4>

        <?php
        // Mapping beberapa field penting
        $fields = [
            "cameraDisabled"       => "Nonaktifkan Kamera",
            "wifiConfigDisabled"   => "Nonaktifkan Konfigurasi Wi-Fi",
            "factoryResetDisabled" => "Nonaktifkan Factory Reset",
            "installAppsDisabled"  => "Nonaktifkan Install Aplikasi",
            "safeBootDisabled"     => "Nonaktifkan Recovery Mode",
            "locationMode"         => "Mode Lokasi",
        ];
?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Fitur</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $field => $label): ?>
                    <tr>
                        <td><?= $label ?></td>
                        <td>
                            <?php
                    $value = $policy->$field ?? null;
                    if (is_bool($value)) {
                        echo $value ? "Aktif" : "Nonaktif";
                    } elseif ($value === null) {
                        echo "Default (Tidak Diatur)";
                    } else {
                        echo htmlspecialchars($value);
                    }
                    ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="index.php?page=konfigurasi" class="btn btn-secondary mt-3">⬅️ Kembali</a>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
