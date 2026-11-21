<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$id = (int)$_GET['id'];
$sql = "SELECT b.*, k.kode_ddc, k.nama_kategori, r.nama_rak 
        FROM buku b 
        LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
        LEFT JOIN rak r ON b.id_rak = r.id_rak 
        WHERE b.id_buku = $id";
$b = mysqli_fetch_assoc(mysqli_query($conn, $sql));
if (!$b) { header("Location: tampil.php"); exit; }

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Koleksi</h4>
    <div class="d-flex gap-2">
        <a href="cetak_label.php?id=<?= $b['id_buku'] ?>" class="btn btn-dark btn-sm"><i class="bi bi-upc-scan me-1"></i> Print Label &amp; Barcode</a>
        <a href="tampil.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center mb-3">
                <?php if(!empty($b['sampul']) && file_exists("../uploads/sampul/" . $b['sampul'])): ?>
                    <img src="../uploads/sampul/<?= htmlspecialchars($b['sampul']) ?>" class="img-fluid rounded shadow">
                <?php else: ?>
                    <div class="border rounded p-5 bg-light text-muted"><i class="bi bi-book fs-1"></i><br>No Cover</div>
                <?php endif; ?>
            </div>
            <div class="col-md-9">
                <table class="table table-bordered table-striped">
                    <tr><th width="210">Nomor Induk</th><td><strong><?= htmlspecialchars($b['nomor_induk']) ?></strong></td></tr>
                    <tr><th>Nomor Barcode</th><td><code><?= htmlspecialchars($b['nomor_barcode']) ?></code></td></tr>
                    <tr><th>ISBN</th><td><?= htmlspecialchars($b['isbn']) ?></td></tr>
                    <tr><th>Nomor Kelas</th><td><?= htmlspecialchars($b['nomor_kelas']) ?></td></tr>
                    <tr><th>No. Panggil</th><td><span class="badge bg-info text-dark fs-6"><?= htmlspecialchars($b['no_panggil']) ?></span></td></tr>
                    <tr><th>Judul Buku</th><td class="fw-bold"><?= htmlspecialchars($b['judul']) ?></td></tr>
                    <tr><th>Pengarang</th><td><?= htmlspecialchars($b['pengarang']) ?></td></tr>
                    <tr><th>Penerbit</th><td><?= htmlspecialchars($b['penerbit']) ?></td></tr>
                    <tr><th>Tempat &amp; Tahun Terbit</th><td><?= htmlspecialchars($b['tempat_terbit']) ?>, <?= $b['tahun_terbit'] ?></td></tr>
                    <tr><th>Bahasa</th><td><?= htmlspecialchars($b['bahasa']) ?></td></tr>
                    <tr><th>Jumlah Halaman</th><td><?= htmlspecialchars($b['jumlah_halaman']) ?></td></tr>
                    <tr><th>Kategori DDC</th><td><?= $b['kode_ddc'] ? $b['kode_ddc'].' - '.htmlspecialchars($b['nama_kategori']) : '-' ?></td></tr>
                    <tr><th>Lokasi Rak</th><td><?= htmlspecialchars($b['nama_rak'] ?? '-') ?></td></tr>
                    <tr><th>Stok Tersedia</th><td><span class="badge bg-success"><?= $b['stok'] ?> Eksemplar</span></td></tr>
                    <tr><th>Ringkasan Isi Buku</th><td><?= nl2br(htmlspecialchars($b['sinopsis'] ?: '-')) ?></td></tr>
                    <tr><th>Lampiran PDF</th><td>
                        <?php if(!empty($b['lampiran_pdf'])): ?>
                            <a href="../uploads/lampiran/<?= htmlspecialchars($b['lampiran_pdf']) ?>" target="_blank" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf"></i> Buka Preview PDF</a>
                        <?php else: ?>
                            <span class="text-muted">Tidak ada lampiran</span>
                        <?php endif; ?>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
