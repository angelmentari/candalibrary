<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$sql = "SELECT s.*, a.kode_anggota, a.nama_anggota, b.nomor_barcode, b.judul 
        FROM sirkulasi s 
        JOIN anggota a ON s.id_anggota = a.id_anggota 
        JOIN buku b ON s.id_buku = b.id_buku 
        WHERE s.status = 'Dipinjam' 
        ORDER BY s.id_transaksi DESC";
$q = mysqli_query($conn, $sql);
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
        --bg-table-head: #DCE8B8;
        --bg-table-hover: #F6F0E8;
        --text-panel: #23281E;
        --text-soft: #646B5E;
        --border-panel: #E5DEC9;
    }

    body.dark-mode-sirkulasi {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-table-head: #293324;
        --bg-table-hover: rgba(243, 162, 196, 0.08);
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

/* Rapikan kontrol DataTables (Show entries & Search) agar sebaris */
    .sirkulasi-wrapper .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 12px;
    }

    .sirkulasi-wrapper .dataTables_length label,
    .sirkulasi-wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        margin-bottom: 0;
        color: var(--text-panel);
    }

    .sirkulasi-wrapper .dataTables_length select {
        width: auto !important;
        display: inline-block !important;
        padding: 0.2rem 1.75rem 0.2rem 0.5rem;
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .sirkulasi-wrapper .dataTables_filter input {
        width: auto !important;
        display: inline-block !important;
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .sirkulasi-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .sirkulasi-card .text-muted {
        color: var(--text-soft) !important;
    }

    .sirkulasi-table {
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .sirkulasi-table thead th {
        background-color: var(--bg-table-head);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .sirkulasi-table tbody tr:hover {
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
	.sirkulasi-table thead th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .sirkulasi-table tbody td {
        vertical-align: middle;
    }

    .sirkulasi-table .col-nowrap {
        white-space: nowrap;
    }

    .sirkulasi-table .col-koleksi {
        min-width: 190px;
    }
</style>

<div class="sirkulasi-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-left-right me-2" style="color: var(--raspberry);"></i>Data Peminjaman Buku
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeSirkulasi" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeSirkulasi"></i>
                <span id="labelModeSirkulasi">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <a href="pinjam.php" class="btn btn-grass btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Pinjam
            </a>
        </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> Transaksi peminjaman berhasil diproses dan stok koleksi berkurang otomatis!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card sirkulasi-card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover datatable align-middle small sirkulasi-table mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Pinjam</th>
                            <th>ID Anggota</th>
                            <th>Nama</th>
                            <th>Koleksi Buku</th>
                            <th>Pinjam</th>
                            <th>Balik</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Denda</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($q && mysqli_num_rows($q) > 0): ?>
                            <?php $no=1; while($s = mysqli_fetch_assoc($q)): 
                                $nominal_denda = isset($pengaturan['nominal_denda']) ? $pengaturan['nominal_denda'] : 0;
                                $cek = hitung_denda_otomatis($s['tgl_jatuh_tempo'], date('Y-m-d'), $nominal_denda);
                            ?>
                            <tr>
                                <td class="col-nowrap"><?= $no++ ?></td>
                                <td class="fw-bold col-nowrap" style="color: var(--grass);"><?= htmlspecialchars($s['no_pinjam']) ?></td>
                                <td class="col-nowrap"><?= htmlspecialchars($s['kode_anggota']) ?></td>
                                <td class="fw-semibold col-nowrap"><?= htmlspecialchars($s['nama_anggota']) ?></td>
                                <td class="col-koleksi">[<code><?= htmlspecialchars($s['nomor_barcode']) ?></code>] <?= htmlspecialchars($s['judul']) ?></td>
                                <td class="col-nowrap"><?= htmlspecialchars($s['tgl_pinjam']) ?></td>
                                <td class="col-nowrap"><?= htmlspecialchars($s['tgl_jatuh_tempo']) ?></td>
                                <td class="col-nowrap text-center">
                                    <span class="badge" style="background-color: var(--bubblegum); color: #23281E;">
                                        <?= htmlspecialchars($s['status']) ?>
                                    </span>
                                </td>
                                <td class="col-nowrap text-end">
                                    <?php if($cek['hari'] > 0): ?>
                                        <span class="fw-bold" style="color: var(--raspberry);">
                                            <?= (int)$cek['hari'] ?> hari<br><?= format_rupiah($cek['denda']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="fw-semibold" style="color: var(--grass);">Tidak Ada Denda</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-nowrap text-center">
                                    <a href="kembali.php?id=<?= (int)$s['id_transaksi'] ?>" class="btn btn-sm btn-grass fw-semibold text-nowrap">
                                        <i class="bi bi-arrow-return-left"></i> Kembalikan
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada transaksi peminjaman aktif saat ini.</td>
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
    const btn = document.getElementById('toggleModeSirkulasi');
    const icon = document.getElementById('iconModeSirkulasi');
    const label = document.getElementById('labelModeSirkulasi');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-sirkulasi');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-sirkulasi');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-sirkulasi');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>