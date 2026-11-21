<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan_error = "";

// Saran kode anggota otomatis
$kode_otomatis = "AG" . rand(100, 999);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_anggota     = isset($_POST['kode_anggota']) ? mysqli_real_escape_string($conn, trim($_POST['kode_anggota'])) : '';
    $nama_anggota     = isset($_POST['nama_anggota']) ? mysqli_real_escape_string($conn, trim($_POST['nama_anggota'])) : '';
    $jenis_kelamin    = isset($_POST['jenis_kelamin']) ? mysqli_real_escape_string($conn, trim($_POST['jenis_kelamin'])) : '';
    $no_telp          = isset($_POST['no_telp']) ? mysqli_real_escape_string($conn, trim($_POST['no_telp'])) : '';
    $email            = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
    $alamat           = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, trim($_POST['alamat'])) : '';
    $status_aktif     = isset($_POST['status_aktif']) ? mysqli_real_escape_string($conn, trim($_POST['status_aktif'])) : 'Aktif';
    $username_anggota = isset($_POST['username_anggota']) ? mysqli_real_escape_string($conn, trim($_POST['username_anggota'])) : '';
    $password_anggota = isset($_POST['password_anggota']) ? mysqli_real_escape_string($conn, $_POST['password_anggota']) : '';

    // Validasi agar Kode Anggota & Username tidak duplikat sebelum disimpan
    $cek_kode = mysqli_query($conn, "SELECT id_anggota FROM anggota WHERE kode_anggota = '$kode_anggota'");
    $cek_user = ($username_anggota !== '')
                ? mysqli_query($conn, "SELECT id_anggota FROM anggota WHERE username_anggota = '$username_anggota'")
                : false;

    if ($cek_kode && mysqli_num_rows($cek_kode) > 0) {
        $pesan_error = "Kode Anggota <strong>" . htmlspecialchars($kode_anggota) . "</strong> sudah digunakan! Silakan gunakan kode lain.";
    } elseif ($cek_user && mysqli_num_rows($cek_user) > 0) {
        $pesan_error = "Username <strong>" . htmlspecialchars($username_anggota) . "</strong> sudah digunakan! Silakan pilih username lain.";
    } else {
        // Upload foto profil anggota (opsional)
        $nama_foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            $file_name   = $_FILES['foto']['name'];
            $file_tmp    = $_FILES['foto']['tmp_name'];
            $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_ext)) {
                $nama_foto  = 'foto_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                $upload_dir = '../uploads/foto/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($file_tmp, $upload_dir . $nama_foto);
            } else {
                $pesan_error = "Format foto tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.";
            }
        }

        if (empty($pesan_error)) {
            $sql = "INSERT INTO anggota (kode_anggota, nama_anggota, jenis_kelamin, no_telp, email, alamat, status_aktif, username_anggota, password_anggota, foto) 
                    VALUES ('$kode_anggota', '$nama_anggota', '$jenis_kelamin', '$no_telp', '$email', '$alamat', '$status_aktif', '$username_anggota', '$password_anggota', '$nama_foto')";

            if (mysqli_query($conn, $sql)) {
                header("Location: tampil.php?msg=1");
                exit;
            } else {
                $pesan_error = "Gagal menyimpan data: " . mysqli_error($conn);
            }
        }
    }
}

require_once '../includes/header.php';
?>

<style>
    :root {
        --pistachio: #DCE8B8;
        --grass: #5E8A17;
        --grass-dark: #466810;
        --raspberry: #D94D86;
        --raspberry-dark: #B5376B;
        --bubblegum: #F3A2C4;
        --ivory: #F6F0E8;

        --bg-panel: #F6F0E8;
        --bg-card: #FFFFFF;
        --text-panel: #23281E;
        --text-soft: #646B5E;
        --border-panel: #E5DEC9;
    }

    body.dark-mode-anggota {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .anggota-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .anggota-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .anggota-card .form-control,
    .anggota-card .form-select {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .anggota-card .text-muted {
        color: var(--text-soft) !important;
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

<div class="anggota-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-person-plus-fill me-2" style="color: var(--grass);"></i>Tambah Anggota Baru
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeAnggota" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeAnggota"></i>
                <span id="labelModeAnggota">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $pesan_error; ?>
        </div>
    <?php endif; ?>

    <div class="card anggota-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kode / ID Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="kode_anggota" class="form-control" placeholder="Contoh: AG002" value="<?php echo isset($_POST['kode_anggota']) ? htmlspecialchars($_POST['kode_anggota']) : $kode_otomatis; ?>" required>
                        <small class="text-muted">Setiap anggota wajib memiliki ID unik.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status Keanggotaan <span class="text-danger">*</span></label>
                        <?php $st_val = isset($_POST['status_aktif']) ? $_POST['status_aktif'] : 'Aktif'; ?>
                        <select name="status_aktif" class="form-select" required>
                            <option value="Aktif" <?php echo ($st_val === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Nonaktif" <?php echo ($st_val === 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_anggota" class="form-control" placeholder="Masukkan nama lengkap anggota..." value="<?php echo isset($_POST['nama_anggota']) ? htmlspecialchars($_POST['nama_anggota']) : ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <?php $jk_val = isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : ''; ?>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" <?php echo ($jk_val === 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($jk_val === 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="no_telp" class="form-control" placeholder="Contoh: 0882xxxxxxxx" value="<?php echo isset($_POST['no_telp']) ? htmlspecialchars($_POST['no_telp']) : ''; ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="text" name="email" class="form-control" placeholder="Contoh: nama@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat domisili anggota..."><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                        <input type="text" name="username_anggota" class="form-control" placeholder="Username untuk login portal anggota" value="<?php echo isset($_POST['username_anggota']) ? htmlspecialchars($_POST['username_anggota']) : ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_anggota" class="form-control" placeholder="Masukkan password awal..." required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Foto Profil Anggota</label>
                        <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .webp">
                        <small class="text-muted">Opsional. Format yang diizinkan: JPG, JPEG, PNG, WEBP.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-grass fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Data Anggota
                    </button>
                    <a href="../admin/dashboard.php" class="btn btn-bubblegum px-4">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                    <a href="tampil.php" class="btn btn-outline-secondary px-4" style="border-color: var(--border-panel); color: var(--text-panel);">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('toggleModeAnggota');
    const icon = document.getElementById('iconModeAnggota');
    const label = document.getElementById('labelModeAnggota');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-anggota');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-anggota');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-anggota');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>