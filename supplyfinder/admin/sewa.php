<?php
session_start();

require_once __DIR__ . '/../config/database.php';

/* =========================
   CEK LOGIN ADMIN
========================= */
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

/* =========================
   UPDATE STATUS SEWA
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_sewa = (int) ($_POST['id_sewa'] ?? 0);
    $status  = $_POST['status'] ?? '';

    $status_valid = [
        'menunggu',
        'diproses',
        'diterima',
        'ditolak',
        'selesai'
    ];

    if (
        $id_sewa > 0 &&
        in_array($status, $status_valid, true)
    ) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE tb_sewa
             SET status = ?
             WHERE id_sewa = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $status,
            $id_sewa
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: sewa.php?updated=1");
    exit;
}

/* =========================
   DATA STATISTIK
========================= */
function jumlahStatus($conn, $status)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM tb_sewa
         WHERE status = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return (int) $data['total'];
}

$total_menunggu = jumlahStatus($conn, 'menunggu');
$total_diproses = jumlahStatus($conn, 'diproses');
$total_diterima = jumlahStatus($conn, 'diterima');
$total_ditolak   = jumlahStatus($conn, 'ditolak');

/* =========================
   AMBIL DATA PENGAJUAN
========================= */
$query = "
    SELECT
        s.id_sewa,
        s.tanggal_pengajuan,
        s.tanggal_mulai,
        s.durasi,
        s.catatan,
        s.status,

        u.nama AS nama_pelanggan,
        u.email,
        u.no_hp,

        k.nama_kos,
        k.alamat,
        k.kota,
        k.harga,
        k.tipe_kamar,
        k.foto

    FROM tb_sewa s

    INNER JOIN tb_user u
        ON s.id_user = u.id_user

    INNER JOIN tb_kos k
        ON s.id_kos = k.id_kos

    ORDER BY s.tanggal_pengajuan DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pengajuan Sewa - KosFinder</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
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

        .nav-right {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-right a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }

        .nav-right a:hover {
            color: white;
        }

        .btn-back {
            background: #2563eb;
            color: white !important;

            padding: 10px 16px;
            border-radius: 9px;

            font-weight: 600;
        }

        .btn-back:hover {
            background: #1d4ed8;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 40px;
        }

        .page-title {
            margin-bottom: 8px;
        }

        .page-title h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .page-title p {
            color: #94a3b8;
            font-size: 14px;
        }

        /* =========================
           ALERT
        ========================= */

        .alert {
            margin-top: 24px;
            margin-bottom: 24px;

            background: #052e16;
            border: 1px solid #166534;

            color: #86efac;

            padding: 14px 18px;
            border-radius: 10px;

            font-size: 14px;
        }

        /* =========================
           STATISTICS
        ========================= */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;

            margin-top: 30px;
            margin-bottom: 30px;
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
           TABLE
        ========================= */

        .table-wrapper {
            background: #0d1728;
            border: 1px solid #1e293b;

            border-radius: 16px;

            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;

            min-width: 1200px;
        }

        thead {
            background: #111c2e;
        }

        th {
            text-align: left;

            color: #94a3b8;

            font-size: 12px;
            text-transform: uppercase;

            padding: 16px;

            border-bottom: 1px solid #1e293b;

            white-space: nowrap;
        }

        td {
            padding: 18px 16px;

            border-bottom: 1px solid #1e293b;

            vertical-align: top;

            font-size: 14px;
        }

        tbody tr:hover {
            background: #101b2e;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* =========================
           KOS
        ========================= */

        .kos-info {
            display: flex;
            gap: 12px;

            min-width: 230px;
        }

        .kos-photo {
            width: 70px;
            height: 70px;

            border-radius: 10px;

            object-fit: cover;

            background: #172033;

            flex-shrink: 0;
        }

        .kos-name {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .kos-location {
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.5;
        }

        /* =========================
           PELANGGAN
        ========================= */

        .customer-name {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .customer-detail {
            color: #94a3b8;
            font-size: 12px;

            line-height: 1.6;
        }

        /* =========================
           PRICE
        ========================= */

        .price {
            color: #60a5fa;
            font-weight: 700;
            white-space: nowrap;
        }

        /* =========================
           STATUS
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
           FORM STATUS
        ========================= */

        .status-form {
            display: flex;
            flex-direction: column;
            gap: 8px;

            min-width: 130px;
        }

        .status-form select {
            width: 100%;

            padding: 9px 10px;

            border-radius: 8px;

            border: 1px solid #334155;

            background: #08111f;
            color: white;

            outline: none;

            font-size: 12px;
        }

        .status-form select:focus {
            border-color: #2563eb;
        }

        .btn-update {
            width: 100%;

            padding: 9px 10px;

            border: none;
            border-radius: 8px;

            background: #2563eb;
            color: white;

            font-weight: 600;
            font-size: 12px;

            cursor: pointer;

            transition: 0.2s;
        }

        .btn-update:hover {
            background: #1d4ed8;
        }

        /* =========================
           EMPTY
        ========================= */

        .empty {
            text-align: center;
            padding: 70px 20px;

            color: #94a3b8;
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty h3 {
            color: white;
            margin-bottom: 8px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .navbar {
                padding: 0 20px;
            }

            .nav-right a:not(.btn-back) {
                display: none;
            }

            .container {
                padding: 25px 20px;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 500px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .page-title h1 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <a href="dashboard.php" class="logo">

        <img
            src="../assets/images/logo-kosfinder.png"
            alt="KosFinder"
        >

        <span>KosFinder</span>

    </a>

    <div class="nav-right">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="kos.php">
            Data Kos
        </a>

        <a href="pelanggan.php">
            Pelanggan
        </a>

        <a href="../logout.php">
            Logout
        </a>

        <a href="../kos.php" class="btn-back">
            Lihat Website
        </a>

    </div>

</nav>


<!-- =========================
     CONTENT
========================= -->

<div class="container">

    <div class="page-title">

        <h1>Pengajuan Sewa</h1>

        <p>
            Kelola dan proses semua pengajuan sewa kos dari pelanggan.
        </p>

    </div>


    <?php if (isset($_GET['updated'])): ?>

        <div class="alert">
            ✓ Status pengajuan berhasil diperbarui.
        </div>

    <?php endif; ?>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">

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


        <div class="stat-card">

            <div class="stat-label">
                Ditolak
            </div>

            <div class="stat-number">
                <?= $total_ditolak; ?>
            </div>

        </div>

    </div>


    <!-- =========================
         TABLE
    ========================= -->

    <div class="table-wrapper">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Pelanggan
                        </th>

                        <th>
                            Kos
                        </th>

                        <th>
                            Harga
                        </th>

                        <th>
                            Pengajuan
                        </th>

                        <th>
                            Mulai
                        </th>

                        <th>
                            Durasi
                        </th>

                        <th>
                            Catatan
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Ubah Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <?php

                        $status_class =
                            'status-' .
                            strtolower($row['status']);

                        $foto = !empty($row['foto'])
                            ? "../assets/images/" . $row['foto']
                            : "../assets/images/no-image.jpg";

                        ?>

                        <tr>

                            <!-- PELANGGAN -->

                            <td>

                                <div class="customer-name">
                                    <?= htmlspecialchars($row['nama_pelanggan']); ?>
                                </div>

                                <div class="customer-detail">

                                    <?= htmlspecialchars($row['email']); ?>

                                    <?php if (!empty($row['no_hp'])): ?>

                                        <br>
                                        <?= htmlspecialchars($row['no_hp']); ?>

                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- KOS -->

                            <td>

                                <div class="kos-info">

                                    <img
                                        src="<?= htmlspecialchars($foto); ?>"
                                        alt="<?= htmlspecialchars($row['nama_kos']); ?>"
                                        class="kos-photo"
                                        onerror="this.src='../assets/images/no-image.jpg';"
                                    >

                                    <div>

                                        <div class="kos-name">

                                            <?= htmlspecialchars(
                                                $row['nama_kos']
                                            ); ?>

                                        </div>

                                        <div class="kos-location">

                                            <?= htmlspecialchars(
                                                $row['kota']
                                            ); ?>

                                            <br>

                                            <?= htmlspecialchars(
                                                $row['tipe_kamar']
                                            ); ?>

                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- HARGA -->

                            <td>

                                <div class="price">

                                    Rp <?= number_format(
                                        $row['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </div>

                                <small style="color:#64748b;">
                                    / bulan
                                </small>

                            </td>


                            <!-- TANGGAL PENGAJUAN -->

                            <td>

                                <?= date(
                                    'd/m/Y',
                                    strtotime(
                                        $row['tanggal_pengajuan']
                                    )
                                ); ?>

                                <br>

                                <small style="color:#64748b;">

                                    <?= date(
                                        'H:i',
                                        strtotime(
                                            $row['tanggal_pengajuan']
                                        )
                                    ); ?>

                                </small>

                            </td>


                            <!-- TANGGAL MULAI -->

                            <td>

                                <?php if (!empty($row['tanggal_mulai'])): ?>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $row['tanggal_mulai']
                                        )
                                    ); ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </td>


                            <!-- DURASI -->

                            <td>

                                <?= (int) $row['durasi']; ?>

                                bulan

                            </td>


                            <!-- CATATAN -->

                            <td>

                                <?php if (!empty($row['catatan'])): ?>

                                    <span
                                        title="<?= htmlspecialchars($row['catatan']); ?>"
                                    >
                                        <?= htmlspecialchars(
                                            mb_strimwidth(
                                                $row['catatan'],
                                                0,
                                                45,
                                                '...'
                                            )
                                        ); ?>
                                    </span>

                                <?php else: ?>

                                    <span style="color:#64748b;">
                                        Tidak ada
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span class="status <?= $status_class; ?>">

                                    <?= htmlspecialchars(
                                        $row['status']
                                    ); ?>

                                </span>

                            </td>


                            <!-- UBAH STATUS -->

                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="id_sewa"
                                        value="<?= (int) $row['id_sewa']; ?>"
                                    >

                                    <select name="status">

                                        <option
                                            value="menunggu"
                                            <?= $row['status'] === 'menunggu'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            Menunggu
                                        </option>

                                        <option
                                            value="diproses"
                                            <?= $row['status'] === 'diproses'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            Diproses
                                        </option>

                                        <option
                                            value="diterima"
                                            <?= $row['status'] === 'diterima'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            Diterima
                                        </option>

                                        <option
                                            value="ditolak"
                                            <?= $row['status'] === 'ditolak'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            Ditolak
                                        </option>

                                        <option
                                            value="selesai"
                                            <?= $row['status'] === 'selesai'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            Selesai
                                        </option>

                                    </select>

                                    <button
                                        type="submit"
                                        class="btn-update"
                                    >
                                        Simpan
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">
                    📋
                </div>

                <h3>
                    Belum Ada Pengajuan
                </h3>

                <p>
                    Belum ada pelanggan yang mengajukan sewa kos.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>