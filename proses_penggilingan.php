<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'operator') {
    header('Location: login.php');
    exit;
}

$staff   = $_SESSION['staff'];
$db      = getDB();
$id      = intval($_GET['id'] ?? 0);
$pesanan = $db->query("SELECT * FROM pesanan WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

if (!$pesanan) { header('Location: operator.php'); exit; }

$pesan = '';
$error = '';

// ── Mulai proses ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mulai_proses'])) {
    $stmt = $db->prepare(
        "UPDATE pesanan SET status='proses',
         waktu_proses=datetime('now','localtime')
         WHERE id=? AND status='antrian'"
    );
    $stmt->execute([$id]);
    simpanLog($staff['username'], $staff['role'], 'Mulai Proses', 'Order: ' . $pesanan['order_id']);
    header("Location: proses_penggilingan.php?id=$id");
    exit;
}

// ── Selesai proses ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selesai_proses'])) {
    $hasil_beras = floatval($_POST['hasil_beras'] ?? 0);
    $hasil_dedak = floatval($_POST['hasil_dedak'] ?? 0);

    if ($hasil_beras <= 0) {
        $error = 'Hasil beras tidak boleh kosong!';
    } else {
        $stmt = $db->prepare(
            "UPDATE pesanan SET status='selesai',
             hasil_beras=?, hasil_dedak=?,
             waktu_selesai=datetime('now','localtime')
             WHERE id=?"
        );
        $stmt->execute([$hasil_beras, $hasil_dedak, $id]);
        simpanLog($staff['username'], $staff['role'], 'Selesai Penggilingan',
            "Beras: {$hasil_beras} kg | Dedak: {$hasil_dedak} kg | Order: " . $pesanan['order_id']);
        $pesan   = 'Proses penggilingan selesai!';
        $pesanan = $db->query("SELECT * FROM pesanan WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    }
}

$estimasi_beras = round($pesanan['berat_padi'] * 0.65, 1);
$estimasi_dedak = round($pesanan['berat_padi'] * 0.10, 1);
$rendemen_actual = 0;
if ($pesanan['status'] === 'selesai' && $pesanan['berat_padi'] > 0) {
    $rendemen_actual = (($pesanan['hasil_beras'] + $pesanan['hasil_dedak']) / $pesanan['berat_padi']) * 100;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Penggilingan — #<?= $pesanan['nomor_antrian'] ?></title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/proses_penggilingan.css">
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
            <a href="operator.php" class="sidebar-link ">
                <span class="sidebar-icon">📊</span> Dashboard
            </a>
            <a href="status_antrian.php" class="sidebar-link">
                <span class="sidebar-icon">📋</span> Status Antrian
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
        <a href="operator.php" class="back-btn">← Kembali</a>
        <h1>⚙️ Proses Penggilingan</h1>
        <div class="subtitle">Order: <?= $pesanan['order_id'] ?></div>
    </div>

    <div class="content">

        <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- ── INFO PESANAN ── -->
        <div class="card">
            <!-- Nomor antrian hero -->
            <div class="pg-antrian-hero">
                <div class="pg-antrian-num">#<?= $pesanan['nomor_antrian'] ?></div>
                <div class="pg-antrian-sub">Nomor Antrian</div>
                <?php
                $badges = [
                    'antrian' => ['badge-antrian', '⏳ Dalam Antrian'],
                    'proses'  => ['badge-proses',  '⚙️ Sedang Diproses'],
                    'selesai' => ['badge-selesai', '✅ Selesai'],
                ];
                [$bc, $bl] = $badges[$pesanan['status']] ?? ['badge-diambil', ucfirst($pesanan['status'])];
                ?>
                <span class="badge <?= $bc ?> pg-status-badge"><?= $bl ?></span>
            </div>

            <div class="divider"></div>

            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">👤 Nama Pelanggan</span>
                    <span class="detail-value"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📞 Telepon</span>
                    <span class="detail-value"><?= htmlspecialchars($pesanan['nomor_telepon']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">⚖️ Berat Padi</span>
                    <span class="detail-value pg-berat-val"><?= number_format($pesanan['berat_padi'], 1) ?> kg</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">💰 Total Bayar</span>
                    <span class="detail-value detail-value-green">Rp <?= number_format($pesanan['total_bayar'], 0, ',', '.') ?></span>
                </div>
                <?php if ($pesanan['waktu_proses']): ?>
                <div class="detail-row">
                    <span class="detail-label">🕐 Mulai Proses</span>
                    <span class="detail-value" style="font-size:12px;"><?= date('H:i:s, d/m/Y', strtotime($pesanan['waktu_proses'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pesanan['status'] === 'antrian'): ?>
        <div class="card pg-action-card">
            <div class="pg-mulai-center">
                <div class="pg-mulai-icon">▶️</div>
                <h3 class="pg-mulai-title">Siap Diproses</h3>
                <p class="pg-mulai-desc">Tekan tombol untuk memulai penggilingan pesanan ini</p>
            </div>
            <form method="POST">
                <input type="hidden" name="mulai_proses" value="1">
                <button type="submit" class="btn btn-primary btn-block pg-btn-mulai">
                    ▶ Mulai Penggilingan
                </button>
            </form>
        </div>

        <?php elseif ($pesanan['status'] === 'proses'): ?>
        <div class="card pg-action-card">
            <h3 class="pg-input-title">📥 Input Hasil Penggilingan</h3>

            <div class="pg-estimasi-bar">
                <span>📊 Estimasi hasil:</span>
                <strong>Beras ≈ <?= $estimasi_beras ?> kg</strong>
                <span>·</span>
                <strong>Dedak ≈ <?= $estimasi_dedak ?> kg</strong>
            </div>

            <form method="POST" id="formHasil" style="display:flex; flex-direction:column; gap:14px;">

                <div class="pg-input-grid">
                    <div class="form-group">
                        <label for="hasilBeras">🌾 Hasil Beras (kg)</label>
                        <input class="input-field pg-input-hasil" type="number"
                               name="hasil_beras" id="hasilBeras"
                               placeholder="<?= $estimasi_beras ?>" step="0.1" min="0"
                               value="<?= $pesanan['hasil_beras'] ?: '' ?>"
                               oninput="hitungHasil()" required>
                    </div>
                    <div class="form-group">
                        <label for="hasilDedak">🪨 Hasil Dedak (kg)</label>
                        <input class="input-field pg-input-hasil" type="number"
                               name="hasil_dedak" id="hasilDedak"
                               placeholder="<?= $estimasi_dedak ?>" step="0.1" min="0"
                               value="<?= $pesanan['hasil_dedak'] ?: '' ?>"
                               oninput="hitungHasil()">
                    </div>
                </div>

                <div class="pg-calc-box">
                    <div class="pg-calc-row">
                        <span class="pg-calc-label">Total Hasil</span>
                        <span class="pg-calc-val" id="totalHasil">—</span>
                    </div>
                    <div class="pg-calc-row">
                        <span class="pg-calc-label">Susut</span>
                        <span class="pg-calc-val pg-calc-susut" id="totalSusut">—</span>
                    </div>
                    <div class="pg-calc-row">
                        <span class="pg-calc-label">Rendemen</span>
                        <span class="pg-calc-val pg-calc-rendemen" id="totalRendemen">—</span>
                    </div>
                </div>

                <input type="hidden" name="selesai_proses" value="1">
                <button type="submit" class="btn btn-primary btn-block"
                        onclick="return confirm('Yakin proses penggilingan sudah selesai?')">
                    ✓ Selesai Penggilingan
                </button>
            </form>
        </div>

        <?php elseif ($pesanan['status'] === 'selesai'): ?>
        <div class="card pg-selesai-card">
            <div class="pg-selesai-header">
                <div class="pg-selesai-icon">✅</div>
                <div>
                    <h3 class="pg-selesai-title">Penggilingan Selesai!</h3>
                    <p class="pg-selesai-sub">Selesai: <?= date('H:i, d/m/Y', strtotime($pesanan['waktu_selesai'])) ?></p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="pg-hasil-grid">
                <div class="pg-hasil-item">
                    <div class="pg-hasil-num"><?= number_format($pesanan['hasil_beras'], 1) ?></div>
                    <div class="pg-hasil-lbl">kg Beras</div>
                </div>
                <div class="pg-hasil-item pg-hasil-dedak">
                    <div class="pg-hasil-num"><?= number_format($pesanan['hasil_dedak'], 1) ?></div>
                    <div class="pg-hasil-lbl">kg Dedak</div>
                </div>
                <div class="pg-hasil-item pg-hasil-rendemen">
                    <div class="pg-hasil-num"><?= number_format($rendemen_actual, 1) ?>%</div>
                    <div class="pg-hasil-lbl">Rendemen</div>
                </div>
            </div>

            <div class="pg-total-row">
                <span>Total Hasil</span>
                <strong><?= number_format($pesanan['hasil_beras'] + $pesanan['hasil_dedak'], 1) ?> kg</strong>
            </div>
        </div>

        <a href="operator.php" class="btn btn-primary btn-block">← Kembali ke Dashboard</a>
        <?php endif; ?>

    </div>
</div>

<script>
var beratPadi = <?= floatval($pesanan['berat_padi']) ?>;

function hitungHasil() {
    var beras    = parseFloat(document.getElementById('hasilBeras').value) || 0;
    var dedak    = parseFloat(document.getElementById('hasilDedak').value) || 0;
    var total    = beras + dedak;
    var susut    = beratPadi - total;
    var rendemen = beratPadi > 0 ? (total / beratPadi * 100) : 0;

    document.getElementById('totalHasil').textContent    = total.toFixed(1) + ' kg';
    document.getElementById('totalSusut').textContent    = susut.toFixed(1) + ' kg';
    document.getElementById('totalRendemen').textContent = rendemen.toFixed(1) + '%';

    var elR = document.getElementById('totalRendemen');
    elR.className = 'pg-calc-val pg-calc-rendemen';
    if (rendemen >= 70) elR.classList.add('rendemen-ok');
    else if (rendemen >= 50) elR.classList.add('rendemen-warn');
    else if (rendemen > 0) elR.classList.add('rendemen-low');
}
hitungHasil();
</script>
<script src="js/theme.js"></script>
</body>
</html>
