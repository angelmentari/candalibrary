<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT s.*, a.kode_anggota, a.nama_anggota, b.nomor_barcode, b.judul 
        FROM sirkulasi s 
        JOIN anggota a ON s.id_anggota = a.id_anggota 
        JOIN buku b ON s.id_buku = b.id_buku 
        WHERE s.id_transaksi = $id AND s.status = 'Dipinjam'";
$q_trx = mysqli_query($conn, $sql);
$trx = $q_trx ? mysqli_fetch_assoc($q_trx) : null;

if (!$trx) {
    header("Location: tampil.php");
    exit;
}

$tgl_sekarang = date('Y-m-d');
$nominal_denda = isset($pengaturan['nominal_denda']) ? $pengaturan['nominal_denda'] : 0;
$hasil_denda  = hitung_denda_otomatis($trx['tgl_jatuh_tempo'], $tgl_sekarang, $nominal_denda);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $denda_final = (int)$_POST['denda'];
    $id_buku     = (int)$trx['id_buku'];

    mysqli_query($conn, "UPDATE sirkulasi SET tgl_kembali = '$tgl_sekarang', status = 'Dikembalikan', denda = $denda_final WHERE id_transaksi = $id");
    mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku");
    header("Location: riwayat_kembali.php?msg=1");
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
        --bg-th: #FAF7F2;
        --text-panel: #23281E;
        --text-soft: #646B5E;
        --border-panel: #E5DEC9;
    }

    body.dark-mode-kembali-proses {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-th: #252C22;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .kembali-proses-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .kembali-proses-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
        overflow: hidden;
    }

    .kembali-proses-card .card-header {
        background-color: var(--grass) !important;
        color: #FFFFFF !important;
        border-bottom: 2px solid var(--bubblegum) !important;
    }

    .detail-table {
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .detail-table th {
        background-color: var(--bg-th);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .detail-table td {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--border-panel);
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

<div class="kembali-proses-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-return-left me-2" style="color: var(--grass);"></i>Pengembalian Buku
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeKembaliProses" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeKembaliProses"></i>
                <span id="labelModeKembaliProses">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-x-lg me-1"></i> Close
            </a>
        </div>
    </div>

    <div class="card kembali-proses-card col-lg-8 shadow-sm">
        <div class="card-header fw-bold">
            <i class="bi bi-receipt-cutoff me-1"></i> Data Peminjaman Buku
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered detail-table align-middle mb-4">
                    <tr>
                        <th width="230">No Peminjaman</th>
                        <td class="fw-bold" style="color: var(--grass);"><?= htmlspecialchars($trx['no_pinjam']) ?></td>
                    </tr>
                    <tr>
                        <th>Tgl Peminjaman</th>
                        <td><?= htmlspecialchars($trx['tgl_pinjam']) ?></td>
                    </tr>
                    <tr>
                        <th>Tgl Pengembalian (Jatuh Tempo)</th>
                        <td><?= htmlspecialchars($trx['tgl_jatuh_tempo']) ?></td>
                    </tr>
                    <tr>
                        <th>ID Anggota</th>
                        <td><?= htmlspecialchars($trx['kode_anggota']) ?> (<strong><?= htmlspecialchars($trx['nama_anggota']) ?></strong>)</td>
                    </tr>
                    <tr>
                        <th>Tanggal Dikembalikan</th>
                        <td><?= htmlspecialchars($tgl_sekarang) ?> <span class="badge" style="background-color: var(--pistachio); color: #23281E;">Sekarang</span></td>
                    </tr>
                    <tr>
                        <th>Terlewat Masa Pengembalian</th>
                        <td>
                            <?php if($hasil_denda['hari'] > 0): ?>
                                <span class="fw-bold" style="color: var(--raspberry);">Terlambat <?= (int)$hasil_denda['hari'] ?> Hari</span>
                            <?php else: ?>
                                <span class="fw-bold" style="color: var(--grass);">Tidak Ada Keterlambatan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Detail Buku</th>
                        <td>[<code><?= htmlspecialchars($trx['nomor_barcode']) ?></code>] <?= htmlspecialchars($trx['judul']) ?></td>
                    </tr>
                    <tr>
                        <th>Total Denda</th>
                        <td class="fs-5 fw-bold" style="color: <?= ((int)$hasil_denda['denda'] > 0) ? 'var(--raspberry)' : 'var(--grass)' ?>;">
                            <?= format_rupiah($hasil_denda['denda']) ?>
                        </td>
                    </tr>
                </table>
            </div>

            <form method="POST" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="denda" value="<?= (int)$hasil_denda['denda'] ?>">
                <button type="submit" class="btn btn-grass fw-bold px-4">
                    <i class="bi bi-check2-circle me-1"></i> Proses Pengembalian
                </button>
                <a href="../admin/dashboard.php" class="btn btn-bubblegum px-4">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
                <a href="tampil.php" class="btn btn-outline-secondary px-4" style="border-color: var(--border-panel); color: var(--text-panel);">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('toggleModeKembaliProses');
    const icon = document.getElementById('iconModeKembaliProses');
    const label = document.getElementById('labelModeKembaliProses');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-kembali-proses');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-kembali-proses');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-kembali-proses');
        setMode(isDark ? 'light' : 'dark');
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>