<?php
session_start();
require_once 'database.php';

// Simpan log logout jika ada session staff
if (!empty($_SESSION['staff'])) {
    $staff = $_SESSION['staff'];
    simpanLog($staff['username'], $staff['role'], 'Logout', '');
}

// Hapus session
session_destroy();

// Redirect ke login
header('Location: login.php');
exit;
?>
