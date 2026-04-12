<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

$staff   = $_SESSION['staff'];
$db      = getDB();
$hari_ini = date('Y-m-d');

$total_pesanan   = $db->query("SELECT COUNT(*) FROM pesanan WHERE DATE(waktu_pesan) = '$hari_ini'")->fetchColumn();
$total_selesai   = $db->query("SELECT COUNT(*) FROM pesanan WHERE DATE(waktu_pesan) = '$hari_ini' AND status IN ('selesai','diambil')")->fetchColumn();
$total_pendapatan = $db->query("SELECT SUM(total_bayar) FROM pesanan WHERE DATE(waktu_pesan) = '$hari_ini'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/owner.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar">
        <div class="topbar-row">
            <div>
                <h1>🌾 Dashboard Owner</h1>
                <div class="subtitle"><?= htmlspecialchars($staff['nama']) ?></div>
            </div>
            <a href="logout.php" class="logout-btn">→ Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="owner-stats">
            <h3>📈 Statistik Hari Ini</h3>
            <div class="owner-stats-grid">
                <div class="owner-stat-item">
                    <div class="val"><?= $total_pesanan ?></div>
                    <div class="lbl">Total Pesanan</div>
                </div>
                <div class="owner-stat-item">
                    <div class="val"><?= $total_selesai ?></div>
                    <div class="lbl">Selesai</div>
                </div>
            </div>
            <div class="owner-pendapatan">
                <div class="lbl">Total Pendapatan</div>
                <div class="val">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
            </div>
        </div>

        <a href="laporan.php" class="menu-item">
            <div class="menu-icon icon-blue">📄</div>
            <div><h3>Laporan Transaksi</h3><p>Lihat dan export laporan harian</p></div>
        </a>

        <a href="kelola_tarif.php" class="menu-item">
            <div class="menu-icon icon-purple">⚙️</div>
            <div><h3>Kelola Tarif</h3><p>Update tarif jasa penggilingan</p></div>
        </a>

        <a href="log_aktivitas.php" class="menu-item">
            <div class="menu-icon icon-green">📊</div>
            <div><h3>Log Aktivitas</h3><p>Monitor aktivitas semua user</p></div>
        </a>

        <div class="card">
            <h3 class="akses-cepat-title">⚡ Akses Cepat</h3>
            <a href="status_antrian.php" class="btn btn-white btn-akses-cepat">Lihat Status Antrian</a>
            <a href="index.php" class="btn btn-gray">Mode Kiosk Pelanggan</a>
        </div>
    </div>
</div>
</body>
</html>
