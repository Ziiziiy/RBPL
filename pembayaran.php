<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['pesanan_baru']['berat'])) {
    header('Location: penimbangan.php');
    exit;
}

$pesanan = $_SESSION['pesanan_baru'];
$tarif   = getTarif();
$total   = $pesanan['berat'] * $tarif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode           = $_POST['metode'] ?? 'cash';
    $db               = getDB();
    $order_id         = buatOrderId();
    $nomor_antrian    = getNomorAntrian();
    $estimasi_menit   = hitungEstimasi($pesanan['berat']);
    $estimasi_selesai = date('H.i', strtotime("+{$estimasi_menit} minutes"));

    $stmt = $db->prepare("
        INSERT INTO pesanan
            (order_id, nomor_antrian, nama_pelanggan, nomor_telepon, berat_padi,
             harga_per_kg, total_bayar, metode_bayar, status, estimasi_selesai,
             waktu_pesan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'antrian', ?, datetime('now','localtime'))
    ");
    $stmt->execute([
        $order_id, $nomor_antrian, $pesanan['nama'], $pesanan['telepon'],
        $pesanan['berat'], $tarif, $total, $metode, $estimasi_selesai
    ]);

    simpanLog('customer', 'customer', 'Buat Pesanan',
        "Order: $order_id | Antrian: $nomor_antrian | Berat: {$pesanan['berat']} kg");

    $_SESSION['tiket'] = [
        'order_id'         => $order_id,
        'nomor_antrian'    => $nomor_antrian,
        'nama'             => $pesanan['nama'],
        'berat'            => $pesanan['berat'],
        'total'            => $total,
        'metode'           => $metode,
        'estimasi_selesai' => $estimasi_selesai,
        'estimasi_menit'   => $estimasi_menit,
    ];
    unset($_SESSION['pesanan_baru']);
    header('Location: tiket.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran — Penggilingan Padi BangunRejo</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/pembayaran.css">
</head>
<body>
<div class="app-wrapper">

    <div class="top-bar" style="text-align:center;">
        <a href="penimbangan.php" class="back-btn">← Kembali</a>
        <h1>💳 Pembayaran</h1>
    </div>

    <div class="content">

        <div class="card">
            <h3 class="fw-bold" style="margin-bottom:12px;">Ringkasan Pesanan</h3>
            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">Nama Pelanggan</span>
                    <span class="detail-value"><?= htmlspecialchars($pesanan['nama']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Berat Padi</span>
                    <span class="detail-value"><?= number_format($pesanan['berat'], 1) ?> kg</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Harga per kg</span>
                    <span class="detail-value">Rp <?= number_format($tarif, 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="total-row">
                <span class="total-label">Total Pembayaran</span>
                <span class="total-value">Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
        </div>

        <form method="POST" id="formBayar">
            <div class="card">
                <h3 class="fw-bold" style="margin-bottom:14px;">Metode Pembayaran</h3>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div class="payment-option selected" id="opt-cash" onclick="pilihMetode('cash')">
                        <span class="pay-icon">💵</span>
                        <div class="pay-text"><h4>Tunai</h4><p>Bayar langsung di kasir</p></div>
                        <div class="pay-check">✓</div>
                    </div>
                    <div class="payment-option" id="opt-transfer" onclick="pilihMetode('transfer')">
                        <span class="pay-icon">🏦</span>
                        <div class="pay-text"><h4>Transfer Bank</h4><p>Transfer ke rekening tujuan</p></div>
                        <div class="pay-check">✓</div>
                    </div>
                    <div class="payment-option" id="opt-qris" onclick="pilihMetode('qris')">
                        <span class="pay-icon">📱</span>
                        <div class="pay-text"><h4>QRIS</h4><p>Scan kode QR untuk bayar</p></div>
                        <div class="pay-check">✓</div>
                    </div>
                </div>
                <input type="hidden" name="metode" id="inputMetode" value="cash">
            </div>

            <button type="submit" class="btn btn-primary btn-block">✓ Konfirmasi Pembayaran</button>
        </form>

    </div>
</div>

<script>
function pilihMetode(m) {
    document.querySelectorAll('.payment-option').forEach(function(el) { el.classList.remove('selected'); });
    document.getElementById('opt-' + m).classList.add('selected');
    document.getElementById('inputMetode').value = m;
}
</script>
</body>
</html>
