<?php
session_start();

require_once __DIR__ . '/config/database.php';

/* =========================
   REDIRECT TUJUAN
========================= */

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$id_kos   = (int) ($_GET['id'] ?? $_POST['id_kos'] ?? 0);


/* =========================
   JIKA SUDAH LOGIN
========================= */

if (
    isset($_SESSION['id_user']) &&
    isset($_SESSION['role'])
) {

    if (
        $_SESSION['role'] === 'admin'
    ) {

        header("Location: admin/dashboard.php");
        exit;
    }

    if (
        $_SESSION['role'] === 'pelanggan'
    ) {

        if (
            $redirect === 'ajukan_sewa' &&
            $id_kos > 0
        ) {

            header(
                "Location: ajukan_sewa.php?id=" .
                $id_kos
            );

            exit;
        }

        header(
            "Location: pelanggan/dashboard.php"
        );

        exit;
    }
}


$error = "";


/* =========================
   PROSES LOGIN
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email =
        trim($_POST['email'] ?? '');

    $password =
        $_POST['password'] ?? '';


    if (
        $email === '' ||
        $password === ''
    ) {

        $error =
            "Email dan password wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                id_user,
                nama,
                email,
                password,
                role
             FROM tb_user
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        $user =
            mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$user) {

            $error =
                "Email atau password salah.";

        } else {

            /*
             * Password baru menggunakan password_hash().
             */

            $password_valid =
                password_verify(
                    $password,
                    $user['password']
                );


            /*
             * Kompatibilitas dengan akun lama
             * yang mungkin masih menggunakan
             * password biasa.
             */

            if (
                !$password_valid &&
                $password === $user['password']
            ) {

                $password_valid = true;

                /*
                 * Langsung ubah password lama
                 * menjadi hash yang aman.
                 */

                $hash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                $stmt_update =
                    mysqli_prepare(
                        $conn,
                        "UPDATE tb_user
                         SET password = ?
                         WHERE id_user = ?"
                    );

                mysqli_stmt_bind_param(
                    $stmt_update,
                    "si",
                    $hash,
                    $user['id_user']
                );

                mysqli_stmt_execute(
                    $stmt_update
                );

                mysqli_stmt_close(
                    $stmt_update
                );
            }


            if (!$password_valid) {

                $error =
                    "Email atau password salah.";

            } else {

                /* =========================
                   SESSION
                ========================= */

                session_regenerate_id(true);

                $_SESSION['id_user'] =
                    $user['id_user'];

                $_SESSION['nama'] =
                    $user['nama'];

                $_SESSION['email'] =
                    $user['email'];

                $_SESSION['role'] =
                    $user['role'];


                /* =========================
                   REDIRECT
                ========================= */

                if (
                    $user['role'] === 'admin'
                ) {

                    header(
                        "Location: admin/dashboard.php"
                    );

                    exit;
                }


                if (
                    $user['role'] === 'pelanggan'
                ) {

                    if (
                        $redirect === 'ajukan_sewa' &&
                        $id_kos > 0
                    ) {

                        header(
                            "Location: ajukan_sewa.php?id=" .
                            $id_kos
                        );

                        exit;
                    }


                    header(
                        "Location: pelanggan/dashboard.php"
                    );

                    exit;
                }
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

    <title>
        Login - KosFinder
    </title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;

            background: #08111f;

            color: #f8fafc;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;
        }

        .login-wrapper {
            width: 100%;

            max-width: 430px;
        }

        .logo {
            text-align: center;

            margin-bottom: 25px;
        }

        .logo img {
            width: 70px;
            height: 70px;

            object-fit: contain;

            margin-bottom: 10px;
        }

        .logo h1 {
            font-size: 25px;

            margin-bottom: 5px;
        }

        .logo p {
            color: #94a3b8;

            font-size: 13px;
        }

        .card {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 16px;

            padding: 30px;
        }

        .card h2 {
            font-size: 21px;

            margin-bottom: 8px;
        }

        .card-description {
            color: #94a3b8;

            font-size: 13px;

            margin-bottom: 25px;
        }

        .alert {
            background: #450a0a;

            border: 1px solid #7f1d1d;

            color: #fca5a5;

            padding: 12px 14px;

            border-radius: 9px;

            font-size: 13px;

            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;

            color: #cbd5e1;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 8px;
        }

        input {
            width: 100%;

            background: #08111f;

            border: 1px solid #334155;

            color: white;

            padding: 12px 13px;

            border-radius: 9px;

            outline: none;

            font-family: inherit;

            font-size: 14px;
        }

        input:focus {
            border-color: #2563eb;
        }

        .btn-login {
            width: 100%;

            border: none;

            background: #2563eb;

            color: white;

            padding: 13px;

            border-radius: 9px;

            font-weight: 700;

            font-size: 14px;

            cursor: pointer;

            margin-top: 5px;

            transition: 0.2s;
        }

        .btn-login:hover {
            background: #1d4ed8;
        }

        .register {
            text-align: center;

            margin-top: 22px;

            color: #64748b;

            font-size: 13px;
        }

        .register a {
            color: #60a5fa;

            text-decoration: none;

            font-weight: 600;
        }

        .register a:hover {
            text-decoration: underline;
        }

        .back {
            display: block;

            text-align: center;

            color: #64748b;

            text-decoration: none;

            font-size: 12px;

            margin-top: 18px;
        }

        .back:hover {
            color: #cbd5e1;
        }

    </style>

</head>

<body>

<div class="login-wrapper">


    <!-- LOGO -->

    <div class="logo">

        <img
            src="assets/images/logo-kosfinder.png"
            alt="KosFinder"
        >

        <h1>
            KosFinder
        </h1>

        <p>
            Temukan kos nyaman sesuai kebutuhanmu
        </p>

    </div>


    <!-- CARD -->

    <div class="card">

        <h2>
            Selamat Datang 👋
        </h2>

        <p class="card-description">
            Silakan login untuk melanjutkan.
        </p>


        <?php if ($error !== ''): ?>

            <div class="alert">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
        >

            <!--
                Pertahankan tujuan redirect
                setelah login.
            -->

            <input
                type="hidden"
                name="redirect"
                value="<?= htmlspecialchars($redirect); ?>"
            >

            <input
                type="hidden"
                name="id_kos"
                value="<?= $id_kos; ?>"
            >


            <div class="form-group">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Masukkan email"
                    value="<?= htmlspecialchars(
                        $_POST['email'] ?? ''
                    ); ?>"
                    required
                    autocomplete="email"
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                    autocomplete="current-password"
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                Login
            </button>

        </form>


        <div class="register">

            Belum punya akun?

            <a href="register.php">
                Daftar sekarang
            </a>

        </div>

    </div>


    <a
        href="kos.php"
        class="back"
    >
        ← Kembali ke daftar kos
    </a>

</div>

</body>

</html>