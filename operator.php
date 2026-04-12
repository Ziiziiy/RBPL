<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'operator') {
    header('Location: login.php');
    exit;
}

$staff = $_SESSION['staff'];
$db    = getDB();
$tab   = $_GET['tab'] ?? 'antrian';

$antrian = $db->query("SELECT * FROM pesanan WHERE status = 'antrian' ORDER BY nomor_antrian ASC")->fetchAll(PDO::FETCH_ASSOC);
$proses  = $db->query("SELECT * FROM pesanan WHERE status = 'proses'  ORDER BY nomor_antrian ASC")->fetchAll(PDO::FETCH_ASSOC);
$selesai = $db->query("SELECT * FROM pesanan WHERE status IN ('selesai','diambil') ORDER BY waktu_selesai DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

$jml_antrian = count($antrian);
$jml_proses  = count($proses);
$jml_selesai = count($selesai);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operator</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/operator.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar">
        <div class="topbar-row">
            <div>
                <h1>🌾 Dashboard Operator</h1>
                <div class="subtitle"><?= htmlspecialchars($staff['nama']) ?></div>
            </div>
            <a href="logout.php" class="logout-btn">→ Logout</a>
        </div>
        <div class="waktu-display">
            <span>🕐 Waktu Saat Ini</span>
            <span id="jamSekarang"><?= date('H.i.s') ?></span>
        </div>
    </div>

    <div class="content">
        <div class="stat-grid">
            <div class="stat-card stat-blue">
                <div class="stat-num"><?= $jml_antrian ?></div>
                <div class="stat-label">Antrian</div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-num"><?= $jml_proses ?></div>
                <div class="stat-label">Proses</div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-num"><?= $jml_selesai ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>

        <div class="tabs">
            <a href="?tab=antrian" class="tab <?= $tab === 'antrian' ? 'active' : '' ?>">Antrian</a>
            <a href="?tab=proses"  class="tab <?= $tab === 'proses'  ? 'active' : '' ?>">Proses</a>
            <a href="?tab=selesai" class="tab <?= $tab === 'selesai' ? 'active' : '' ?>">Selesai</a>
        </div>

        <div class="section-title">
            <?php
            if ($tab === 'antrian')     echo 'Daftar Antrian';
            elseif ($tab === 'proses')  echo 'Sedang Diproses';
            else                        echo 'Selesai';
            ?>
            <a href="operator.php?tab=<?= $tab ?>" class="refresh-btn-content">🔄 Refresh</a>
        </div>

        <?php if ($tab === 'antrian'): ?>
            <?php if (empty($antrian)): ?>
            <div class="empty-state card"><div class="empty-icon">📭</div><p>Tidak ada antrian</p></div>
            <?php else: ?>
            <?php foreach ($antrian as $item): ?>
            <div class="card operator-card">
                <div class="operator-card-header">
                    <div class="operator-card-header-left">
                        <div class="antrian-number-badge antrian-badge-sm">#<?= $item['nomor_antrian'] ?></div>
                        <span class="badge badge-antrian">Dalam Antrian</span>
                    </div>
                    <a href="proses_penggilingan.php?id=<?= $item['id'] ?>" class="btn btn-orange btn-sm">▶ Proses</a>
                </div>
                <div class="operator-card-body">
                    <div>👤 <?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                    <div>⚖️ <?= number_format($item['berat_padi'], 1) ?> kg</div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        <?php elseif ($tab === 'proses'): ?>
            <?php if (empty($proses)): ?>
            <div class="empty-state card"><div class="empty-icon">⚙️</div><p>Tidak ada yang sedang diproses</p></div>
            <?php else: ?>
            <?php foreach ($proses as $item): ?>
            <div class="card operator-card">
                <div class="operator-card-header">
                    <div class="operator-card-header-left">
                        <div class="antrian-number-badge antrian-badge-sm">#<?= $item['nomor_antrian'] ?></div>
                        <span class="badge badge-proses">Sedang Diproses</span>
                    </div>
                    <a href="proses_penggilingan.php?id=<?= $item['id'] ?>" class="btn btn-gray btn-sm">Lihat</a>
                </div>
                <div class="operator-card-body">
                    <div>👤 <?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                    <div>⚖️ <?= number_format($item['berat_padi'], 1) ?> kg</div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <?php if (empty($selesai)): ?>
            <div class="empty-state card"><div class="empty-icon">✅</div><p>Belum ada yang selesai</p></div>
            <?php else: ?>
            <?php foreach ($selesai as $item): ?>
            <div class="card operator-card">
                <div class="operator-card-header">
                    <div class="operator-card-header-left">
                        <div class="antrian-number-badge antrian-badge-sm antrian-number-badge-green">#<?= $item['nomor_antrian'] ?></div>
                        <span class="badge badge-selesai">Selesai</span>
                    </div>
                </div>
                <div class="operator-card-body">
                    <div>👤 <?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                    <div>⚖️ <?= number_format($item['berat_padi'], 1) ?> kg padi</div>
                    <?php if ($item['hasil_beras'] > 0): ?>
                    <div class="hasil-text">
                        Hasil: <?= number_format($item['hasil_beras'], 1) ?> kg beras, <?= number_format($item['hasil_dedak'], 1) ?> kg dedak
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
setInterval(function() {
    var now = new Date();
    var j = String(now.getHours()).padStart(2,'0');
    var m = String(now.getMinutes()).padStart(2,'0');
    var s = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('jamSekarang').textContent = j+'.'+m+'.'+s;
}, 1000);
</script>
</body>
</html>
