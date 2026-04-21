<?php
session_start();
require_once 'database.php';

$db       = getDB();
$hari_ini = date('Y-m-d');

$antrian_aktif  = $db->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('antrian','proses') AND DATE(waktu_pesan) = '$hari_ini'")->fetchColumn();

$nomor_terakhir = $db->query("SELECT MAX(nomor_antrian) FROM pesanan WHERE DATE(waktu_pesan) = '$hari_ini'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penggilingan Padi BangunRejo</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="app-wrapper">

    <div class="kiosk-header">
        <div class="kiosk-logo">🌾</div>
        <h1>Penggilingan Padi</h1>
        <p>Sistem Antrian Digital BangunRejo</p>
        <div class="kiosk-date"><?= date('l, d F Y') ?></div>
    </div>

    <div class="content">

        <div class="card-antrian-hero">
            <div class="antrian-label">Antrian Terakhir Hari Ini</div>
            <div class="antrian-number"><?= $nomor_terakhir ?: '—' ?></div>
            <div class="antrian-keterangan">
                <?php if ($antrian_aktif > 0): ?>
                    <?= $antrian_aktif ?> antrian aktif saat ini
                <?php else: ?>
                    Tidak ada antrian aktif
                <?php endif; ?>
            </div>
        </div>

        <a href="buat_pesanan.php" class="menu-item">
            <div class="menu-icon icon-orange">📋</div>
            <div class="menu-text">
                <h3>Buat Pesanan Baru</h3>
                <p>Mulai proses penggilingan padi Anda</p>
            </div>
            <span class="menu-chevron">›</span>
        </a>

        <a href="status_antrian.php" class="menu-item">
            <div class="menu-icon icon-blue">🕐</div>
            <div class="menu-text">
                <h3>Cek Status Antrian</h3>
                <p>Lihat status dan estimasi waktu selesai</p>
            </div>
            <span class="menu-chevron">›</span>
        </a>

        <a href="ambil_hasil.php" class="menu-item">
            <div class="menu-icon icon-green">📦</div>
            <div class="menu-text">
                <h3>Ambil Hasil</h3>
                <p>Konfirmasi pengambilan hasil penggilingan</p>
            </div>
            <span class="menu-chevron">›</span>
        </a>

        <a href="login.php" class="btn btn-navy btn-block">👤 Login Staff</a>

    </div>
</div>
</body>
</html>
