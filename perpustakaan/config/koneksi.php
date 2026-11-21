<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ganti nilai di bawah ini sesuai kredensial MySQL dari InfinityFree
$host = "sqlXXX.infinityfree.com";          // MySQL Hostname dari halaman MySQL Databases
$user = "if0_43497672";                     // MySQL Username akun kamu[cite: 2, 7]
$pass = "PASSWORD_MYSQL_KAMU";              // Password MySQL dari panel InfinityFree
$db   = "if0_43497672_db_candalibrary";      // Nama database tujuan[cite: 4]

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:8px;margin:20px;'>
        <h3>Koneksi Database Gagal!</h3>
        <p>Periksa kembali host, username, password, dan nama database <code>$db</code> di InfinityFree.</p>
        <p>Detail Error: " . mysqli_connect_error() . "</p>
    </div>");
}

// Cek tabel pengaturan_denda, buat otomatis jika belum ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS pengaturan_denda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nominal_denda INT DEFAULT 1000,
    lama_pinjam_default INT DEFAULT 7,
    nama_perpustakaan VARCHAR(100) DEFAULT 'CANDA Library'
)");

$q_set = mysqli_query($conn, "SELECT * FROM pengaturan_denda LIMIT 1");
$pengaturan = mysqli_fetch_assoc($q_set);
if (!$pengaturan) {
    mysqli_query($conn, "INSERT INTO pengaturan_denda (nominal_denda, lama_pinjam_default, nama_perpustakaan) VALUES (1000, 7, 'CANDA Library')");
    $pengaturan = [
        'nominal_denda' => 1000,
        'lama_pinjam_default' => 7,
        'nama_perpustakaan' => 'CANDA Library'
    ];
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$hostName = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_dir = preg_replace('#/(admin|bibliografi|anggota|sirkulasi|auth|includes|config)$#', '', $scriptName);
$BASE_URL = rtrim($protocol . "://" . $hostName . $base_dir, '/');

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka) {
        return "Rp " . number_format((float)$angka, 0, ',', '.');
    }
}

if (!function_exists('hitung_denda_otomatis')) {
    function hitung_denda_otomatis($tgl_jatuh_tempo, $tgl_kembali, $nominal_denda_per_hari = 1000) {
        $jatuh_tempo = strtotime($tgl_jatuh_tempo);
        $kembali     = strtotime($tgl_kembali);

        if ($kembali > $jatuh_tempo) {
            $selisih_detik = $kembali - $jatuh_tempo;
            $terlambat     = floor($selisih_detik / (60 * 60 * 24));
            $denda         = $terlambat * (int)$nominal_denda_per_hari;
            return array(
                'hari'      => $terlambat,
                'terlambat' => $terlambat,
                'denda'     => $denda
            );
        }

        return array(
            'hari'      => 0,
            'terlambat' => 0,
            'denda'     => 0
        );
    }
}

$nama_instansi = "CANDA Library";
?>