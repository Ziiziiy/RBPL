<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

$staff    = $_SESSION['staff'];
$db       = getDB();
$hari_ini = date('Y-m-d');

$total_pesanan    = $db->query("
    SELECT COUNT(*) FROM pesanan
    WHERE DATE(waktu_pesan) = '$hari_ini'
")->fetchColumn();

$total_selesai    = $db->query("
    SELECT COUNT(*) FROM pesanan
    WHERE DATE(waktu_pesan) = '$hari_ini'
      AND status IN ('selesai','diambil')
")->fetchColumn();

$total_antrian    = $db->query("
    SELECT COUNT(*) FROM pesanan
    WHERE DATE(waktu_pesan) = '$hari_ini'
      AND status IN ('antrian','proses')
")->fetchColumn();

$total_pendapatan = $db->query("
    SELECT COALESCE(SUM(total_bayar), 0) FROM pesanan
    WHERE DATE(waktu_pesan) = '$hari_ini'
      AND status IN ('selesai','diambil')
")->fetchColumn();

$total_berat      = $db->query("
    SELECT COALESCE(SUM(berat_padi), 0) FROM pesanan
    WHERE DATE(waktu_pesan) = '$hari_ini'
")->fetchColumn();

$bulan_ini        = date('Y-m');
$pendapatan_bulan = $db->query("
    SELECT COALESCE(SUM(total_bayar), 0) FROM pesanan
    WHERE strftime('%Y-%m', waktu_pesan) = '$bulan_ini'
      AND status IN ('selesai','diambil')
")->fetchColumn();

$antrian_aktif_now = $db->query("
    SELECT COUNT(*) FROM pesanan
    WHERE status IN ('antrian','proses')
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner — Penggilingan Padi BangunRejo</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/owner.css">
</head>
<body>
<div class="app-wrapper">

    <div class="top-bar">
        <div class="topbar-row">
            <div>
                <h1>🌾 Dashboard Owner</h1>
                <div class="subtitle">Selamat datang, <?= htmlspecialchars($staff['nama']) ?></div>
            </div>
            <a href="logout.php" class="logout-btn">Logout →</a>
        </div>
        <div class="topbar-date"><?= date('l, d F Y') ?></div>
    </div>

    <div class="content">

        <div class="stats-section">
            <div class="stats-label">📈 Statistik Hari Ini</div>

            <div class="stats-trio">
                <div class="stat-box">
                    <div class="stat-num"><?= $total_pesanan ?></div>
                    <div class="stat-lbl">Total Pesanan</div>
                </div>
                <div class="stat-box stat-box-green">
                    <div class="stat-num"><?= $total_selesai ?></div>
                    <div class="stat-lbl">Selesai</div>
                </div>
                <div class="stat-box stat-box-orange">
                    <div class="stat-num"><?= $total_antrian ?></div>
                    <div class="stat-lbl">Antrian</div>
                </div>
            </div>

            <div class="info-row">
                <span class="info-row-label">⚖️ Total Berat Padi Hari Ini</span>
                <span class="info-row-value"><?= number_format($total_berat, 1) ?> kg</span>
            </div>

            <?php if ($antrian_aktif_now > 0): ?>
            <div class="info-row info-row-live">
                <span class="info-row-label">🔴 Antrian Aktif Sekarang</span>
                <span class="info-row-value"><?= $antrian_aktif_now ?> pesanan</span>
            </div>
            <?php endif; ?>

            <div class="pendapatan-box">
                <div class="pendapatan-label">💰 Pendapatan Hari Ini</div>
                <div class="pendapatan-value">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                <div class="pendapatan-note">Dari <?= $total_selesai ?> pesanan selesai/diambil</div>
            </div>

            <div class="info-row">
                <span class="info-row-label">📅 Pendapatan Bulan Ini</span>
                <span class="info-row-value">Rp <?= number_format($pendapatan_bulan, 0, ',', '.') ?></span>
            </div>
        </div>

        <a href="laporan.php" class="menu-item">
            <div class="menu-icon icon-blue">📄</div>
            <div class="menu-item-text">
                <h3>Laporan Transaksi</h3>
                <p>Lihat, filter & export laporan harian</p>
            </div>
            <span class="menu-chevron">›</span>
        </a>

        <a href="kelola_tarif.php" class="menu-item">
            <div class="menu-icon icon-purple">⚙️</div>
            <div class="menu-item-text">
                <h3>Kelola Tarif</h3>
                <p>Update tarif jasa penggilingan per kg</p>
            </div>
            <span class="menu-chevron">›</span>
        </a>

        <a href="log_aktivitas.php" class="menu-item">
            <div class="menu-icon icon-green">📊</div>
            <div class="menu-item-text">
                <h3>Log Aktivitas</h3>
                <p>Monitor aktivitas semua user</p>
            </div>
            <span class="menu-chevron">›</span>
        </a>

        <div class="card">
            <div class="akses-cepat-title">⚡ Akses Cepat</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="status_antrian.php" class="btn btn-blue btn-block">👁️ Lihat Status Antrian</a>
                <a href="index.php" class="btn btn-ghost btn-block">🖥️ Mode Kiosk Pelanggan</a>
            </div>
        </div>

    </div>
</div>
</body>
</html>
