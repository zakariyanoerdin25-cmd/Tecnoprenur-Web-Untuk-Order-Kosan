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


/*
|--------------------------------------------------------------------------
| Hitung Total Kos
|--------------------------------------------------------------------------
*/

$query_kos = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tb_kos"
);

$data_kos = mysqli_fetch_assoc($query_kos);

$total_kos = $data_kos['total'];


/*
|--------------------------------------------------------------------------
| Hitung Kos Tersedia
|--------------------------------------------------------------------------
*/

$query_tersedia = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tb_kos
     WHERE status = 'tersedia'"
);

$data_tersedia = mysqli_fetch_assoc($query_tersedia);

$total_tersedia = $data_tersedia['total'];


/*
|--------------------------------------------------------------------------
| Hitung Pelanggan
|--------------------------------------------------------------------------
*/

$query_pelanggan = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tb_user
     WHERE role = 'pelanggan'"
);

$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);

$total_pelanggan = $data_pelanggan['total'];


/*
|--------------------------------------------------------------------------
| Hitung Pengajuan Sewa
|--------------------------------------------------------------------------
*/

$query_sewa = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tb_sewa"
);

$data_sewa = mysqli_fetch_assoc($query_sewa);

$total_sewa = $data_sewa['total'];


/*
|--------------------------------------------------------------------------
| Hitung Pengajuan Menunggu
|--------------------------------------------------------------------------
*/

$query_menunggu = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tb_sewa
     WHERE status = 'menunggu'"
);

$data_menunggu = mysqli_fetch_assoc($query_menunggu);

$total_menunggu = $data_menunggu['total'];

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
        Dashboard Admin - SupplyFinder
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .dashboard-page {
            padding: 45px 0;
        }


        .dashboard-header {
            margin-bottom: 35px;
        }


        .dashboard-header h1 {
            margin: 10px 0;
        }


        .dashboard-header p {
            color: #666;
        }


        /* SUMMARY */

        .summary {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(180px, 1fr)
                );

            gap: 15px;

            margin-bottom: 35px;

        }


        .summary-card {

            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow:
                0 5px 15px rgba(0,0,0,.06);

        }


        .summary-card h4 {

            margin: 0 0 10px;

            color: #666;

            font-size: 15px;

        }


        .summary-number {

            font-size: 30px;

            font-weight: bold;

            color: #0d2b52;

        }


        /* MENU */

        .dashboard-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(220px, 1fr)
                );

            gap: 20px;

        }


        .dashboard-card {

            display: block;

            padding: 25px;

            background: white;

            border-radius: 14px;

            text-decoration: none;

            color: #222;

            box-shadow:
                0 5px 20px rgba(0,0,0,.08);

            transition: .2s;

        }


        .dashboard-card:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 8px 25px rgba(0,0,0,.12);

        }


        .dashboard-card .icon {

            font-size: 38px;

            margin-bottom: 10px;

        }


        .dashboard-card h3 {

            margin:
                10px 0 8px;

        }


        .dashboard-card p {

            color: #666;

            margin: 0;

            line-height: 1.5;

        }


        .jumlah {

            display: block;

            margin-top: 15px;

            font-size: 28px;

            font-weight: bold;

            color: #0d6efd;

        }


        .logout {

            color: #dc3545 !important;

        }


        /* RESPONSIVE */

        @media (max-width: 600px) {

            .dashboard-page {
                padding: 30px 15px;
            }

            .summary {
                grid-template-columns: 1fr 1fr;
            }

        }

    </style>

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="container nav-content">


        <a
            href="../index.php"
            class="brand"
        >

            <span>SF</span>

            SupplyFinder

        </a>


        <div class="nav-links">

            <a href="../kos.php">
                Lihat Kos
            </a>


            <a href="dashboard.php">
                Dashboard
            </a>


            <a
                href="../logout.php"
                class="logout"
            >
                Logout
            </a>

        </div>


    </div>

</nav>



<!-- DASHBOARD -->

<section class="dashboard-page">


    <div class="container">


        <!-- HEADER -->

        <div class="dashboard-header">


            <span class="eyebrow">
                ADMIN
            </span>


            <h1>
                Dashboard Admin 👋
            </h1>


            <p>
                Kelola data kos, pelanggan,
                dan pengajuan sewa.
            </p>


        </div>



        <!-- RINGKASAN -->

        <div class="summary">


            <!-- TOTAL KOS -->

            <div class="summary-card">

                <h4>
                    Total Kos
                </h4>

                <div class="summary-number">

                    <?= $total_kos; ?>

                </div>

            </div>


            <!-- KOS TERSEDIA -->

            <div class="summary-card">

                <h4>
                    Kos Tersedia
                </h4>

                <div class="summary-number">

                    <?= $total_tersedia; ?>

                </div>

            </div>


            <!-- PELANGGAN -->

            <div class="summary-card">

                <h4>
                    Pelanggan
                </h4>

                <div class="summary-number">

                    <?= $total_pelanggan; ?>

                </div>

            </div>


            <!-- PENGAJUAN -->

            <div class="summary-card">

                <h4>
                    Pengajuan Sewa
                </h4>

                <div class="summary-number">

                    <?= $total_sewa; ?>

                </div>

            </div>


            <!-- MENUNGGU -->

            <div class="summary-card">

                <h4>
                    Menunggu
                </h4>

                <div class="summary-number">

                    <?= $total_menunggu; ?>

                </div>

            </div>


        </div>



        <!-- MENU ADMIN -->

        <div class="dashboard-grid">


            <!-- DATA KOS -->

            <a
                class="dashboard-card"
                href="kos.php"
            >

                <div class="icon">
                    🏠
                </div>


                <h3>
                    Data Kos
                </h3>


                <p>
                    Tambah, edit, hapus,
                    dan kelola data kos.
                </p>


                <span class="jumlah">
                    <?= $total_kos; ?>
                </span>


            </a>



            <!-- PENGAJUAN SEWA -->

            <a
                class="dashboard-card"
                href="sewa.php"
            >

                <div class="icon">
                    📝
                </div>


                <h3>
                    Pengajuan Sewa
                </h3>


                <p>
                    Lihat dan proses pengajuan
                    sewa dari pelanggan.
                </p>


                <span class="jumlah">
                    <?= $total_menunggu; ?>
                </span>


            </a>



            <!-- DATA PELANGGAN -->

            <a
                class="dashboard-card"
                href="pelanggan.php"
            >

                <div class="icon">
                    👥
                </div>


                <h3>
                    Data Pelanggan
                </h3>


                <p>
                    Lihat data pelanggan
                    yang telah terdaftar.
                </p>


                <span class="jumlah">
                    <?= $total_pelanggan; ?>
                </span>


            </a>



            <!-- LIHAT WEBSITE -->

            <a
                class="dashboard-card"
                href="../kos.php"
            >

                <div class="icon">
                    🌐
                </div>


                <h3>
                    Lihat Website
                </h3>


                <p>
                    Lihat tampilan website
                    sebagai calon penyewa.
                </p>


            </a>


        </div>


    </div>


</section>


</body>

</html>