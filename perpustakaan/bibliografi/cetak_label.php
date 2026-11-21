<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$where = "";
$selected_ids = [];
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $where = "WHERE id_buku = $id";
    $selected_ids[] = $id;
} elseif (isset($_POST['pilih_buku']) && is_array($_POST['pilih_buku'])) {
    $ids = array_map('intval', $_POST['pilih_buku']);
    $selected_ids = $ids;
    if (!empty($ids)) {
        $where = "WHERE id_buku IN (" . implode(',', $ids) . ")";
    }
}

$q_semua = mysqli_query($conn, "SELECT * FROM buku ORDER BY nomor_barcode ASC");
$q_cetak = mysqli_query($conn, "SELECT * FROM buku $where ORDER BY nomor_barcode ASC");

$nama_perpus = isset($pengaturan['nama_perpustakaan']) && $pengaturan['nama_perpustakaan'] !== ''
               ? $pengaturan['nama_perpustakaan']
               : 'CANDA Library';

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
        --bg-card-soft: #FAF7F2;
        --text-panel: #23281E;
        --text-soft: #646B5E;
        --border-panel: #E5DEC9;
    }

    body.dark-mode-label {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-card-soft: #252C22;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .label-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .label-panel-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .label-panel-card .card-header {
        background-color: var(--bg-card-soft) !important;
        color: var(--text-panel) !important;
        border-bottom: 1px solid var(--border-panel) !important;
    }

    .label-panel-card .form-select {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .label-panel-card .form-select:focus {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--grass);
        box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
    }

    .label-wrapper .text-muted {
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

    .label-card {
        border: 1.5px solid var(--border-panel);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-panel);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(35, 45, 27, 0.06);
    }

    .label-top {
        background: var(--grass);
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        padding: 4px 8px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 2px solid var(--bubblegum);
    }

    .call-num {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 800;
        font-size: 15px;
        line-height: 1.25;
        text-align: center;
        color: var(--text-panel);
    }

    @media print {
        .main-header, .main-sidebar, .no-print, footer { display: none !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; }
        .label-wrapper {
            background: #fff !important;
            color: #000 !important;
            border: none !important;
            padding: 0 !important;
        }
        body { background: #fff !important; color: #000 !important; }
        .label-card {
            page-break-inside: avoid;
            border: 1.5px solid #000 !important;
            background: #fff !important;
            color: #000 !important;
            box-shadow: none !important;
        }
        .label-top {
            background: #5E8A17 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .call-num { color: #000 !important; }
    }
</style>

<div class="label-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-upc-scan me-2" style="color: var(--grass);"></i>Cetak Label &amp; Barcode Koleksi
            </h4>
            <small class="text-muted">Mencetak Nomor Panggil (Call Number) &amp; Barcode (Nomor Barcode &amp; Nomor Induk) siap tempel.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModeLabel" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModeLabel"></i>
                <span id="labelModeLabel">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-journal-bookmark me-1"></i> Katalog Buku
            </a>
            <button onclick="window.print()" class="btn btn-grass btn-sm fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Print Label &amp; Barcode
            </button>
        </div>
    </div>

    <div class="card label-panel-card mb-4 no-print border-0 shadow-sm">
        <div class="card-header fw-bold">
            <i class="bi bi-funnel-fill me-1" style="color: var(--raspberry);"></i> Pilih Koleksi yang Akan Dicetak
        </div>
        <div class="card-body">
            <form method="POST" class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label class="form-label small">Tahan tombol <b>Ctrl</b> untuk memilih lebih dari satu buku (atau kosongkan untuk mencetak semua):</label>
                    <select name="pilih_buku[]" class="form-select" multiple size="4">
                        <?php if ($q_semua): ?>
                            <?php while($s = mysqli_fetch_assoc($q_semua)): ?>
                                <option value="<?= (int)$s['id_buku'] ?>" <?= in_array((int)$s['id_buku'], $selected_ids) ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($s['nomor_barcode']) ?> | <?= htmlspecialchars($s['nomor_induk']) ?>] <?= htmlspecialchars($s['judul']) ?> (<?= htmlspecialchars($s['no_panggil']) ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-grid gap-2">
                    <button type="submit" class="btn btn-grass fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Tampilkan Pilihan
                    </button>
                    <a href="cetak_label.php" class="btn btn-bubblegum btn-sm">Tampilkan Semua</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <?php if ($q_cetak && mysqli_num_rows($q_cetak) > 0): ?>
            <?php while($b = mysqli_fetch_assoc($q_cetak)):
                $baris_panggil = explode(' ', trim($b['no_panggil']));
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="label-card">
                    <div class="label-top"><?= htmlspecialchars($nama_perpus) ?></div>
                    <div class="row g-0 align-items-center p-2">
                        <div class="col-5 border-end pe-2" style="border-color: var(--border-panel) !important;">
                            <div class="call-num">
                                <?php foreach($baris_panggil as $bp): ?>
                                    <div><?= htmlspecialchars($bp) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-7 ps-2 text-center">
                            <div class="small fw-semibold text-truncate" style="font-size:11px;"><?= htmlspecialchars($b['judul']) ?></div>
                            <div class="text-muted" style="font-size:10px;">Induk: <?= htmlspecialchars($b['nomor_induk']) ?></div>
                            <svg class="barcode-item"
                                 jsbarcode-format="CODE128"
                                 jsbarcode-value="<?= htmlspecialchars($b['nomor_barcode']) ?>"
                                 jsbarcode-width="1.5"
                                 jsbarcode-height="34"
                                 jsbarcode-fontSize="11"
                                 jsbarcode-margin="2">
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card label-panel-card p-4 text-center text-muted">
                    Tidak ada koleksi buku yang cocok untuk dicetak.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode(".barcode-item").init();

    (function () {
        const btn = document.getElementById('toggleModeLabel');
        const icon = document.getElementById('iconModeLabel');
        const label = document.getElementById('labelModeLabel');
        const key = 'canda_admin_theme';

        function setMode(mode) {
            if (mode === 'dark') {
                document.body.classList.add('dark-mode-label');
                icon.className = 'bi bi-sun-fill me-1';
                label.textContent = 'Light Mode';
            } else {
                document.body.classList.remove('dark-mode-label');
                icon.className = 'bi bi-moon-stars-fill me-1';
                label.textContent = 'Dark Mode';
            }
            localStorage.setItem(key, mode);
        }

        setMode(localStorage.getItem(key) || 'light');

        btn.addEventListener('click', function () {
            const isDark = document.body.classList.contains('dark-mode-label');
            setMode(isDark ? 'light' : 'dark');
        });
    })();
</script>
<?php require_once '../includes/footer.php'; ?>