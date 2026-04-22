<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

$staff = $_SESSION['staff'];
$db    = getDB();
$pesan = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harga = floatval($_POST['harga_per_kg'] ?? 0);
    if ($harga <= 0) {
        $error = 'Harga per kg harus lebih dari 0!';
    } else {
        $db->exec("DELETE FROM tarif");
        $stmt = $db->prepare("INSERT INTO tarif (harga_per_kg) VALUES (?)");
        $stmt->execute([$harga]);
        simpanLog($staff['username'], $staff['role'], 'Update Tarif', "Tarif baru: Rp {$harga}/kg");
        $pesan = "Tarif berhasil diubah menjadi Rp " . number_format($harga, 0, ',', '.') . "/kg!";
    }
}

$tarif = getTarif();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tarif</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/kelola_tarif.css">
    <script>(function(){var s=localStorage.getItem("rbpl-theme");var p=window.matchMedia&&window.matchMedia("(prefers-color-scheme:dark)").matches?"dark":"light";document.documentElement.setAttribute("data-theme",s||p);})();</script>
</head>
<body>
<div class="app-wrapper layout-sidebar">

    <!-- ── SIDEBAR ── -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">🌾</div>
            <div class="sidebar-name">Penggilingan Padi</div>
            <div class="sidebar-sub">BangunRejo</div>
        </div>
        <div class="sidebar-nav">
            <a href="owner.php" class="sidebar-link ">
                <span class="sidebar-icon">📈</span> Dashboard
            </a>
            <a href="laporan.php" class="sidebar-link ">
                <span class="sidebar-icon">📄</span> Laporan
            </a>
            <a href="kelola_tarif.php" class="sidebar-link active">
                <span class="sidebar-icon">⚙️</span> Kelola Tarif
            </a>
            <a href="log_aktivitas.php" class="sidebar-link ">
                <span class="sidebar-icon">📊</span> Log Aktivitas
            </a>
            <a href="status_antrian.php" class="sidebar-link">
                <span class="sidebar-icon">👁️</span> Status Antrian
            </a>
            <a href="index.php" class="sidebar-link">
                <span class="sidebar-icon">🖥️</span> Mode Kiosk
            </a>
        </div>
        <div class="sidebar-sep"></div>
        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-link">
                <span class="sidebar-icon">🚪</span> Logout
            </a>
        </div>
    </nav>
    <!-- ── MAIN AREA ── -->
    <div class="main-area">

    <div class="top-bar">
        <a href="owner.php" class="back-btn">← Kembali</a>
        <h1>⚙️ Kelola Tarif Jasa</h1>
        <div class="subtitle">Update harga jasa penggilingan per kilogram</div>
    </div>

    <div class="content">
        <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 class="tarif-title">Tarif Penggilingan</h3>
            <p class="tarif-desc">Harga tetap per kilogram untuk jasa penggilingan padi</p>

            <form method="POST">
                <div class="form-group">
                    <label>Harga per Kilogram</label>
                    <div class="tarif-input-row">
                        <span class="rp-label">Rp</span>
                        <input type="number" name="harga_per_kg"
                               value="<?= $tarif ?>" placeholder="500"
                               min="1" step="50" id="inputHarga"
                               oninput="updatePreview(this.value)" required>
                    </div>
                    <p class="tarif-note">Tarif ini akan berlaku untuk semua jenis penggilingan</p>
                </div>

                <div class="preview-box">
                    <h4>Preview Tarif</h4>
                    <div class="detail-row" style="border:none;">
                        <span class="label">Tarif Penggilingan</span>
                        <span class="value-orange" id="previewTarif">Rp <?= number_format($tarif, 0, ',', '.') ?> / kg</span>
                    </div>
                </div>

                <div class="contoh-box">
                    <h4>Contoh Perhitungan</h4>
                    <div class="detail-row"><span class="label">50 kg padi:</span><span class="value" id="calc50">-</span></div>
                    <div class="detail-row"><span class="label">100 kg padi:</span><span class="value" id="calc100">-</span></div>
                    <div class="detail-row" style="border:none;"><span class="label">200 kg padi:</span><span class="value" id="calc200">-</span></div>
                </div>

                <button type="submit" class="btn btn-orange">💾 Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<script>
function formatRupiah(num) {
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
function updatePreview(val) {
    var h = parseFloat(val) || 0;
    document.getElementById('previewTarif').textContent = formatRupiah(h) + ' / kg';
    document.getElementById('calc50').textContent  = formatRupiah(50  * h);
    document.getElementById('calc100').textContent = formatRupiah(100 * h);
    document.getElementById('calc200').textContent = formatRupiah(200 * h);
}
updatePreview(<?= $tarif ?>);
</script>
<script src="js/theme.js"></script>
</body>
</html>
