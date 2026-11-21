<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q_buku = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku = $id");
$b = $q_buku ? mysqli_fetch_assoc($q_buku) : null;

if (!$b) {
    header("Location: tampil.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_induk    = mysqli_real_escape_string($conn, $_POST['nomor_induk']);
    $nomor_barcode  = mysqli_real_escape_string($conn, $_POST['nomor_barcode']);
    $isbn           = mysqli_real_escape_string($conn, $_POST['isbn']);
    $nomor_kelas    = mysqli_real_escape_string($conn, $_POST['nomor_kelas']);
    $no_panggil     = mysqli_real_escape_string($conn, $_POST['no_panggil']);
    $judul          = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang      = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $penerbit       = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tempat_terbit  = mysqli_real_escape_string($conn, $_POST['tempat_terbit']);
    $tahun_terbit   = (int)$_POST['tahun_terbit'];
    $bahasa         = mysqli_real_escape_string($conn, $_POST['bahasa']);
    $jumlah_halaman = mysqli_real_escape_string($conn, $_POST['jumlah_halaman']);
    $sinopsis       = mysqli_real_escape_string($conn, $_POST['sinopsis']);
    $id_kategori    = !empty($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : "NULL";
    $id_rak         = !empty($_POST['id_rak']) ? (int)$_POST['id_rak'] : "NULL";
    $stok           = (int)$_POST['stok'];

    $nama_sampul = $b['sampul'];
    if (!empty($_FILES['sampul']['name']) && $_FILES['sampul']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['sampul']['name'], PATHINFO_EXTENSION));
        $nama_sampul = "cover_" . time() . "." . $ext;
        $dir_sampul = "../uploads/sampul/";
        if (!is_dir($dir_sampul)) {
            mkdir($dir_sampul, 0777, true);
        }
        move_uploaded_file($_FILES['sampul']['tmp_name'], $dir_sampul . $nama_sampul);
    }

    $nama_pdf = $b['lampiran_pdf'];
    if (!empty($_FILES['lampiran_pdf']['name']) && $_FILES['lampiran_pdf']['error'] === 0) {
        $ext_pdf = strtolower(pathinfo($_FILES['lampiran_pdf']['name'], PATHINFO_EXTENSION));
        if ($ext_pdf === 'pdf') {
            $nama_pdf = "sample_" . time() . ".pdf";
            $dir_pdf = "../uploads/lampiran/";
            if (!is_dir($dir_pdf)) {
                mkdir($dir_pdf, 0777, true);
            }
            move_uploaded_file($_FILES['lampiran_pdf']['tmp_name'], $dir_pdf . $nama_pdf);
        }
    }

    $sql = "UPDATE buku SET 
            nomor_induk='$nomor_induk', nomor_barcode='$nomor_barcode', isbn='$isbn',
            nomor_kelas='$nomor_kelas', no_panggil='$no_panggil', judul='$judul',
            pengarang='$pengarang', penerbit='$penerbit', tempat_terbit='$tempat_terbit',
            tahun_terbit=$tahun_terbit, bahasa='$bahasa', jumlah_halaman='$jumlah_halaman',
            sinopsis='$sinopsis', id_kategori=$id_kategori, id_rak=$id_rak, stok=$stok,
            sampul='$nama_sampul', lampiran_pdf='$nama_pdf'
            WHERE id_buku = $id";
    mysqli_query($conn, $sql);
    header("Location: tampil.php?msg=updated");
    exit;
}

$q_kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY kode_ddc ASC");
$q_rak = mysqli_query($conn, "SELECT * FROM rak ORDER BY nama_rak ASC");
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

    body.dark-mode-ubah-buku {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .ubah-buku-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .ubah-buku-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .ubah-buku-card .form-control,
    .ubah-buku-card .form-select {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .ubah-buku-card .form-control:focus,
    .ubah-buku-card .form-select:focus {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--grass);
        box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
    }

    .ubah-buku-card .text-muted {
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

<div class="ubah-buku-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-pencil-square me-2" style="color: var(--grass);"></i>Edit Katalog Koleksi Buku
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeUbahBuku" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeUbahBuku"></i>
                <span id="labelModeUbahBuku">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card ubah-buku-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nomor Induk *</label>
                        <input type="text" name="nomor_induk" class="form-control" value="<?= htmlspecialchars($b['nomor_induk']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nomor Barcode *</label>
                        <input type="text" name="nomor_barcode" class="form-control" value="<?= htmlspecialchars($b['nomor_barcode']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($b['isbn']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nomor Kelas *</label>
                        <input type="text" name="nomor_kelas" class="form-control" value="<?= htmlspecialchars($b['nomor_kelas']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nomor Panggil *</label>
                        <input type="text" name="no_panggil" class="form-control" value="<?= htmlspecialchars($b['no_panggil']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kategori DDC</label>
                        <select name="id_kategori" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            <?php if($q_kat): ?>
                                <?php while($k = mysqli_fetch_assoc($q_kat)): ?>
                                    <option value="<?= (int)$k['id_kategori'] ?>" <?= ($b['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['kode_ddc']) ?> - <?= htmlspecialchars($k['nama_kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Judul Buku *</label>
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($b['judul']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pengarang *</label>
                        <input type="text" name="pengarang" class="form-control" value="<?= htmlspecialchars($b['pengarang']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Penerbit *</label>
                        <input type="text" name="penerbit" class="form-control" value="<?= htmlspecialchars($b['penerbit']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tempat Terbit *</label>
                        <input type="text" name="tempat_terbit" class="form-control" value="<?= htmlspecialchars($b['tempat_terbit']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tahun Terbit *</label>
                        <input type="number" name="tahun_terbit" class="form-control" value="<?= (int)$b['tahun_terbit'] ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Bahasa</label>
                        <input type="text" name="bahasa" class="form-control" value="<?= htmlspecialchars($b['bahasa']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Jumlah Halaman</label>
                        <input type="text" name="jumlah_halaman" class="form-control" value="<?= htmlspecialchars($b['jumlah_halaman']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lokasi Rak</label>
                        <select name="id_rak" class="form-select">
                            <option value="">-- Pilih Lokasi Rak --</option>
                            <?php if($q_rak): ?>
                                <?php while($r = mysqli_fetch_assoc($q_rak)): ?>
                                    <option value="<?= (int)$r['id_rak'] ?>" <?= ($b['id_rak'] == $r['id_rak']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_rak']) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Stok</label>
                        <input type="number" name="stok" class="form-control" value="<?= (int)$b['stok'] ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ganti Cover Buku (Opsional)</label>
                        <input type="file" name="sampul" class="form-control" accept="image/*">
                        <?php if(!empty($b['sampul'])): ?>
                            <small class="text-muted">Cover saat ini: <code><?= htmlspecialchars($b['sampul']) ?></code></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ganti Lampiran PDF (Opsional)</label>
                        <input type="file" name="lampiran_pdf" class="form-control" accept="application/pdf">
                        <?php if(!empty($b['lampiran_pdf'])): ?>
                            <small class="text-muted">PDF saat ini: <code><?= htmlspecialchars($b['lampiran_pdf']) ?></code></small>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Ringkasan / Sinopsis Isi Buku</label>
                        <textarea name="sinopsis" class="form-control" rows="3"><?= htmlspecialchars($b['sinopsis']) ?></textarea>
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
    const btn = document.getElementById('toggleModeUbahBuku');
    const icon = document.getElementById('iconModeUbahBuku');
    const label = document.getElementById('labelModeUbahBuku');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-ubah-buku');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-ubah-buku');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-ubah-buku');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>