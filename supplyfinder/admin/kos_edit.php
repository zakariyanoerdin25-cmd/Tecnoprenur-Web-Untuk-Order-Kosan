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

$id_kos = (int) ($_GET['id'] ?? 0);

if ($id_kos <= 0) {
    header("Location: kos.php");
    exit;
}

$error = "";

/* =========================
   FUNGSI HAPUS FILE
========================= */
function hapusFileFoto($nama_foto)
{
    if (empty($nama_foto)) {
        return;
    }

    $file = __DIR__ . '/../assets/images/' . $nama_foto;

    if (file_exists($file)) {
        unlink($file);
    }
}

/* =========================
   PROSES HAPUS FOTO
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['hapus_foto'])
) {

    $id_foto = (int) ($_POST['id_foto'] ?? 0);

    if ($id_foto > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT nama_foto
             FROM tb_kos_foto
             WHERE id_foto = ?
             AND id_kos = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $id_foto,
            $id_kos
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $foto = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ($foto) {

            $nama_foto = $foto['nama_foto'];

            /* Hapus dari database */

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM tb_kos_foto
                 WHERE id_foto = ?
                 AND id_kos = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $id_foto,
                $id_kos
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            /* Hapus file */

            hapusFileFoto($nama_foto);
        }
    }

    header(
        "Location: kos_edit.php?id=" .
        $id_kos .
        "&success=foto"
    );

    exit;
}

/* =========================
   PROSES UPDATE KOS
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_kos'])
) {

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
           FOTO BARU
        ========================= */

        $upload_dir = __DIR__ . '/../assets/images/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $foto_baru = [];

        if (
            isset($_FILES['foto']) &&
            isset($_FILES['foto']['name']) &&
            is_array($_FILES['foto']['name'])
        ) {

            $jumlah_file =
                count($_FILES['foto']['name']);

            for (
                $i = 0;
                $i < $jumlah_file;
                $i++
            ) {

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

                /* Maksimal 5 MB */

                if (
                    $ukuran >
                    5 * 1024 * 1024
                ) {
                    continue;
                }

                /* =========================
                   CEK MIME
                ========================= */

                $mime =
                    mime_content_type(
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

                $extension =
                    strtolower(
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
                   NAMA FILE
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

                    $foto_baru[] =
                        $nama_file;
                }
            }
        }

        /* =========================
           UPDATE DATA KOS
        ========================= */

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE tb_kos
             SET
                nama_kos = ?,
                alamat = ?,
                kota = ?,
                harga = ?,
                tipe_kamar = ?,
                jumlah_kamar = ?,
                fasilitas = ?,
                deskripsi = ?,
                status = ?
             WHERE id_kos = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssisisssi",
            $nama_kos,
            $alamat,
            $kota,
            $harga,
            $tipe_kamar,
            $jumlah_kamar,
            $fasilitas,
            $deskripsi,
            $status,
            $id_kos
        );

        $berhasil =
            mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        if ($berhasil) {

            /* =========================
               SIMPAN FOTO BARU
            ========================= */

            if (count($foto_baru) > 0) {

                $stmt_foto =
                    mysqli_prepare(
                        $conn,
                        "INSERT INTO tb_kos_foto
                        (id_kos, nama_foto)
                        VALUES (?, ?)"
                    );

                foreach (
                    $foto_baru
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

                /* =========================
                   JIKA FOTO UTAMA KOS
                   MASIH KOSONG
                ========================= */

                $stmt = mysqli_prepare(
                    $conn,
                    "SELECT foto
                     FROM tb_kos
                     WHERE id_kos = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_kos
                );

                mysqli_stmt_execute($stmt);

                $result_foto =
                    mysqli_stmt_get_result(
                        $stmt
                    );

                $data_foto =
                    mysqli_fetch_assoc(
                        $result_foto
                    );

                mysqli_stmt_close($stmt);

                if (
                    empty($data_foto['foto'])
                ) {

                    $foto_utama =
                        $foto_baru[0];

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE tb_kos
                         SET foto = ?
                         WHERE id_kos = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "si",
                        $foto_utama,
                        $id_kos
                    );

                    mysqli_stmt_execute($stmt);

                    mysqli_stmt_close($stmt);
                }
            }

            header(
                "Location: kos_edit.php?id=" .
                $id_kos .
                "&success=1"
            );

            exit;

        } else {

            /* Hapus file jika update gagal */

            foreach (
                $foto_baru
                as $nama_foto
            ) {
                hapusFileFoto($nama_foto);
            }

            $error =
                "Gagal memperbarui data kos.";
        }
    }
}

/* =========================
   AMBIL DATA KOS
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM tb_kos
     WHERE id_kos = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_kos
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$kos = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$kos) {
    header("Location: kos.php");
    exit;
}

/* =========================
   AMBIL FOTO
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id_foto, nama_foto
     FROM tb_kos_foto
     WHERE id_kos = ?
     ORDER BY id_foto ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_kos
);

mysqli_stmt_execute($stmt);

$result_foto =
    mysqli_stmt_get_result($stmt);

$fotos = [];

while (
    $foto = mysqli_fetch_assoc(
        $result_foto
    )
) {
    $fotos[] = $foto;
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
        Edit Kos - KosFinder
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

        .container {
            max-width: 950px;

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

        .card {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 16px;

            padding: 30px;

            margin-bottom: 25px;
        }

        .card-title {
            font-size: 19px;

            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;
        }

        .full {
            grid-column: 1 / -1;
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

        .alert {
            padding: 13px 16px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 13px;
        }

        .success {
            background: #052e16;

            border: 1px solid #166534;

            color: #86efac;
        }

        .error {
            background: #450a0a;

            border: 1px solid #7f1d1d;

            color: #fca5a5;
        }

        /* =========================
           FOTO
        ========================= */

        .photo-grid {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }

        .photo-card {
            background: #08111f;

            border: 1px solid #1e293b;

            border-radius: 11px;

            overflow: hidden;
        }

        .photo-card img {
            width: 100%;

            height: 140px;

            object-fit: cover;

            display: block;
        }

        .photo-info {
            padding: 10px;
        }

        .photo-name {
            color: #94a3b8;

            font-size: 11px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

            margin-bottom: 8px;
        }

        .photo-actions {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 8px;
        }

        .main-label {
            color: #60a5fa;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;
        }

        .delete-photo {
            background: #450a0a;

            color: #f87171;

            border: 1px solid #7f1d1d;

            border-radius: 6px;

            padding: 6px 8px;

            font-size: 10px;

            cursor: pointer;
        }

        .delete-photo:hover {
            background: #7f1d1d;
        }

        .upload-box {
            border: 1px dashed #475569;

            border-radius: 12px;

            padding: 22px;

            text-align: center;

            background: #08111f;
        }

        .upload-box input {
            border: none;

            background: transparent;

            padding: 10px 0;
        }

        .help {
            color: #64748b;

            font-size: 11px;

            line-height: 1.5;
        }

        .actions {
            display: flex;

            justify-content: flex-end;

            gap: 12px;

            margin-top: 25px;

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

        .btn-save {
            background: #2563eb;

            color: white;
        }

        .btn-save:hover {
            background: #1d4ed8;
        }

        @media (max-width: 800px) {

            .photo-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 650px) {

            .navbar {
                padding: 0 20px;
            }

            .container {
                padding: 25px 20px 50px;
            }

            .card {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .photo-grid {
                grid-template-columns: 1fr 1fr;
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
            Edit Kos
        </h1>

        <p>
            Perbarui informasi dan foto kos.
        </p>

    </div>


    <?php if (isset($_GET['success'])): ?>

        <div class="alert success">

            ✓ Data kos berhasil diperbarui.

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="alert error">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         FOTO SAAT INI
    ========================= -->

    <div class="card">

        <h2 class="card-title">
            Foto Kos Saat Ini
        </h2>


        <?php if (count($fotos) > 0): ?>

            <div class="photo-grid">

                <?php foreach ($fotos as $index => $foto): ?>

                    <div class="photo-card">

                        <img
                            src="../assets/images/<?= htmlspecialchars($foto['nama_foto']); ?>"
                            alt="Foto kos"
                            onerror="this.style.display='none';"
                        >

                        <div class="photo-info">

                            <div class="photo-name">

                                <?= htmlspecialchars(
                                    $foto['nama_foto']
                                ); ?>

                            </div>

                            <div class="photo-actions">

                                <?php if ($index === 0): ?>

                                    <span class="main-label">
                                        Foto Utama
                                    </span>

                                <?php else: ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Hapus foto ini?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_foto"
                                            value="<?= (int) $foto['id_foto']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="hapus_foto"
                                            class="delete-photo"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <p style="color:#64748b; font-size:13px;">
                Belum ada foto tambahan.
            </p>

        <?php endif; ?>


        <div class="upload-box">

            <div style="font-size:32px; margin-bottom:8px;">
                📷
            </div>

            <strong>
                Tambahkan Foto Baru
            </strong>

            <br>

            <input
                type="file"
                name="foto[]"
                form="form-edit"
                accept=".jpg,.jpeg,.png,.webp"
                multiple
            >

            <div class="help">
                Pilih beberapa foto sekaligus.
                Maksimal 5 MB per foto.
            </div>

        </div>

    </div>


    <!-- =========================
         FORM DATA
    ========================= -->

    <div class="card">

        <h2 class="card-title">
            Informasi Kos
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
            id="form-edit"
        >

            <input
                type="hidden"
                name="update_kos"
                value="1"
            >


            <div class="form-grid">


                <div class="form-group full">

                    <label>
                        Nama Kos *
                    </label>

                    <input
                        type="text"
                        name="nama_kos"
                        value="<?= htmlspecialchars(
                            $kos['nama_kos']
                        ); ?>"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Alamat *
                    </label>

                    <textarea
                        name="alamat"
                        required
                    ><?= htmlspecialchars(
                        $kos['alamat']
                    ); ?></textarea>

                </div>


                <div class="form-group">

                    <label>
                        Kota *
                    </label>

                    <input
                        type="text"
                        name="kota"
                        value="<?= htmlspecialchars(
                            $kos['kota']
                        ); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Harga per Bulan *
                    </label>

                    <input
                        type="number"
                        name="harga"
                        min="1"
                        value="<?= (int) $kos['harga']; ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Tipe Kamar *
                    </label>

                    <input
                        type="text"
                        name="tipe_kamar"
                        value="<?= htmlspecialchars(
                            $kos['tipe_kamar']
                        ); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Jumlah Kamar
                    </label>

                    <input
                        type="number"
                        name="jumlah_kamar"
                        min="1"
                        value="<?= (int) $kos['jumlah_kamar']; ?>"
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Fasilitas
                    </label>

                    <textarea
                        name="fasilitas"
                    ><?= htmlspecialchars(
                        $kos['fasilitas']
                    ); ?></textarea>

                </div>


                <div class="form-group full">

                    <label>
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                    ><?= htmlspecialchars(
                        $kos['deskripsi']
                    ); ?></textarea>

                </div>


                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option
                            value="tersedia"
                            <?= $kos['status'] === 'tersedia'
                                ? 'selected'
                                : ''; ?>
                        >
                            Tersedia
                        </option>

                        <option
                            value="penuh"
                            <?= $kos['status'] === 'penuh'
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
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

</body>

</html>