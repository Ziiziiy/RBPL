<?php
session_start();
require_once 'database.php';

$db     = getDB();
$pesanan = null;
$pesan  = '';
$error  = '';
$cari   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari'])) {
    $cari = trim($_POST['cari']);
    if (!empty($cari)) {
        $stmt = $db->prepare("SELECT * FROM pesanan WHERE nomor_antrian = ? OR order_id = ? LIMIT 1");
        $stmt->execute([$cari, $cari]);
        $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pesanan) $error = 'Pesanan tidak ditemukan. Periksa kembali nomor antrian atau ID pesanan Anda.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_order_id'])) {
    $order_id = $_POST['konfirmasi_order_id'];
    $stmt = $db->prepare("UPDATE pesanan SET status = 'diambil', waktu_selesai = CURRENT_TIMESTAMP WHERE order_id = ? AND status = 'selesai'");
    $stmt->execute([$order_id]);
    if ($stmt->rowCount() > 0) {
        $pesan  = 'Berhasil! Hasil penggilingan telah dikonfirmasi pengambilannya. Terima kasih!';
        $pesanan = null;
        $cari   = '';
    } else {
        $error = 'Konfirmasi gagal. Pastikan status pesanan sudah "Siap Diambil".';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Hasil Penggilingan</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/ambil_hasil.css">
    <script>(function(){var s=localStorage.getItem("rbpl-theme");var p=window.matchMedia&&window.matchMedia("(prefers-color-scheme:dark)").matches?"dark":"light";document.documentElement.setAttribute("data-theme",s||p);})();</script>
</head>
<body>
<div class="app-wrapper layout-kiosk">
    <div class="top-bar">
        <a href="index.php" class="back-btn">← Kembali</a>
        <h1>📦 Ambil Hasil Penggilingan</h1>
    </div>

    <div class="content">
        <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Form pencarian -->
        <div class="card">
            <div class="search-header">
                <span class="search-header-icon">📦</span>
                <div>
                    <h3 class="search-header-title">Ambil Hasil Penggilingan</h3>
                    <p class="search-header-desc">Masukkan nomor antrian atau ID pesanan untuk mengambil hasil</p>
                </div>
            </div>
            <div class="divider"></div>
            <form method="POST">
                <div class="form-group">
                    <label>Nomor Antrian / ID Pesanan</label>
                    <div class="search-ambil">
                        <div class="input-wrapper">
                            <span class="search-icon search-icon-abs">🔍</span>
                            <input type="text" name="cari"
                                   placeholder="Contoh: 123 atau ORD123456"
                                   value="<?= htmlspecialchars($cari) ?>">
                        </div>
                        <button type="submit" class="btn btn-orange btn-sm">Cari</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Hasil pencarian -->
        <?php if ($pesanan): ?>
        <div class="card <?= $pesanan['status'] === 'selesai' ? 'card-hasil-selesai' : 'card-hasil-proses' ?>">
            <div class="hasil-card-header">
                <h3 class="hasil-card-title">Detail Pesanan</h3>
                <?php if ($pesanan['status'] === 'selesai'): ?>
                    <span class="badge badge-selesai">✅ Siap Diambil</span>
                <?php elseif ($pesanan['status'] === 'antrian'): ?>
                    <span class="badge badge-antrian">Dalam Antrian</span>
                <?php elseif ($pesanan['status'] === 'proses'): ?>
                    <span class="badge badge-proses">Sedang Diproses</span>
                <?php elseif ($pesanan['status'] === 'diambil'): ?>
                    <span class="badge badge-diambil">Sudah Diambil</span>
                <?php endif; ?>
            </div>

            <div class="tiket-number-sm">
                <div class="label">Nomor Antrian</div>
                <div class="number"><?= $pesanan['nomor_antrian'] ?></div>
            </div>

            <div class="detail-row">
                <span class="label">👤 Nama Pelanggan</span>
                <span class="value"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">⚖️ Berat Padi Awal</span>
                <span class="value"><?= number_format($pesanan['berat_padi'], 1) ?> kg</span>
            </div>

            <?php if (in_array($pesanan['status'], ['selesai', 'diambil'])): ?>
            <div class="hasil-box">
                <h4>🌾 Hasil Penggilingan</h4>
                <div class="detail-row" style="border:none; padding:4px 0;">
                    <span class="label">⚖️ Beras</span>
                    <span class="value-green"><?= number_format($pesanan['hasil_beras'], 1) ?> kg</span>
                </div>
                <div class="detail-row" style="border:none; padding:4px 0;">
                    <span class="label">⚖️ Dedak</span>
                    <span class="value-green"><?= number_format($pesanan['hasil_dedak'], 1) ?> kg</span>
                </div>
                <div class="divider"></div>
                <div class="detail-row" style="padding:4px 0;">
                    <span class="label">Total Hasil</span>
                    <span class="value"><?= number_format($pesanan['hasil_beras'] + $pesanan['hasil_dedak'], 1) ?> kg</span>
                </div>
                <div class="detail-row" style="border:none; padding:4px 0;">
                    <span class="label">Rendemen</span>
                    <span class="value">
                        <?php
                        $rendemen = $pesanan['berat_padi'] > 0
                            ? (($pesanan['hasil_beras'] + $pesanan['hasil_dedak']) / $pesanan['berat_padi']) * 100 : 0;
                        echo number_format($rendemen, 1);
                        ?>%
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="label">💰 Total Dibayar</span>
                <span class="value-orange">Rp <?= number_format($pesanan['total_bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="detail-row">
                <span class="label">💳 Metode Pembayaran</span>
                <span class="value"><?= strtoupper($pesanan['metode_bayar']) ?></span>
            </div>

            <?php if (in_array($pesanan['status'], ['antrian', 'proses'])): ?>
            <div class="alert alert-warning" style="margin-top:14px;">
                ⏳ Pesanan Anda masih dalam proses. Harap tunggu hingga status menjadi "Siap Diambil".
            </div>
            <?php elseif ($pesanan['status'] === 'selesai'): ?>
            <form method="POST" style="margin-top:14px;">
                <input type="hidden" name="konfirmasi_order_id" value="<?= $pesanan['order_id'] ?>">
                <button type="submit" class="btn btn-green"
                        onclick="return confirm('Konfirmasi pengambilan hasil penggilingan?')">
                    ✓ Konfirmasi Pengambilan
                </button>
            </form>
            <?php elseif ($pesanan['status'] === 'diambil'): ?>
            <div class="alert alert-success" style="margin-top:14px;">
                ✅ Hasil penggilingan sudah diambil. Terima kasih!
            </div>
            <?php endif; ?>

            <p class="order-id-footer">ID Pesanan: <?= $pesanan['order_id'] ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="js/theme.js"></script>
</body>
</html>
