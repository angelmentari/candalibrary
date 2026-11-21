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

// Ambil data anggota saat ini dari database
$query_current = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id_anggota");
$data = $query_current ? mysqli_fetch_assoc($query_current) : null;

if (!$data) {
    header("Location: ../auth/logout.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_anggota   = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $jenis_kelamin  = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $alamat         = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tempat_lahir   = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir  = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $no_telp        = mysqli_real_escape_string($conn, $_POST['no_telp']);

    // Proses update ke database menggunakan kolom tempat_lahir dan tanggal_lahir
    $update = mysqli_query($conn, "UPDATE anggota SET 
                nama_anggota = '$nama_anggota', 
                jenis_kelamin = '$jenis_kelamin', 
                alamat = '$alamat', 
                tempat_lahir = '$tempat_lahir', 
                tanggal_lahir = '$tanggal_lahir', 
                no_telp = '$no_telp' 
              WHERE id_anggota = $id_anggota");

    if ($update) {
        $sukses = "Profil berhasil diperbarui!";
        // Perbarui data di variabel lokal agar langsung nampak perubahannya
        $query_current = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id_anggota");
        $data = $query_current ? mysqli_fetch_assoc($query_current) : $data;
        
        // Update session nama jika digunakan di header
        $_SESSION['nama_anggota'] = $nama_anggota;
    } else {
        $pesan = "Gagal memperbarui profil: " . mysqli_error($conn);
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
    <title>Edit Profil Anggota - <?= htmlspecialchars($nama_perpus) ?></title>
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
            padding: 30px 15px;
            font-family: 'Segoe UI', system-ui, sans-serif;
            transition: background 0.25s ease, color 0.25s ease;
        }

        .profil-wrapper {
            max-width: 620px;
            margin: 0 auto;
        }

        .theme-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .profil-card {
            width: 100%;
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-soft) !important;
            border-radius: 14px;
            box-shadow: 0 14px 32px rgba(35, 45, 27, 0.16);
            overflow: hidden;
        }

        .profil-header {
            background-color: var(--bg-header);
            color: var(--text-main);
            border-bottom: 2px solid var(--bubblegum);
        }

        .profil-header .text-soft {
            color: var(--text-soft);
        }

        .form-control,
        .form-select {
            background-color: var(--bg-input);
            color: var(--text-main);
            border-color: var(--border-soft);
        }

        .form-control::placeholder {
            color: var(--text-soft);
            opacity: 0.85;
        }

        .form-control:focus,
        .form-select:focus {
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
<div class="profil-wrapper">
    <div class="theme-bar">
        <button type="button" id="toggleModeEditProfil" class="btn btn-sm btn-raspberry rounded-pill px-3 shadow-sm">
            <i class="bi bi-moon-stars-fill me-1" id="iconModeEditProfil"></i>
            <span id="labelModeEditProfil">Dark Mode</span>
        </button>
    </div>

    <div class="card profil-card border-0">
        <div class="card-header profil-header py-3 px-4 text-center">
            <i class="bi bi-person-gear fs-2" style="color: var(--raspberry);"></i>
            <h4 class="fw-bold mt-1 mb-0">Edit Profil Anggota</h4>
            <small class="text-soft">Perbarui informasi data diri Anda di sini</small>
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
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama_anggota" class="form-control" value="<?= isset($data['nama_anggota']) ? htmlspecialchars($data['nama_anggota']) : '' ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="Perempuan" <?= (isset($data['jenis_kelamin']) && $data['jenis_kelamin'] === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                            <option value="Laki-laki" <?= (isset($data['jenis_kelamin']) && $data['jenis_kelamin'] === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                        <input type="text" name="no_telp" class="form-control" value="<?= isset($data['no_telp']) ? htmlspecialchars($data['no_telp']) : '' ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" placeholder="Contoh: Semarang" value="<?= isset($data['tempat_lahir']) ? htmlspecialchars($data['tempat_lahir']) : '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= isset($data['tanggal_lahir']) ? htmlspecialchars($data['tanggal_lahir']) : '' ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat domisili saat ini..."><?= isset($data['alamat']) ? htmlspecialchars($data['alamat']) : '' ?></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-grass fw-bold py-2">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
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
    const btn = document.getElementById('toggleModeEditProfil');
    const icon = document.getElementById('iconModeEditProfil');
    const label = document.getElementById('labelModeEditProfil');
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