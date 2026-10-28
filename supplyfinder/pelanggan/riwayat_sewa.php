<?php

session_start();

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| CEK LOGIN PELANGGAN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'pelanggan'
) {
    header("Location: ../login.php");
    exit;
}


$id_user = (int) $_SESSION['id_user'];


/*
|--------------------------------------------------------------------------
| AMBIL DATA SEWA
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        s.id_sewa,
        s.tanggal_pengajuan,
        s.tanggal_mulai,
        s.durasi,
        s.catatan,
        s.status,

        k.nama_kos,
        k.alamat,
        k.kota,
        k.harga,
        k.tipe_kamar,
        k.foto

     FROM tb_sewa s

     INNER JOIN tb_kos k
        ON s.id_kos = k.id_kos

     WHERE s.id_user = ?

     ORDER BY s.id_sewa DESC"
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_user
);


mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

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
        Riwayat Sewa - KosFinder
    </title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    180deg,
                    #020817,
                    #03152d
                );

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

            background: #020817;

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


        .brand {

            width: 150px;

            height: 60px;

            display: flex;

            align-items: center;

            overflow: hidden;

        }


        .brand img {

            width: 145px;

            height: 58px;

            object-fit: contain;

        }


        .nav-links {

            display: flex;

            align-items: center;

            gap: 25px;

        }


        .nav-links a {

            color: #dce7f7;

            font-size: 14px;

        }


        .nav-links a:hover {

            color: #078cff;

        }


        .logout {

            border:
                1px solid
                rgba(255,255,255,0.5);

            padding:
                9px 20px;

            border-radius: 25px;

        }


        /* =====================================================
           PAGE
        ===================================================== */

        .page {

            min-height:
                calc(100vh - 78px);

            padding:
                60px 0 90px;

            background:

                radial-gradient(
                    circle at 85% 5%,
                    rgba(0,130,255,0.18),
                    transparent 30%
                );

        }


        .header {

            margin-bottom: 35px;

        }


        .eyebrow {

            color: #078cff;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: 2px;

        }


        .header h1 {

            margin:
                10px 0;

            font-size: 38px;

        }


        .header p {

            color: #9fb2ca;

            line-height: 1.7;

        }


        /* =====================================================
           SUCCESS
        ===================================================== */

        .success {

            background:
                rgba(0,170,100,0.12);

            border:
                1px solid
                rgba(0,190,110,0.35);

            color: #6dffc0;

            padding:
                15px 18px;

            border-radius: 10px;

            margin-bottom: 25px;

            font-size: 14px;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {

            text-align: center;

            padding: 70px 20px;

            background:
                linear-gradient(
                    145deg,
                    #0a213f,
                    #061326
                );

            border:
                1px solid
                rgba(0,133,255,0.3);

            border-radius: 16px;

        }


        .empty-icon {

            font-size: 55px;

            margin-bottom: 15px;

        }


        .empty h2 {

            margin:
                0 0 10px;

        }


        .empty p {

            color: #8fa4bc;

            margin-bottom: 25px;

        }


        .search-button {

            display: inline-block;

            padding:
                12px 22px;

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white;

            border-radius: 8px;

            font-weight: 700;

        }


        /* =====================================================
           SEWA CARD
        ===================================================== */

        .rental-list {

            display: grid;

            gap: 20px;

        }


        .rental-card {

            background:
                linear-gradient(
                    145deg,
                    #0a213f,
                    #061326
                );

            border:
                1px solid
                rgba(0,133,255,0.3);

            border-radius: 16px;

            overflow: hidden;

            display: grid;

            grid-template-columns:
                250px 1fr;

            transition: 0.2s;

        }


        .rental-card:hover {

            border-color:
                rgba(0,155,255,0.7);

        }


        /* =====================================================
           FOTO
        ===================================================== */

        .rental-photo {

            min-height: 230px;

            background:
                linear-gradient(
                    135deg,
                    #102b4d,
                    #061426
                );

        }


        .rental-photo img {

            width: 100%;

            height: 100%;

            min-height: 230px;

            object-fit: cover;

            display: block;

        }


        .no-photo {

            min-height: 230px;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #7890a9;

        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .rental-content {

            padding: 25px;

        }


        .rental-top {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 15px;

        }


        .rental-content h2 {

            margin:
                0 0 8px;

            font-size: 23px;

        }


        .location {

            color: #91a7c0;

            font-size: 13px;

            line-height: 1.6;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status {

            flex-shrink: 0;

            padding:
                8px 13px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 700;

        }


        .status-menunggu {

            color: #ffd66b;

            background:
                rgba(255,190,50,0.12);

            border:
                1px solid
                rgba(255,190,50,0.3);

        }


        .status-diproses {

            color: #55baff;

            background:
                rgba(0,140,255,0.12);

            border:
                1px solid
                rgba(0,140,255,0.3);

        }


        .status-diterima {

            color: #69ffc1;

            background:
                rgba(0,180,100,0.12);

            border:
                1px solid
                rgba(0,180,100,0.3);

        }


        .status-ditolak {

            color: #ff8792;

            background:
                rgba(220,50,70,0.12);

            border:
                1px solid
                rgba(220,50,70,0.3);

        }


        .status-selesai {

            color: #c4a7ff;

            background:
                rgba(130,80,220,0.12);

            border:
                1px solid
                rgba(130,80,220,0.3);

        }


        /* =====================================================
           DETAIL
        ===================================================== */

        .rental-details {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 10px;

            margin:
                18px 0;

        }


        .detail-box {

            background:
                rgba(0,130,255,0.07);

            border:
                1px solid
                rgba(0,140,255,0.18);

            border-radius: 8px;

            padding: 11px;

        }


        .detail-box small {

            display: block;

            color: #7088a2;

            font-size: 10px;

            margin-bottom: 5px;

        }


        .detail-box strong {

            font-size: 13px;

            color: #e6f0ff;

        }


        /* =====================================================
           CATATAN
        ===================================================== */

        .note {

            border-top:
                1px solid
                rgba(255,255,255,0.07);

            padding-top: 15px;

            color: #8fa4bc;

            font-size: 13px;

            line-height: 1.6;

        }


        .note strong {

            color: #dce7f7;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .back-button {

            display: inline-block;

            margin-top: 25px;

            padding:
                11px 20px;

            border:
                1px solid
                rgba(255,255,255,0.25);

            color: #dce7f7;

            border-radius: 8px;

        }


        .back-button:hover {

            color: #078cff;

            border-color:
                #078cff;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 800px) {

            .rental-card {

                grid-template-columns: 1fr;

            }


            .rental-photo {

                min-height: 230px;

            }


            .rental-photo img {

                height: 230px;

                min-height: 230px;

            }


            .rental-top {

                flex-direction: column;

            }

        }


        @media (max-width: 600px) {

            .nav-content {

                flex-direction: column;

                padding: 12px 0;

                gap: 10px;

            }


            .nav-links {

                flex-wrap: wrap;

                justify-content: center;

                gap: 13px;

            }


            .header h1 {

                font-size: 30px;

            }


            .rental-details {

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


        <a
            href="../index.php"
            class="brand"
        >

            <img
                src="../assets/images/logo-kosfinder.png"
                alt="KosFinder"
            >

        </a>


        <div class="nav-links">

            <a href="../index.php">
                Beranda
            </a>

            <a href="../kos.php">
                Cari Kos
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


<!-- =====================================================
     PAGE
===================================================== -->

<main class="page">

    <div class="container">


        <div class="header">

            <div class="eyebrow">
                AKUN PELANGGAN
            </div>

            <h1>
                Riwayat Sewa
            </h1>

            <p>
                Lihat semua pengajuan sewa dan
                status prosesnya.
            </p>

        </div>


        <!-- =================================================
             SUCCESS MESSAGE
        ================================================= -->

        <?php if (
            isset($_GET['success']) &&
            $_GET['success'] == '1'
        ): ?>

            <div class="success">

                ✓ Pengajuan sewa berhasil dikirim.

                Pengajuan Anda sedang menunggu
                diproses oleh admin.

            </div>

        <?php endif; ?>


        <?php if (
            mysqli_num_rows($result) === 0
        ): ?>


            <!-- =================================================
                 BELUM ADA SEWA
            ================================================= -->

            <div class="empty">

                <div class="empty-icon">
                    📋
                </div>

                <h2>
                    Belum Ada Pengajuan
                </h2>

                <p>
                    Anda belum pernah mengajukan
                    sewa kos.
                </p>

                <a
                    href="../kos.php"
                    class="search-button"
                >
                    🔍 Cari Kos Sekarang
                </a>

            </div>


        <?php else: ?>


            <!-- =================================================
                 LIST SEWA
            ================================================= -->

            <div class="rental-list">


                <?php while (
                    $sewa = mysqli_fetch_assoc($result)
                ): ?>


                    <div class="rental-card">


                        <!-- FOTO -->

                        <div class="rental-photo">


                            <?php if (
                                !empty($sewa['foto'])
                            ): ?>

                                <img
                                    src="../assets/images/<?= htmlspecialchars($sewa['foto']); ?>"
                                    alt="<?= htmlspecialchars($sewa['nama_kos']); ?>"
                                >

                            <?php else: ?>

                                <div class="no-photo">

                                    🏠

                                    Foto belum tersedia

                                </div>

                            <?php endif; ?>


                        </div>


                        <!-- CONTENT -->

                        <div class="rental-content">


                            <div class="rental-top">


                                <div>

                                    <h2>

                                        <?= htmlspecialchars(
                                            $sewa['nama_kos']
                                        ); ?>

                                    </h2>


                                    <div class="location">

                                        📍

                                        <?= htmlspecialchars(
                                            $sewa['alamat']
                                        ); ?>

                                        <br>

                                        <?= htmlspecialchars(
                                            $sewa['kota']
                                        ); ?>

                                    </div>

                                </div>


                                <!-- STATUS -->

                                <?php

                                $status_class =
                                    'status-' .
                                    $sewa['status'];

                                $status_text = [
                                    'menunggu' => 'Menunggu',
                                    'diproses' => 'Diproses',
                                    'diterima' => 'Diterima',
                                    'ditolak' => 'Ditolak',
                                    'selesai' => 'Selesai'
                                ];

                                ?>


                                <div
                                    class="status <?= $status_class; ?>"
                                >

                                    <?= $status_text[
                                        $sewa['status']
                                    ] ?? ucfirst(
                                        $sewa['status']
                                    ); ?>

                                </div>


                            </div>


                            <!-- DETAIL -->

                            <div class="rental-details">


                                <div class="detail-box">

                                    <small>
                                        Harga
                                    </small>

                                    <strong>

                                        Rp

                                        <?= number_format(
                                            $sewa['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                        /bulan

                                    </strong>

                                </div>


                                <div class="detail-box">

                                    <small>
                                        Mulai Sewa
                                    </small>

                                    <strong>

                                        <?= date(
                                            'd-m-Y',
                                            strtotime(
                                                $sewa['tanggal_mulai']
                                            )
                                        ); ?>

                                    </strong>

                                </div>


                                <div class="detail-box">

                                    <small>
                                        Durasi
                                    </small>

                                    <strong>

                                        <?= (int)
                                            $sewa['durasi']; ?>

                                        Bulan

                                    </strong>

                                </div>


                                <div class="detail-box">

                                    <small>
                                        Tipe Kamar
                                    </small>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $sewa['tipe_kamar']
                                        ); ?>

                                    </strong>

                                </div>


                                <div class="detail-box">

                                    <small>
                                        Diajukan
                                    </small>

                                    <strong>

                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime(
                                                $sewa['tanggal_pengajuan']
                                            )
                                        ); ?>

                                    </strong>

                                </div>


                            </div>


                            <!-- CATATAN -->

                            <div class="note">

                                <strong>
                                    Catatan:
                                </strong>

                                <?php if (
                                    !empty($sewa['catatan'])
                                ): ?>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $sewa['catatan']
                                        )
                                    ); ?>

                                <?php else: ?>

                                    Tidak ada catatan.

                                <?php endif; ?>

                            </div>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php endif; ?>


        <a
            href="dashboard.php"
            class="back-button"
        >
            ← Kembali ke Dashboard
        </a>


    </div>

</main>


</body>

</html>