<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan_sukses = "";
$pesan_error  = "";

if (isset($_GET['msg'])) {
    $pesan_sukses = "Pengembalian Pinjam Buku Sukses! Stok koleksi telah bertambah otomatis.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_kembali'])) {
    $tipe_input = isset($_POST['tipe_input']) ? trim($_POST['tipe_input']) : 'nomor_barcode';
    $nilai_cari = isset($_POST['nilai_cari']) ? trim($_POST['nilai_cari']) : '';

    if ($nilai_cari === '') {
        $pesan_error = "Silakan masukkan nomor barcode atau kode anggota terlebih dahulu.";
    } else {
        $nilai_aman = mysqli_real_escape_string($conn, $nilai_cari);

        if ($tipe_input === 'kode_anggota') {
            $kondisi = "a.kode_anggota = '$nilai_aman'";
        } else {
            $kondisi = "b.nomor_barcode = '$nilai_aman'";
        }

        $q_cari = mysqli_query($conn, "SELECT s.*, a.kode_anggota, b.nomor_barcode
                                       FROM sirkulasi s
                                       JOIN anggota a ON s.id_anggota = a.id_anggota
                                       JOIN buku b ON s.id_buku = b.id_buku
                                       WHERE $kondisi AND s.status != 'Dikembalikan'
                                       ORDER BY s.id_transaksi ASC LIMIT 1");
        $trx = $q_cari ? mysqli_fetch_assoc($q_cari) : null;

        if (!$trx) {
            $pesan_error = "Data peminjaman aktif tidak ditemukan.";
        } else {
            $id_transaksi = (int)$trx['id_transaksi'];
            $id_buku      = (int)$trx['id_buku'];
            $tgl_kembali  = date('Y-m-d');

            $denda = 0;
            if (!empty($trx['tgl_jatuh_tempo']) && $trx['tgl_jatuh_tempo'] !== '0000-00-00') {
                $selisih_hari = floor((strtotime($tgl_kembali) - strtotime($trx['tgl_jatuh_tempo'])) / 86400);
                if ($selisih_hari > 0) {
                    $denda = $selisih_hari * 1000;
                }
            }

            mysqli_query($conn, "UPDATE sirkulasi SET status = 'Dikembalikan', tgl_kembali = '$tgl_kembali', denda = '$denda' WHERE id_transaksi = $id_transaksi");
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku");

            header("Location: riwayat_kembali.php?msg=1");
            exit;
        }
    }
}

$sql = "SELECT s.*, a.kode_anggota, a.nama_anggota, b.nomor_barcode, b.judul 
        FROM sirkulasi s 
        JOIN anggota a ON s.id_anggota = a.id_anggota 
        JOIN buku b ON s.id_buku = b.id_buku 
        WHERE s.status = 'Dikembalikan' 
        ORDER BY s.tgl_kembali DESC, s.id_transaksi DESC";
$q = mysqli_query($conn, $sql);
require_once '../includes/header.php';

$tipe_dipilih = isset($_POST['tipe_input']) ? $_POST['tipe_input'] : 'nomor_barcode';
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

    body.dark-mode-kembali {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-table-head: #293324;
        --bg-table-hover: rgba(243, 162, 196, 0.08);
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }
/* Rapikan kontrol DataTables (Show entries & Search) agar sebaris */
    .kembali-wrapper .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 12px;
    }

    .kembali-wrapper .dataTables_length label,
    .kembali-wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        margin-bottom: 0;
        color: var(--text-panel);
    }

    .kembali-wrapper .dataTables_length select {
        width: auto !important;
        display: inline-block !important;
        padding: 0.2rem 1.75rem 0.2rem 0.5rem;
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .kembali-wrapper .dataTables_filter input {
        width: auto !important;
        display: inline-block !important;
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    /* Rapikan kolom & baris tabel agar judul sebaris */
    .kembali-table thead th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .kembali-table tbody td {
        vertical-align: middle;
    }

    .kembali-table .col-nowrap {
        white-space: nowrap;
    }

    .kembali-table .col-buku {
        min-width: 190px;
    }
    .kembali-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .kembali-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .kembali-card .text-muted {
        color: var(--text-soft) !important;
    }

    .kembali-table {
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .kembali-table thead th {
        background-color: var(--bg-table-head);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .kembali-table tbody tr:hover {
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
</style>

<div class="kembali-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-journal-check me-2" style="color: var(--grass);"></i>Data Pengembalian Buku
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeKembali" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeKembali"></i>
                <span id="labelModeKembali">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-arrow-left-right me-1"></i> Data Pinjam
            </a>
        </div>
    </div>

    <?php if ($pesan_sukses !== ""): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($pesan_sukses) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($pesan_error !== ""): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($pesan_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card kembali-card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small mb-1">Kembalikan Berdasarkan</label>
                    <select name="tipe_input" class="form-select">
                        <option value="nomor_barcode" <?= $tipe_dipilih == 'nomor_barcode' ? 'selected' : '' ?>>Nomor Barcode</option>
                        <option value="kode_anggota" <?= $tipe_dipilih == 'kode_anggota' ? 'selected' : '' ?>>Kode Anggota</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Nomor Barcode / Kode Anggota</label>
                    <input type="text" name="nilai_cari" class="form-control" placeholder="Masukkan nomor barcode buku atau kode anggota" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" name="proses_kembali" value="1" class="btn btn-grass fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Kembalikan Buku
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card kembali-card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover datatable align-middle small kembali-table mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Pinjam</th>
                            <th>ID Anggota</th>
                            <th>Nama</th>
                            <th>Buku</th>
                            <th>Pinjam</th>
                            <th>Balik</th>
                            <th class="text-center">Status</th>
                            <th>Kembali</th>
                            <th class="text-end">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($q && mysqli_num_rows($q) > 0): ?>
                            <?php $no=1; while($r = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td class="col-nowrap"><?= $no++ ?></td>
                                <td class="fw-bold col-nowrap" style="color: var(--grass);"><?= htmlspecialchars($r['no_pinjam']) ?></td>
                                <td class="col-nowrap"><?= htmlspecialchars($r['kode_anggota']) ?></td>
                                <td class="fw-semibold col-nowrap"><?= htmlspecialchars($r['nama_anggota']) ?></td>
                                <td class="col-buku">[<code><?= htmlspecialchars($r['nomor_barcode']) ?></code>] <?= htmlspecialchars($r['judul']) ?></td>
                                <td class="col-nowrap"><?= htmlspecialchars($r['tgl_pinjam']) ?></td>
                                <td class="col-nowrap"><?= htmlspecialchars($r['tgl_jatuh_tempo']) ?></td>
                                <td class="col-nowrap text-center">
                                    <span class="badge" style="background-color: var(--pistachio); color: #23281E;">
                                        Dikembalikan
                                    </span>
                                </td>
                                <td class="col-nowrap"><?= htmlspecialchars($r['tgl_kembali']) ?></td>
                                <td class="fw-bold col-nowrap text-end" style="color: <?= ((float)$r['denda'] > 0) ? 'var(--raspberry)' : 'var(--grass)' ?>;">
                                    <?= format_rupiah($r['denda']) ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada riwayat pengembalian buku.</td>
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
    const btn = document.getElementById('toggleModeKembali');
    const icon = document.getElementById('iconModeKembali');
    const label = document.getElementById('labelModeKembali');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-kembali');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-kembali');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-kembali');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>