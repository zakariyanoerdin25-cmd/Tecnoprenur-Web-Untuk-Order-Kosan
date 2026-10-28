<?php
session_start();
include 'config/database.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id_user FROM tb_user WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $cek = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO tb_user (nama,email,password,no_hp,alamat,role) VALUES (?,?,?,?,?,'pelanggan')");
            mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $hash, $no_hp, $alamat);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registrasi berhasil. Silakan login.";
            } else {
                $error = "Registrasi gagal.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SupplyFinder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-card">
        <div class="login-logo"><span>SF</span><h2>SupplyFinder</h2></div>
        <div class="login-header">
            <h1>Buat Akun</h1>
            <p>Daftar untuk menggunakan layanan SupplyFinder.</p>
        </div>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="login-success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" placeholder="Masukkan nama lengkap" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="Masukkan email" required></div>
            <div class="form-group"><label>No. HP</label><input type="text" name="no_hp" placeholder="Masukkan nomor HP" required></div>
            <div class="form-group"><label>Alamat</label><textarea name="alamat" placeholder="Masukkan alamat" rows="3" required></textarea></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Buat password" required></div>
            <div class="form-group"><label>Konfirmasi Password</label><input type="password" name="konfirmasi" placeholder="Ulangi password" required></div>
            <button type="submit" name="register" class="login-button">Daftar</button>
        </form>

        <div class="register-text">Sudah memiliki akun? <a href="login.php">Login sekarang</a></div>
        <div class="back-home"><a href="index.php">← Kembali ke halaman utama</a></div>
    </div>
</div>
</body>
</html>
