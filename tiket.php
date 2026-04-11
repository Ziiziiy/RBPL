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
    <title>Tiket Antrian</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/tiket.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar top-bar-tiket">
        <h1>🎫 Tiket Antrian</h1>
    </div>

    <div class="content">
        <div class="card card-tiket">
            <div class="tiket-number">
                <div class="label">Nomor Antrian</div>
                <div class="number"><?= $tiket['nomor_antrian'] ?></div>
                <div class="selesai">🕐 Selesai sekitar <?= $tiket['estimasi_selesai'] ?></div>
            </div>

            <div class="detail-row">
                <span class="label">👤 Nama Pelanggan</span>
                <span class="value"><?= htmlspecialchars($tiket['nama']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">⚖️ Berat Padi</span>
                <span class="value"><?= number_format($tiket['berat'], 1) ?> kg</span>
            </div>
            <div class="detail-row">
                <span class="label">🕐 Estimasi Waktu</span>
                <span class="value"><?= $tiket['estimasi_menit'] ?> menit</span>
            </div>
            <div class="detail-row">
                <span class="label">🆔 ID Pesanan</span>
                <span class="value" style="font-size:12px;"><?= $tiket['order_id'] ?></span>
            </div>
            <div class="divider"></div>
            <div class="detail-row">
                <span class="value">Total Dibayar</span>
                <span class="value-orange" style="font-size:18px;">Rp <?= number_format($tiket['total'], 0, ',', '.') ?></span>
            </div>

            <div class="info-box info-box-blue" style="margin-top:14px;">
                <strong>Catatan:</strong><br>
                Harap datang kembali sesuai estimasi waktu yang tertera. Anda dapat melihat status antrian pada menu Status Antrian.
            </div>
        </div>

        <div class="btn-row" style="margin-bottom:12px;">
            <button onclick="window.print()" class="btn btn-gray">🖨️ Cetak Struk</button>
            <a href="status_antrian.php" class="btn btn-blue">👁️ Lihat Status</a>
        </div>

        <a href="index.php" class="btn btn-orange">Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>
