<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['tiket'])) {
    header('Location: index.php');
    exit;
}

$tiket = $_SESSION['tiket'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrian — Penggilingan Padi BangunRejo</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/tiket.css">
    <script>(function(){var s=localStorage.getItem("rbpl-theme");var p=window.matchMedia&&window.matchMedia("(prefers-color-scheme:dark)").matches?"dark":"light";document.documentElement.setAttribute("data-theme",s||p);})();</script>
</head>
<body>
<div class="app-wrapper layout-kiosk">

    <div class="top-bar top-bar-center" style="padding-bottom:20px;">
        <h1>🎫 Tiket Antrian</h1>
        <div class="subtitle">Simpan nomor antrian Anda</div>
    </div>

    <div class="content">

        <div class="card">
            <!-- Nomor antrian hero -->
            <div class="tiket-hero">
                <div class="label">Nomor Antrian Anda</div>
                <div class="number"><?= $tiket['nomor_antrian'] ?></div>
                <div class="estimasi">🕐 Selesai sekitar pukul <?= $tiket['estimasi_selesai'] ?></div>
            </div>

            <div class="divider"></div>

            <!-- Detail -->
            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">👤 Nama Pelanggan</span>
                    <span class="detail-value"><?= htmlspecialchars($tiket['nama']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">⚖️ Berat Padi</span>
                    <span class="detail-value"><?= number_format($tiket['berat'], 1) ?> kg</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🕐 Estimasi Waktu</span>
                    <span class="detail-value"><?= $tiket['estimasi_menit'] ?> menit</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">💳 Metode Bayar</span>
                    <span class="detail-value"><?= strtoupper($tiket['metode']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🆔 ID Pesanan</span>
                    <span class="detail-value font-mono text-small"><?= $tiket['order_id'] ?></span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Total -->
            <div class="total-row">
                <span class="total-label">Total Dibayar</span>
                <span class="total-value">Rp <?= number_format($tiket['total'], 0, ',', '.') ?></span>
            </div>

            <!-- Catatan -->
            <div class="info-box mt-md">
                <strong>📌 Catatan:</strong><br>
                Harap datang kembali sesuai estimasi waktu. Anda dapat memantau status antrian melalui menu Status Antrian.
            </div>
        </div>

        <!-- Action buttons -->
        <div class="btn-row">
            <button onclick="window.print()" class="btn btn-ghost">🖨️ Cetak Struk</button>
            <a href="status_antrian.php" class="btn btn-blue">👁️ Lihat Status</a>
        </div>

        <a href="index.php" class="btn btn-primary btn-block">Kembali ke Beranda</a>

    </div>
</div>
<script src="js/theme.js"></script>
</body>
</html>
