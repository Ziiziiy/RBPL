<?php
session_start();
require_once 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');

    if (empty($nama)) {
        $error = 'Nama pelanggan tidak boleh kosong!';
    } elseif (empty($telepon)) {
        $error = 'Nomor telepon tidak boleh kosong!';
    } else {
        $_SESSION['pesanan_baru'] = ['nama' => $nama, 'telepon' => $telepon];
        header('Location: penimbangan.php');
        exit;
    }
}

$tarif = getTarif();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan Baru</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/buat_pesanan.css">
</head>
<body>
<div class="app-wrapper">
    <div class="top-bar">
        <a href="index.php" class="back-btn">← Kembali</a>
        <h1>📋 Buat Pesanan Baru</h1>
        <div class="subtitle">Isi data pelanggan Anda</div>
    </div>

    <div class="content">
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama" placeholder="Masukkan nama lengkap"
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="tel" name="telepon" placeholder="08xx xxxx xxxx"
                           value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" required>
                </div>

                <div class="info-box info-tarif">
                    <strong>Tarif:</strong><br>
                    Rp <?= number_format($tarif, 0, ',', '.') ?>/kg — Harga akan dihitung berdasarkan berat padi setelah penimbangan.
                </div>

                <button type="submit" class="btn btn-orange">Lanjut ke Penimbangan →</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
