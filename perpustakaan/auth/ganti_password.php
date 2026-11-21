<?php
require_once '../config/koneksi.php';
require_once '../includes/cek_login.php';

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass_baru = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi'];
    $id_user = $_SESSION['id_petugas'];

    if ($pass_baru !== $konfirmasi) {
        $pesan = "<div class='alert alert-danger'>Konfirmasi password baru tidak cocok!</div>";
    } elseif (strlen($pass_baru) < 5) {
        $pesan = "<div class='alert alert-danger'>Password minimal 5 karakter!</div>";
    } else {
        $hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password = '$hash' WHERE id_user = $id_user");
        $pesan = "<div class='alert alert-success'>Password berhasil diperbarui!</div>";
    }
}
require_once '../includes/header.php';
?>
<h4 class="fw-bold mb-4"><i class="bi bi-key-fill text-success me-2"></i>Ganti Password Pustakawan</h4>
<div class="card col-md-6">
    <div class="card-body">
        <?= $pesan ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="pass_baru" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="konfirmasi" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Simpan Password</button>
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
