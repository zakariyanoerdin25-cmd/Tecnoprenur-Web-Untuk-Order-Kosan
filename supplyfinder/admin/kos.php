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

$result = mysqli_query(
    $conn,
    "SELECT * FROM tb_kos ORDER BY id_kos DESC"
);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Data Kos - SupplyFinder</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn-tambah {
            background: #0d6efd;
            color: white;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #0d2b52;
            color: white;
        }

        .foto {
            width: 90px;
            height: 65px;
            object-fit: cover;
            border-radius: 7px;
        }

        .btn-edit {
            color: #0d6efd;
            text-decoration: none;
            margin-right: 8px;
        }

        .btn-hapus {
            color: red;
            text-decoration: none;
        }

        .status-tersedia {
            color: green;
            font-weight: bold;
        }

        .status-penuh {
            color: red;
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>Data Kos</h1>

            <p>
                Kelola data kos yang tersedia untuk disewa.
            </p>

        </div>

        <a
            href="kos_tambah.php"
            class="btn-tambah"
        >
            + Tambah Kos
        </a>

    </div>


    <!-- TABEL DATA KOS -->

    <table>

        <thead>

            <tr>

                <th>No</th>

                <th>Foto</th>

                <th>Nama Kos</th>

                <th>Kota</th>

                <th>Harga/Bulan</th>

                <th>Tipe Kamar</th>

                <th>Status</th>

                <th>Aksi</th>

            </tr>

        </thead>


        <tbody>

        <?php

        $no = 1;

        if (mysqli_num_rows($result) > 0) {

            while ($kos = mysqli_fetch_assoc($result)) {

        ?>

            <tr>

                <!-- NO -->

                <td>
                    <?= $no++; ?>
                </td>


                <!-- FOTO -->

                <td>

                    <?php if (!empty($kos['foto'])): ?>

                        <img
                            src="../assets/images/<?= htmlspecialchars($kos['foto']); ?>"
                            class="foto"
                            alt="Foto Kos"
                        >

                    <?php else: ?>

                        Tidak ada foto

                    <?php endif; ?>

                </td>


                <!-- NAMA KOS -->

                <td>

                    <?= htmlspecialchars(
                        $kos['nama_kos']
                    ); ?>

                </td>


                <!-- KOTA -->

                <td>

                    <?= htmlspecialchars(
                        $kos['kota']
                    ); ?>

                </td>


                <!-- HARGA -->

                <td>

                    Rp <?= number_format(
                        $kos['harga'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </td>


                <!-- TIPE KAMAR -->

                <td>

                    <?= htmlspecialchars(
                        $kos['tipe_kamar']
                    ); ?>

                </td>


                <!-- STATUS -->

                <td>

                    <?php if ($kos['status'] === 'tersedia'): ?>

                        <span class="status-tersedia">
                            Tersedia
                        </span>

                    <?php else: ?>

                        <span class="status-penuh">
                            Penuh
                        </span>

                    <?php endif; ?>

                </td>


                <!-- AKSI -->

                <td>

                    <a
                        href="kos_edit.php?id=<?= $kos['id_kos']; ?>"
                        class="btn-edit"
                    >
                        Edit
                    </a>


                    <a
                        href="kos_hapus.php?id=<?= $kos['id_kos']; ?>"
                        class="btn-hapus"
                        onclick="return confirm('Yakin ingin menghapus kos ini?')"
                    >
                        Hapus
                    </a>

                </td>

            </tr>

        <?php

            }

        } else {

        ?>

            <tr>

                <td
                    colspan="8"
                    style="text-align:center;"
                >

                    Belum ada data kos.

                </td>

            </tr>

        <?php

        }

        ?>

        </tbody>

    </table>


    <br>


    <a href="dashboard.php">
        ← Kembali ke Dashboard
    </a>

</div>

</body>

</html>