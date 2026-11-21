<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';
$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM buku WHERE id_buku = $id");
header("Location: tampil.php");
exit;
