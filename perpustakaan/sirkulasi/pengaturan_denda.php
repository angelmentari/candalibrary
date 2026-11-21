<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominal = (int)$_POST['nominal_denda'];
    $lama    = (int)$_POST['lama_pinjam_default'];
    $nama_p  = mysqli_real_escape_string($conn, $_POST['nama_perpustakaan']);
    mysqli_query($conn, "UPDATE pengaturan_denda SET nominal_denda=$nominal, lama_pinjam_default=$lama, nama_perpustakaan='$nama_p' WHERE id_denda=1");
    $pengaturan['nominal_denda'] = $nominal;
    $pengaturan['lama_pinjam_default'] = $lama;
    $pengaturan['nama_perpustakaan'] = $nama_p;
    $pesan = "<div class='alert alert-success'>Harga denda &amp; pengaturan berhasil diperbarui!</div>";
}
require_once '../includes/header.php';
?>
<h4 class="fw-bold mb-3"><i class="bi bi-cash-coin text-warning me-2"></i>Pengaturan Harga Denda</h4>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white fw-bold">Ubah Harga Denda &amp; Identitas Perpustakaan</div>
            <div class="card-body">
                <?= $pesan ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Perpustakaan</label>
                        <input type="text" name="nama_perpustakaan" class="form-control" value="<?= htmlspecialchars($pengaturan['nama_perpustakaan']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Denda per Hari (Rp)</label>
                        <input type="number" name="nominal_denda" class="form-control" value="<?= $pengaturan['nominal_denda'] ?>" min="0" step="500" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lama Peminjaman Default (Hari)</label>
                        <input type="number" name="lama_pinjam_default" class="form-control" value="<?= $pengaturan['lama_pinjam_default'] ?>" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary">+ Simpan Pengaturan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white fw-bold">Status Tarif Denda Aktif</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr><th>Harga Denda / Hari</th><th>Lama Pinjam</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold fs-5 text-success"><?= format_rupiah($pengaturan['nominal_denda']) ?></td>
                            <td><?= $pengaturan['lama_pinjam_default'] ?> Hari</td>
                            <td><span class="badge bg-success">Aktif</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
