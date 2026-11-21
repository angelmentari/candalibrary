<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan_error = "";

// Proses Hapus Data Buku jika ada parameter ?hapus=id
if (isset($_GET['hapus'])) {
    $id_buku = (int)$_GET['hapus'];
    $query_hapus = mysqli_query($conn, "DELETE FROM buku WHERE id_buku = $id_buku");
    if ($query_hapus) {
        header("Location: tampil.php?pesan=sukses_hapus");
        exit;
    } else {
        $pesan_error = "Gagal menghapus data buku: " . mysqli_error($conn);
    }
}

// Ambil data pencarian jika ada
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, trim($_GET['cari'])) : '';
$where = "";
if (!empty($keyword)) {
    $where = "WHERE judul LIKE '%$keyword%' OR pengarang LIKE '%$keyword%' OR penerbit LIKE '%$keyword%' OR nomor_barcode LIKE '%$keyword%'";
}

$query_buku = mysqli_query($conn, "SELECT * FROM buku $where ORDER BY id_buku DESC");

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
        --bg-card-soft: #FAF7F2;
        --bg-table-head: #DCE8B8;
        --bg-table-hover: #F6F0E8;
        --text-panel: #23281E;
        --text-soft: #646B5E;
        --border-panel: #E5DEC9;
    }

    body.dark-mode-biblio {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-card-soft: #252C22;
        --bg-table-head: #293324;
        --bg-table-hover: rgba(243, 162, 196, 0.08);
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

    .biblio-card .card-header {
        background-color: var(--bg-card-soft) !important;
        color: var(--text-panel) !important;
        border-bottom: 1px solid var(--border-panel) !important;
    }

    .biblio-card .form-control {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .biblio-card .form-control:focus {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--grass);
        box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
    }

    .biblio-card .text-muted {
        color: var(--text-soft) !important;
    }

    .biblio-table {
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .biblio-table thead th {
        background-color: var(--bg-table-head);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .biblio-table tbody tr:hover {
        background-color: var(--bg-table-hover);
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

    code {
        color: var(--raspberry);
    }
</style>

<div class="biblio-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-journal-bookmark-fill me-2" style="color: var(--grass);"></i>Manajemen Katalog Koleksi
            <small class="text-muted fs-6 fw-normal">Bibliografi Buku</small>
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeBiblio" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeBiblio"></i>
                <span id="labelModeBiblio">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <a href="tambah.php" class="btn btn-grass btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Buku Baru
            </a>
            <a href="cetak_label.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-upc-scan me-1"></i> Cetak Label &amp; Barcode
            </a>
        </div>
    </div>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> Data buku baru berhasil ditambahkan ke katalog!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_ubah'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> Data buku berhasil diperbarui!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_hapus'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> Data buku berhasil dihapus dari sistem!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger"><?= $pesan_error ?></div>
    <?php endif; ?>

    <!-- TABEL DAFTAR BUKU -->
    <div class="card biblio-card border-0 shadow-sm">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold"><i class="bi bi-table me-1" style="color: var(--raspberry);"></i> Daftar Seluruh Koleksi Buku Perpustakaan</span>
            <form action="" method="GET" class="d-flex" style="width: 280px;">
                <input type="text" name="cari" class="form-control form-control-sm me-2" placeholder="Cari judul/pengarang..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="btn btn-grass btn-sm"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 small biblio-table">
                    <thead class="text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Barcode / ISBN</th>
                            <th width="28%">Judul Buku</th>
                            <th width="18%">Pengarang</th>
                            <th width="12%">Penerbit / Thn</th>
                            <th width="8%">Rak</th>
                            <th width="6%">Stok</th>
                            <th width="11%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_buku && mysqli_num_rows($query_buku) > 0): ?>
                            <?php $no = 1; while ($b = mysqli_fetch_assoc($query_buku)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center fw-bold"><code><?= htmlspecialchars($b['nomor_barcode']) ?></code></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($b['judul']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($b['pengarang']) ?></td>
                                <td><?= htmlspecialchars($b['penerbit']) ?> (<?= htmlspecialchars($b['tahun_terbit']) ?>)</td>
                                <td class="text-center">
                                    <span class="badge" style="background-color: var(--pistachio); color: #23281E;">
                                        <?= htmlspecialchars(isset($b['nama_rak']) && $b['nama_rak'] !== '' ? $b['nama_rak'] : '-') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge" style="background-color: var(--bubblegum); color: #23281E;">
                                        <?= (int)$b['stok'] ?> eks
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="ubah.php?id=<?= (int)$b['id_buku'] ?>" class="btn btn-grass btn-sm" title="Ubah Data"><i class="bi bi-pencil-square"></i></a>
                                        <a href="tampil.php?hapus=<?= (int)$b['id_buku'] ?>" class="btn btn-raspberry btn-sm" title="Hapus Data" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini dari katalog?')"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data koleksi buku yang terdaftar di sistem.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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