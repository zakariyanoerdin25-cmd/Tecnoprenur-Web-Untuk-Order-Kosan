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
| Ambil Data Pelanggan
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        id_user,
        nama,
        email,
        no_hp,
        alamat
    FROM tb_user
    WHERE role = 'pelanggan'
    ORDER BY id_user DESC
";

$result = mysqli_query(
    $conn,
    $query
);


if (!$result) {

    die(
        "Query gagal: " .
        mysqli_error($conn)
    );

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
        Data Pelanggan - SupplyFinder
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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

            margin-bottom: 30px;

        }


        .header h1 {

            margin-bottom: 8px;

        }


        .header p {

            color: #666;

            margin: 0;

        }


        .table-wrapper {

            overflow-x: auto;

            background: white;

            border-radius: 12px;

            box-shadow:
                0 5px 20px rgba(0,0,0,.08);

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 800px;

        }


        th,
        td {

            padding: 14px;

            border-bottom:
                1px solid #ddd;

            text-align: left;

        }


        th {

            background: #0d2b52;

            color: white;

        }


        tr:hover {

            background: #f8f9fa;

        }


        .nama {

            font-weight: bold;

        }


        .email {

            color: #555;

        }


        .telepon {

            color: #555;

        }


        .alamat {

            max-width: 300px;

        }


        .kosong {

            color: #999;

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

            <span>SF</span>
            SupplyFinder

        </a>


        <div class="nav-links">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="kos.php">
                Data Kos
            </a>

            <a href="sewa.php">
                Pengajuan Sewa
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



<div class="container">


    <div class="header">


        <div>

            <h1>
                Data Pelanggan
            </h1>

            <p>
                Daftar pelanggan yang telah
                mendaftar di SupplyFinder.
            </p>

        </div>


    </div>



    <div class="table-wrapper">


        <table>


            <thead>

                <tr>

                    <th>
                        No
                    </th>

                    <th>
                        Nama
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        No. HP
                    </th>

                    <th>
                        Alamat
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php

            $no = 1;

            if (
                mysqli_num_rows($result) > 0
            ):

                while (
                    $pelanggan =
                    mysqli_fetch_assoc($result)
                ):

            ?>


                <tr>


                    <td>

                        <?= $no++; ?>

                    </td>


                    <td>

                        <div class="nama">

                            <?= htmlspecialchars(
                                $pelanggan['nama']
                            ); ?>

                        </div>

                    </td>


                    <td>

                        <div class="email">

                            <?= htmlspecialchars(
                                $pelanggan['email']
                            ); ?>

                        </div>

                    </td>


                    <td>

                        <?php if (
                            !empty(
                                $pelanggan['no_hp']
                            )
                        ): ?>

                            <div class="telepon">

                                📞
                                <?= htmlspecialchars(
                                    $pelanggan['no_hp']
                                ); ?>

                            </div>

                        <?php else: ?>

                            <span class="kosong">
                                Belum diisi
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php if (
                            !empty(
                                $pelanggan['alamat']
                            )
                        ): ?>

                            <div class="alamat">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $pelanggan['alamat']
                                    )
                                ); ?>

                            </div>

                        <?php else: ?>

                            <span class="kosong">
                                Belum diisi
                            </span>

                        <?php endif; ?>

                    </td>


                </tr>


            <?php

                endwhile;

            else:

            ?>


                <tr>

                    <td
                        colspan="5"
                        style="text-align:center;"
                    >

                        Belum ada pelanggan
                        yang terdaftar.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>

        </table>


    </div>


    <a
        href="dashboard.php"
        class="back"
    >

        ← Kembali ke Dashboard

    </a>


</div>


</body>

</html>