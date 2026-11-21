<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama', '$user', '$pass', '$role')");
    header("Location: user_tampil.php");
    exit;
}
require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Tambah Akun Pustakawan</h4>
    <a href="user_tampil.php" class="btn btn-secondary">Kembali</a>
</div>
<div class="card col-md-6">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="petugas">Petugas / Pustakawan</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Simpan Akun</button>
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
