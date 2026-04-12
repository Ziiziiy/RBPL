<?php
session_start();
require_once 'database.php';

$db             = getDB();
$antrian_list   = $db->query("SELECT * FROM pesanan WHERE status IN ('antrian', 'proses') ORDER BY nomor_antrian ASC")->fetchAll(PDO::FETCH_ASSOC);
$waktu_sekarang = date('H.i.s');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Antrian</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/status_antrian.css">
    <meta http-equiv="refresh" content="10">
</head>
<body>
<div class="app-wrapper">

    <div class="top-bar">
        <a href="index.php" class="back-btn">← Kembali</a>
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>📋 Status Antrian</h1>
                <div class="subtitle" id="waktuDisplay"><?= $waktu_sekarang ?></div>
            </div>
            <a href="status_antrian.php" class="refresh-btn">🔄 Refresh</a>
        </div>
    </div>

    <div class="content">

        <?php if (empty($antrian_list)): ?>
        <div class="empty-state card">
            <div class="empty-icon">✅</div>
            <p style="font-weight:700; font-size:16px; color:#555;">Tidak ada antrian saat ini</p>
            <p style="margin-top:6px;">Semua pesanan sudah selesai diproses.</p>
        </div>

        <?php else: ?>
        <?php foreach ($antrian_list as $item):
            $selesai_parts  = explode('.', $item['estimasi_selesai']);
            $selesai_total  = (intval($selesai_parts[0] ?? 0) * 60) + intval($selesai_parts[1] ?? 0);
            $sekarang_total = intval(date('H')) * 60 + intval(date('i'));
            $sisa_menit     = max(0, $selesai_total - $sekarang_total);
            $is_proses      = ($item['status'] === 'proses');
        ?>

        <div class="antrian-card">

            <div class="antrian-number-badge-lg">
                <?= $item['nomor_antrian'] ?>
                <span>No.</span>
            </div>

            <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:6px;">
                    <h4 style="font-size:15px; font-weight:800; margin:0; color:#222; word-break:break-word;">
                        <?= htmlspecialchars($item['nama_pelanggan']) ?>
                    </h4>
                    <span class="badge <?= $is_proses ? 'badge-proses' : 'badge-antrian' ?>" style="flex-shrink:0;">
                        <?= $is_proses ? '⚙️ Dalam Proses' : '⏳ Dalam Antrian' ?>
                    </span>
                </div>

                <p style="margin-top:4px; font-size:13px; color:#777;">
                    Selesai: <strong><?= $item['estimasi_selesai'] ?></strong>
                    &nbsp;|&nbsp;
                    Berat: <strong><?= number_format($item['berat_padi'], 0) ?> kg</strong>
                </p>

                <div class="waktu-tersisa">⏱️ <?= $sisa_menit ?> menit lagi</div>

                <!-- ID Pesanan -->
                <div class="order-id-box">
                    <span class="order-id-label">🆔 ID:</span>
                    <span class="order-id-value"><?= htmlspecialchars($item['order_id']) ?></span>
                    <button class="copy-btn"
                            onclick="salinId('<?= htmlspecialchars($item['order_id'], ENT_QUOTES) ?>', this)"
                            title="Salin ID Pesanan">
                        📋 Salin
                    </button>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="refresh-info">
            <div class="refresh-dot"></div>
            Status antrian diperbarui secara otomatis setiap 10 detik
        </div>

        <a href="buat_pesanan.php" class="btn btn-orange" style="margin-top:16px;">
            + Buat Pesanan Baru
        </a>
    </div>
</div>

<div id="toast">✅ ID Pesanan disalin!</div>

<script>
function salinId(orderId, btn) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(orderId)
            .then(function() { tampilkanFeedback(btn); })
            .catch(function() { salinFallback(orderId, btn); });
    } else {
        salinFallback(orderId, btn);
    }
}

function salinFallback(teks, btn) {
    var input = document.createElement('input');
    input.value = teks;
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.focus();
    input.select();
    try { document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(input);
    tampilkanFeedback(btn);
}

function tampilkanFeedback(btn) {
    var semula = btn.innerHTML;
    btn.innerHTML = '✅ Tersalin!';
    btn.classList.add('copied');
    var toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(function() {
        btn.innerHTML = semula;
        btn.classList.remove('copied');
        toast.classList.remove('show');
    }, 2000);
}

function updateWaktu() {
    var now = new Date();
    var h = String(now.getHours()).padStart(2,'0');
    var m = String(now.getMinutes()).padStart(2,'0');
    var s = String(now.getSeconds()).padStart(2,'0');
    var el = document.getElementById('waktuDisplay');
    if (el) el.textContent = h+'.'+m+'.'+s;
}
setInterval(updateWaktu, 1000);
</script>
</body>
</html>
