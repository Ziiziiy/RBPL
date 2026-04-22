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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_log'])) {
    $db->exec("DELETE FROM log_aktivitas");
    simpanLog($staff['username'], $staff['role'], 'Hapus Log', 'Semua log dihapus');
    $pesan = 'Semua log aktivitas berhasil dihapus.';
}

$cari        = trim($_GET['cari'] ?? '');
$tanggal     = $_GET['tanggal'] ?? '';
$role_filter = $_GET['role'] ?? '';

$where = []; $params = [];
if (!empty($cari)) {
    $where[] = "(username LIKE ? OR aksi LIKE ? OR detail LIKE ?)";
    $params  = array_merge($params, ["%$cari%", "%$cari%", "%$cari%"]);
}
if (!empty($tanggal))     { $where[] = "DATE(waktu) = ?"; $params[] = $tanggal; }
if (!empty($role_filter)) { $where[] = "role = ?";        $params[] = $role_filter; }

$sql  = "SELECT * FROM log_aktivitas" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY waktu DESC LIMIT 100";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$jml_customer = $db->query("SELECT COUNT(*) FROM log_aktivitas WHERE role = 'customer'")->fetchColumn();
$jml_operator = $db->query("SELECT COUNT(*) FROM log_aktivitas WHERE role = 'operator'")->fetchColumn();
$jml_owner    = $db->query("SELECT COUNT(*) FROM log_aktivitas WHERE role = 'owner'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/log_aktivitas.css">
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
            <a href="kelola_tarif.php" class="sidebar-link ">
                <span class="sidebar-icon">⚙️</span> Kelola Tarif
            </a>
            <a href="log_aktivitas.php" class="sidebar-link active">
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
        <div class="topbar-row-log">
            <h1>📊 Log Aktivitas</h1>
            <a href="log_aktivitas.php" class="refresh-btn">🔄</a>
        </div>
        <div class="subtitle">Monitor semua aktivitas user sistem</div>
    </div>

    <div class="content">
        <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>

        <!-- Stat per role -->
        <div class="stat-grid stat-grid-log">
            <div class="stat-card">
                <div class="stat-num stat-customer"><?= $jml_customer ?></div>
                <div class="stat-label">Customer</div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-num"><?= $jml_operator ?></div>
                <div class="stat-label">Operator</div>
            </div>
            <div class="stat-card">
                <div class="stat-num stat-owner"><?= $jml_owner ?></div>
                <div class="stat-label">Owner</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card card-filter-log">
            <h3 class="filter-log-title">🔍 Filter & Pencarian</h3>
            <form method="GET">
                <div class="form-group">
                    <label>Cari</label>
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="cari"
                               placeholder="Username, aksi, atau detail"
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="filter-row">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $tanggal ?>">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role">
                            <option value="">Semua Role</option>
                            <option value="operator" <?= $role_filter === 'operator' ? 'selected':'' ?>>Operator</option>
                            <option value="owner"    <?= $role_filter === 'owner'    ? 'selected':'' ?>>Owner</option>
                            <option value="customer" <?= $role_filter === 'customer' ? 'selected':'' ?>>Customer</option>
                        </select>
                    </div>
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn btn-orange">🔍 Filter</button>
                    <a href="log_aktivitas.php" class="btn btn-gray">Reset</a>
                </div>
            </form>
            <form method="POST" class="btn-hapus-log">
                <input type="hidden" name="hapus_log" value="1">
                <button type="submit" class="btn btn-red"
                        onclick="return confirm('Yakin ingin menghapus semua log? Tindakan ini tidak bisa dibatalkan!')">
                    🗑️ Hapus Log
                </button>
            </form>
        </div>

        <div class="section-title">Aktivitas Terbaru (<?= count($logs) ?>)</div>

        <?php if (empty($logs)): ?>
        <div class="empty-state card"><div class="empty-icon">📭</div><p>Tidak ada log aktivitas</p></div>
        <?php else: ?>
        <?php foreach ($logs as $log):
            $role_class = 'role-customer';
            if ($log['role'] === 'operator') $role_class = 'role-operator';
            if ($log['role'] === 'owner')    $role_class = 'role-owner';
        ?>
        <div class="log-item">
            <div class="log-header">
                <div class="log-header-left">
                    <span class="log-username"><?= htmlspecialchars($log['username']) ?></span>
                    <span class="log-role-badge <?= $role_class ?>"><?= ucfirst($log['role']) ?></span>
                </div>
                <span class="log-waktu"><?= date('H.i', strtotime($log['waktu'])) ?></span>
            </div>
            <div class="log-aksi"><?= htmlspecialchars($log['aksi']) ?></div>
            <?php if (!empty($log['detail'])): ?>
            <div class="log-detail"><?= nl2br(htmlspecialchars($log['detail'])) ?></div>
            <?php endif; ?>
            <div class="log-waktu"><?= date('j F Y', strtotime($log['waktu'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <p class="log-footer">Total <?= count($logs) ?> aktivitas</p>
    </div>
</div>
<script src="js/theme.js"></script>
</body>
</html>
