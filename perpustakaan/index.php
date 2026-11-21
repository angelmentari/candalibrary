<?php
require_once 'config/koneksi.php';

$keyword = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$field   = isset($_GET['field']) ? mysqli_real_escape_string($conn, $_GET['field']) : 'semua';
$id_kat  = isset($_GET['genre']) ? (int)$_GET['genre'] : 0;

$where = "WHERE 1=1";
if ($keyword !== '') {
    if ($field == 'judul') {
        $where .= " AND b.judul LIKE '%$keyword%'";
    } elseif ($field == 'pengarang') {
        $where .= " AND b.pengarang LIKE '%$keyword%'";
    } elseif ($field == 'penerbit') {
        $where .= " AND b.penerbit LIKE '%$keyword%'";
    } elseif ($field == 'isbn') {
        $where .= " AND b.isbn LIKE '%$keyword%'";
    } elseif ($field == 'nomor_induk') {
        $where .= " AND b.nomor_induk LIKE '%$keyword%'";
    } else {
        $where .= " AND (b.judul LIKE '%$keyword%' OR b.pengarang LIKE '%$keyword%' OR b.penerbit LIKE '%$keyword%' OR b.isbn LIKE '%$keyword%' OR b.no_panggil LIKE '%$keyword%' OR b.nomor_induk LIKE '%$keyword%' OR b.nomor_barcode LIKE '%$keyword%')";
    }
}

// Filter berdasarkan Genre/Kategori jika dipilih
if ($id_kat > 0) {
    $where .= " AND b.id_kategori = $id_kat";
}

$sql = "SELECT b.*, k.kode_ddc, k.nama_kategori, r.nama_rak 
        FROM buku b 
        LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
        LEFT JOIN rak r ON b.id_rak = r.id_rak 
        $where ORDER BY b.id_buku DESC";
$query = mysqli_query($conn, $sql);
$q_kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPAC Katalog Publik - <?= htmlspecialchars($pengaturan['nama_perpustakaan']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #F0FFDF; font-family: 'Garamond', system-ui, serif; font-weight: 1000; }
        .navbar-custom { background: linear-gradient(135deg, #1b4332 0%, #B8DB80 100%); box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
        .hero-opac {
            background: linear-gradient(135deg, #1b4332 0%, #B8DB80 100%);
            color: #fff;
            padding: 48px 0;
        }
        .book-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .book-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .cover-box {
            width: 100px;
            height: 145px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<!-- NAVBAR PUBLIK -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
  <!-- Ganti tag <i> dengan tag <img> untuk menggunakan foto/logo sendiri -->
    <img src="uploads/lampiran/logo.png" alt="Logo CANDA Library" style="height: 40px; width: auto; object-fit: contain;">
    <span style="font-size: 24px !important; color: #F8FF95 !important; font-weight: 800;">CANDA LIBRARY</span>
</a>
        <div class="ms-auto d-flex flex-wrap gap-2">
            <?php if(isset($_SESSION['login_petugas']) && $_SESSION['login_petugas'] === true): ?>
                <a href="admin/dashboard.php" class="btn btn-sm fw-bold border-0" style="background-color: #F8FF95;">
                    <i class="bi bi-speedometer2 me-1"></i> Kembali ke Dashboard Pustakawan
                </a>
            <?php elseif(isset($_SESSION['login_anggota']) && $_SESSION['login_anggota'] === true): ?>
                <a href="anggota_area.php" class="btn btn-light btn-sm fw-bold text-success">
                    <i class="bi bi-person-badge me-1"></i> Area Anggota (<?= htmlspecialchars($_SESSION['nama_anggota']) ?>)
                </a>
            <?php else: ?>
                <a href="auth/login.php?role=anggota" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="bi bi-person-check me-1"></i> Login Anggota
                </a>
                <a href="auth/login.php?role=petugas" class="btn btn-sm fw-bold text-dark" style="background-color: #F8FF95; border-color: #F8FF95;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Login Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HERO PENCARIAN OPAC -->
<section class="hero-opac text-center">
    <div class="container">
		<span class="badge text-black mb-2 px-3 py-1" style="background-color: #F8FF95; font-size: 20px;">Online Public Access Catalog (OPAC)</span>
        <h2 class="fw-bold mb-2" style="font-size: 50px;">Telusuri Katalog Koleksi Perpustakaan</h2>
        <p class="opacity-75 mb-4" style="font-size: 20px;">Cari berdasarkan Judul, Pengarang, Penerbit, ISBN, Nomor Panggil, atau Nomor Induk Koleksi</p>

<form method="GET" class="row g-2 justify-content-center">
    <div class="col-md-4">
        <input type="text" name="q" style="background-color: #F0FFDF;" class="form-control form-control-lg" placeholder="Kata kunci..." value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-3">
        <select name="field" class="form-select form-select-lg" style="background-color: #F0FFDF;">
            <option value="judul" <?= ($field == 'judul') ? 'selected' : '' ?>>Judul Buku</option>
            <option value="pengarang" <?= ($field == 'pengarang') ? 'selected' : '' ?>>Penulis / Pengarang</option>
            <option value="penerbit" <?= ($field == 'penerbit') ? 'selected' : '' ?>>Penerbit</option>
            <option value="isbn" <?= ($field == 'isbn') ? 'selected' : '' ?>>ISBN / ISSN</option>
            <option value="nomor_induk" <?= ($field == 'nomor_induk') ? 'selected' : '' ?>>Nomor Induk</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="genre" class="form-select form-select-lg" style="background-color: #F0FFDF;">
            <option value="0">Semua Genre</option>
            <?php while($k = mysqli_fetch_assoc($q_kat)): ?>
                <option value="<?= $k['id_kategori'] ?>" <?= ($id_kat == $k['id_kategori']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-lg fw-bold shadow-sm" style="background-color: #F8FF95; border-color: #F8FF95;"><i class="bi bi-search me-1"></i> Cari</button>
    </div>
</form>
    </div>
</section>

<!-- DAFTAR KOLEKSI BUKU -->
<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">Daftar Koleksi Tersedia (<?= mysqli_num_rows($query) ?> Judul)</h5>
        <?php if($keyword !== '' || $field !== 'semua'): ?>
    <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset Pencarian</a>
<?php endif; ?>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($query) == 0): ?>
            <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                <i class="bi bi-search fs-1 text-muted"></i>
                <p class="mt-2 text-muted mb-0">Koleksi buku yang Anda cari tidak ditemukan.</p>
            </div>
        <?php endif; ?>

        <?php while($b = mysqli_fetch_assoc($query)): ?>
            <div class="col-md-6 col-lg-6">
                <div class="card book-card shadow-sm h-100">
                    <div class="card-body d-flex gap-3">
                        <div class="flex-shrink-0">
                            <?php if(!empty($b['sampul']) && file_exists("uploads/sampul/" . $b['sampul'])): ?>
                                <img src="uploads/sampul/<?= htmlspecialchars($b['sampul']) ?>" class="cover-box shadow-sm" alt="Sampul">
                            <?php else: ?>
                                <div class="cover-box bg-secondary bg-opacity-10 d-flex flex-column align-items-center justify-content-center text-muted text-center p-2 border">
                                    <i class="bi bi-book fs-2"></i>
                                    <small style="font-size:10px;">No Cover</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <span class="badge text-dark font-monospace" style="background-color: #FFD8DF;">No. Panggil: <?= htmlspecialchars($b['no_panggil']) ?></span>
                                <?php if($b['stok'] > 0): ?>
                                    <span class="badge text-black mb-2 px-3 py-1" style="background-color: #FFD8DF;">Tersedia (<?= $b['stok'] ?> eks)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Dipinjam Semua</span>
                                <?php endif; ?>
                            </div>
                            <h5 class="fw-bold mb-1">
                                <a href="detail_publik.php?id=<?= $b['id_buku'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($b['judul']) ?>
                                </a>
                            </h5>
                            <div class="small text-muted mb-1"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($b['pengarang']) ?></div>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-building"></i> <?= htmlspecialchars($b['penerbit']) ?> &bull; <?= htmlspecialchars($b['tempat_terbit']) ?>, <?= $b['tahun_terbit'] ?>
                            </div>
                            <p class="small text-secondary mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                <?= htmlspecialchars($b['sinopsis'] ?: 'Klik rincian buku untuk melihat metadata bibliografi lengkap.') ?>
                            </p>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars(isset($b['nama_rak']) ? $b['nama_rak'] : '-') ?></small>
                                <a href="detail_publik.php?id=<?= $b['id_buku'] ?>" class="btn btn-sm btn-success fw-semibold text-black border-0 shadow-sm" style="background-color: #FFD8DF;">
                                    <i class="bi bi-info-circle me-1"></i> Rincian Isi Buku
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<footer class="bg-green border-top py-3 text-center text-muted small">
    &copy; <?= date('Y') ?> <strong><?= htmlspecialchars($pengaturan['nama_perpustakaan']) ?></strong> &mdash; Online Public Access Catalog
</footer>
</body>
</html>
