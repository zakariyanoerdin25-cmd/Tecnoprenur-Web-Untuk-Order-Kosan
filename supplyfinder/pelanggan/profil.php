<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (
    !isset($_SESSION['id_user']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

include '../config/database.php';

$id_user = $_SESSION['id_user'];

$pesan = "";
$error = "";

/*
|--------------------------------------------------------------------------
| Ambil data pelanggan
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT nama, email, no_hp, alamat
     FROM tb_user
     WHERE id_user = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_user
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Data pelanggan tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| Update Profil
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_profil'])) {

    $nama   = trim($_POST['nama']);
    $email  = trim($_POST['email']);
    $no_hp  = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);

    if ($nama === "" || $email === "") {

        $error = "Nama dan email wajib diisi.";

    } else {

        // Cek apakah email sudah digunakan akun lain
        $cek = mysqli_prepare(
            $conn,
            "SELECT id_user
             FROM tb_user
             WHERE email = ?
             AND id_user != ?"
        );

        mysqli_stmt_bind_param(
            $cek,
            "si",
            $email,
            $id_user
        );

        mysqli_stmt_execute($cek);

        $cek_result = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($cek_result) > 0) {

            $error = "Email tersebut sudah digunakan.";

        } else {

            $update = mysqli_prepare(
                $conn,
                "UPDATE tb_user
                 SET nama = ?,
                     email = ?,
                     no_hp = ?,
                     alamat = ?
                 WHERE id_user = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ssssi",
                $nama,
                $email,
                $no_hp,
                $alamat,
                $id_user
            );

            if (mysqli_stmt_execute($update)) {

                $_SESSION['nama']  = $nama;
                $_SESSION['email'] = $email;

                $pesan = "Profil berhasil diperbarui.";

                // Update data yang ditampilkan
                $user['nama']   = $nama;
                $user['email']  = $email;
                $user['no_hp']  = $no_hp;
                $user['alamat'] = $alamat;

            } else {

                $error = "Gagal memperbarui profil.";

            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Ubah Password
|--------------------------------------------------------------------------
*/

if (isset($_POST['ubah_password'])) {

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    // Ambil password dari database
    $stmt_pass = mysqli_prepare(
        $conn,
        "SELECT password
         FROM tb_user
         WHERE id_user = ?"
    );

    mysqli_stmt_bind_param(
        $stmt_pass,
        "i",
        $id_user
    );

    mysqli_stmt_execute($stmt_pass);

    $pass_result = mysqli_stmt_get_result($stmt_pass);

    $data_pass = mysqli_fetch_assoc($pass_result);

    if (!$data_pass) {

        $error = "Data password tidak ditemukan.";

    } else {

        /*
         * Mendukung password yang masih plaintext
         * maupun password yang sudah menggunakan hash.
         */

        $password_valid = false;

        if (
            password_get_info($data_pass['password'])['algo'] !== 0
        ) {

            $password_valid = password_verify(
                $password_lama,
                $data_pass['password']
            );

        } else {

            $password_valid =
                ($password_lama === $data_pass['password']);
        }


        if (!$password_valid) {

            $error = "Password lama salah.";

        } elseif (strlen($password_baru) < 6) {

            $error = "Password baru minimal 6 karakter.";

        } elseif ($password_baru !== $konfirmasi) {

            $error = "Konfirmasi password tidak cocok.";

        } else {

            /*
             * Gunakan hash untuk password baru.
             */
            $password_hash = password_hash(
                $password_baru,
                PASSWORD_DEFAULT
            );

            $update_pass = mysqli_prepare(
                $conn,
                "UPDATE tb_user
                 SET password = ?
                 WHERE id_user = ?"
            );

            mysqli_stmt_bind_param(
                $update_pass,
                "si",
                $password_hash,
                $id_user
            );

            if (mysqli_stmt_execute($update_pass)) {

                $pesan = "Password berhasil diubah.";

            } else {

                $error = "Gagal mengubah password.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profil Saya - SupplyFinder</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .profile-page {
            padding: 45px 0;
        }

        .profile-header {
            margin-bottom: 30px;
        }

        .profile-header h1 {
            margin: 10px 0;
        }

        .profile-header p {
            color: #666;
        }

        .profile-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .profile-card h2 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .btn {
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            background: #0d6efd;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .password-info {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
        }

    </style>

</head>

<body>

<nav class="navbar">

    <div class="container nav-content">

        <a
            href="../index.php"
            class="brand"
        >
            <span>SF</span> SupplyFinder
        </a>

        <div class="nav-links">

            <a href="../kos.php">
                Cari Kos
            </a>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a
                href="../logout.php"
                class="nav-login"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<section class="profile-page">

    <div class="container">

        <div class="profile-header">

            <span class="eyebrow">
                PELANGGAN
            </span>

            <h1>
                Profil Saya
            </h1>

            <p>
                Kelola informasi akun dan password Anda.
            </p>

        </div>


        <?php if ($pesan !== ""): ?>

            <div class="alert-success">
                <?= htmlspecialchars($pesan); ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="alert-error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <div class="profile-grid">


            <!-- PROFIL -->

            <div class="profile-card">

                <h2>
                    👤 Informasi Profil
                </h2>

                <form
                    method="POST"
                >

                    <div class="form-group">

                        <label>
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            name="nama"
                            value="<?= htmlspecialchars($user['nama']); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($user['email']); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Nomor HP
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            value="<?= htmlspecialchars($user['no_hp'] ?? ''); ?>"
                            placeholder="Contoh: 08123456789"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            placeholder="Masukkan alamat Anda"
                        ><?= htmlspecialchars($user['alamat'] ?? ''); ?></textarea>

                    </div>


                    <button
                        type="submit"
                        name="update_profil"
                        class="btn"
                    >
                        Simpan Perubahan
                    </button>

                </form>

            </div>


            <!-- PASSWORD -->

            <div class="profile-card">

                <h2>
                    🔐 Ubah Password
                </h2>

                <p class="password-info">
                    Password baru minimal 6 karakter.
                </p>

                <form
                    method="POST"
                >

                    <div class="form-group">

                        <label>
                            Password Lama
                        </label>

                        <input
                            type="password"
                            name="password_lama"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Password Baru
                        </label>

                        <input
                            type="password"
                            name="password_baru"
                            minlength="6"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Konfirmasi Password Baru
                        </label>

                        <input
                            type="password"
                            name="konfirmasi_password"
                            minlength="6"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="ubah_password"
                        class="btn"
                    >
                        Ubah Password
                    </button>

                </form>

            </div>

        </div>


        <a
            href="dashboard.php"
            class="back"
        >
            ← Kembali ke Dashboard
        </a>

    </div>

</section>

</body>

</html>