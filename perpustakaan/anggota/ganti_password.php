<?php
require_once '../config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Cek apakah yang login adalah anggota
if (!isset($_SESSION['login_anggota']) || $_SESSION['login_anggota'] !== true) {
    header("Location: ../auth/login.php");
    exit;
}

$id_anggota = (int)$_SESSION['id_anggota'];
$pesan = "";
$sukses = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_baru = trim($_POST['password_baru']);
    
    if (!empty($password_baru)) {
        $password_aman = mysqli_real_escape_string($conn, $password_baru);
        // Update password secara simpel (teks biasa) ke tabel anggota
        $q_update = mysqli_query($conn, "UPDATE anggota SET password_anggota = '$password_aman' WHERE id_anggota = $id_anggota");
        
        if ($q_update) {
            $sukses = "Password berhasil diperbarui!";
        } else {
            $pesan = "Gagal memperbarui password: " . mysqli_error($conn);
        }
    } else {
        $pesan = "Password baru tidak boleh kosong!";
    }
}

$nama_perpus = (isset($pengaturan['nama_perpustakaan']) && $pengaturan['nama_perpustakaan'] !== '')
               ? $pengaturan['nama_perpustakaan']
               : 'CANDA Library';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password Anggota - <?= htmlspecialchars($nama_perpus) ?></title>
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
            --text-main: #F6F0E8;
            --text-soft: #B6BEA9;
            --border-soft: #323B2D;
        }

        body {
            background: linear-gradient(135deg, var(--bg-page-start) 0%, var(--bg-page-end) 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            font-family: 'Segoe UI', system-ui, sans-serif;
            transition: background 0.25s ease, color 0.25s ease;
        }

        .password-wrapper {
            max-width: 460px;
            width: 100%;
            margin: 0 auto;
        }

        .theme-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .password-card {
            width: 100%;
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-soft) !important;
            border-radius: 14px;
            box-shadow: 0 14px 32px rgba(35, 45, 27, 0.16);
            overflow: hidden;
        }

        .password-header {
            background-color: var(--bg-header);
            color: var(--text-main);
            border-bottom: 2px solid var(--bubblegum);
        }

        .password-header .text-soft {
            color: var(--text-soft);
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

        .btn-bubblegum {
            background-color: var(--bubblegum);
            border-color: var(--bubblegum);
            color: #23281E;
            font-weight: 600;
        }

        .btn-bubblegum:hover {
            background-color: #e489b0;
            border-color: #e489b0;
            color: #23281E;
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
    </style>
</head>
<body>
<div class="password-wrapper">
    <div class="theme-bar">
        <button type="button" id="toggleModeGantiPass" class="btn btn-sm btn-raspberry rounded-pill px-3 shadow-sm">
            <i class="bi bi-moon-stars-fill me-1" id="iconModeGantiPass"></i>
            <span id="labelModeGantiPass">Dark Mode</span>
        </button>
    </div>

    <div class="card password-card border-0 mx-auto">
        <div class="card-header password-header py-3 px-4 text-center">
            <i class="bi bi-key fs-2" style="color: var(--raspberry);"></i>
            <h4 class="fw-bold mt-1 mb-0">Ganti Password</h4>
            <small class="text-soft">Perbarui kata sandi akun Anda di sini</small>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($pesan)): ?>
                <div class="alert alert-danger py-2 small" style="border-color: var(--raspberry);">
                    <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($sukses)): ?>
                <div class="alert alert-success py-2 small" style="background-color: var(--pistachio); color: #23281E; border-color: var(--grass);">
                    <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($sukses) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Masukkan Password Baru</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password_baru" class="form-control" placeholder="Kata sandi baru..." required>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-grass fw-bold py-2">
                        <i class="bi bi-save me-1"></i> Simpan Password Baru
                    </button>
                    <a href="../anggota_area.php" class="btn btn-bubblegum btn-sm py-2">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Area Anggota
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const htmlEl = document.documentElement;
    const btn = document.getElementById('toggleModeGantiPass');
    const icon = document.getElementById('iconModeGantiPass');
    const label = document.getElementById('labelModeGantiPass');
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