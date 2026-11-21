<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$q = mysqli_query($conn, "SELECT * FROM anggota ORDER BY id_anggota DESC");

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

    body.dark-mode-anggota {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-table-head: #293324;
        --bg-table-hover: rgba(243, 162, 196, 0.08);
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

    .anggota-card .text-muted {
        color: var(--text-soft) !important;
    }

    .anggota-table {
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .anggota-table thead th {
        background-color: var(--bg-table-head);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .anggota-table tbody tr:hover {
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

<div class="anggota-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people-fill me-2" style="color: var(--grass);"></i>Daftar Data Anggota
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeAnggota" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeAnggota"></i>
                <span id="labelModeAnggota">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <a href="tambah.php" class="btn btn-grass btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Anggota
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> Data anggota baru berhasil disimpan!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card anggota-card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle small anggota-table mb-0">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>ID Anggota</th>
                            <th>Foto</th>
                            <th>Nama &amp; Username</th>
                            <th>Jenis Kelamin</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th width="185">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($q && mysqli_num_rows($q) > 0): ?>
                            <?php $no = 1; while($a = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="fw-bold" style="color: var(--grass);"><?php echo htmlspecialchars($a['kode_anggota']); ?></td>
                                <td class="text-center">
                                    <?php if(!empty($a['foto']) && file_exists("../uploads/foto/" . $a['foto'])): ?>
                                        <img src="../uploads/foto/<?php echo htmlspecialchars($a['foto']); ?>" width="45" height="55" style="object-fit:cover;" class="rounded shadow-sm">
                                    <?php else: ?>
                                        <span class="badge" style="background-color: var(--pistachio); color: #23281E;">No Foto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($a['nama_anggota']); ?></div>
                                    <small class="text-muted">User: <code><?php echo htmlspecialchars($a['username_anggota']); ?></code></small>
                                </td>
                                <td><?php echo htmlspecialchars($a['jenis_kelamin']); ?></td>
                                <td><?php echo htmlspecialchars($a['no_telp']); ?></td>
                                <td><?php echo htmlspecialchars($a['alamat']); ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="ubah.php?id=<?php echo $a['id_anggota']; ?>" class="btn btn-sm btn-grass" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <a href="hapus.php?id=<?php echo $a['id_anggota']; ?>" onclick="return confirm('Hapus anggota ini?')" class="btn btn-sm btn-raspberry" title="Hapus"><i class="bi bi-trash"></i></a>
                                        <a href="cetak_kartu.php?id=<?php echo $a['id_anggota']; ?>" class="btn btn-sm btn-bubblegum"><i class="bi bi-printer"></i> Kartu</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data anggota terdaftar.</td>
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