<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

if (isset($_POST['tambah'])) {
    $kode = mysqli_real_escape_string($conn, $_POST['kode_ddc']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "INSERT INTO kategori (kode_ddc, nama_kategori) VALUES ('$kode', '$nama')");
    header("Location: kategori.php");
    exit;
}
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori = $id");
    header("Location: kategori.php");
    exit;
}
$q = mysqli_query($conn, "SELECT * FROM kategori ORDER BY kode_ddc ASC");
require_once '../includes/header.php';
?>
<h4 class="fw-bold mb-3">Data Kategori (Klasifikasi DDC)</h4>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white fw-bold">Tambah Kategori</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Kode DDC</label>
                        <input type="text" name="kode_ddc" class="form-control" placeholder="Contoh: 800" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Sastra" required>
                    </div>
                    <button type="submit" name="tambah" class="btn btn-primary w-100">+ Tambah Kategori</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover datatable">
                    <thead class="table-dark">
                        <tr><th width="50">No</th><th>Kode DDC</th><th>Kategori</th><th width="80">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($k = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($k['kode_ddc']) ?></strong></td>
                            <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
                            <td><a href="?hapus=<?= $k['id_kategori'] ?>" onclick="return confirm('Hapus kategori?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
