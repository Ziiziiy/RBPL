<?php
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . __DIR__ . '/penggilingan.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA journal_mode=WAL');
    }
    return $db;
}

function setupDatabase() {
    $db = getDB();

    $db->exec("CREATE TABLE IF NOT EXISTS pesanan (
        id                  INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id            TEXT UNIQUE NOT NULL,
        nomor_antrian       INTEGER NOT NULL,
        nama_pelanggan      TEXT NOT NULL,
        nomor_telepon       TEXT NOT NULL,
        berat_padi          REAL NOT NULL DEFAULT 0,
        harga_per_kg        REAL NOT NULL DEFAULT 500,
        total_bayar         REAL NOT NULL DEFAULT 0,
        metode_bayar        TEXT DEFAULT 'cash',
        status              TEXT DEFAULT 'antrian',
        hasil_beras         REAL DEFAULT 0,
        hasil_dedak         REAL DEFAULT 0,
        estimasi_selesai    TEXT DEFAULT '',
        estimasi_menit      INTEGER DEFAULT 0,
        waktu_pesan         DATETIME DEFAULT (datetime('now','localtime')),
        waktu_proses        DATETIME,
        waktu_selesai       DATETIME,
        waktu_diambil       DATETIME
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS tarif (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        harga_per_kg REAL NOT NULL DEFAULT 500,
        updated_at   DATETIME DEFAULT (datetime('now','localtime'))
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS log_aktivitas (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        role     TEXT NOT NULL,
        aksi     TEXT NOT NULL,
        detail   TEXT DEFAULT '',
        waktu    DATETIME DEFAULT (datetime('now','localtime'))
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role     TEXT NOT NULL,
        nama     TEXT NOT NULL
    )");

    $cek = $db->query("SELECT COUNT(*) as n FROM tarif")->fetch(PDO::FETCH_ASSOC);
    if ($cek['n'] == 0) {
        $db->exec("INSERT INTO tarif (harga_per_kg) VALUES (500)");
    }

    $cekUser = $db->query("SELECT COUNT(*) as n FROM users")->fetch(PDO::FETCH_ASSOC);
    if ($cekUser['n'] == 0) {
        $db->exec("INSERT INTO users (username, password, role, nama) VALUES ('operator', 'pass123', 'operator', 'Budi')");
        $db->exec("INSERT INTO users (username, password, role, nama) VALUES ('owner', 'pass123', 'owner', 'Yanto')");
    }
}

setupDatabase();

function getTarif() {
    $db  = getDB();
    $row = $db->query("SELECT harga_per_kg FROM tarif ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    return $row ? floatval($row['harga_per_kg']) : 500;
}

function getNomorAntrian() {
    $db       = getDB();
    $hari_ini = date('Y-m-d');
    $row = $db->query(
        "SELECT MAX(nomor_antrian) as m FROM pesanan
         WHERE DATE(waktu_pesan) = '$hari_ini'"
    )->fetch(PDO::FETCH_ASSOC);
    return ($row['m'] !== null) ? intval($row['m']) + 1 : 1;
}

function buatOrderId() {
    return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -5));
}

function simpanLog($username, $role, $aksi, $detail = '') {
    $db   = getDB();
    $stmt = $db->prepare(
        "INSERT INTO log_aktivitas (username, role, aksi, detail, waktu)
         VALUES (?, ?, ?, ?, datetime('now','localtime'))"
    );
    $stmt->execute([$username, $role, $aksi, $detail]);
}

function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function hitungEstimasi($berat) {
    return max(15, intval(round($berat * 0.6)));
}