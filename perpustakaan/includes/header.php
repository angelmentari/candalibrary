<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conn)) {
    require_once __DIR__ . '/../config/koneksi.php';
}

$nama_perpus = isset($pengaturan['nama_perpustakaan']) ? $pengaturan['nama_perpustakaan'] : 'CANDA Library';
$role_user   = isset($_SESSION['nama_petugas']) ? $_SESSION['nama_petugas'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - <?= htmlspecialchars(strtoupper($nama_perpus)) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --pistachio: #DCE8B8;
            --grass: #5E8A17;
            --grass-dark: #466810;
            --raspberry: #D94D86;
            --raspberry-dark: #B5376B;
            --bubblegum: #F3A2C4;
            --ivory: #F6F0E8;
            --ink: #23281E;
            --muted: #646B5E;
            --border-soft: #E5DEC9;
        }

        body {
            background-color: var(--ivory);
            color: var(--ink);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-header {
            background: linear-gradient(135deg, #232D1B 0%, #1B2314 100%);
            border-bottom: 2px solid var(--bubblegum);
            padding: 10px 20px;
            box-shadow: 0 2px 8px rgba(35, 45, 27, 0.14);
        }

        .brand-logo-img {
            height: 36px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .brand-title-caps {
            color: var(--pistachio);
            letter-spacing: 0.6px;
            font-size: 1.05rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .main-content {
            padding: 25px;
        }

        .btn-canda-opac {
            background-color: var(--pistachio);
            border-color: var(--pistachio);
            color: var(--ink);
            font-weight: 600;
        }

        .btn-canda-opac:hover {
            background-color: var(--bubblegum);
            border-color: var(--bubblegum);
            color: var(--ink);
        }

        .btn-canda-logout {
            background-color: var(--raspberry);
            border-color: var(--raspberry);
            color: #FFFFFF;
            font-weight: 600;
        }

        .btn-canda-logout:hover {
            background-color: var(--raspberry-dark);
            border-color: var(--raspberry-dark);
            color: #FFFFFF;
        }

        .canda-footer {
            background-color: #FFFFFF;
            color: var(--ink);
            border: 1px solid var(--border-soft);
            box-shadow: 0 2px 10px rgba(35, 45, 27, 0.05);
        }

        /* Kotak Statistik AdminLTE Small Box */
        .small-box {
            border-radius: 8px;
            position: relative;
            display: block;
            margin-bottom: 20px;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            color: #fff;
            overflow: hidden;
        }
        .small-box .inner {
            padding: 15px;
        }
        .small-box .icon {
            position: absolute;
            top: 10px;
            right: 15px;
            z-index: 0;
            font-size: 60px;
            color: rgba(0,0,0,0.15);
        }
        .small-box-footer {
            position: relative;
            text-align: center;
            padding: 5px 0;
            color: #fff;
            background: rgba(0,0,0,0.1);
            text-decoration: none;
            display: block;
            font-size: 12px;
            font-weight: 600;
        }
        .small-box-footer:hover {
            color: #fff;
            background: rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <!-- TOP NAVBAR ATAS (NAVIGASI UTAMA) -->
    <nav class="navbar navbar-expand main-header mb-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 py-0" href="../admin/dashboard.php">
                <img src="../uploads/lampiran/logo.png" alt="Logo <?= htmlspecialchars(strtoupper($nama_perpus)) ?>" class="brand-logo-img">
                <span class="brand-title-caps text-uppercase"><?= htmlspecialchars($nama_perpus) ?></span>
            </a>
            <div class="navbar-nav ms-auto align-items-center">
                <span class="navbar-text me-3 small" style="color: var(--pistachio);">
                    <i class="bi bi-person-circle me-1" style="color: var(--bubblegum);"></i> Login sebagai:
                    <strong style="color: var(--ivory);"><?= htmlspecialchars($role_user) ?></strong>
                </span>
                <a href="../index.php" target="_blank" class="btn btn-canda-opac btn-sm me-2">
                    <i class="bi bi-globe me-1"></i> OPAC
                </a>
                <a href="../auth/logout.php" onclick="return confirm('Keluar dari sistem?')" class="btn btn-canda-logout btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- CONTAINER UTAMA TANPA SIDEBAR -->
    <div class="container main-content">