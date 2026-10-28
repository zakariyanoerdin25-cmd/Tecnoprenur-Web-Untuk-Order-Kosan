<?php
session_start();

require_once __DIR__ . '/../config/database.php';

/* =========================
   CEK ADMIN
========================= */
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

$error = "";

/* =========================
   PROSES TAMBAH KOS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_kos     = trim($_POST['nama_kos'] ?? '');
    $alamat       = trim($_POST['alamat'] ?? '');
    $kota         = trim($_POST['kota'] ?? '');
    $harga        = (int) ($_POST['harga'] ?? 0);
    $tipe_kamar   = trim($_POST['tipe_kamar'] ?? '');
    $jumlah_kamar = (int) ($_POST['jumlah_kamar'] ?? 1);
    $fasilitas    = trim($_POST['fasilitas'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $status       = $_POST['status'] ?? 'tersedia';

    /* =========================
       VALIDASI
    ========================= */

    if (
        $nama_kos === '' ||
        $alamat === '' ||
        $kota === '' ||
        $harga <= 0 ||
        $tipe_kamar === ''
    ) {

        $error = "Data wajib belum lengkap.";

    } elseif (
        !in_array(
            $status,
            ['tersedia', 'penuh'],
            true
        )
    ) {

        $error = "Status kos tidak valid.";

    } else {

        /* =========================
           FOLDER FOTO
        ========================= */

        $upload_dir = __DIR__ . '/../assets/images/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $foto_utama = '';

        $foto_tersimpan = [];

        /* =========================
           PROSES FOTO
        ========================= */

        if (
            isset($_FILES['foto']) &&
            isset($_FILES['foto']['name']) &&
            is_array($_FILES['foto']['name'])
        ) {

            $jumlah_file = count(
                $_FILES['foto']['name']
            );

            for ($i = 0; $i < $jumlah_file; $i++) {

                if (
                    $_FILES['foto']['error'][$i]
                    !== UPLOAD_ERR_OK
                ) {
                    continue;
                }

                $nama_asli =
                    $_FILES['foto']['name'][$i];

                $tmp_name =
                    $_FILES['foto']['tmp_name'][$i];

                $ukuran =
                    $_FILES['foto']['size'][$i];

                /* =========================
                   CEK UKURAN
                ========================= */

                if ($ukuran > 5 * 1024 * 1024) {
                    continue;
                }

                /* =========================
                   CEK MIME
                ========================= */

                $mime = mime_content_type(
                    $tmp_name
                );

                $mime_valid = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (
                    !in_array(
                        $mime,
                        $mime_valid,
                        true
                    )
                ) {
                    continue;
                }

                /* =========================
                   EXTENSION
                ========================= */

                $extension = strtolower(
                    pathinfo(
                        $nama_asli,
                        PATHINFO_EXTENSION
                    )
                );

                $extension_valid = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp'
                ];

                if (
                    !in_array(
                        $extension,
                        $extension_valid,
                        true
                    )
                ) {
                    continue;
                }

                /* =========================
                   NAMA FILE BARU
                ========================= */

                $nama_file =
                    'kos_' .
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $extension;

                $tujuan =
                    $upload_dir .
                    $nama_file;

                if (
                    move_uploaded_file(
                        $tmp_name,
                        $tujuan
                    )
                ) {

                    $foto_tersimpan[] =
                        $nama_file;
                }
            }

            /* Foto pertama menjadi foto utama */

            if (
                count($foto_tersimpan) > 0
            ) {

                $foto_utama =
                    $foto_tersimpan[0];
            }
        }


        /* =========================
           INSERT DATA KOS
        ========================= */

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO tb_kos
            (
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
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssisissss",
            $nama_kos,
            $alamat,
            $kota,
            $harga,
            $tipe_kamar,
            $jumlah_kamar,
            $fasilitas,
            $deskripsi,
            $foto_utama,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {

            $id_kos =
                mysqli_insert_id($conn);

            mysqli_stmt_close($stmt);


            /* =========================
               SIMPAN FOTO TAMBAHAN
            ========================= */

            if (
                count($foto_tersimpan) > 0
            ) {

                $stmt_foto = mysqli_prepare(
                    $conn,
                    "INSERT INTO tb_kos_foto
                    (id_kos, nama_foto)
                    VALUES (?, ?)"
                );

                foreach (
                    $foto_tersimpan
                    as $nama_foto
                ) {

                    mysqli_stmt_bind_param(
                        $stmt_foto,
                        "is",
                        $id_kos,
                        $nama_foto
                    );

                    mysqli_stmt_execute(
                        $stmt_foto
                    );
                }

                mysqli_stmt_close(
                    $stmt_foto
                );
            }


            header(
                "Location: kos.php?success=1"
            );

            exit;

        } else {

            mysqli_stmt_close($stmt);

            /* Hapus file jika database gagal */

            foreach (
                $foto_tersimpan
                as $nama_foto
            ) {

                $file =
                    $upload_dir .
                    $nama_foto;

                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $error =
                "Gagal menyimpan data kos.";
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
        Tambah Kos - KosFinder
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
        }

        .logo {
            display: flex;

            align-items: center;

            gap: 12px;

            color: white;

            text-decoration: none;
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

        .back {
            color: #cbd5e1;

            text-decoration: none;

            font-size: 14px;
        }

        .back:hover {
            color: white;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 900px;

            margin: auto;

            padding: 40px 25px 60px;
        }

        .title {
            margin-bottom: 25px;
        }

        .title h1 {
            font-size: 30px;

            margin-bottom: 7px;
        }

        .title p {
            color: #94a3b8;

            font-size: 14px;
        }

        /* =========================
           FORM
        ========================= */

        .form-card {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 16px;

            padding: 30px;
        }

        .form-grid {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .full {
            grid-column: 1 / -1;
        }

        label {
            display: block;

            color: #cbd5e1;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;

            background: #08111f;

            border: 1px solid #334155;

            border-radius: 9px;

            padding: 12px 13px;

            color: white;

            font-family: inherit;

            font-size: 14px;

            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2563eb;
        }

        textarea {
            min-height: 120px;

            resize: vertical;
        }

        .help {
            color: #64748b;

            font-size: 11px;

            margin-top: 7px;

            line-height: 1.5;
        }

        /* =========================
           FOTO
        ========================= */

        .upload-box {
            border: 1px dashed #475569;

            border-radius: 12px;

            padding: 25px;

            text-align: center;

            background: #08111f;
        }

        .upload-icon {
            font-size: 35px;

            margin-bottom: 10px;
        }

        .upload-box input {
            border: none;

            background: transparent;

            padding: 10px 0;
        }

        /* =========================
           ERROR
        ========================= */

        .error {
            background: #450a0a;

            border: 1px solid #7f1d1d;

            color: #fca5a5;

            padding: 13px 16px;

            border-radius: 9px;

            margin-bottom: 22px;

            font-size: 13px;
        }

        /* =========================
           BUTTON
        ========================= */

        .actions {
            display: flex;

            gap: 12px;

            justify-content: flex-end;

            margin-top: 10px;

            padding-top: 25px;

            border-top: 1px solid #1e293b;
        }

        .btn {
            padding: 12px 20px;

            border-radius: 9px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            border: none;

            cursor: pointer;
        }

        .btn-cancel {
            background: #1e293b;

            color: #cbd5e1;
        }

        .btn-cancel:hover {
            background: #334155;
        }

        .btn-save {
            background: #2563eb;

            color: white;
        }

        .btn-save:hover {
            background: #1d4ed8;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 650px) {

            .navbar {
                padding: 0 20px;
            }

            .form-card {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;

                text-align: center;
            }

        }

    </style>

</head>

<body>

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

    <a
        href="kos.php"
        class="back"
    >
        ← Kembali ke Data Kos
    </a>

</nav>


<div class="container">

    <div class="title">

        <h1>
            Tambah Kos
        </h1>

        <p>
            Tambahkan informasi kos dan beberapa foto untuk ditampilkan kepada pelanggan.
        </p>

    </div>


    <?php if ($error !== ''): ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <div class="form-card">

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">


                <!-- NAMA -->

                <div class="form-group full">

                    <label>
                        Nama Kos *
                    </label>

                    <input
                        type="text"
                        name="nama_kos"
                        placeholder="Contoh: Kos Mawar Residence"
                        value="<?= htmlspecialchars(
                            $_POST['nama_kos'] ?? ''
                        ); ?>"
                        required
                    >

                </div>


                <!-- ALAMAT -->

                <div class="form-group full">

                    <label>
                        Alamat *
                    </label>

                    <textarea
                        name="alamat"
                        placeholder="Masukkan alamat lengkap kos..."
                        required
                    ><?= htmlspecialchars(
                        $_POST['alamat'] ?? ''
                    ); ?></textarea>

                </div>


                <!-- KOTA -->

                <div class="form-group">

                    <label>
                        Kota *
                    </label>

                    <input
                        type="text"
                        name="kota"
                        placeholder="Contoh: Palembang"
                        value="<?= htmlspecialchars(
                            $_POST['kota'] ?? ''
                        ); ?>"
                        required
                    >

                </div>


                <!-- HARGA -->

                <div class="form-group">

                    <label>
                        Harga per Bulan *
                    </label>

                    <input
                        type="number"
                        name="harga"
                        min="1"
                        placeholder="Contoh: 1000000"
                        value="<?= htmlspecialchars(
                            $_POST['harga'] ?? ''
                        ); ?>"
                        required
                    >

                </div>


                <!-- TIPE -->

                <div class="form-group">

                    <label>
                        Tipe Kamar *
                    </label>

                    <input
                        type="text"
                        name="tipe_kamar"
                        placeholder="Contoh: Putra / Putri / Campur"
                        value="<?= htmlspecialchars(
                            $_POST['tipe_kamar'] ?? ''
                        ); ?>"
                        required
                    >

                </div>


                <!-- JUMLAH -->

                <div class="form-group">

                    <label>
                        Jumlah Kamar
                    </label>

                    <input
                        type="number"
                        name="jumlah_kamar"
                        min="1"
                        value="<?= htmlspecialchars(
                            $_POST['jumlah_kamar'] ?? '1'
                        ); ?>"
                    >

                </div>


                <!-- FASILITAS -->

                <div class="form-group full">

                    <label>
                        Fasilitas
                    </label>

                    <textarea
                        name="fasilitas"
                        placeholder="Contoh: WiFi, AC, Kamar Mandi Dalam, Parkir, CCTV"
                    ><?= htmlspecialchars(
                        $_POST['fasilitas'] ?? ''
                    ); ?></textarea>

                </div>


                <!-- DESKRIPSI -->

                <div class="form-group full">

                    <label>
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                        placeholder="Jelaskan kondisi dan keunggulan kos..."
                    ><?= htmlspecialchars(
                        $_POST['deskripsi'] ?? ''
                    ); ?></textarea>

                </div>


                <!-- FOTO -->

                <div class="form-group full">

                    <label>
                        Foto Kos
                    </label>

                    <div class="upload-box">

                        <div class="upload-icon">
                            📷
                        </div>

                        <input
                            type="file"
                            name="foto[]"
                            accept=".jpg,.jpeg,.png,.webp"
                            multiple
                        >

                        <div class="help">
                            Kamu dapat memilih beberapa foto sekaligus.
                            Format JPG, JPEG, PNG, atau WEBP.
                            Maksimal 5 MB per foto.
                        </div>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option
                            value="tersedia"
                            <?= ($_POST['status'] ?? 'tersedia') === 'tersedia'
                                ? 'selected'
                                : ''; ?>
                        >
                            Tersedia
                        </option>

                        <option
                            value="penuh"
                            <?= ($_POST['status'] ?? '') === 'penuh'
                                ? 'selected'
                                : ''; ?>
                        >
                            Penuh
                        </option>

                    </select>

                </div>

            </div>


            <div class="actions">

                <a
                    href="kos.php"
                    class="btn btn-cancel"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="btn btn-save"
                >
                    Simpan Kos
                </button>

            </div>

        </form>

    </div>

</div>

</body>

</html>