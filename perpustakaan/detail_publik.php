<?php
require_once 'config/koneksi.php';

// Ambil ID buku dari URL dengan aman
$id_buku = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data buku berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku = $id_buku");
$buku = mysqli_fetch_assoc($query);

// Jika buku tidak ditemukan, kembalikan ke halaman utama (OPAC)
if (!$buku) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku: <?php echo htmlspecialchars($buku['judul']); ?> - <?php echo htmlspecialchars($pengaturan['nama_perpustakaan']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {background-color: #F0FFDF; font-family: 'Garamond', system-ui, serif; font-weight: 1000; }
        .navbar-custom {
            background: linear-gradient(135deg, #1b4332 0%, #222d32 100%);
        }
    </style>
</head>
<body>

<!-- Navbar Publik OPAC -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm py-3" style="background: linear-gradient(135deg, #1b4332 0%, #B8DB80 100%)">
    <div class="container" background: linear-gradient(135deg, #1b4332 0%, #B8DB80 100%)>
<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
	<!-- Ganti tag <i> dengan tag <img> untuk menggunakan foto/logo sendiri -->
    <img src="uploads/lampiran/logo.png" alt="Logo CANDA Library" style="height: 40px; width: auto; object-fit: contain;">
    <span style="font-size: 24px !important; color: #F8FF95 !important; font-weight: 800;">CANDA LIBRARY</span>
</a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Pencarian Katalog
            </a>
        </div>
    </div>
</nav>

<!-- Konten Rincian Buku -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm p-4 bg-white rounded-4">
                <div class="row g-4">
                    <!-- Kolom Gambar Sampul / Ilustrasi Buku -->
<div class="col-md-4 text-center">
    <div class="bg-light border-0 rounded-3 p-3 d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 280px;">
        <?php if (!empty($buku['sampul']) && file_exists("uploads/sampul/" . $buku['sampul'])): ?>
            <img src="uploads/sampul/<?php echo htmlspecialchars($buku['sampul']); ?>" alt="Cover Buku" class="img-fluid rounded shadow-sm mb-2" style="max-height: 250px; object-fit: cover;">
            <span class="badge bg-secondary"><?php echo (isset($buku['tipe_koleksi']) && $buku['tipe_koleksi'] !== '') ? htmlspecialchars($buku['tipe_koleksi']) : 'Buku Teks'; ?></span>
        <?php else: ?>
            <i class="bi bi-journal-text display-1 text-success mb-2"></i>
            <span class="badge bg-secondary"><?php echo (isset($buku['tipe_koleksi']) && $buku['tipe_koleksi'] !== '') ? htmlspecialchars($buku['tipe_koleksi']) : 'Buku Teks'; ?></span>
        <?php endif; ?>
    </div>
</div>

                    <!-- Kolom Informasi Rincian Bibliografi -->
                    <div class="col-md-8">
                        <span class="badge bg-success mb-2">Barcode: <?php echo (isset($buku['nomor_barcode']) && $buku['nomor_barcode'] !== '') ? htmlspecialchars($buku['nomor_barcode']) : '-'; ?></span>
                        <h3 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($buku['judul']); ?></h3>
                        
                        <table class="table table-borderless small mb-4">
                            <tr>
                                <th class="text-muted w-25">Pengarang</th>
                                <td class="fw-semibold">: <?php echo (isset($buku['pengarang']) && $buku['pengarang'] !== '') ? htmlspecialchars($buku['pengarang']) : 'Tidak ada data'; ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Penerbit</th>
                                <td>: <?php echo (isset($buku['penerbit']) && $buku['penerbit'] !== '') ? htmlspecialchars($buku['penerbit']) : '-'; ?> (<?php echo (isset($buku['tahun_terbit']) && $buku['tahun_terbit'] !== '') ? htmlspecialchars($buku['tahun_terbit']) : '-'; ?>)</td>
                            </tr>
                            <tr>
                                <th class="text-muted">ISBN / ISSN</th>
                                <td>: <?php echo (isset($buku['isbn']) && $buku['isbn'] !== '') ? htmlspecialchars($buku['isbn']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">No. Panggil</th>
                                <td>: <code><?php echo (isset($buku['no_panggil']) && $buku['no_panggil'] !== '') ? htmlspecialchars($buku['no_panggil']) : '-'; ?></code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Lokasi / Rak</th>
                                <td>: <?php echo (isset($buku['lokasi']) && $buku['lokasi'] !== '') ? htmlspecialchars($buku['lokasi']) : 'Rak Utama Perpustakaan'; ?></td>
                            </tr>
							<tr>
                                <th class="text-muted">Tempat Terbit</th>
                                <td>: <?php echo (isset($buku['tempat_terbit']) && $buku['tempat_terbit'] !== '') ? htmlspecialchars($buku['tempat_terbit']) : '-'; ?></td>
                            </tr>
							<tr>
                                <th class="text-muted">Tahun Terbit</th>
                                <td>: <?php echo (isset($buku['tahun_terbit']) && $buku['tahun_terbit'] !== '') ? htmlspecialchars($buku['tahun_terbit']) : '-'; ?></td>
                            </tr>
							<tr>
                                <th class="text-muted">Bahasa</th>
                                <td>: <?php echo (isset($buku['bahasa']) && $buku['bahasa'] !== '') ? htmlspecialchars($buku['bahasa']) : '-'; ?></td>
                            </tr>
							<tr>
                                <th class="text-muted">Jumlah Halaman</th>
                                <td>: <?php echo (isset($buku['jumlah_halaman']) && $buku['jumlah_halaman'] !== '') ? htmlspecialchars($buku['jumlah_halaman']) : '-'; ?></td>
                            </tr>
                        </table>

                        <hr>

                        <h5 class="fw-bold text-secondary fs-6 mb-2">Sinopsis:</h5>
                        <p class="text-muted small lh-base mb-4">
                            <?php echo !empty($buku['sinopsis']) ? nl2br(htmlspecialchars($buku['sinopsis'])) : 'Belum ada catatan atau ringkasan isi untuk buku ini.'; ?>
                        </p>

                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-success px-4" style="background-color:#008d4c;">
                                <i class="bi bi-search me-1"></i> Cari Buku Lain
                            </a>
                            <a href="auth/login.php?role=anggota" class="btn btn-outline-success px-4">
                                <i class="bi bi-bookmark-check me-1"></i> Login untuk Pinjam
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>