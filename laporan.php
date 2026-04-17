<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

$db            = getDB();
$cari          = trim($_GET['cari'] ?? '');
$tanggal       = $_GET['tanggal'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = []; $params = [];
if (!empty($cari)) {
    $where[] = "(nama_pelanggan LIKE ? OR order_id LIKE ? OR nomor_telepon LIKE ?)";
    $params  = array_merge($params, ["%$cari%", "%$cari%", "%$cari%"]);
}
if (!empty($tanggal)) { $where[] = "DATE(waktu_pesan) = ?"; $params[] = $tanggal; }
if (!empty($status_filter)) { $where[] = "status = ?"; $params[] = $status_filter; }

$sql  = "SELECT * FROM pesanan" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY waktu_pesan DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_berat      = array_sum(array_column($transaksi, 'berat_padi'));
$total_pendapatan = array_sum(array_column($transaksi, 'total_bayar'));
$total_selesai    = count(array_filter($transaksi, fn($t) => in_array($t['status'], ['selesai','diambil'])));

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_' . date('Ymd') . '.csv');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['ID Pesanan','Nomor Antrian','Nama','Telepon','Berat (kg)','Total Bayar','Metode','Status','Tanggal']);
    foreach ($transaksi as $t) {
        fputcsv($out, [$t['order_id'],$t['nomor_antrian'],$t['nama_pelanggan'],$t['nomor_telepon'],
                       $t['berat_padi'],$t['total_bayar'],$t['metode_bayar'],$t['status'],$t['waktu_pesan']]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/laporan.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar">
        <a href="owner.php" class="back-btn">← Kembali</a>
        <h1>📄 Laporan Transaksi</h1>
        <div class="subtitle">Filter dan export data transaksi</div>
    </div>

    <div class="content">
        <!-- Ringkasan -->
        <div class="card card-ringkasan">
            <h3 class="ringkasan-title">Ringkasan</h3>
            <div class="stat-grid stat-grid-2">
                <div class="stat-card stat-blue">
                    <div class="stat-num"><?= count($transaksi) ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-num"><?= $total_selesai ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
            </div>
            <div class="box-berat">
                <div class="lbl">Total Berat Padi</div>
                <div class="val"><?= number_format($total_berat, 1) ?> kg</div>
            </div>
            <div class="box-pendapatan">
                <div class="lbl">Total Pendapatan</div>
                <div class="val">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card card-filter">
            <h3 class="filter-title">🔍 Filter & Pencarian</h3>
            <form method="GET">
                <div class="form-group">
                    <label>Cari Pelanggan / ID</label>
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="cari"
                               placeholder="Nama, ID pesanan, atau nomor telepon"
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="filter-row">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $tanggal ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="antrian" <?= $status_filter === 'antrian' ? 'selected':'' ?>>Antrian</option>
                            <option value="proses"  <?= $status_filter === 'proses'  ? 'selected':'' ?>>Proses</option>
                            <option value="selesai" <?= $status_filter === 'selesai' ? 'selected':'' ?>>Selesai</option>
                            <option value="diambil" <?= $status_filter === 'diambil' ? 'selected':'' ?>>Diambil</option>
                        </select>
                    </div>
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn btn-orange">🔍 Cari</button>
                    <a href="laporan.php" class="btn btn-gray">Reset Filter</a>
                </div>
            </form>
        </div>

        <!-- Export & Cetak -->
        <div class="btn-row" style="margin-bottom:16px;">
            <a href="laporan.php?<?= http_build_query(['cari'=>$cari,'tanggal'=>$tanggal,'status'=>$status_filter,'export'=>1]) ?>"
               class="btn btn-green">⬇️ Export CSV</a>
            <button onclick="window.print()" class="btn btn-blue">🖨️ Cetak</button>
        </div>

        <div class="section-title">Daftar Transaksi (<?= count($transaksi) ?>)</div>

        <?php if (empty($transaksi)): ?>
        <div class="empty-state card"><div class="empty-icon">📭</div><p>Tidak ada data transaksi</p></div>
        <?php else: ?>
        <?php foreach ($transaksi as $t):
            $bc = 'badge-antrian';
            if ($t['status'] === 'proses')  $bc = 'badge-proses';
            if ($t['status'] === 'selesai') $bc = 'badge-selesai';
            if ($t['status'] === 'diambil') $bc = 'badge-selesai';
        ?>
        <div class="transaksi-item">
            <div class="trans-header">
                <div class="trans-header-left">
                    <div class="antrian-number-badge antrian-badge-xs">#<?= $t['nomor_antrian'] ?></div>
                    <span class="badge <?= $bc ?>"><?= ucfirst($t['status']) ?></span>
                </div>
                <span class="trans-harga">Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></span>
            </div>
            <p class="trans-waktu"><?= date('j/n/Y, H.i', strtotime($t['waktu_pesan'])) ?></p>
            <div class="trans-body">
                <div class="detail-row" style="padding:3px 0;">
                    <span class="label">Pelanggan</span>
                    <span class="value"><?= htmlspecialchars($t['nama_pelanggan']) ?></span>
                </div>
                <div class="detail-row" style="padding:3px 0;">
                    <span class="label">Telepon</span>
                    <span class="value"><?= htmlspecialchars($t['nomor_telepon']) ?></span>
                </div>
                <div class="detail-row" style="padding:3px 0; border:none;">
                    <span class="label">Berat Padi</span>
                    <span class="value"><?= number_format($t['berat_padi'], 1) ?> kg</span>
                </div>
            </div>
            <p class="trans-id">ID: <?= $t['order_id'] ?></p>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
