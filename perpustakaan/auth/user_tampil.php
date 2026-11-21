<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id != $_SESSION['id_petugas']) {
        mysqli_query($conn, "DELETE FROM users WHERE id_user = $id");
    }
    header("Location: user_tampil.php");
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user ASC");
require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Manajemen Akun Pustakawan / Admin</h4>
    <a href="user_tambah.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Tambah Pustakawan</a>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover datatable align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="50">No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($u = mysqli_fetch_assoc($q)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                    <td><span class="badge bg-<?= $u['role']=='admin'?'danger':'info' ?>"><?= strtoupper($u['role']) ?></span></td>
                    <td>
                        <?php if($u['id_user'] != $_SESSION['id_petugas']): ?>
                            <a href="?hapus=<?= $u['id_user'] ?>" onclick="return confirm('Hapus akun ini?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                        <?php else: ?>
                            <span class="badge bg-secondary">Aktif</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
