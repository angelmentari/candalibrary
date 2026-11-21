<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_rak']);
    mysqli_query($conn, "INSERT INTO rak (nama_rak) VALUES ('$nama')");
    header("Location: rak.php");
    exit;
}
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM rak WHERE id_rak = $id");
    header("Location: rak.php");
    exit;
}
$q = mysqli_query($conn, "SELECT * FROM rak ORDER BY id_rak ASC");
require_once '../includes/header.php';
?>
<h4 class="fw-bold mb-3">Data Lokasi Koleksi</h4>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white fw-bold">Tambah Lokasi</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi</label>
                        <input type="text" name="nama_rak" class="form-control" placeholder="Contoh: Ruang Koleksi Umum" required>
                    </div>
                    <button type="submit" name="tambah" class="btn btn-primary w-100">+ Tambah Lokasi</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover datatable">
                    <thead class="table-dark">
                        <tr><th width="50">No</th><th>Lokasi Buku</th><th width="80">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($r = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($r['nama_rak']) ?></td>
                            <td><a href="?hapus=<?= $r['id_rak'] ?>" onclick="return confirm('Hapus lokasi?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
