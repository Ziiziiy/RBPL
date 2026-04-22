<?php
session_start();
require_once 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']    ?? '');
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
    <title>Buat Pesanan — Penggilingan Padi BangunRejo</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/buat_pesanan.css">
    <script>(function(){var s=localStorage.getItem("rbpl-theme");var p=window.matchMedia&&window.matchMedia("(prefers-color-scheme:dark)").matches?"dark":"light";document.documentElement.setAttribute("data-theme",s||p);})();</script>
</head>
<body>
<div class="app-wrapper layout-kiosk">

    <div class="top-bar top-bar-center">
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
                <div class="form-group" style="margin-bottom:14px;">
                    <label for="nama">Nama Pelanggan</label>
                    <input class="input-field" type="text" id="nama" name="nama"
                           placeholder="Masukkan nama lengkap"
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group" style="margin-bottom:18px;">
                    <label for="telepon">Nomor Telepon</label>
                    <input class="input-field" type="tel" id="telepon" name="telepon"
                           placeholder="08xx xxxx xxxx"
                           value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" required>
                </div>

                <div class="tarif-info-box" style="margin-bottom:18px;">
                    <strong>💰 Tarif saat ini: Rp <?= number_format($tarif, 0, ',', '.') ?>/kg</strong>
                    Total dihitung berdasarkan berat padi setelah penimbangan.
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Lanjut ke Penimbangan →
                </button>
            </form>
        </div>
    </div>
</div>
<script src="js/theme.js"></script>
</body>
</html>