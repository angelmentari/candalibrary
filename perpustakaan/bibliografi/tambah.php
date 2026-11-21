<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_induk   = isset($_POST['nomor_induk']) ? mysqli_real_escape_string($conn, trim($_POST['nomor_induk'])) : '';
    $nomor_barcode = isset($_POST['nomor_barcode']) ? mysqli_real_escape_string($conn, trim($_POST['nomor_barcode'])) : '';
    $judul         = isset($_POST['judul']) ? mysqli_real_escape_string($conn, trim($_POST['judul'])) : '';
    $pengarang     = isset($_POST['pengarang']) ? mysqli_real_escape_string($conn, trim($_POST['pengarang'])) : '';
    $penerbit      = isset($_POST['penerbit']) ? mysqli_real_escape_string($conn, trim($_POST['penerbit'])) : '';
    $tahun_terbit  = isset($_POST['tahun_terbit']) ? mysqli_real_escape_string($conn, trim($_POST['tahun_terbit'])) : '';
    $isbn          = isset($_POST['isbn']) ? mysqli_real_escape_string($conn, trim($_POST['isbn'])) : '';
    $no_panggil    = isset($_POST['no_panggil']) ? mysqli_real_escape_string($conn, trim($_POST['no_panggil'])) : '';
    $stok          = isset($_POST['stok']) ? (int)$_POST['stok'] : 1;
    $sinopsis      = isset($_POST['sinopsis']) ? mysqli_real_escape_string($conn, trim($_POST['sinopsis'])) : '';

    $nama_sampul = '';
    // Proses Upload Foto Sampul Buku
    if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] === 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name   = $_FILES['sampul']['name'];
        $file_tmp    = $_FILES['sampul']['tmp_name'];
        $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $nama_sampul = 'cover_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
            $upload_dir  = '../uploads/sampul/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            move_uploaded_file($file_tmp, $upload_dir . $nama_sampul);
        }
    }

    // Validasi agar Nomor Induk tidak duplikat sebelum disimpan
    $cek_induk = mysqli_query($conn, "SELECT id_buku FROM buku WHERE nomor_induk = '$nomor_induk'");
    if ($cek_induk && mysqli_num_rows($cek_induk) > 0) {
        $pesan_error = "Nomor Induk <strong>" . htmlspecialchars($nomor_induk) . "</strong> sudah digunakan buku lain! Silakan masukkan Nomor Induk yang berbeda.";
    } else {
        $sql = "INSERT INTO buku (nomor_induk, nomor_barcode, judul, pengarang, penerbit, tahun_terbit, isbn, no_panggil, stok, sinopsis, sampul) 
                VALUES ('$nomor_induk', '$nomor_barcode', '$judul', '$pengarang', '$penerbit', '$tahun_terbit', '$isbn', '$no_panggil', $stok, '$sinopsis', '$nama_sampul')";

        if (mysqli_query($conn, $sql)) {
            header("Location: tampil.php?msg=1");
            exit;
        } else {
            $pesan_error = "Gagal menyimpan data: " . mysqli_error($conn);
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

    body.dark-mode-biblio {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .biblio-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .biblio-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .biblio-card .form-control,
    .biblio-card .form-select {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .biblio-card .form-control:focus,
    .biblio-card .form-select:focus {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--grass);
        box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
    }

    .biblio-card .text-muted {
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

<div class="biblio-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-journal-plus me-2" style="color: var(--grass);"></i>Tambah Koleksi Bibliografi
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeBiblio" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeBiblio"></i>
                <span id="labelModeBiblio">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Kembali ke Dashboard
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

    <div class="card biblio-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor Induk <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_induk" class="form-control" placeholder="Contoh: IND-2026-001" value="<?php echo isset($_POST['nomor_induk']) ? htmlspecialchars($_POST['nomor_induk']) : ''; ?>" required>
                        <small class="text-muted">Setiap buku wajib memiliki Nomor Induk unik.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor Barcode <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_barcode" class="form-control" placeholder="Contoh: B0001" value="<?php echo isset($_POST['nomor_barcode']) ? htmlspecialchars($_POST['nomor_barcode']) : ''; ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" placeholder="Masukkan judul lengkap buku..." value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pengarang</label>
                        <input type="text" name="pengarang" class="form-control" placeholder="Nama pengarang..." value="<?php echo isset($_POST['pengarang']) ? htmlspecialchars($_POST['pengarang']) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Penerbit</label>
                        <input type="text" name="penerbit" class="form-control" placeholder="Nama penerbit..." value="<?php echo isset($_POST['penerbit']) ? htmlspecialchars($_POST['penerbit']) : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tahun Terbit</label>
                        <input type="text" name="tahun_terbit" class="form-control" placeholder="2026" maxlength="4" value="<?php echo isset($_POST['tahun_terbit']) ? htmlspecialchars($_POST['tahun_terbit']) : ''; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ISBN / ISSN</label>
                        <input type="text" name="isbn" class="form-control" placeholder="978-xxx-xxx-xxx-x" value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nomor Panggil (Call Number)</label>
                        <input type="text" name="no_panggil" class="form-control" placeholder="Contoh: 020.5 HAR p" value="<?php echo isset($_POST['no_panggil']) ? htmlspecialchars($_POST['no_panggil']) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jumlah Eksemplar / Stok</label>
                        <input type="number" name="stok" class="form-control" value="<?php echo isset($_POST['stok']) ? (int)$_POST['stok'] : 1; ?>" min="1" required>
                    </div>

                    <!-- INPUT FILE FOTO SAMPUL -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">File Sampul Buku (Cover)</label>
                        <input type="file" name="sampul" class="form-control" accept=".jpg, .jpeg, .png, .webp">
                        <small class="text-muted">Format yang diizinkan: JPG, JPEG, PNG, WEBP.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Sinopsis / Abstrak / Catatan Isi Buku</label>
                        <textarea name="sinopsis" class="form-control" rows="3" placeholder="Ringkasan atau deskripsi singkat buku..."><?php echo isset($_POST['sinopsis']) ? htmlspecialchars($_POST['sinopsis']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-grass fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Data Buku
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
    const btn = document.getElementById('toggleModeBiblio');
    const icon = document.getElementById('iconModeBiblio');
    const label = document.getElementById('labelModeBiblio');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-biblio');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-biblio');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-biblio');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>