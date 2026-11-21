<?php
require_once '../config/koneksi.php';
$role_tab = (isset($_GET['role']) && $_GET['role'] === 'anggota') ? 'anggota' : 'petugas';
$nama_perpus = (isset($pengaturan['nama_perpustakaan']) && $pengaturan['nama_perpustakaan'] !== '')
               ? $pengaturan['nama_perpustakaan']
               : 'CANDA Library';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem - <?= htmlspecialchars($nama_perpus) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

            --bg-page-start: #DCE8B8;
            --bg-page-end: #F6F0E8;
            --bg-card: #FFFFFF;
            --bg-header: #DCE8B8;
            --bg-input: #F6F0E8;
            --bg-tab: #F6F0E8;
            --text-main: #23281E;
            --text-soft: #646B5E;
            --border-soft: #E5DEC9;
        }

        html[data-theme="dark"] {
            --bg-page-start: #141812;
            --bg-page-end: #1E251B;
            --bg-card: #1D231A;
            --bg-header: #263022;
            --bg-input: #141812;
            --bg-tab: #273023;
            --text-main: #F6F0E8;
            --text-soft: #B6BEA9;
            --border-soft: #323B2D;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-page-start) 0%, var(--bg-page-end) 100%);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            font-family: 'Segoe UI', system-ui, sans-serif;
            transition: background 0.25s ease, color 0.25s ease;
        }

        .login-container {
            max-width: 460px;
            width: 100%;
        }

        .theme-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .login-box {
            width: 100%;
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-soft) !important;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 14px 32px rgba(35, 45, 27, 0.16);
        }

        .login-header {
            background-color: var(--bg-header);
            color: var(--text-main);
            border-bottom: 2px solid var(--bubblegum);
        }

        .login-header .text-soft {
            color: var(--text-soft);
        }

        .nav-pills .nav-link {
            color: var(--text-main);
            background-color: var(--bg-tab);
            border: 1px solid var(--border-soft);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            border-color: var(--grass);
        }

        .nav-pills .nav-link.active {
            background-color: var(--grass);
            border-color: var(--grass);
            color: #FFFFFF;
            font-weight: 600;
        }

        .form-control,
        .input-group-text {
            background-color: var(--bg-input);
            color: var(--text-main);
            border-color: var(--border-soft);
        }

        .form-control::placeholder {
            color: var(--text-soft);
            opacity: 0.85;
        }

        .form-control:focus {
            background-color: var(--bg-card);
            color: var(--text-main);
            border-color: var(--grass);
            box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
        }

        .btn-grass {
            background-color: var(--grass);
            border-color: var(--grass);
            color: #FFFFFF;
        }

        .btn-grass:hover {
            background-color: var(--grass-dark);
            border-color: var(--grass-dark);
            color: #FFFFFF;
        }

        .btn-raspberry {
            background-color: var(--raspberry);
            border-color: var(--raspberry);
            color: #FFFFFF;
        }

        .btn-raspberry:hover {
            background-color: var(--raspberry-dark);
            border-color: var(--raspberry-dark);
            color: #FFFFFF;
        }

        .link-grass {
            color: var(--grass);
        }

        .link-grass:hover {
            color: var(--raspberry);
        }

        hr {
            border-color: var(--border-soft);
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="theme-bar">
        <button type="button" id="toggleModeLogin" class="btn btn-sm btn-raspberry rounded-pill px-3 shadow-sm">
            <i class="bi bi-moon-stars-fill me-1" id="iconModeLogin"></i>
            <span id="labelModeLogin">Dark Mode</span>
        </button>
    </div>

    <div class="card login-box border-0">
        <div class="card-header login-header text-center py-4 px-3">
            <img src="/perpustakaan/uploads/lampiran/logo.png" alt="Logo <?= htmlspecialchars($nama_perpus) ?>" style="height: 60px; width: auto; object-fit: contain;">
            <h4 class="fw-bold mt-2 mb-0">Selamat Datang di <?= htmlspecialchars($nama_perpus) ?></h4>
            <small class="text-soft">Silakan pilih jenis akun untuk masuk ke sistem</small>
        </div>

        <div class="card-body p-4">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger py-2 small" style="border-color: var(--raspberry);">
                    <i class="bi bi-exclamation-triangle me-1"></i> Username atau password tidak sesuai!
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success py-2 small" style="background-color: var(--pistachio); color: #23281E; border-color: var(--grass);">
                    <i class="bi bi-check-circle me-1"></i> Anda telah berhasil keluar (Sign Out).
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_daftar'): ?>
                <div class="alert alert-success py-2 small" style="background-color: var(--pistachio); color: #23281E; border-color: var(--grass);">
                    <i class="bi bi-check-circle me-1"></i> Pendaftaran berhasil! Silakan masuk menggunakan akun anggota Anda.
                </div>
            <?php endif; ?>

            <!-- TAB PILIHAN LOGIN: PUSTAKAWAN/ADMIN vs PENGUNJUNG/ANGGOTA -->
            <ul class="nav nav-pills nav-fill gap-2 mb-4" id="loginTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($role_tab === 'petugas') ? 'active' : '' ?>" id="petugas-tab" data-bs-toggle="pill" data-bs-target="#tab-petugas" type="button">
                        <i class="bi bi-shield-lock me-1"></i> Admin
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($role_tab === 'anggota') ? 'active' : '' ?>" id="anggota-tab" data-bs-toggle="pill" data-bs-target="#tab-anggota" type="button">
                        <i class="bi bi-person me-1"></i> Anggota
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- FORM LOGIN PUSTAKAWAN / ADMIN -->
                <div class="tab-pane fade <?= ($role_tab === 'petugas') ? 'show active' : '' ?>" id="tab-petugas">
                    <form action="proses_login.php" method="POST">
                        <input type="hidden" name="jenis_login" value="petugas">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username Admin</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-grass fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In sebagai Admin
                            </button>
                        </div>
                    </form>
                </div>

                <!-- FORM LOGIN PENGUNJUNG / ANGGOTA -->
                <div class="tab-pane fade <?= ($role_tab === 'anggota') ? 'show active' : '' ?>" id="tab-anggota">
                    <form action="proses_login.php" method="POST">
                        <input type="hidden" name="jenis_login" value="anggota">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username Anggota</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Contoh: aqilah" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Anggota</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-grass fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In sebagai Anggota
                            </button>
                        </div>
                    </form>

                    <!-- LINK MENU PENDAFTARAN ANGGOTA BARU -->
                    <div class="text-center mb-3">
                        <a href="../anggota/daftar.php" class="text-decoration-none small link-grass fw-semibold">
                            <i class="bi bi-person-plus-fill me-1"></i> Belum punya akun? Daftar Anggota Baru
                        </a>
                    </div>
                </div>
            </div>

            <hr class="my-3">
            <div class="text-center">
                <a href="../index.php" class="text-decoration-none fw-semibold link-grass small">
                    <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Katalog Publik (OPAC)
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const htmlEl = document.documentElement;
    const btn = document.getElementById('toggleModeLogin');
    const icon = document.getElementById('iconModeLogin');
    const label = document.getElementById('labelModeLogin');
    const key = 'canda_admin_theme';

    function applyTheme(mode) {
        htmlEl.setAttribute('data-theme', mode);
        localStorage.setItem(key, mode);
        if (mode === 'dark') {
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
    }

    applyTheme(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        applyTheme(htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
})();
</script>
</body>
</html>