<?php
session_start(); // Wajib ada di awal untuk mengaktifkan sesi
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menggunakan operator ternary agar kompatibel dengan PHP versi lama
    $jenis    = isset($_POST['jenis_login']) ? $_POST['jenis_login'] : 'petugas';
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    if ($jenis === 'petugas') {
        $q = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' LIMIT 1");
        if ($u = mysqli_fetch_assoc($q)) {
            // Cek password hash, teks biasa, atau password demo 'admin123'
            if (($password === $u['password']) || password_verify($password, $u['password']) || ($password === 'admin123' && in_array($username, ['admin', 'petugas']))) {
                $_SESSION['login_petugas'] = true;
                $_SESSION['id_petugas']    = $u['id_user'];
                $_SESSION['nama_petugas']  = $u['nama_lengkap'];
                $_SESSION['role_petugas']  = $u['role'];
                header("Location: ../admin/dashboard.php");
                exit;
            }
        }
        header("Location: login.php?role=petugas&error=1");
        exit;
    } else {
        // Cek data anggota berdasarkan username atau kode anggota
        $q = mysqli_query($conn, "SELECT * FROM anggota WHERE (username_anggota = '$username' OR kode_anggota = '$username') LIMIT 1");
        if ($a = mysqli_fetch_assoc($q)) {
            // Validasi password (mendukung password hash, teks biasa, atau password demo 'anggota123')
            if (($password === $a['password_anggota']) || password_verify($password, $a['password_anggota']) || $password === 'anggota123') {
                $_SESSION['login_anggota'] = true;
                $_SESSION['id_anggota']    = $a['id_anggota'];
                $_SESSION['kode_anggota']  = $a['kode_anggota'];
                $_SESSION['nama_anggota']  = $a['nama_anggota'];
                header("Location: ../anggota_area.php"); // Sesuaikan halaman tujuan anggota
                exit;
            }
        }
        header("Location: login.php?role=anggota&error=1");
        exit;
    }
}
header("Location: login.php");
exit;
?>