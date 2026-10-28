<?php
session_start();

require_once __DIR__ . '/../config/database.php';

/* =========================
   CEK LOGIN PELANGGAN
========================= */
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'pelanggan'
) {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id_user'];

/* =========================
   DATA PELANGGAN
========================= */
$stmt = mysqli_prepare(
    $conn,
    "SELECT nama, email
     FROM tb_user
     WHERE id_user = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);

$result_user = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result_user);

mysqli_stmt_close($stmt);

/* =========================
   STATISTIK SEWA
========================= */

function jumlahSewa($conn, $id_user, $status = null)
{
    if ($status !== null) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM tb_sewa
             WHERE id_user = ?
             AND status = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "is",
            $id_user,
            $status
        );

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM tb_sewa
             WHERE id_user = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_user
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return (int) $data['total'];
}

$total_sewa = jumlahSewa($conn, $id_user);

$total_menunggu = jumlahSewa(
    $conn,
    $id_user,
    'menunggu'
);

$total_diproses = jumlahSewa(
    $conn,
    $id_user,
    'diproses'
);

$total_diterima = jumlahSewa(
    $conn,
    $id_user,
    'diterima'
);

$total_ditolak = jumlahSewa(
    $conn,
    $id_user,
    'ditolak'
);

/* =========================
   PENGAJUAN TERBARU
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        s.id_sewa,
        s.status,
        s.tanggal_pengajuan,
        s.tanggal_mulai,
        s.durasi,
        k.nama_kos,
        k.kota,
        k.foto

     FROM tb_sewa s

     INNER JOIN tb_kos k
        ON s.id_kos = k.id_kos

     WHERE s.id_user = ?

     ORDER BY s.tanggal_pengajuan DESC

     LIMIT 5"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_user
);

mysqli_stmt_execute($stmt);

$result_sewa = mysqli_stmt_get_result($stmt);

/* =========================
   STATUS TERBARU
========================= */

$pengajuan_terbaru = null;

if (mysqli_num_rows($result_sewa) > 0) {

    $rows = [];

    while ($row = mysqli_fetch_assoc($result_sewa)) {
        $rows[] = $row;
    }

    $pengajuan_terbaru = $rows[0];
}

mysqli_stmt_close($stmt);

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
        Dashboard Pelanggan - KosFinder
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
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            height: 72px;

            background: #0d1728;

            border-bottom: 1px solid #1e293b;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 40px;

            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;

            gap: 12px;

            text-decoration: none;

            color: white;
        }

        .logo img {
            width: 42px;
            height: 42px;

            object-fit: contain;
        }

        .logo span {
            font-size: 20px;
            font-weight: 700;
        }

        .nav-menu {
            display: flex;
            align-items: center;

            gap: 22px;
        }

        .nav-menu a {
            color: #cbd5e1;

            text-decoration: none;

            font-size: 14px;

            transition: 0.2s;
        }

        .nav-menu a:hover {
            color: white;
        }

        .logout {
            background: #dc2626;

            color: white !important;

            padding: 9px 14px;

            border-radius: 8px;
        }

        .logout:hover {
            background: #b91c1c !important;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1200px;

            margin: auto;

            padding: 40px;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .welcome h1 {
            font-size: 30px;

            margin-bottom: 8px;
        }

        .welcome p {
            color: #94a3b8;

            font-size: 14px;
        }

        /* =========================
           NOTIFICATION
        ========================= */

        .notification {
            display: flex;
            align-items: center;

            gap: 16px;

            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 28px;
        }

        .notification-icon {
            width: 48px;
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background: #172554;

            font-size: 22px;

            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 700;

            margin-bottom: 5px;
        }

        .notification-text {
            color: #94a3b8;

            font-size: 13px;
        }

        .notification-link {
            color: #60a5fa;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;
        }

        .notification-link:hover {
            text-decoration: underline;
        }

        /* =========================
           STATS
        ========================= */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 35px;
        }

        .stat-card {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 14px;

            padding: 22px;

            transition: 0.2s;
        }

        .stat-card:hover {
            border-color: #334155;

            transform: translateY(-2px);
        }

        .stat-label {
            color: #94a3b8;

            font-size: 13px;

            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 30px;

            font-weight: 700;
        }

        /* =========================
           SECTION TITLE
        ========================= */

        .section-title {
            font-size: 20px;

            margin-bottom: 18px;
        }

        /* =========================
           MENU
        ========================= */

        .menu-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 40px;
        }

        .menu-card {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 14px;

            padding: 25px;

            text-decoration: none;

            color: white;

            transition: 0.2s;
        }

        .menu-card:hover {
            border-color: #2563eb;

            transform: translateY(-3px);
        }

        .menu-icon {
            width: 48px;
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #172554;

            border-radius: 12px;

            font-size: 23px;

            margin-bottom: 16px;
        }

        .menu-card h3 {
            margin-bottom: 8px;

            font-size: 17px;
        }

        .menu-card p {
            color: #94a3b8;

            font-size: 13px;

            line-height: 1.5;
        }

        /* =========================
           STATUS TERBARU
        ========================= */

        .latest {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 14px;

            padding: 25px;
        }

        .latest-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;
        }

        .latest-header h2 {
            font-size: 19px;
        }

        .latest-header a {
            color: #60a5fa;

            text-decoration: none;

            font-size: 13px;
        }

        .latest-item {
            display: flex;

            align-items: center;

            gap: 16px;

            padding: 15px 0;

            border-bottom: 1px solid #1e293b;
        }

        .latest-item:last-child {
            border-bottom: none;
        }

        .latest-photo {
            width: 70px;
            height: 60px;

            object-fit: cover;

            border-radius: 9px;

            background: #172033;

            flex-shrink: 0;
        }

        .latest-info {
            flex: 1;
        }

        .latest-name {
            font-weight: 700;

            margin-bottom: 5px;
        }

        .latest-location {
            color: #94a3b8;

            font-size: 12px;
        }

        /* =========================
           STATUS BADGE
        ========================= */

        .status {
            display: inline-block;

            padding: 6px 10px;

            border-radius: 999px;

            font-size: 11px;

            font-weight: 700;

            text-transform: capitalize;

            white-space: nowrap;
        }

        .status-menunggu {
            background: #422006;
            color: #fbbf24;
        }

        .status-diproses {
            background: #172554;
            color: #60a5fa;
        }

        .status-diterima {
            background: #052e16;
            color: #4ade80;
        }

        .status-ditolak {
            background: #450a0a;
            color: #f87171;
        }

        .status-selesai {
            background: #1e293b;
            color: #cbd5e1;
        }

        /* =========================
           EMPTY
        ========================= */

        .empty {
            text-align: center;

            padding: 30px 10px;

            color: #94a3b8;

            font-size: 14px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .navbar {
                padding: 0 20px;
            }

            .nav-menu a:not(.logout) {
                display: none;
            }

            .container {
                padding: 30px 20px;
            }

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .menu-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .welcome h1 {
                font-size: 25px;
            }

            .notification {
                align-items: flex-start;
            }

            .latest-item {
                align-items: flex-start;
            }

        }

    </style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <a
        href="dashboard.php"
        class="logo"
    >

        <img
            src="../assets/images/logo-kosfinder.png"
            alt="KosFinder"
        >

        <span>
            KosFinder
        </span>

    </a>


    <div class="nav-menu">

        <a href="../index.php">
            Beranda
        </a>

        <a href="../kos.php">
            Cari Kos
        </a>

        <a href="riwayat_sewa.php">
            Riwayat Sewa
        </a>

        <a href="profil.php">
            Profil
        </a>

        <a
            href="../logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     CONTENT
========================= -->

<div class="container">

    <div class="welcome">

        <h1>
            Halo, <?= htmlspecialchars($user['nama']); ?> 👋
        </h1>

        <p>
            Selamat datang di dashboard KosFinder.
            Temukan kos yang sesuai dengan kebutuhanmu.
        </p>

    </div>


    <!-- =========================
         NOTIFICATION
    ========================= -->

    <?php if ($pengajuan_terbaru): ?>

        <?php

        $status = $pengajuan_terbaru['status'];

        $status_class =
            'status-' . strtolower($status);

        ?>

        <div class="notification">

            <div class="notification-icon">

                <?php if ($status === 'menunggu'): ?>
                    ⏳
                <?php elseif ($status === 'diproses'): ?>
                    🔄
                <?php elseif ($status === 'diterima'): ?>
                    ✅
                <?php elseif ($status === 'ditolak'): ?>
                    ❌
                <?php else: ?>
                    🏁
                <?php endif; ?>

            </div>


            <div class="notification-content">

                <div class="notification-title">

                    Pengajuan terbaru:
                    <?= htmlspecialchars(
                        $pengajuan_terbaru['nama_kos']
                    ); ?>

                </div>

                <div class="notification-text">

                    Status saat ini:

                    <span class="status <?= $status_class; ?>">

                        <?= htmlspecialchars($status); ?>

                    </span>

                </div>

            </div>


            <a
                href="riwayat_sewa.php"
                class="notification-link"
            >
                Lihat Detail →
            </a>

        </div>

    <?php endif; ?>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-label">
                Total Pengajuan
            </div>

            <div class="stat-number">
                <?= $total_sewa; ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Menunggu
            </div>

            <div class="stat-number">
                <?= $total_menunggu; ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Diproses
            </div>

            <div class="stat-number">
                <?= $total_diproses; ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Diterima
            </div>

            <div class="stat-number">
                <?= $total_diterima; ?>
            </div>

        </div>

    </div>


    <!-- =========================
         MENU
    ========================= -->

    <h2 class="section-title">
        Menu Pelanggan
    </h2>


    <div class="menu-grid">

        <a
            href="../kos.php"
            class="menu-card"
        >

            <div class="menu-icon">
                🔍
            </div>

            <h3>
                Cari Kos
            </h3>

            <p>
                Cari kos berdasarkan lokasi,
                harga, tipe kamar, dan fasilitas.
            </p>

        </a>


        <a
            href="../ajukan_sewa.php"
            class="menu-card"
        >

            <div class="menu-icon">
                🏠
            </div>

            <h3>
                Ajukan Sewa
            </h3>

            <p>
                Ajukan permintaan sewa
                kos yang kamu pilih.
            </p>

        </a>


        <a
            href="riwayat_sewa.php"
            class="menu-card"
        >

            <div class="menu-icon">
                📋
            </div>

            <h3>
                Riwayat Sewa
            </h3>

            <p>
                Lihat semua pengajuan dan
                status sewa kamu.
            </p>

        </a>


        <a
            href="profil.php"
            class="menu-card"
        >

            <div class="menu-icon">
                👤
            </div>

            <h3>
                Profil Saya
            </h3>

            <p>
                Kelola informasi akun,
                nomor HP, alamat, dan password.
            </p>

        </a>

    </div>


    <!-- =========================
         PENGAJUAN TERBARU
    ========================= -->

    <div class="latest">

        <div class="latest-header">

            <h2>
                Pengajuan Terbaru
            </h2>

            <a href="riwayat_sewa.php">
                Lihat Semua →
            </a>

        </div>


        <?php if (count($rows) > 0): ?>

            <?php foreach ($rows as $row): ?>

                <?php

                $foto = !empty($row['foto'])
                    ? "../assets/images/" . $row['foto']
                    : "../assets/images/no-image.jpg";

                $status_class =
                    'status-' .
                    strtolower($row['status']);

                ?>

                <div class="latest-item">

                    <img
                        src="<?= htmlspecialchars($foto); ?>"
                        alt="<?= htmlspecialchars($row['nama_kos']); ?>"
                        class="latest-photo"
                        onerror="this.src='../assets/images/no-image.jpg';"
                    >


                    <div class="latest-info">

                        <div class="latest-name">

                            <?= htmlspecialchars(
                                $row['nama_kos']
                            ); ?>

                        </div>

                        <div class="latest-location">

                            <?= htmlspecialchars(
                                $row['kota']
                            ); ?>

                            • Mulai:

                            <?= !empty($row['tanggal_mulai'])
                                ? date(
                                    'd/m/Y',
                                    strtotime(
                                        $row['tanggal_mulai']
                                    )
                                )
                                : '-'; ?>

                        </div>

                    </div>


                    <span
                        class="status <?= $status_class; ?>"
                    >

                        <?= htmlspecialchars(
                            $row['status']
                        ); ?>

                    </span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty">

                Kamu belum memiliki pengajuan sewa.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>