<?php

session_start();

require_once __DIR__ . '/config/database.php';

/* =====================================================
   CEK LOGIN PELANGGAN
===================================================== */

if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'pelanggan'
) {
    header("Location: login.php");
    exit;
}

$id_user = (int) $_SESSION['id_user'];
$error = "";

/* =====================================================
   AMBIL ID KOS
===================================================== */

$id_kos = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


/* =====================================================
   PROSES PENGAJUAN SEWA
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_kos = (int) ($_POST['id_kos'] ?? 0);
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $durasi = (int) ($_POST['durasi'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');

    /* -------------------------------------------------
       VALIDASI DATA
    ------------------------------------------------- */

    if (
        $id_kos <= 0 ||
        empty($tanggal_mulai) ||
        $durasi <= 0
    ) {

        $error = "Data pengajuan belum lengkap.";

    } else {

        /* -------------------------------------------------
           VALIDASI TANGGAL
        ------------------------------------------------- */

        $tanggal_hari_ini = date('Y-m-d');

        if ($tanggal_mulai < $tanggal_hari_ini) {

            $error = "Tanggal mulai sewa tidak boleh sebelum hari ini.";

        } else {

            /* -------------------------------------------------
               AMBIL DATA KOS
            ------------------------------------------------- */

            $stmt = mysqli_prepare(
                $conn,
                "SELECT
                    id_kos,
                    nama_kos,
                    alamat,
                    kota,
                    harga,
                    tipe_kamar,
                    jumlah_kamar,
                    fasilitas,
                    deskripsi,
                    foto,
                    status
                 FROM tb_kos
                 WHERE id_kos = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_kos
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $kos_post = mysqli_fetch_assoc($result);

            if (!$kos_post) {

                $error = "Kos tidak ditemukan.";

            } else {

                /* -------------------------------------------------
                   JUMLAH KAMAR
                ------------------------------------------------- */

                $jumlah_kamar_post =
                    (int) $kos_post['jumlah_kamar'];


                /* -------------------------------------------------
                   HITUNG KAMAR TERISI
                ------------------------------------------------- */

                $stmt_terisi_post = mysqli_prepare(
                    $conn,
                    "SELECT COUNT(*) AS terisi
                     FROM tb_sewa
                     WHERE id_kos = ?
                     AND status = 'diterima'"
                );

                mysqli_stmt_bind_param(
                    $stmt_terisi_post,
                    "i",
                    $id_kos
                );

                mysqli_stmt_execute(
                    $stmt_terisi_post
                );

                $result_terisi_post =
                    mysqli_stmt_get_result(
                        $stmt_terisi_post
                    );

                $data_terisi_post =
                    mysqli_fetch_assoc(
                        $result_terisi_post
                    );

                $kamar_terisi_post =
                    (int) $data_terisi_post['terisi'];


                /* -------------------------------------------------
                   CEK KAMAR PENUH
                ------------------------------------------------- */

                if (
                    $kamar_terisi_post >=
                    $jumlah_kamar_post
                ) {

                    /* Otomatis ubah status menjadi penuh */

                    $stmt_status = mysqli_prepare(
                        $conn,
                        "UPDATE tb_kos
                         SET status = 'penuh'
                         WHERE id_kos = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_status,
                        "i",
                        $id_kos
                    );

                    mysqli_stmt_execute(
                        $stmt_status
                    );

                    $error =
                        "Maaf, kamar kos ini sudah penuh.";

                } else {

                    /* -------------------------------------------------
                       CEK PENGAJUAN AKTIF
                    ------------------------------------------------- */

                    $stmt_cek = mysqli_prepare(
                        $conn,
                        "SELECT id_sewa
                         FROM tb_sewa
                         WHERE id_kos = ?
                         AND id_user = ?
                         AND status IN
                         ('menunggu','diproses','diterima')
                         LIMIT 1"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_cek,
                        "ii",
                        $id_kos,
                        $id_user
                    );

                    mysqli_stmt_execute(
                        $stmt_cek
                    );

                    $result_cek =
                        mysqli_stmt_get_result(
                            $stmt_cek
                        );


                    if (
                        mysqli_num_rows(
                            $result_cek
                        ) > 0
                    ) {

                        $error =
                            "Anda sudah memiliki pengajuan aktif untuk kos ini.";

                    } else {

                        /* -------------------------------------------------
                           SIMPAN PENGAJUAN
                        ------------------------------------------------- */

                        $stmt_insert = mysqli_prepare(
                            $conn,
                            "INSERT INTO tb_sewa
                            (
                                id_kos,
                                id_user,
                                tanggal_mulai,
                                durasi,
                                catatan,
                                status
                            )
                            VALUES
                            (?, ?, ?, ?, ?, 'menunggu')"
                        );

                        mysqli_stmt_bind_param(
                            $stmt_insert,
                            "iisis",
                            $id_kos,
                            $id_user,
                            $tanggal_mulai,
                            $durasi,
                            $catatan
                        );


                        if (
                            mysqli_stmt_execute(
                                $stmt_insert
                            )
                        ) {

                            header(
                                "Location: pelanggan/riwayat_sewa.php?success=1"
                            );

                            exit;

                        } else {

                            $error =
                                "Pengajuan sewa gagal disimpan.";
                        }
                    }
                }
            }
        }
    }
}


/* =====================================================
   AMBIL DATA KOS UNTUK TAMPILAN
===================================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM tb_kos
     WHERE id_kos = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_kos
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$kos = mysqli_fetch_assoc($result);


if (!$kos) {

    die("Kos tidak ditemukan.");
}


/* =====================================================
   HITUNG KAMAR TERISI
===================================================== */

$stmt_terisi = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS terisi
     FROM tb_sewa
     WHERE id_kos = ?
     AND status = 'diterima'"
);

mysqli_stmt_bind_param(
    $stmt_terisi,
    "i",
    $id_kos
);

mysqli_stmt_execute($stmt_terisi);

$result_terisi =
    mysqli_stmt_get_result($stmt_terisi);

$data_terisi =
    mysqli_fetch_assoc($result_terisi);

$kamar_terisi =
    (int) $data_terisi['terisi'];

$jumlah_kamar =
    (int) $kos['jumlah_kamar'];

$kamar_tersisa =
    max(
        0,
        $jumlah_kamar - $kamar_terisi
    );


/* =====================================================
   FOTO KOS
===================================================== */

$foto = !empty($kos['foto'])
    ? 'assets/images/' . $kos['foto']
    : 'assets/images/no-image.jpg';

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
        Ajukan Sewa - KosFinder
    </title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                #0b1120;

            color:
                #ffffff;

            min-height:
                100vh;
        }


        /* =====================================
           NAVBAR
        ===================================== */

        nav {

            height:
                70px;

            background:
                #111827;

            border-bottom:
                1px solid #1f2937;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            padding:
                0 7%;

            position:
                sticky;

            top:
                0;

            z-index:
                100;
        }


        .logo {

            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            text-decoration:
                none;

            color:
                white;

            font-size:
                22px;

            font-weight:
                bold;
        }


        .logo img {

            width:
                38px;

            height:
                38px;

            object-fit:
                contain;
        }


        .nav-right {

            display:
                flex;

            align-items:
                center;

            gap:
                20px;
        }


        .nav-right a {

            color:
                #cbd5e1;

            text-decoration:
                none;

            font-size:
                14px;
        }


        .nav-right a:hover {

            color:
                #22c55e;
        }


        /* =====================================
           CONTAINER
        ===================================== */

        .container {

            width:
                90%;

            max-width:
                1100px;

            margin:
                40px auto;

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                30px;
        }


        /* =====================================
           CARD
        ===================================== */

        .card {

            background:
                #111827;

            border:
                1px solid #1f2937;

            border-radius:
                18px;

            padding:
                25px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,.25);
        }


        /* =====================================
           FOTO
        ===================================== */

        .kos-image {

            width:
                100%;

            height:
                300px;

            object-fit:
                cover;

            border-radius:
                14px;

            margin-bottom:
                20px;

            background:
                #1f2937;
        }


        /* =====================================
           DETAIL KOS
        ===================================== */

        .kos-name {

            font-size:
                27px;

            margin-bottom:
                10px;
        }


        .location {

            color:
                #94a3b8;

            line-height:
                1.6;

            margin-bottom:
                20px;
        }


        .price {

            color:
                #22c55e;

            font-size:
                25px;

            font-weight:
                bold;

            margin-bottom:
                15px;
        }


        .price span {

            font-size:
                14px;

            color:
                #94a3b8;

            font-weight:
                normal;
        }


        /* =====================================
           INFO
        ===================================== */

        .info {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                12px;

            margin-top:
                20px;
        }


        .info-item {

            background:
                #0b1120;

            border:
                1px solid #1f2937;

            padding:
                14px;

            border-radius:
                12px;
        }


        .info-item small {

            display:
                block;

            color:
                #64748b;

            margin-bottom:
                5px;
        }


        /* =====================================
           KAPASITAS
        ===================================== */

        .capacity-box {

            margin-top:
                20px;

            padding:
                18px;

            border-radius:
                14px;

            background:
                rgba(34,197,94,.10);

            border:
                1px solid
                rgba(34,197,94,.25);

            color:
                #22c55e;
        }


        .capacity-box strong {

            font-size:
                28px;
        }


        .capacity-box span {

            display:
                block;

            margin-top:
                5px;

            color:
                #94a3b8;

            font-size:
                13px;
        }


        /* =====================================
           FORM
        ===================================== */

        .form-title {

            font-size:
                24px;

            margin-bottom:
                25px;
        }


        .form-group {

            margin-bottom:
                20px;
        }


        label {

            display:
                block;

            margin-bottom:
                8px;

            color:
                #cbd5e1;

            font-size:
                14px;
        }


        input,
        select,
        textarea {

            width:
                100%;

            background:
                #0b1120;

            border:
                1px solid #334155;

            color:
                white;

            border-radius:
                10px;

            padding:
                13px 14px;

            outline:
                none;

            font-family:
                inherit;

            font-size:
                14px;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color:
                #22c55e;

            box-shadow:
                0 0 0 3px
                rgba(34,197,94,.08);
        }


        textarea {

            min-height:
                120px;

            resize:
                vertical;
        }


        /* =====================================
           KALENDER
        ===================================== */

        .date-input-wrapper {

            position:
                relative;
        }


        .date-input-wrapper input {

            padding-right:
                50px;

            cursor:
                pointer;
        }


        .calendar-icon {

            position:
                absolute;

            right:
                15px;

            top:
                50%;

            transform:
                translateY(-50%);

            font-size:
                21px;

            cursor:
                pointer;

            user-select:
                none;

            z-index:
                2;
        }


        .calendar-icon:hover {

            transform:
                translateY(-50%)
                scale(1.1);
        }


        /* =====================================
           BUTTON
        ===================================== */

        .btn {

            width:
                100%;

            border:
                none;

            padding:
                14px;

            border-radius:
                10px;

            background:
                #22c55e;

            color:
                #052e16;

            font-weight:
                bold;

            font-size:
                15px;

            cursor:
                pointer;

            transition:
                .2s;
        }


        .btn:hover {

            background:
                #16a34a;

            color:
                white;
        }


        /* =====================================
           ERROR
        ===================================== */

        .error {

            background:
                rgba(239,68,68,.10);

            border:
                1px solid
                rgba(239,68,68,.30);

            color:
                #f87171;

            padding:
                14px;

            border-radius:
                10px;

            margin-bottom:
                20px;

            line-height:
                1.5;
        }


        /* =====================================
           FULL NOTICE
        ===================================== */

        .full-notice {

            margin-top:
                20px;

            padding:
                18px;

            border-radius:
                12px;

            background:
                rgba(239,68,68,.10);

            border:
                1px solid
                rgba(239,68,68,.30);

            color:
                #f87171;

            text-align:
                center;

            line-height:
                1.6;
        }


        /* =====================================
           BACK BUTTON
        ===================================== */

        .back {

            display:
                inline-block;

            margin-top:
                15px;

            color:
                #94a3b8;

            text-decoration:
                none;

            font-size:
                14px;
        }


        .back:hover {

            color:
                #22c55e;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 800px) {

            .container {

                grid-template-columns:
                    1fr;
            }


            nav {

                padding:
                    0 5%;
            }


            .nav-right {

                gap:
                    10px;
            }


            .nav-right a {

                font-size:
                    12px;
            }
        }

    </style>

</head>


<body>


<!-- =====================================
     NAVBAR
===================================== -->

<nav>

    <a
        href="index.php"
        class="logo"
    >

        <img
            src="assets/images/logo-kosfinder.png"
            alt="KosFinder"
        >

        KosFinder

    </a>


    <div class="nav-right">

        <a href="kos.php">
            Cari Kos
        </a>

        <a href="pelanggan/dashboard.php">
            Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =====================================
     CONTENT
===================================== -->

<div class="container">


    <!-- =================================
         DETAIL KOS
    ================================== -->

    <div class="card">

        <img
            src="<?= htmlspecialchars($foto); ?>"
            alt="<?= htmlspecialchars($kos['nama_kos']); ?>"
            class="kos-image"
            onerror="this.src='assets/images/no-image.jpg';"
        >


        <h1 class="kos-name">

            <?= htmlspecialchars(
                $kos['nama_kos']
            ); ?>

        </h1>


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


        <div class="info">

            <div class="info-item">

                <small>
                    Tipe Kamar
                </small>

                <?= htmlspecialchars(
                    $kos['tipe_kamar']
                ); ?>

            </div>


            <div class="info-item">

                <small>
                    Jumlah Kamar
                </small>

                <?= $jumlah_kamar; ?>
                kamar

            </div>

        </div>


        <!-- =================================
             KAPASITAS KAMAR
        ================================== -->

        <div class="capacity-box">

            <strong>
                <?= $kamar_tersisa; ?>
            </strong>

            kamar tersedia

            <span>

                <?= $kamar_terisi; ?>
                kamar sedang terisi
                dari
                <?= $jumlah_kamar; ?>
                kamar

            </span>

        </div>


    </div>


    <!-- =================================
         FORM PENGAJUAN
    ================================== -->

    <div class="card">

        <h2 class="form-title">
            Ajukan Sewa
        </h2>


        <?php if (!empty($error)): ?>

            <div class="error">

                <?= htmlspecialchars(
                    $error
                ); ?>

            </div>

        <?php endif; ?>


        <?php if ($kamar_tersisa <= 0): ?>

            <div class="full-notice">

                <strong>
                    Kos Sudah Penuh
                </strong>

                <br>

                Saat ini tidak tersedia
                kamar untuk disewa.

            </div>

        <?php else: ?>


            <form
                method="POST"
                action=""
            >

                <input
                    type="hidden"
                    name="id_kos"
                    value="<?= $id_kos; ?>"
                >


                <!-- =================================
                     TANGGAL MULAI
                ================================== -->

                <div class="form-group">

                    <label for="tanggal_mulai">

                        Tanggal Mulai Sewa

                    </label>


                    <div class="date-input-wrapper">

                        <input
                            type="date"
                            id="tanggal_mulai"
                            name="tanggal_mulai"
                            required
                            min="<?= date('Y-m-d'); ?>"
                        >


                        <span
                            class="calendar-icon"
                            onclick="bukaKalender()"
                            title="Pilih tanggal"
                        >
                            📅
                        </span>

                    </div>

                </div>


                <!-- =================================
                     DURASI
                ================================== -->

                <div class="form-group">

                    <label for="durasi">

                        Durasi Sewa

                    </label>


                    <select
                        id="durasi"
                        name="durasi"
                        required
                    >

                        <option value="">
                            Pilih Durasi
                        </option>

                        <option value="1">
                            1 Bulan
                        </option>

                        <option value="3">
                            3 Bulan
                        </option>

                        <option value="6">
                            6 Bulan
                        </option>

                        <option value="12">
                            12 Bulan
                        </option>

                    </select>

                </div>


                <!-- =================================
                     CATATAN
                ================================== -->

                <div class="form-group">

                    <label for="catatan">

                        Catatan

                    </label>


                    <textarea
                        id="catatan"
                        name="catatan"
                        placeholder="Tambahkan catatan jika diperlukan..."
                    ></textarea>

                </div>


                <!-- =================================
                     SUBMIT
                ================================== -->

                <button
                    type="submit"
                    class="btn"
                >

                    Ajukan Sewa

                </button>


            </form>


        <?php endif; ?>


        <a
            href="detail_kos.php?id=<?= $id_kos; ?>"
            class="back"
        >

            ← Kembali ke Detail Kos

        </a>

    </div>

</div>


<script>

/* =====================================================
   BUKA KALENDER
===================================================== */

function bukaKalender() {

    const input =
        document.getElementById(
            'tanggal_mulai'
        );

    if (input.showPicker) {

        input.showPicker();

    } else {

        input.focus();
    }
}


/* =====================================================
   KLIK INPUT JUGA MEMBUKA KALENDER
===================================================== */

document
    .getElementById('tanggal_mulai')
    .addEventListener(
        'click',
        function () {

            if (this.showPicker) {

                this.showPicker();
            }

        }
    );

</script>


</body>

</html>