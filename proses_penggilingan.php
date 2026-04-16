<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'operator') {
    header('Location: login.php');
    exit;
}

$staff = $_SESSION['staff'];
$db    = getDB();
$id    = intval($_GET['id'] ?? 0);
$pesanan = $db->query("SELECT * FROM pesanan WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

if (!$pesanan) {
    header('Location: operator.php');
    exit;
}

$pesan = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mulai_proses'])) {
    $stmt = $db->prepare("UPDATE pesanan SET status = 'proses', waktu_proses = CURRENT_TIMESTAMP WHERE id = ? AND status = 'antrian'");
    $stmt->execute([$id]);
    simpanLog($staff['username'], $staff['role'], 'Mulai Proses Penggilingan', 'Order: ' . $pesanan['order_id']);
    header("Location: proses_penggilingan.php?id=$id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selesai_proses'])) {
    $hasil_beras = floatval($_POST['hasil_beras'] ?? 0);
    $hasil_dedak = floatval($_POST['hasil_dedak'] ?? 0);

    if ($hasil_beras <= 0) {
        $error = 'Hasil beras tidak boleh kosong!';
    } else {
        $stmt = $db->prepare("UPDATE pesanan SET status = 'selesai', hasil_beras = ?, hasil_dedak = ?, waktu_selesai = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$hasil_beras, $hasil_dedak, $id]);
        simpanLog($staff['username'], $staff['role'], 'Selesai Proses Penggilingan',
            "Beras: {$hasil_beras} kg, Dedak: {$hasil_dedak} kg\nOrder: " . $pesanan['order_id']);
        $pesan   = 'Proses penggilingan selesai!';
        $pesanan = $db->query("SELECT * FROM pesanan WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
    }
}

$estimasi_beras = round($pesanan['berat_padi'] * 0.65, 1);
$estimasi_dedak = round($pesanan['berat_padi'] * 0.10, 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Penggilingan</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/proses_penggilingan.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar">
        <a href="operator.php" class="back-btn">← Kembali</a>
        <h1>⚙️ Proses Penggilingan</h1>
    </div>

    <div class="content">
        <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Status badge -->
        <div class="status-header">
            <h3 class="status-header-title">Proses Penggilingan</h3>
            <?php
            $badge_class = 'badge-antrian'; $badge_label = 'Dalam Antrian';
            if ($pesanan['status'] === 'proses')  { $badge_class = 'badge-proses';  $badge_label = 'Sedang Diproses'; }
            if ($pesanan['status'] === 'selesai') { $badge_class = 'badge-selesai'; $badge_label = 'Selesai'; }
            ?>
            <span class="badge <?= $badge_class ?>"><?= $badge_label ?></span>
        </div>
        <p class="order-id-info">Order ID: <?= $pesanan['order_id'] ?></p>

        <!-- Info pesanan -->
        <div class="card">
            <h3 class="info-pesanan-title">Informasi Pesanan</h3>
            <div class="antrian-badge-row">
                <div class="antrian-number-badge antrian-badge-xl">#<?= $pesanan['nomor_antrian'] ?></div>
                <span class="antrian-badge-label">Nomor Antrian</span>
            </div>
            <div class="detail-row">
                <span class="label">👤 Nama Pelanggan</span>
                <span class="value"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">📞 Nomor Telepon</span>
                <span class="value"><?= htmlspecialchars($pesanan['nomor_telepon']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">⚖️ Berat Padi</span>
                <span class="berat-value"><?= number_format($pesanan['berat_padi'], 1) ?> kg</span>
            </div>
            <?php if ($pesanan['waktu_proses']): ?>
            <div class="detail-row">
                <span class="label">🕐 Mulai Diproses</span>
                <span class="value waktu-proses-text"><?= date('j/n/Y, H.i.s', strtotime($pesanan['waktu_proses'])) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action berdasarkan status -->
        <?php if ($pesanan['status'] === 'antrian'): ?>
        <div class="proses-section">
            <div class="mulai-center">
                <div class="icon-mulai">▶️</div>
                <h3>Mulai Proses Penggilingan</h3>
                <p class="mulai-desc">Klik tombol di bawah untuk memulai proses penggilingan pesanan ini</p>
            </div>
            <form method="POST">
                <input type="hidden" name="mulai_proses" value="1">
                <button type="submit" class="btn btn-blue">▶ Mulai Penggilingan</button>
            </form>
        </div>

        <?php elseif ($pesanan['status'] === 'proses'): ?>
        <div class="proses-section">
            <h3 class="input-hasil-title">📥 Input Hasil Penggilingan</h3>
            <div class="estimasi-bar">
                Estimasi hasil: Beras ≈ <?= $estimasi_beras ?> kg, Dedak ≈ <?= $estimasi_dedak ?> kg
            </div>
            <form method="POST" id="formHasil">
                <div class="form-group">
                    <label>Hasil Beras (kg)</label>
                    <input type="number" name="hasil_beras" id="hasilBeras"
                           placeholder="<?= $estimasi_beras ?>" step="0.1" min="0"
                           value="<?= $pesanan['hasil_beras'] ?: '' ?>"
                           oninput="hitungHasil()" required>
                </div>
                <div class="form-group">
                    <label>Hasil Dedak (kg)</label>
                    <input type="number" name="hasil_dedak" id="hasilDedak"
                           placeholder="<?= $estimasi_dedak ?>" step="0.1" min="0"
                           value="<?= $pesanan['hasil_dedak'] ?: '' ?>"
                           oninput="hitungHasil()">
                </div>
                <div class="card card-perhitungan">
                    <div class="detail-row">
                        <span class="label">Total Hasil</span>
                        <span class="value" id="totalHasil">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Susut</span>
                        <span class="value" id="totalSusut">-</span>
                    </div>
                    <div class="detail-row" style="border:none;">
                        <span class="label">Rendemen</span>
                        <span class="value" id="totalRendemen">-</span>
                    </div>
                </div>
                <input type="hidden" name="selesai_proses" value="1">
                <button type="submit" class="btn btn-green"
                        onclick="return confirm('Yakin proses penggilingan sudah selesai?')">
                    ✓ Selesai Penggilingan
                </button>
            </form>
        </div>

        <?php elseif ($pesanan['status'] === 'selesai'): ?>
        <div class="result-box hasil-selesai-box">
            <h4 class="selesai-title">✅ Penggilingan Selesai</h4>
            <div class="detail-row" style="border:none; padding:4px 0;">
                <span class="label">⚖️ Beras</span>
                <span class="value-green hasil-value"><?= number_format($pesanan['hasil_beras'], 1) ?> kg</span>
            </div>
            <div class="detail-row" style="border:none; padding:4px 0;">
                <span class="label">⚖️ Dedak</span>
                <span class="value-green hasil-value"><?= number_format($pesanan['hasil_dedak'], 1) ?> kg</span>
            </div>
        </div>
        <a href="operator.php" class="btn btn-orange" style="margin-top:14px;">← Kembali ke Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<script>
var beratPadi = <?= $pesanan['berat_padi'] ?>;

function hitungHasil() {
    var beras   = parseFloat(document.getElementById('hasilBeras').value) || 0;
    var dedak   = parseFloat(document.getElementById('hasilDedak').value) || 0;
    var total   = beras + dedak;
    var susut   = beratPadi - total;
    var rendemen = beratPadi > 0 ? (total / beratPadi * 100) : 0;
    document.getElementById('totalHasil').textContent   = total.toFixed(1) + ' kg';
    document.getElementById('totalSusut').textContent   = susut.toFixed(1) + ' kg';
    document.getElementById('totalRendemen').textContent = rendemen.toFixed(1) + '%';
}
hitungHasil();
</script>
</body>
</html>
