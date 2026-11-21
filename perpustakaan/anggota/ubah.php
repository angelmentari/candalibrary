<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q_anggota = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id");
$a = $q_anggota ? mysqli_fetch_assoc($q_anggota) : null;

if (!$a) {
    header("Location: tampil.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $jk     = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tmp    = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tgl    = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $telp   = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $nama_foto = $a['foto'];
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nama_foto = "member_" . time() . "." . $ext;
        $upload_dir = "../uploads/foto/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $nama_foto);
    }

   $pass_sql = "";
if (!empty($_POST['password_baru'])) {
    $pass_aman = mysqli_real_escape_string($conn, trim($_POST['password_baru']));
    $pass_sql = ", password_anggota = '$pass_aman'";
}

    mysqli_query($conn, "UPDATE anggota SET nama_anggota='$nama', jenis_kelamin='$jk', tempat_lahir='$tmp', tanggal_lahir='$tgl', no_telp='$telp', email='$email', alamat='$alamat', foto='$nama_foto' $pass_sql WHERE id_anggota = $id");
    header("Location: tampil.php?msg=1");
    exit;
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

    body.dark-mode-ubah-anggota {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .ubah-anggota-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .ubah-anggota-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .ubah-anggota-card .form-control,
    .ubah-anggota-card .form-select {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .ubah-anggota-card .form-control:focus,
    .ubah-anggota-card .form-select:focus {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--grass);
        box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
    }

    .ubah-anggota-card .text-muted {
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

<div class="ubah-anggota-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-person-gear me-2" style="color: var(--grass);"></i>Update Anggota - <?= htmlspecialchars($a['nama_anggota']) ?>
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeUbahAnggota" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeUbahAnggota"></i>
                <span id="labelModeUbahAnggota">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card ubah-anggota-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nama Pengguna</label>
                        <input type="text" name="nama_anggota" class="form-control" value="<?= htmlspecialchars($a['nama_anggota']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($a['tempat_lahir']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($a['tanggal_lahir']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="Perempuan" <?= $a['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
                            <option value="Laki-laki" <?= $a['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Telepon</label>
                        <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($a['no_telp']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($a['email']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Foto Profil Anggota</label>
                        <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .webp">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Alamat</label>
                        <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($a['alamat']) ?>" required>
                    </div>
                </div>
                <hr class="my-4" style="border-color: var(--border-panel);">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-grass fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
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
    const btn = document.getElementById('toggleModeUbahAnggota');
    const icon = document.getElementById('iconModeUbahAnggota');
    const label = document.getElementById('labelModeUbahAnggota');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-ubah-anggota');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-ubah-anggota');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-ubah-anggota');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>