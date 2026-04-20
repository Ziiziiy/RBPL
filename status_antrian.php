<?php
session_start();
require_once 'database.php';

$db = getDB();

$antrian_list = $db->query("
    SELECT * FROM pesanan 
    WHERE status IN ('antrian','proses') 
    ORDER BY nomor_antrian ASC
")->fetchAll(PDO::FETCH_ASSOC);

$selesai_list = $db->query("
    SELECT * FROM pesanan 
    WHERE status = 'selesai' 
    ORDER BY waktu_selesai DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$waktu_sekarang = date('H.i.s');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Antrian — Penggilingan Padi BangunRejo</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/status_antrian.css">
    <meta http-equiv="refresh" content="10">
</head>
<body>
<div class="app-wrapper">

    <div class="top-bar">
        <a href="index.php" class="back-btn">← Kembali</a>
        <div class="top-bar-row">
            <div>
                <h1>📋 Status Antrian</h1>
                <div class="subtitle" id="waktuDisplay"><?= $waktu_sekarang ?></div>
            </div>
            <a href="status_antrian.php" class="refresh-btn">🔄 Refresh</a>
        </div>
    </div>

    <div class="content">

        <?php if (empty($antrian_list)): ?>
        <div class="card empty-state">
            <div class="empty-icon">✅</div>
            <h3>Tidak ada antrian aktif</h3>
            <p>Semua pesanan sudah selesai diproses.</p>
        </div>
        <?php else: ?>

        <div class="section-title">⏳ Antrian Aktif</div>

        <?php foreach ($antrian_list as $item):
            $parts        = explode('.', $item['estimasi_selesai']);
            $selesai_tot  = intval($parts[0] ?? 0) * 60 + intval($parts[1] ?? 0);
            $sekarang_tot = intval(date('H')) * 60 + intval(date('i'));
            $sisa_menit   = max(0, $selesai_tot - $sekarang_tot);
            $is_proses    = ($item['status'] === 'proses');
        ?>

        <div class="antrian-card <?= $is_proses ? 'is-proses' : '' ?>">

            <div class="antrian-badge">
                <span class="no-label">No.</span>
                <span class="no-num"><?= $item['nomor_antrian'] ?></span>
            </div>

            <div class="antrian-info">
                <div style="display:flex; justify-content:space-between; gap:8px;">
                    <div class="antrian-name"><?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                    <span class="badge <?= $is_proses ? 'badge-proses' : 'badge-antrian' ?>">
                        <?= $is_proses ? '⚙️ Proses' : '⏳ Antrian' ?>
                    </span>
                </div>

                <div class="antrian-meta">
                    Selesai: <strong><?= $item['estimasi_selesai'] ?></strong>
                    · Berat: <strong><?= number_format($item['berat_padi'], 0) ?> kg</strong>
                </div>

                <div class="antrian-sisa">⏱ <?= $sisa_menit ?> menit lagi</div>

                <div class="order-id-box">
                    <span class="order-id-label">ID:</span>
                    <span class="order-id-value"><?= htmlspecialchars($item['order_id']) ?></span>
                    <button class="copy-btn"
                        onclick="salinId('<?= htmlspecialchars($item['order_id'], ENT_QUOTES) ?>', this)">
                        📋
                    </button>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($selesai_list)): ?>

        <div class="section-title">✅ Sudah Selesai</div>

        <?php foreach ($selesai_list as $item): ?>

        <div class="antrian-card is-selesai">

            <div class="antrian-badge">
                <span class="no-label">No.</span>
                <span class="no-num"><?= $item['nomor_antrian'] ?></span>
            </div>

            <div class="antrian-info">
                <div style="display:flex; justify-content:space-between;">
                    <div class="antrian-name"><?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                    <span class="badge badge-selesai">✅ Selesai</span>
                </div>

                <div class="antrian-meta">
                    Selesai: <strong><?= $item['estimasi_selesai'] ?></strong>
                    · Berat: <strong><?= number_format($item['berat_padi'], 0) ?> kg</strong>
                </div>

                <div class="order-id-box">
                    <span class="order-id-label">ID:</span>
                    <span class="order-id-value"><?= htmlspecialchars($item['order_id']) ?></span>
                </div>
            </div>

        </div>

        <?php endforeach; ?>
        <?php endif; ?>


        <!-- FOOTER -->
        <div class="refresh-info">
            <div class="refresh-dot"></div>
            Diperbarui otomatis setiap 10 detik
        </div>

        <a href="buat_pesanan.php" class="btn btn-primary btn-block">
            + Buat Pesanan Baru
        </a>

    </div>
</div>

<div id="toast">✅ ID disalin</div>

<script>
function salinId(orderId, btn) {
    navigator.clipboard.writeText(orderId).then(function() {
        btn.innerHTML = '✅';
        setTimeout(() => btn.innerHTML = '📋', 1500);
    });
}

function updateWaktu() {
    const now = new Date();
    const pad = n => String(n).padStart(2,'0');
    document.getElementById('waktuDisplay').textContent =
        pad(now.getHours()) + '.' + pad(now.getMinutes()) + '.' + pad(now.getSeconds());
}
setInterval(updateWaktu, 1000);
</script>

</body>
</html>