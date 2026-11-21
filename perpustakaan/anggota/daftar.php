<?php
require_once '../config/koneksi.php';

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_anggota     = mysqli_real_escape_string($conn, $_POST['kode_anggota']);
    $username_anggota = mysqli_real_escape_string($conn, $_POST['username_anggota']);
    $nama_anggota     = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $jenis_kelamin    = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $no_telp          = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $alamat           = mysqli_real_escape_string($conn, $_POST['alamat']);
    $password         = $_POST['password']; // Disimpan sesuai alur autentikasi yang ada

    // Cek apakah username atau kode anggota sudah terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM anggota WHERE username_anggota = '$username_anggota' OR kode_anggota = '$kode_anggota'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $pesan = "Gagal: Username atau ID/Kode Anggota tersebut sudah digunakan!";
    } else {
        $password_sql = mysqli_real_escape_string($conn, $password);
        $query = "INSERT INTO anggota (kode_anggota, username_anggota, password_anggota, nama_anggota, jenis_kelamin, no_telp, alamat, status_aktif) 
                  VALUES ('$kode_anggota', '$username_anggota', '$password_sql', '$nama_anggota', '$jenis_kelamin', '$no_telp', '$alamat', 'Aktif')";
                  
        if (mysqli_query($conn, $query)) {
            header("Location: ../auth/login.php?role=anggota&pesan=sukses_daftar");
            exit;
        } else {
            $pesan = "Gagal menyimpan data ke database: " . mysqli_error($conn);
        }
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
    <title>Pendaftaran Anggota Baru - <?= htmlspecialchars($nama_perpus) ?></title>
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

        .daftar-container {
            max-width: 520px;
            width: 100%;
        }

        .theme-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .daftar-box {
            width: 100%;
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-soft) !important;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 14px 32px rgba(35, 45, 27, 0.16);
        }

        .daftar-header {
            background-color: var(--bg-header);
            color: var(--text-main);
            border-bottom: 2px solid var(--bubblegum);
        }

        .daftar-header .text-soft {
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
<div class="daftar-container">
    <div class="theme-bar">
        <button type="button" id="toggleModeDaftar" class="btn btn-sm btn-raspberry rounded-pill px-3 shadow-sm">
            <i class="bi bi-moon-stars-fill me-1" id="iconModeDaftar"></i>
            <span id="labelModeDaftar">Dark Mode</span>
        </button>
    </div>

    <div class="card daftar-box border-0">
        <div class="card-header daftar-header text-center py-4 px-3">
            <i class="bi bi-person-plus-fill fs-1" style="color: var(--raspberry);"></i>
            <h4 class="fw-bold mt-2 mb-0">Pendaftaran Anggota Baru</h4>
            <small class="text-soft">Silakan isi formulir di bawah ini dengan lengkap &mdash; <?= htmlspecialchars($nama_perpus) ?></small>
        </div>

        <div class="card-body p-4">
            <?php if (!empty($pesan)): ?>
                <div class="alert alert-danger py-2 small" style="border-color: var(--raspberry);">
                    <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">ID / Kode Anggota</label>
                        <input type="text" name="kode_anggota" class="form-control" placeholder="Contoh: AG002" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Username untuk Login</label>
                        <input type="text" name="username_anggota" class="form-control" placeholder="Contoh: aqilah" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama_anggota" class="form-control" placeholder="Nama lengkap Anda..." required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="Perempuan">Perempuan</option>
                            <option value="Laki-laki">Laki-laki</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                        <input type="text" name="no_telp" class="form-control" placeholder="0882xxxxxxxx" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat domisili saat ini..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Akun (Simpel)</label>
                    <input type="password" name="password" class="form-control" placeholder="Password untuk login..." required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-grass fw-bold py-2">
                        <i class="bi bi-check-circle me-1"></i> Daftar Sekarang
                    </button>
                </div>
            </form>

            <hr class="my-3">
            <div class="text-center">
                <a href="../auth/login.php?role=anggota" class="text-decoration-none fw-semibold small link-grass">
                    <i class="bi bi-arrow-left-circle me-1"></i> Sudah punya akun? Kembali ke Login Anggota
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const htmlEl = document.documentElement;
    const btn = document.getElementById('toggleModeDaftar');
    const icon = document.getElementById('iconModeDaftar');
    const label = document.getElementById('labelModeDaftar');
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