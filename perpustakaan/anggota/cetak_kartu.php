<?php
require_once '../config/koneksi.php';

// Bisa diakses oleh pustakawan maupun anggota yang sedang login
$is_petugas = (isset($_SESSION['login_petugas']) && $_SESSION['login_petugas'] === true);
$is_anggota = (isset($_SESSION['login_anggota']) && $_SESSION['login_anggota'] === true);

if (!$is_petugas && !$is_anggota) {
    header("Location: ../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$is_petugas && $id !== (int)$_SESSION['id_anggota']) {
    $id = (int)$_SESSION['id_anggota'];
}

$q_anggota = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id");
$a = $q_anggota ? mysqli_fetch_assoc($q_anggota) : null;
if (!$a) {
    header("Location: tampil.php");
    exit;
}

$nama_perpus = (isset($pengaturan['nama_perpustakaan']) && $pengaturan['nama_perpustakaan'] !== '')
               ? $pengaturan['nama_perpustakaan']
               : 'CANDA Library';

$ttl_tampil = !empty($a['tempat_lahir']) ? htmlspecialchars($a['tempat_lahir']) : '-';
if (!empty($a['tanggal_lahir']) && $a['tanggal_lahir'] !== '0000-00-00') {
    $ttl_tampil .= ($ttl_tampil !== '-' ? ', ' : '') . date('d F Y', strtotime($a['tanggal_lahir']));
}

$tgl_gabung_tampil = (!empty($a['tgl_bergabung']) && $a['tgl_bergabung'] !== '0000-00-00')
                     ? date('d F Y', strtotime($a['tgl_bergabung']))
                     : '-';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Anggota - <?= htmlspecialchars($a['nama_anggota']) ?></title>
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
            --text-page: #23281E;
            --border-page: #E5DEC9;
            --card-bg-start: #232D1B;
            --card-bg-mid: #2F3E23;
            --card-bg-end: #3B4A28;
        }

        html[data-theme="dark"] {
            --bg-page: #141812;
            --text-page: #F6F0E8;
            --border-page: #323B2D;
            --card-bg-start: #1A2214;
            --card-bg-mid: #25311C;
            --card-bg-end: #2E3D23;
        }

        body {
            background: var(--bg-page);
            color: var(--text-page);
            padding: 30px 15px;
            font-family: 'Segoe UI', system-ui, sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .action-toolbar {
            max-width: 560px;
            margin: 0 auto 22px auto;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 6px 0;
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

        .kartu-anggota {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            background: linear-gradient(145deg, var(--card-bg-start) 0%, var(--card-bg-mid) 65%, var(--card-bg-end) 100%);
            color: #F6F0E8;
            border: 2px solid var(--pistachio);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 10px 24px rgba(35, 45, 27, 0.22);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .kartu-header {
            border-bottom: 2px solid var(--bubblegum) !important;
        }

        .kartu-perpus {
            color: var(--pistachio);
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        .kartu-biodata-box {
            background: transparent !important;
            color: #F6F0E8 !important;
        }

        .kartu-biodata-box .table,
        .kartu-biodata-box .table tr,
        .kartu-biodata-box .table td,
        .kartu-biodata-box .table th {
            background-color: transparent !important;
            color: #F6F0E8 !important;
            border-color: transparent !important;
        }

        .kartu-badge-id {
            background-color: var(--pistachio);
            color: #23281E;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
        }

        .kartu-barcode-box {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 4px 0 0 0;
        }

        #barcodeKartu {
            background: transparent;
            max-width: 100%;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .kartu-anggota {
                box-shadow: none !important;
                border: 2px solid #232D1B !important;
            }
        }
    </style>
</head>
<body>

<div class="action-toolbar text-center no-print">
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <button onclick="window.print()" class="btn btn-grass btn-sm fw-bold px-3">
            <i class="bi bi-printer-fill me-1"></i> Print File Kartu Anggota
        </button>
        <?php if ($is_petugas): ?>
            <a href="tampil.php" class="btn btn-bubblegum btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Anggota
            </a>
        <?php else: ?>
            <a href="../anggota_area.php" class="btn btn-bubblegum btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="kartu-anggota">
    <div class="text-center kartu-header pb-2 mb-3">
        <h5 class="fw-bold mb-0 text-uppercase" style="color: #F6F0E8;">
            <i class="bi bi-person-badge-fill me-1" style="color: var(--bubblegum);"></i> Kartu Anggota Perpustakaan
        </h5>
        <small class="kartu-perpus"><?= htmlspecialchars($nama_perpus) ?></small>
    </div>
    <div class="row align-items-center g-2 kartu-biodata-box">
        <div class="col-8 small">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td width="100">ID Anggota</td>
                    <td>: <span class="kartu-badge-id"><?= htmlspecialchars($a['kode_anggota']) ?></span></td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td class="fw-semibold">: <?= htmlspecialchars($a['nama_anggota']) ?></td>
                </tr>
                <tr>
                    <td>TTL</td>
                    <td>: <?= $ttl_tampil ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: <?= htmlspecialchars($a['alamat']) ?></td>
                </tr>
                <tr>
                    <td>Tgl Bergabung</td>
                    <td>: <?= $tgl_gabung_tampil ?></td>
                </tr>
            </table>
        </div>
        <div class="col-4 text-center">
            <?php if(!empty($a['foto']) && file_exists("../uploads/foto/" . $a['foto'])): ?>
                <img src="../uploads/foto/<?= htmlspecialchars($a['foto']) ?>" class="img-fluid rounded shadow-sm" style="width:85px;height:105px;object-fit:cover;border:2px solid var(--pistachio) !important;">
            <?php else: ?>
                <div class="rounded d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width:85px;height:105px;background-color:var(--ivory);color:#23281E;border:2px solid var(--pistachio);">
                    <i class="bi bi-person-fill fs-1" style="color: var(--grass);"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-3 text-center kartu-barcode-box">
        <svg id="barcodeKartu"></svg>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode("#barcodeKartu", "<?= htmlspecialchars($a['kode_anggota']) ?>", {
        format: "CODE128",
        width: 1.8,
        height: 32,
        fontSize: 12,
        margin: 0,
        background: "transparent",
        lineColor: "#F6F0E8"
    });

    (function () {
        const htmlEl = document.documentElement;
        const key = 'canda_admin_theme';
        const savedTheme = localStorage.getItem(key) || 'light';
        htmlEl.setAttribute('data-theme', savedTheme);
    })();
</script>
</body>
</html>