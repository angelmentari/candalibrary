<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_petugas']) || $_SESSION['login_petugas'] !== true) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}
?>