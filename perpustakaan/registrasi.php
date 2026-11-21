<?php
require_once 'config/koneksi.php';

$pesan_sukses = "";
$pesan_error  = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_anggota  = "AG-" . rand(1000, 9999); // Generate kode anggota otomatis
    $nama_anggota  = mysqli_real_escape_string($conn, trim($_POST['nama_anggota']));
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $no_telp       = mysqli_real_escape_string($conn, trim($_POST['no_telp']));
    $alamat        = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $username      = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password      = md5($_POST['password']); // Atau gunakan password_hash jika disamakan dengan sistem loginmu
    $status_aktif  = 'Aktif';
    $tgl_gabung    = date('Y-m-d');

    // Cek apakah username sudah digunakan
    $cek_user = mysqli_query($conn, "SELECT * FROM anggota WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan_error = "Username <strong>$username</strong> sudah digunakan! Silakan gunakan username lain.";
    } else {
        // Proses Upload Foto Profil
        $nama_foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            $file_name   = $_FILES['foto']['name'];
            $file_tmp    = $_FILES['foto']['tmp_name'];
            $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_ext)) {
                $nama_foto = 'foto_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                $upload_dir  = 'uploads/foto/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($file_tmp, $upload_dir . $nama_foto);
            }
        }

        // Simpan data ke tabel anggota
        $sql = "INSERT INTO anggota (kode_anggota, nama_anggota, jenis_kelamin, no_telp, alamat, username, password, status_aktif, foto) 
                VALUES ('$kode_anggota', '$nama_anggota', '$jenis_kelamin', '$no_telp', '$alamat', '$username', '$password', '$status_aktif', '$nama_foto')";
        
        if (mysqli_query($conn, $sql)) {
            $pesan_sukses = "Pendaftaran berhasil! Kode Anggota Anda: <strong>$kode_anggota</strong>. Silakan <a href='auth/login.php?role=anggota' class='alert-link'>Login di sini</a>.";
        } else {
            $pesan_error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Anggota - CANDA Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center py-4" style="background-color: #F0FFDF !important; font-family: 'Garamond', system-ui, serif;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white fw-bold py-3 text-center" style="background: linear-gradient(135deg, #1b4332 0%, #B8DB80 100%); font-size: 20px;">
                    <i class="bi bi-person-plus-fill me-2"></i> Pendaftaran Anggota Baru CANDA Library
                </div>
                <div class="card-body p-4">
                    
                    <?php if (!empty($pesan_sukses)): ?>
                        <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_anggota" class="form-control" placeholder="Masukkan nama lengkap..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="no_telp" class="form-control" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat domisili..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" placeholder="Buat username login..." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Buat password..." required>
                            </div>
                        </div>

                        <!-- INPUT UPLOAD FOTO PROFIL MANDIRI -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Unggah Foto Profil <span class="text-danger">*</span></label>
                            <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .webp" required>
                            <small class="text-muted">Pilih foto terbaikmu (Format: JPG, PNG, WEBP).</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn text-white fw-bold py-2" style="background-color: #C37070;">
                                <i class="bi bi-check-circle me-1"></i> Daftar Sekarang
                            </button>
                            <a href="auth/login.php?role=anggota" class="btn btn-outline-secondary btn-sm">Sudah punya akun? Login di sini</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>