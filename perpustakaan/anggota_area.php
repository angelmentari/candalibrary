<?php
require_once 'config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['login_anggota']) || $_SESSION['login_anggota'] !== true) {
    header("Location: auth/login.php?role=anggota");
    exit;
}

$id_anggota = (int)$_SESSION['id_anggota'];
$q_anggota  = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id_anggota");
$anggota    = $q_anggota ? mysqli_fetch_assoc($q_anggota) : null;

if (!$anggota) {
    header("Location: auth/logout.php");
    exit;
}

$pesan_sukses = "";
$pesan_error  = "";

// Batas maksimal peminjaman aktif per akun
$batas_maks_pinjam = 3;

// Ambil nominal denda per hari dari pengaturan (default 1000 jika belum diatur di database)
$nominal_denda_harian = isset($pengaturan['nominal_denda']) && (int)$pengaturan['nominal_denda'] > 0 
                        ? (int)$pengaturan['nominal_denda'] 
                        : 1000;

// Fungsi bantu hitung denda berjalan per hari (kelipatan harian selama belum dikembalikan)
function hitung_denda_berjalan($tgl_jatuh_tempo, $tarif_harian) {
    $jatuh_tempo = new DateTime(date('Y-m-d', strtotime($tgl_jatuh_tempo)));
    $hari_ini    = new DateTime(date('Y-m-d'));
    
    if ($hari_ini > $jatuh_tempo) {
        $selisih_hari = $jatuh_tempo->diff($hari_ini)->days;
        return [
            'hari_telat' => $selisih_hari,
            'denda'      => $selisih_hari * $tarif_harian
        ];
    }
    return ['hari_telat' => 0, 'denda' => 0];
}

// Cek jumlah buku yang sedang dipinjam & denda aktif HANYA dari buku yang masih berstatus 'Dipinjam'
$total_denda_aktif     = 0;
$jumlah_dipinjam_aktif = 0;

$q_cek_status = mysqli_query($conn, "SELECT status, tgl_jatuh_tempo, denda FROM sirkulasi WHERE id_anggota = $id_anggota AND status = 'Dipinjam'");
if ($q_cek_status) {
    while ($row = mysqli_fetch_assoc($q_cek_status)) {
        $jumlah_dipinjam_aktif++;
        
        // Hitung denda berjalan per hari jika melewati jatuh tempo
        $cek_telat  = hitung_denda_berjalan($row['tgl_jatuh_tempo'], $nominal_denda_harian);
        $denda_item = max((int)$row['denda'], $cek_telat['denda']);
        
        if ($denda_item > 0) {
            $total_denda_aktif += $denda_item;
        }
    }
}

// PROSES SIMPAN PEMINJAMAN MANDIRI OLEH ANGGOTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajukan_pinjam'])) {
    $id_buku     = (int)$_POST['id_buku'];
    $lama_pinjam = isset($_POST['lama_pinjam']) ? (int)$_POST['lama_pinjam'] : 7;

    if ($total_denda_aktif > 0) {
        $denda_rp = function_exists('format_rupiah') ? format_rupiah($total_denda_aktif) : 'Rp ' . number_format($total_denda_aktif, 0, ',', '.');
        $pesan_error = "Anda masih memiliki buku terlambat dengan denda berjalan sebesar <strong>$denda_rp</strong>. Silakan kembalikan buku dan selesaikan denda melalui pustakawan terlebih dahulu.";
    } elseif ($jumlah_dipinjam_aktif >= $batas_maks_pinjam) {
        $pesan_error = "Anda sudah mencapai batas maksimal peminjaman (<strong>$batas_maks_pinjam buku</strong>). Silakan kembalikan buku yang sedang dipinjam terlebih dahulu.";
    } elseif ($id_buku > 0) {
        // Buat nomor transaksi peminjaman unik otomatis
        $no_pinjam       = "PJ-" . date("Ymd") . "-" . rand(100, 999);
        $tgl_pinjam      = date("Y-m-d");
        $tgl_jatuh_tempo = date("Y-m-d", strtotime("+$lama_pinjam days"));
        
        // Cek apakah anggota sedang meminjam buku yang sama dan belum dikembalikan
        $cek_pinjam = mysqli_query($conn, "SELECT * FROM sirkulasi WHERE id_anggota = $id_anggota AND id_buku = $id_buku AND status = 'Dipinjam'");
        
        if ($cek_pinjam && mysqli_num_rows($cek_pinjam) > 0) {
            $pesan_error = "Anda masih meminjam buku ini dan belum mengembalikannya.";
        } else {
            // Simpan transaksi ke tabel sirkulasi
            $query_simpan = "INSERT INTO sirkulasi (no_pinjam, id_anggota, id_buku, tgl_pinjam, tgl_jatuh_tempo, status, denda) 
                             VALUES ('$no_pinjam', $id_anggota, $id_buku, '$tgl_pinjam', '$tgl_jatuh_tempo', 'Dipinjam', 0)";
            
            if (mysqli_query($conn, $query_simpan)) {
                $pesan_sukses = "Peminjaman berhasil dicatat! Nomor Transaksi: <strong>" . htmlspecialchars($no_pinjam) . "</strong> (Jatuh tempo: " . htmlspecialchars($tgl_jatuh_tempo) . ").";
                $jumlah_dipinjam_aktif++;
            } else {
                $pesan_error = "Gagal menyimpan peminjaman ke database: " . htmlspecialchars(mysqli_error($conn));
            }
        }
    } else {
        $pesan_error = "Silakan pilih judul buku terlebih dahulu.";
    }
}

// Ambil daftar semua buku dari tabel buku untuk pilihan peminjaman
$q_daftar_buku = mysqli_query($conn, "SELECT id_buku, judul, nomor_barcode, pengarang FROM buku ORDER BY judul ASC");

// Ambil riwayat peminjaman anggota saat ini
$q_pinjam = mysqli_query($conn, "SELECT s.*, b.judul, b.nomor_barcode, b.no_panggil 
                                 FROM sirkulasi s 
                                 JOIN buku b ON s.id_buku = b.id_buku 
                                 WHERE s.id_anggota = $id_anggota 
                                 ORDER BY s.id_transaksi DESC");

$nama_perpus = (isset($pengaturan['nama_perpustakaan']) && $pengaturan['nama_perpustakaan'] !== '')
               ? $pengaturan['nama_perpustakaan']
               : 'CANDA Library';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Anggota - <?= htmlspecialchars($nama_perpus) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --pistachio: #DCE8B8;
            --grass: #5E8A17;
            --grass-dark: #466810;
            --raspberry: #D94D86;
            --raspberry-dark: #B5376B;
            --bubblegum: #F3A2C4;
            --ivory: #F6F0E8;

            --bg-page: #F6F0E8;
            --bg-card: #FFFFFF;
            --bg-nav: #DCE8B8;
            --bg-subcard: #FAF7F2;
            --bg-table-head: #DCE8B8;
            --bg-table-hover: #F6F0E8;
            --text-main: #23281E;
            --text-soft: #646B5E;
            --border-soft: #E5DEC9;
        }

        html[data-theme="dark"] {
            --bg-page: #141812;
            --bg-card: #1D231A;
            --bg-nav: #1D2419;
            --bg-subcard: #252C22;
            --bg-table-head: #293324;
            --bg-table-hover: rgba(243, 162, 196, 0.08);
            --text-main: #F6F0E8;
            --text-soft: #B6BEA9;
            --border-soft: #323B2D;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            font-family: 'Segoe UI', system-ui, sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .navbar-anggota {
            background-color: var(--bg-nav);
            border-bottom: 2px solid var(--bubblegum);
            box-shadow: 0 2px 8px rgba(35, 45, 27, 0.08);
        }

        .brand-anggota {
            color: var(--text-main) !important;
            font-weight: 800;
        }

        .card-canda {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-soft) !important;
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header-dark-canda {
            background-color: #232D1B;
            color: #F6F0E8;
            border-bottom: 2px solid var(--pistachio);
        }

        .card-header-grass-canda {
            background-color: var(--grass);
            color: #FFFFFF;
            border-bottom: 2px solid var(--bubblegum);
        }

        .card-header-soft-canda {
            background-color: var(--bg-subcard);
            color: var(--text-main);
            border-bottom: 1px solid var(--border-soft);
        }

        .form-control,
        .form-select {
            background-color: var(--bg-page);
            color: var(--text-main);
            border-color: var(--border-soft);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--bg-card);
            color: var(--text-main);
            border-color: var(--grass);
            box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
        }

        .table-canda {
            color: var(--text-main);
            border-color: var(--border-soft);
        }

        .table-canda th,
        .table-canda td {
            background-color: transparent;
            color: var(--text-main);
            border-color: var(--border-soft);
        }

        .table-canda thead th {
            background-color: var(--bg-table-head);
            color: var(--text-main);
        }
		.table-canda thead th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table-canda tbody td {
            vertical-align: middle;
        }

        .col-no-pinjam,
        .col-tgl,
        .col-status,
        .col-denda {
            white-space: nowrap;
        }

        .col-judul {
            min-width: 180px;
        }

        .table-canda tbody tr:hover {
            background-color: var(--bg-table-hover);
        }

        .text-muted {
            color: var(--text-soft) !important;
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
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-anggota sticky-top">
    <div class="container">
        <a class="navbar-brand brand-anggota" href="anggota_area.php">
            <i class="bi bi-person-badge me-2" style="color: var(--grass);"></i>Portal Anggota
        </a>
        <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
            <button type="button" id="toggleModeAnggotaArea" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeAnggotaArea"></i>
                <span id="labelModeAnggotaArea">Dark Mode</span>
            </button>
            <a href="index.php" class="btn btn-sm btn-bubblegum">
                <i class="bi bi-search me-1"></i> Cari Buku (OPAC)
            </a>
            <a href="auth/logout.php" class="btn btn-sm btn-raspberry fw-semibold">
                <i class="bi bi-box-arrow-right me-1"></i> Sign Out
            </a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <!-- Notifikasi Sukses / Error Peminjaman -->
    <?php if (!empty($pesan_sukses)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="background-color: var(--pistachio); color: #23281E; border-color: var(--grass);">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_sukses ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-color: var(--raspberry);">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Kolom Kiri: Profil & Tombol Aksi -->
        <div class="col-md-4">
            <div class="card card-canda shadow-sm">
                <div class="card-header card-header-dark-canda fw-bold">
                    <i class="bi bi-card-heading me-1" style="color: var(--pistachio);"></i> Profil &amp; Kartu Anggota
                </div>
                <div class="card-body text-center">
                    <!-- Kotak Foto / Inisial Profil -->
                    <div class="mb-3">
                        <?php if (!empty($anggota['foto']) && file_exists("uploads/foto/" . $anggota['foto'])): ?>
                            <img src="uploads/foto/<?= htmlspecialchars($anggota['foto']) ?>" alt="Foto Profil" class="rounded-circle shadow-sm mx-auto" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid var(--pistachio);">
                        <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-3 shadow-sm" style="width: 80px; height: 80px; background-color: var(--grass); color: #FFFFFF;">
                                <?= strtoupper(substr($anggota['nama_anggota'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($anggota['nama_anggota']) ?></h5>
                    <span class="badge mb-3" style="background-color: var(--pistachio); color: #23281E;">
                        ID: <?= htmlspecialchars($anggota['kode_anggota']) ?>
                    </span>

                    <table class="table table-sm table-canda text-start small mb-3">
                        <tr><th>Jenis Kelamin</th><td><?= htmlspecialchars($anggota['jenis_kelamin']) ?></td></tr>
                        <tr><th>Telepon</th><td><?= htmlspecialchars($anggota['no_telp']) ?></td></tr>
                        <tr><th>Alamat</th><td><?= htmlspecialchars($anggota['alamat']) ?></td></tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge" style="background-color: var(--grass); color: #FFFFFF;">
                                    <?= htmlspecialchars($anggota['status_aktif']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Kuota Pinjam</th>
                            <td>
                                <span class="badge" style="background-color: var(--bubblegum); color: #23281E;">
                                    <?= (int)$jumlah_dipinjam_aktif ?> / <?= (int)$batas_maks_pinjam ?> Buku
                                </span>
                            </td>
                        </tr>
                    </table>

                    <div class="d-grid gap-2 mt-3">
                        <a href="anggota/cetak_kartu.php?id=<?= (int)$anggota['id_anggota'] ?>&self=1" target="_blank" class="btn btn-grass btn-sm fw-semibold">
                            <i class="bi bi-printer me-1"></i> Cetak Kartu Anggota
                        </a>
                        <a href="anggota/edit_profil.php" class="btn btn-bubblegum btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profil Anggota
                        </a>
                        <a href="anggota/ganti_password.php" class="btn btn-outline-secondary btn-sm fw-semibold" style="border-color: var(--raspberry); color: var(--raspberry);">
                            <i class="bi bi-key me-1"></i> Ganti Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Pinjam Buku Mandiri + Riwayat Peminjaman -->
        <div class="col-md-8">
            <!-- Form Pengajuan Peminjaman Mandiri -->
            <div class="card card-canda shadow-sm mb-4">
                <div class="card-header card-header-grass-canda fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-journal-plus me-1"></i> Ajukan Peminjaman Buku Mandiri</span>
                    <span class="badge" style="background-color: var(--pistachio); color: #23281E;">
                        Dipinjam: <?= (int)$jumlah_dipinjam_aktif ?>/<?= (int)$batas_maks_pinjam ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if ($total_denda_aktif > 0): ?>
                        <div class="alert alert-warning border-0 shadow-sm mb-0 small" style="background-color: var(--bubblegum); color: #23281E;">
                            <i class="bi bi-exclamation-octagon-fill me-1" style="color: var(--raspberry);"></i>
                            Anda memiliki keterlambatan pengembalian dengan denda berjalan sebesar 
                            <strong><?= function_exists('format_rupiah') ? format_rupiah($total_denda_aktif) : 'Rp ' . number_format($total_denda_aktif, 0, ',', '.') ?></strong> 
                            (tarif <?= function_exists('format_rupiah') ? format_rupiah($nominal_denda_harian) : 'Rp ' . number_format($nominal_denda_harian, 0, ',', '.') ?>/hari). 
                            Kembalikan buku dan lunasi denda melalui pustakawan untuk membuka kembali akses peminjaman.
                        </div>
                    <?php elseif ($jumlah_dipinjam_aktif >= $batas_maks_pinjam): ?>
                        <div class="alert alert-info border-0 shadow-sm mb-0 small" style="background-color: var(--pistachio); color: #23281E;">
                            <i class="bi bi-info-circle-fill me-1" style="color: var(--grass);"></i>
                            Anda sudah mencapai batas maksimal peminjaman (<strong><?= (int)$batas_maks_pinjam ?> buku aktif</strong>). 
                            Kembalikan salah satu buku yang sedang dipinjam untuk meminjam judul lainnya.
                        </div>
                    <?php else: ?>
                        <form action="" method="POST" class="row g-3 align-items-end">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold small">Pilih Judul Buku yang Ingin Dipinjam</label>
                                <select name="id_buku" class="form-select form-select-sm" required>
                                    <option value="">Pilih Judul Buku dari Katalog</option>
                                    <?php if ($q_daftar_buku): ?>
                                        <?php while($b = mysqli_fetch_assoc($q_daftar_buku)): ?>
                                            <option value="<?= (int)$b['id_buku'] ?>" <?= (isset($_GET['pinjam_id']) && (int)$_GET['pinjam_id'] == (int)$b['id_buku']) ? 'selected' : '' ?>>
                                                [<?= htmlspecialchars($b['nomor_barcode']) ?>] <?= htmlspecialchars($b['judul']) ?> - <?= htmlspecialchars($b['pengarang']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Durasi Pinjam</label>
                                <select name="lama_pinjam" class="form-select form-select-sm">
                                    <option value="3">3 Hari</option>
                                    <option value="7" selected>7 Hari (Standar)</option>
                                    <option value="14">14 Hari</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" name="ajukan_pinjam" class="btn btn-grass btn-sm fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> Pinjam
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Riwayat Peminjaman & Status Denda -->
            <div class="card card-canda shadow-sm">
                <div class="card-header card-header-soft-canda fw-bold d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span><i class="bi bi-clock-history me-1" style="color: var(--grass);"></i> Status Peminjaman &amp; Denda Saya</span>
                    <small class="text-muted fw-normal">
                        Denda keterlambatan: <?= function_exists('format_rupiah') ? format_rupiah($nominal_denda_harian) : 'Rp ' . number_format($nominal_denda_harian, 0, ',', '.') ?> / hari
                    </small>
                </div>
<div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-canda align-middle small mb-0">
                            <thead>
                                <tr>
                                    <th>No. Pinjam</th>
                                    <th>Judul Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Jatuh Tempo</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Denda</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!$q_pinjam || mysqli_num_rows($q_pinjam) == 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat peminjaman buku.</td></tr>
                                <?php else: ?>
                                    <?php while($p = mysqli_fetch_assoc($q_pinjam)): 
                                        $denda_tampil = (int)$p['denda'];
                                        $hari_telat   = 0;
                                        if ($p['status'] === 'Dipinjam') {
                                            $cek_telat    = hitung_denda_berjalan($p['tgl_jatuh_tempo'], $nominal_denda_harian);
                                            $hari_telat   = $cek_telat['hari_telat'];
                                            $denda_tampil = max($denda_tampil, $cek_telat['denda']);
                                        }
                                    ?>
                                    <tr>
                                        <td class="fw-bold col-no-pinjam" style="color: var(--grass);"><?= htmlspecialchars($p['no_pinjam']) ?></td>
                                        <td class="col-judul">
                                            <?= htmlspecialchars($p['judul']) ?><br>
                                            <small class="text-muted">[<?= htmlspecialchars($p['nomor_barcode']) ?>]</small>
                                        </td>
                                        <td class="col-tgl"><?= htmlspecialchars($p['tgl_pinjam']) ?></td>
                                        <td class="col-tgl"><?= htmlspecialchars($p['tgl_jatuh_tempo']) ?></td>
                                        <td class="col-status text-center">
                                            <?php if ($p['status'] === 'Dipinjam'): ?>
                                                <span class="badge" style="background-color: var(--bubblegum); color: #23281E;">
                                                    <?= htmlspecialchars($p['status']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background-color: var(--pistachio); color: #23281E;">
                                                    <?= htmlspecialchars($p['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold col-denda text-end" style="color: <?= ($p['status'] === 'Dipinjam' && $denda_tampil > 0) ? 'var(--raspberry)' : 'var(--text-soft)' ?>;">
                                            <?= function_exists('format_rupiah') ? format_rupiah($denda_tampil) : 'Rp ' . number_format($denda_tampil, 0, ',', '.') ?>
                                            <?php if ($p['status'] === 'Dipinjam' && $hari_telat > 0): ?>
                                                <br><small class="fw-normal" style="color: var(--raspberry);">(Telat <?= (int)$hari_telat ?> hari)</small>
                                            <?php elseif ($p['status'] !== 'Dipinjam' && $denda_tampil > 0): ?>
                                                <br><small class="fw-normal" style="color: var(--grass);">(Selesai / Lunas)</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const htmlEl = document.documentElement;
    const btn = document.getElementById('toggleModeAnggotaArea');
    const icon = document.getElementById('iconModeAnggotaArea');
    const label = document.getElementById('labelModeAnggotaArea');
    const key = 'canda_admin_theme';

    function applyTheme(mode) {
        htmlEl.setAttribute('data-theme', mode);
        localStorage.setItem(key, mode);
        if (mode === 'dark') {
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
    }

    applyTheme(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        applyTheme(htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
})();
</script>
</body>
</html>