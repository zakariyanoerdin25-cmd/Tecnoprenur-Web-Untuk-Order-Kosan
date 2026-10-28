<?php

session_start();

require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kota = isset($_GET['kota']) ? trim($_GET['kota']) : '';
$tipe = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';

$harga_min = isset($_GET['harga_min']) && $_GET['harga_min'] !== ''
    ? (int) $_GET['harga_min']
    : 0;

$harga_max = isset($_GET['harga_max']) && $_GET['harga_max'] !== ''
    ? (int) $_GET['harga_max']
    : 0;


/*
|--------------------------------------------------------------------------
| QUERY KOS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM tb_kos
    WHERE status = 'tersedia'
";

$params = [];
$types = "";


/* Pencarian */

if ($q !== '') {

    $sql .= "
        AND (
            nama_kos LIKE ?
            OR kota LIKE ?
            OR alamat LIKE ?
        )
    ";

    $search = "%" . $q . "%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "sss";
}


/* Kota */

if ($kota !== '') {

    $sql .= " AND kota = ? ";

    $params[] = $kota;

    $types .= "s";
}


/* Tipe kamar */

if ($tipe !== '') {

    $sql .= " AND tipe_kamar = ? ";

    $params[] = $tipe;

    $types .= "s";
}


/* Harga minimum */

if ($harga_min > 0) {

    $sql .= " AND harga >= ? ";

    $params[] = $harga_min;

    $types .= "i";
}


/* Harga maksimum */

if ($harga_max > 0) {

    $sql .= " AND harga <= ? ";

    $params[] = $harga_max;

    $types .= "i";
}


$sql .= "
    ORDER BY id_kos DESC
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}


if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| DATA KOTA
|--------------------------------------------------------------------------
*/

$kota_result = mysqli_query(
    $conn,
    "SELECT DISTINCT kota
     FROM tb_kos
     WHERE status = 'tersedia'
     ORDER BY kota ASC"
);


/*
|--------------------------------------------------------------------------
| DATA TIPE KAMAR
|--------------------------------------------------------------------------
*/

$tipe_result = mysqli_query(
    $conn,
    "SELECT DISTINCT tipe_kamar
     FROM tb_kos
     WHERE status = 'tersedia'
     ORDER BY tipe_kamar ASC"
);

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
        Cari Kos - KosFinder
    </title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #020817;

            color: white;
        }

        a {
            text-decoration: none;
        }

        .container {
            width: 90%;

            max-width: 1200px;

            margin: auto;
        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {

            width: 100%;

            background:
                #020817;

            border-bottom:
                1px solid
                rgba(255,255,255,0.08);

        }


        .nav-content {

            min-height: 78px;

            display: flex;

            align-items: center;

            justify-content: space-between;

        }


        /* LOGO */

        .brand {

            display: flex;

            align-items: center;

            width: 150px;

            height: 60px;

            overflow: hidden;

        }


        .brand img {

            width: 145px;

            height: 58px;

            object-fit: contain;

            display: block;

        }


        /* MENU */

        .nav-links {

            display: flex;

            align-items: center;

            gap: 26px;

        }


        .nav-links a {

            color: #dce7f7;

            font-size: 14px;

            transition: 0.2s;

        }


        .nav-links a:hover {

            color: #078cff;

        }


        .nav-links .active {

            color: #078cff;

            font-weight: 600;

        }


        .login-btn {

            border:
                1px solid
                rgba(255,255,255,0.6);

            padding:
                9px 20px;

            border-radius: 25px;

        }


        .register-btn {

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white !important;

            padding:
                10px 21px;

            border-radius: 25px;

            font-weight: 600;

        }


        /* =====================================================
           MAIN
        ===================================================== */

        .page {

            min-height: calc(100vh - 78px);

            padding:
                65px 0 90px;

            background:

                radial-gradient(
                    circle at 85% 5%,
                    rgba(0,130,255,0.18),
                    transparent 28%
                ),

                linear-gradient(
                    180deg,
                    #020817,
                    #03152d
                );

        }


        /* =====================================================
           HEADER
        ===================================================== */

        .page-header {

            text-align: center;

            margin-bottom: 40px;

        }


        .eyebrow {

            color: #078cff;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: 2px;

        }


        .page-header h1 {

            margin:
                12px 0;

            font-size: 44px;

            line-height: 1.15;

        }


        .page-header h1 span {

            color: #078cff;

        }


        .page-header p {

            color: #9fb2ca;

            max-width: 650px;

            margin: auto;

            line-height: 1.7;

        }


        /* =====================================================
           SEARCH BOX
        ===================================================== */

        .search-box {

            background:
                rgba(7,31,59,0.90);

            border:
                1px solid
                rgba(0,140,255,0.35);

            border-radius: 18px;

            padding: 22px;

            margin-bottom: 40px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.20);

        }


        .search-row {

            display: flex;

            gap: 10px;

            margin-bottom: 15px;

        }


        .search-input {

            flex: 1;

            height: 52px;

            border: none;

            outline: none;

            border-radius: 9px;

            padding:
                0 17px;

            font-size: 14px;

            color: #222;

        }


        .search-button {

            height: 52px;

            border: none;

            border-radius: 9px;

            padding:
                0 28px;

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white;

            font-weight: 700;

            cursor: pointer;

        }


        .filter-row {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 10px;

        }


        .filter-row select,

        .filter-row input {

            width: 100%;

            height: 46px;

            border: none;

            outline: none;

            border-radius: 8px;

            padding:
                0 12px;

            background: white;

            color: #222;

        }


        .filter-actions {

            display: flex;

            justify-content: flex-end;

            margin-top: 12px;

        }


        .reset-button {

            color: #54b5ff;

            border:
                1px solid
                rgba(0,145,255,0.5);

            padding:
                9px 17px;

            border-radius: 8px;

            font-size: 13px;

        }


        .reset-button:hover {

            background:
                rgba(0,130,255,0.12);

        }


        /* =====================================================
           HASIL
        ===================================================== */

        .result-top {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 22px;

        }


        .result-top h2 {

            margin: 0;

            font-size: 24px;

        }


        .result-count {

            color: #8da3bc;

            font-size: 14px;

        }


        /* =====================================================
           KOS GRID
        ===================================================== */

        .kos-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(280px, 1fr)
                );

            gap: 25px;

        }


        /* =====================================================
           KOS CARD
        ===================================================== */

        .kos-card {

            background:
                linear-gradient(
                    145deg,
                    #0a213f,
                    #061326
                );

            border:
                1px solid
                rgba(0,133,255,0.35);

            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.18);

            transition: 0.25s;

        }


        .kos-card:hover {

            transform:
                translateY(-6px);

            border-color:
                rgba(0,155,255,0.8);

            box-shadow:
                0 15px 40px
                rgba(0,100,255,0.20);

        }


        /* =====================================================
           FOTO
        ===================================================== */

        .kos-photo {

            width: 100%;

            height: 220px;

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #102b4d,
                    #061426
                );

        }


        .kos-photo img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

            transition: 0.3s;

        }


        .kos-card:hover
        .kos-photo img {

            transform:
                scale(1.04);

        }


        .no-photo {

            width: 100%;

            height: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #7890a9;

            font-size: 15px;

        }


        .status {

            position: absolute;

            top: 13px;

            right: 13px;

            background:
                rgba(0,150,90,0.92);

            color: white;

            padding:
                6px 11px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 700;

        }


        /* =====================================================
           CARD CONTENT
        ===================================================== */

        .kos-content {

            padding: 20px;

        }


        .kos-content h3 {

            color: white;

            font-size: 20px;

            margin:
                0 0 9px;

        }


        .location {

            color: #9fb2ca;

            font-size: 13px;

            line-height: 1.5;

            min-height: 40px;

            margin-bottom: 10px;

        }


        .price {

            color: #0795ff;

            font-size: 20px;

            font-weight: 700;

            margin-bottom: 12px;

        }


        .price span {

            color: #8fa4bc;

            font-size: 12px;

            font-weight: normal;

        }


        .badges {

            display: flex;

            flex-wrap: wrap;

            gap: 7px;

            margin-bottom: 17px;

        }


        .badge {

            color: #42adff;

            background:
                rgba(0,130,255,0.12);

            border:
                1px solid
                rgba(0,140,255,0.25);

            padding:
                6px 9px;

            border-radius: 6px;

            font-size: 12px;

        }


        .detail-button {

            display: block;

            width: 100%;

            text-align: center;

            padding:
                11px;

            border-radius: 8px;

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white;

            font-weight: 600;

            transition: 0.2s;

        }


        .detail-button:hover {

            box-shadow:
                0 6px 18px
                rgba(0,120,255,0.30);

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {

            text-align: center;

            padding:
                70px 20px;

            border:
                1px solid
                rgba(0,140,255,0.3);

            background:
                rgba(5,28,55,0.7);

            border-radius: 16px;

        }


        .empty-icon {

            font-size: 50px;

            margin-bottom: 15px;

        }


        .empty h3 {

            margin:
                0 0 10px;

        }


        .empty p {

            color: #9fb2ca;

        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {

            background: #010713;

            padding:
                50px 0 20px;

        }


        .footer-grid {

            display: grid;

            grid-template-columns:
                2fr 1fr 1fr;

            gap: 40px;

        }


        .footer-logo {

            width: 145px;

            height: 55px;

            object-fit: contain;

            margin-bottom: 12px;

        }


        .footer p {

            color: #8999ad;

            line-height: 1.7;

            max-width: 430px;

        }


        .footer h4 {

            margin-top: 0;

            color: white;

        }


        .footer a {

            display: block;

            color: #8999ad;

            margin-bottom: 10px;

        }


        .footer a:hover {

            color: #0795ff;

        }


        .copyright {

            border-top:
                1px solid
                #1c2a3b;

            margin-top: 35px;

            padding-top: 20px;

            text-align: center;

            color: #66768b;

            font-size: 13px;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            .filter-row {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .hero {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 700px) {

            .nav-content {

                flex-direction: column;

                padding:
                    12px 0;

                gap: 10px;

            }


            .nav-links {

                flex-wrap: wrap;

                justify-content: center;

                gap: 13px;

            }


            .page-header h1 {

                font-size: 34px;

            }


            .search-row {

                flex-direction: column;

            }


            .search-button {

                width: 100%;

            }


            .filter-row {

                grid-template-columns: 1fr;

            }


            .result-top {

                flex-direction: column;

                align-items: flex-start;

                gap: 7px;

            }


            .footer-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <div class="container nav-content">


        <!-- LOGO -->

        <a
            href="index.php"
            class="brand"
        >

            <img
                src="assets/images/logo-kosfinder.png"
                alt="KosFinder"
            >

        </a>


        <!-- MENU -->

        <div class="nav-links">


            <a href="index.php">
                Beranda
            </a>


            <a
                href="kos.php"
                class="active"
            >
                Cari Kos
            </a>


            <a href="index.php#tentang">
                Tentang Kami
            </a>


            <?php if (
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'admin'
            ): ?>

                <a href="admin/dashboard.php">
                    Dashboard
                </a>

                <a
                    href="logout.php"
                    class="login-btn"
                >
                    Logout
                </a>


            <?php elseif (
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'pelanggan'
            ): ?>

                <a href="pelanggan/dashboard.php">
                    Dashboard
                </a>

                <a
                    href="logout.php"
                    class="login-btn"
                >
                    Logout
                </a>


            <?php else: ?>

                <a
                    href="login.php"
                    class="login-btn"
                >
                    Login
                </a>

                <a
                    href="register.php"
                    class="register-btn"
                >
                    Daftar
                </a>

            <?php endif; ?>


        </div>

    </div>

</nav>


<!-- =====================================================
     PAGE
===================================================== -->

<main class="page">

    <div class="container">


        <!-- HEADER -->

        <div class="page-header">

            <div class="eyebrow">
                KOSFINDER
            </div>

            <h1>
                Temukan
                <span>Kos Impianmu</span>
            </h1>

            <p>
                Cari kos berdasarkan lokasi, harga,
                tipe kamar, dan kebutuhan Anda.
            </p>

        </div>


        <!-- =================================================
             SEARCH
        ================================================= -->

        <form
            action="kos.php"
            method="GET"
            class="search-box"
        >


            <div class="search-row">

                <input
                    type="text"
                    name="q"
                    class="search-input"
                    value="<?= htmlspecialchars($q); ?>"
                    placeholder="🔍 Cari nama kos, kota, atau alamat..."
                >

                <button
                    type="submit"
                    class="search-button"
                >
                    🔍 Cari Kos
                </button>

            </div>


            <div class="filter-row">


                <!-- KOTA -->

                <select name="kota">

                    <option value="">
                        Semua Kota
                    </option>


                    <?php while (
                        $row = mysqli_fetch_assoc($kota_result)
                    ): ?>

                        <option
                            value="<?= htmlspecialchars($row['kota']); ?>"
                            <?= $kota === $row['kota']
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars(
                                $row['kota']
                            ); ?>

                        </option>

                    <?php endwhile; ?>

                </select>


                <!-- TIPE -->

                <select name="tipe">

                    <option value="">
                        Semua Tipe Kamar
                    </option>


                    <?php while (
                        $row = mysqli_fetch_assoc($tipe_result)
                    ): ?>

                        <option
                            value="<?= htmlspecialchars($row['tipe_kamar']); ?>"
                            <?= $tipe === $row['tipe_kamar']
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars(
                                $row['tipe_kamar']
                            ); ?>

                        </option>

                    <?php endwhile; ?>

                </select>


                <!-- HARGA MIN -->

                <input
                    type="number"
                    name="harga_min"
                    value="<?= $harga_min > 0
                        ? $harga_min
                        : ''; ?>"
                    placeholder="Harga minimum"
                >


                <!-- HARGA MAX -->

                <input
                    type="number"
                    name="harga_max"
                    value="<?= $harga_max > 0
                        ? $harga_max
                        : ''; ?>"
                    placeholder="Harga maksimum"
                >

            </div>


            <div class="filter-actions">

                <a
                    href="kos.php"
                    class="reset-button"
                >
                    ↻ Reset Filter
                </a>

            </div>


        </form>


        <!-- =================================================
             HASIL
        ================================================= -->

        <div class="result-top">

            <h2>
                Kos Tersedia
            </h2>

            <div class="result-count">

                <?= mysqli_num_rows($result); ?>

                kos ditemukan

            </div>

        </div>


        <?php if (
            mysqli_num_rows($result) > 0
        ): ?>


            <!-- =================================================
                 KOS
            ================================================= -->

            <div class="kos-grid">


                <?php while (
                    $kos = mysqli_fetch_assoc($result)
                ): ?>


                    <article class="kos-card">


                        <!-- FOTO -->

                        <div class="kos-photo">


                            <?php if (
                                !empty($kos['foto'])
                            ): ?>

                                <img
                                    src="assets/images/<?= htmlspecialchars($kos['foto']); ?>"
                                    alt="<?= htmlspecialchars($kos['nama_kos']); ?>"
                                >

                            <?php else: ?>

                                <div class="no-photo">

                                    🏠
                                    &nbsp;
                                    Foto belum tersedia

                                </div>

                            <?php endif; ?>


                            <div class="status">
                                ✓ Tersedia
                            </div>


                        </div>


                        <!-- CONTENT -->

                        <div class="kos-content">


                            <h3>

                                <?= htmlspecialchars(
                                    $kos['nama_kos']
                                ); ?>

                            </h3>


                            <div class="location">

                                📍

                                <?= htmlspecialchars(
                                    $kos['alamat']
                                ); ?>

                                <br>

                                <?= htmlspecialchars(
                                    $kos['kota']
                                ); ?>

                            </div>


                            <div class="price">

                                Rp
                                <?= number_format(
                                    $kos['harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                                <span>
                                    / bulan
                                </span>

                            </div>


                            <div class="badges">


                                <div class="badge">

                                    🛏️

                                    <?= htmlspecialchars(
                                        $kos['tipe_kamar']
                                    ); ?>

                                </div>


                                <div class="badge">

                                    🚪

                                    <?= (int)
                                        $kos['jumlah_kamar']; ?>

                                    kamar

                                </div>


                            </div>


                            <a
                                href="detail_kos.php?id=<?= $kos['id_kos']; ?>"
                                class="detail-button"
                            >
                                Lihat Detail
                            </a>


                        </div>


                    </article>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <!-- TIDAK ADA DATA -->

            <div class="empty">

                <div class="empty-icon">
                    🔍
                </div>

                <h3>
                    Kos Tidak Ditemukan
                </h3>

                <p>
                    Tidak ada kos yang sesuai dengan
                    pencarian atau filter Anda.
                </p>

                <br>

                <a
                    href="kos.php"
                    class="detail-button"
                    style="
                        display:inline-block;
                        width:auto;
                        padding:11px 25px;
                    "
                >
                    Lihat Semua Kos
                </a>

            </div>


        <?php endif; ?>


    </div>

</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="container">


        <div class="footer-grid">


            <!-- INFORMASI -->

            <div>

                <img
                    src="assets/images/logo-kosfinder.png"
                    alt="KosFinder"
                    class="footer-logo"
                >

                <p>

                    KosFinder adalah platform pencarian
                    kos yang membantu Anda menemukan
                    tempat tinggal yang nyaman,
                    sesuai lokasi dan budget.

                </p>

            </div>


            <!-- MENU -->

            <div>

                <h4>
                    Menu
                </h4>

                <a href="index.php">
                    Beranda
                </a>

                <a href="kos.php">
                    Cari Kos
                </a>

                <a href="index.php#tentang">
                    Tentang Kami
                </a>

            </div>


            <!-- AKUN -->

            <div>

                <h4>
                    Akun
                </h4>


                <?php if (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === 'pelanggan'
                ): ?>

                    <a href="pelanggan/dashboard.php">
                        Dashboard
                    </a>

                    <a href="pelanggan/profil.php">
                        Profil Saya
                    </a>

                    <a href="logout.php">
                        Logout
                    </a>


                <?php elseif (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === 'admin'
                ): ?>

                    <a href="admin/dashboard.php">
                        Dashboard Admin
                    </a>

                    <a href="logout.php">
                        Logout
                    </a>


                <?php else: ?>

                    <a href="login.php">
                        Login
                    </a>

                    <a href="register.php">
                        Daftar
                    </a>

                <?php endif; ?>


            </div>


        </div>


        <div class="copyright">

            © <?= date('Y'); ?>

            KosFinder.

            Semua hak dilindungi.

        </div>


    </div>

</footer>


</body>

</html>