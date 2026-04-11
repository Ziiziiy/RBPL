<?php
session_start();
require_once 'database.php';

if (empty($_SESSION['pesanan_baru'])) {
    header('Location: buat_pesanan.php');
    exit;
}

$pesanan = $_SESSION['pesanan_baru'];
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $berat = floatval($_POST['berat'] ?? 0);
    if ($berat <= 0) {
        $error = 'Berat padi harus lebih dari 0 kg!';
    } else {
        $_SESSION['pesanan_baru']['berat'] = $berat;
        header('Location: pembayaran.php');
        exit;
    }
}

$berat_val = $_SESSION['pesanan_baru']['berat'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penimbangan Padi</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/penimbangan.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar">
        <a href="buat_pesanan.php" class="back-btn">← Kembali</a>
        <h1>⚖️ Penimbangan Padi</h1>
        <div class="subtitle">ID: <?= htmlspecialchars($_SESSION['pesanan_baru']['order_id'] ?? 'Belum dibuat') ?></div>
    </div>

    <div class="content">
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card card-info-pelanggan">
            <div class="detail-row">
                <span class="label">👤 Nama Pelanggan</span>
                <span class="value"><?= htmlspecialchars($pesanan['nama']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">📞 Nomor Telepon</span>
                <span class="value"><?= htmlspecialchars($pesanan['telepon']) ?></span>
            </div>
        </div>

        <div class="card">
            <form method="POST" id="formBerat">
                <div class="form-group">
                    <label>Berat Padi (kg)</label>
                    <div class="berat-input-row">
                        <input type="number" name="berat" id="inputBerat"
                               placeholder="0" step="0.1" min="0.1"
                               value="<?= $berat_val ?>"
                               oninput="updateDisplay(this.value)" required>
                        <button type="button" class="btn btn-gray btn-timbang" onclick="simulasiTimbang()">
                            ⚡ Timbang Otomatis
                        </button>
                    </div>
                </div>

                <div class="weight-display" id="weightDisplay">
                    <div class="weight-num" id="weightNum"><?= $berat_val ?: '0' ?></div>
                    <div class="weight-unit">kg</div>
                </div>

                <button type="submit" class="btn btn-orange" style="margin-top:16px;">
                    Lanjut ke Pembayaran →
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function updateDisplay(val) {
    var num = parseFloat(val) || 0;
    document.getElementById('weightNum').textContent = num % 1 === 0 ? num : num.toFixed(1);
}

function simulasiTimbang() {
    var berat = Math.floor(Math.random() * 80) + 20;
    document.getElementById('inputBerat').value = berat;
    updateDisplay(berat);
    var d = document.getElementById('weightDisplay');
    d.style.transform = 'scale(1.05)';
    setTimeout(function() { d.style.transform = 'scale(1)'; }, 200);
}
</script>
</body>
</html>
