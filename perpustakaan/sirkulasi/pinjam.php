<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan_error = "";

// Buat nomor transaksi otomatis
$no_pinjam_otomatis = "PJ-" . date("Ymd") . "-" . rand(100, 999);
$tgl_pinjam_default = date("Y-m-d");
$lama_default       = isset($pengaturan['lama_pinjam']) ? (int)$pengaturan['lama_pinjam'] : 7;
$tgl_tempo_default  = date("Y-m-d", strtotime("+$lama_default days"));

// Ambil daftar anggota aktif lebih dulu agar bisa dipakai di POST maupun form
$daftar_anggota = [];
$q_anggota = mysqli_query($conn, "SELECT id_anggota, kode_anggota, nama_anggota FROM anggota WHERE status_aktif = 'Aktif' ORDER BY nama_anggota ASC");
if ($q_anggota) {
    while ($row_a = mysqli_fetch_assoc($q_anggota)) {
        $daftar_anggota[] = $row_a;
    }
}

// Ambil daftar buku
$daftar_buku = [];
$q_buku = mysqli_query($conn, "SELECT id_buku, nomor_barcode, judul FROM buku ORDER BY judul ASC");
if ($q_buku) {
    while ($row_b = mysqli_fetch_assoc($q_buku)) {
        $daftar_buku[] = $row_b;
    }
}

$kode_anggota_input = "";
$barcode_input      = "";
$id_anggota_post    = 0;
$id_buku_post       = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_pinjam          = isset($_POST['no_pinjam']) ? mysqli_real_escape_string($conn, trim($_POST['no_pinjam'])) : '';
    $id_anggota_post    = isset($_POST['id_anggota']) ? (int)$_POST['id_anggota'] : 0;
    $id_buku_post       = isset($_POST['id_buku']) ? (int)$_POST['id_buku'] : 0;
    $kode_anggota_input = isset($_POST['kode_anggota_input']) ? trim($_POST['kode_anggota_input']) : '';
    $barcode_input      = isset($_POST['barcode_input']) ? trim($_POST['barcode_input']) : '';
    $tgl_pinjam         = isset($_POST['tgl_pinjam']) ? mysqli_real_escape_string($conn, $_POST['tgl_pinjam']) : $tgl_pinjam_default;
    $tgl_jatuh_tempo    = isset($_POST['tgl_jatuh_tempo']) ? mysqli_real_escape_string($conn, $_POST['tgl_jatuh_tempo']) : $tgl_tempo_default;

    // Prioritaskan input teks kode anggota jika diisi
    if ($kode_anggota_input !== '') {
        $kode_aman = mysqli_real_escape_string($conn, $kode_anggota_input);
        $cek_a = mysqli_query($conn, "SELECT id_anggota FROM anggota WHERE kode_anggota = '$kode_aman' AND status_aktif = 'Aktif' LIMIT 1");
        if ($cek_a && $row_ca = mysqli_fetch_assoc($cek_a)) {
            $id_anggota_post = (int)$row_ca['id_anggota'];
        } else {
            $id_anggota_post = 0;
            $pesan_error = "Kode anggota \"" . htmlspecialchars($kode_anggota_input) . "\" tidak ditemukan atau tidak aktif!";
        }
    }

    // Prioritaskan input teks nomor barcode jika diisi
    if ($pesan_error === "" && $barcode_input !== '') {
        $barcode_aman = mysqli_real_escape_string($conn, $barcode_input);
        $cek_b = mysqli_query($conn, "SELECT id_buku FROM buku WHERE nomor_barcode = '$barcode_aman' LIMIT 1");
        if ($cek_b && $row_cb = mysqli_fetch_assoc($cek_b)) {
            $id_buku_post = (int)$row_cb['id_buku'];
        } else {
            $id_buku_post = 0;
            $pesan_error = "Nomor barcode \"" . htmlspecialchars($barcode_input) . "\" tidak ditemukan!";
        }
    }

    if ($pesan_error === "") {
        if ($id_anggota_post > 0 && $id_buku_post > 0) {
            // Cek apakah anggota sedang meminjam buku yang sama
            $cek = mysqli_query($conn, "SELECT * FROM sirkulasi WHERE id_anggota = $id_anggota_post AND id_buku = $id_buku_post AND status = 'Dipinjam'");
            if ($cek && mysqli_num_rows($cek) > 0) {
                $pesan_error = "Anggota tersebut masih meminjam buku ini dan belum mengembalikannya!";
            } else {
                // Simpan transaksi peminjaman ke tabel sirkulasi
                $sql_simpan = "INSERT INTO sirkulasi (no_pinjam, id_anggota, id_buku, tgl_pinjam, tgl_jatuh_tempo, status, denda) 
                               VALUES ('$no_pinjam', $id_anggota_post, $id_buku_post, '$tgl_pinjam', '$tgl_jatuh_tempo', 'Dipinjam', 0)";
                
                if (mysqli_query($conn, $sql_simpan)) {
                    // Kurangi stok buku otomatis jika kolom stok/jumlah tersedia di tabel buku
                    @mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku_post AND stok > 0");
                    
                    header("Location: tampil.php?msg=1");
                    exit;
                } else {
                    $pesan_error = "Gagal menyimpan ke database: " . mysqli_error($conn);
                }
            }
        } else {
            $pesan_error = "Silakan pilih atau ketik Kode Anggota dan Koleksi Buku / Nomor Barcode terlebih dahulu!";
        }
    }
}

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
        --bg-readonly: #EFE9DF;
        --text-panel: #23281E;
        --text-soft: #646B5E;
        --border-panel: #E5DEC9;
    }

    body.dark-mode-pinjam {
        --bg-panel: #141812;
        --bg-card: #1D231A;
        --bg-readonly: #2A3326;
        --text-panel: #F6F0E8;
        --text-soft: #B6BEA9;
        --border-panel: #323B2D;
    }

    .pinjam-wrapper {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border: 1px solid var(--border-panel);
        border-radius: 12px;
        padding: 20px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .pinjam-card {
        background-color: var(--bg-card) !important;
        color: var(--text-panel) !important;
        border: 1px solid var(--border-panel) !important;
        border-radius: 10px;
    }

    .pinjam-card .form-control,
    .pinjam-card .form-select {
        background-color: var(--bg-panel);
        color: var(--text-panel);
        border-color: var(--border-panel);
    }

    .pinjam-card .form-control[readonly] {
        background-color: var(--bg-readonly);
        color: var(--grass);
    }

    .pinjam-card .form-control:focus,
    .pinjam-card .form-select:focus {
        background-color: var(--bg-card);
        color: var(--text-panel);
        border-color: var(--grass);
        box-shadow: 0 0 0 0.2rem rgba(94, 138, 23, 0.2);
    }

    .pinjam-card .text-muted {
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

<div class="pinjam-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-journal-plus me-2" style="color: var(--grass);"></i>Input Transaksi Peminjaman Baru
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="toggleModePinjam" class="btn btn-sm btn-raspberry">
                <i class="bi bi-moon-stars-fill me-1" id="iconModePinjam"></i>
                <span id="labelModePinjam">Dark Mode</span>
            </button>
            <a href="../admin/dashboard.php" class="btn btn-bubblegum btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <a href="tampil.php" class="btn btn-outline-secondary btn-sm" style="border-color: var(--grass); color: var(--text-panel);">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $pesan_error; ?>
        </div>
    <?php endif; ?>

    <div class="card pinjam-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="" method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">No. Peminjaman</label>
                        <input type="text" name="no_pinjam" class="form-control fw-bold" value="<?php echo htmlspecialchars($no_pinjam_otomatis); ?>" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam" class="form-control" value="<?php echo htmlspecialchars($tgl_pinjam_default); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                        <input type="date" name="tgl_jatuh_tempo" class="form-control" value="<?php echo htmlspecialchars($tgl_tempo_default); ?>" required>
                    </div>

                    <!-- ANGGOTA: KETIK KODE ANGGOTA ATAU PILIH DROPDOWN -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Kode Anggota (Ketik)</label>
                        <input type="text" name="kode_anggota_input" id="kode_anggota_input" class="form-control"
                               placeholder="Contoh: AG001" value="<?php echo htmlspecialchars($kode_anggota_input); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Pilih Pemustaka / Anggota</label>
                        <select name="id_anggota" id="id_anggota" class="form-select">
                            <option value="">-- Cari / Pilih Nama Anggota --</option>
                            <?php foreach ($daftar_anggota as $a): ?>
                                <option value="<?php echo (int)$a['id_anggota']; ?>"
                                        data-kode="<?php echo htmlspecialchars($a['kode_anggota']); ?>"
                                        <?php echo ($id_anggota_post === (int)$a['id_anggota']) ? 'selected' : ''; ?>>
                                    [<?php echo htmlspecialchars($a['kode_anggota']); ?>] <?php echo htmlspecialchars($a['nama_anggota']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- BUKU: KETIK / SCAN NOMOR BARCODE ATAU PILIH DROPDOWN -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Nomor Barcode (Ketik / Scan)</label>
                        <input type="text" name="barcode_input" id="barcode_input" class="form-control"
                               placeholder="Contoh: BK001" value="<?php echo htmlspecialchars($barcode_input); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Pilih Koleksi Buku</label>
                        <select name="id_buku" id="id_buku" class="form-select">
                            <option value="">-- Cari / Pilih Judul Buku --</option>
                            <?php foreach ($daftar_buku as $b): ?>
                                <option value="<?php echo (int)$b['id_buku']; ?>"
                                        data-barcode="<?php echo htmlspecialchars($b['nomor_barcode']); ?>"
                                        <?php echo ($id_buku_post === (int)$b['id_buku']) ? 'selected' : ''; ?>>
                                    [<?php echo htmlspecialchars($b['nomor_barcode']); ?>] <?php echo htmlspecialchars($b['judul']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-grass fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Proses Peminjaman
                    </button>
                    <a href="../admin/dashboard.php" class="btn btn-bubblegum px-4">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                    <a href="tampil.php" class="btn btn-outline-secondary px-4" style="border-color: var(--border-panel); color: var(--text-panel);">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('toggleModePinjam');
    const icon = document.getElementById('iconModePinjam');
    const label = document.getElementById('labelModePinjam');
    const key = 'canda_admin_theme';

    function setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode-pinjam');
            icon.className = 'bi bi-sun-fill me-1';
            label.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode-pinjam');
            icon.className = 'bi bi-moon-stars-fill me-1';
            label.textContent = 'Dark Mode';
        }
        localStorage.setItem(key, mode);
    }

    setMode(localStorage.getItem(key) || 'light');

    btn.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-mode-pinjam');
        setMode(isDark ? 'light' : 'dark');
    });

    // Sinkronisasi Kode Anggota <-> Dropdown Anggota
    const kodeInput = document.getElementById('kode_anggota_input');
    const selectAnggota = document.getElementById('id_anggota');

    if (kodeInput && selectAnggota) {
        kodeInput.addEventListener('input', function () {
            const val = this.value.trim().toLowerCase();
            let matched = false;
            for (let i = 0; i < selectAnggota.options.length; i++) {
                const opt = selectAnggota.options[i];
                const kode = (opt.getAttribute('data-kode') || '').toLowerCase();
                if (val !== '' && kode === val) {
                    selectAnggota.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched && val === '') {
                selectAnggota.selectedIndex = 0;
            }
        });

        selectAnggota.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const kode = opt ? (opt.getAttribute('data-kode') || '') : '';
            kodeInput.value = kode;
        });
    }

    // Sinkronisasi Nomor Barcode <-> Dropdown Buku
    const barcodeInput = document.getElementById('barcode_input');
    const selectBuku = document.getElementById('id_buku');

    if (barcodeInput && selectBuku) {
        barcodeInput.addEventListener('input', function () {
            const val = this.value.trim().toLowerCase();
            let matched = false;
            for (let i = 0; i < selectBuku.options.length; i++) {
                const opt = selectBuku.options[i];
                const bc = (opt.getAttribute('data-barcode') || '').toLowerCase();
                if (val !== '' && bc === val) {
                    selectBuku.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched && val === '') {
                selectBuku.selectedIndex = 0;
            }
        });

        selectBuku.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const bc = opt ? (opt.getAttribute('data-barcode') || '') : '';
            barcodeInput.value = bc;
        });
    }
})();
</script>

<?php require_once '../includes/footer.php'; ?>