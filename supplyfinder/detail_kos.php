<?php
session_start();

require_once __DIR__ . '/config/database.php';

$id_kos = (int) ($_GET['id'] ?? 0);

if ($id_kos <= 0) {
    header("Location: kos.php");
    exit;
}

/* =========================
   DATA KOS
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM tb_kos
     WHERE id_kos = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_kos);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$kos = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$kos) {
    header("Location: kos.php");
    exit;
}

/* =========================
   SEMUA FOTO
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id_foto, nama_foto
     FROM tb_kos_foto
     WHERE id_kos = ?
     ORDER BY id_foto ASC"
);

mysqli_stmt_bind_param($stmt, "i", $id_kos);
mysqli_stmt_execute($stmt);

$result_foto = mysqli_stmt_get_result($stmt);

$fotos = [];

while ($foto = mysqli_fetch_assoc($result_foto)) {
    $fotos[] = $foto['nama_foto'];
}

mysqli_stmt_close($stmt);

/*
 * Kalau tb_kos_foto kosong,
 * gunakan foto utama dari tb_kos.
 */
if (count($fotos) === 0 && !empty($kos['foto'])) {
    $fotos[] = $kos['foto'];
}

$status_tersedia = $kos['status'] === 'tersedia';

/* =========================
   DATA FOTO UNTUK JAVASCRIPT
========================= */

$foto_urls = [];

foreach ($fotos as $foto) {
    $foto_urls[] = 'assets/images/' . $foto;
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
        <?= htmlspecialchars($kos['nama_kos']); ?> - KosFinder
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

        body.no-scroll {
            overflow: hidden;
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

        .login-btn {
            background: #2563eb;

            color: white !important;

            padding: 9px 15px;

            border-radius: 8px;
        }

        .login-btn:hover {
            background: #1d4ed8 !important;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1200px;

            margin: auto;

            padding: 35px 40px 60px;
        }

        .back {
            display: inline-block;

            color: #94a3b8;

            text-decoration: none;

            font-size: 14px;

            margin-bottom: 25px;
        }

        .back:hover {
            color: white;
        }

        /* =========================
           GALLERY
        ========================= */

        .gallery {
            display: grid;

            grid-template-columns:
                2fr 1fr;

            gap: 12px;

            margin-bottom: 35px;

            height: 430px;
        }

        .main-photo {
            position: relative;

            overflow: hidden;

            border-radius: 16px;

            background: #111c2e;

            cursor: pointer;
        }

        .main-photo img {
            width: 100%;
            height: 100%;

            object-fit: cover;

            display: block;

            transition: 0.25s;
        }

        .main-photo:hover img {
            transform: scale(1.02);
        }

        .photo-overlay {
            position: absolute;

            left: 15px;

            bottom: 15px;

            background: rgba(0, 0, 0, 0.65);

            color: white;

            padding: 9px 13px;

            border-radius: 8px;

            font-size: 12px;
        }

        .photo-count {
            position: absolute;

            right: 15px;

            bottom: 15px;

            background: rgba(0, 0, 0, 0.7);

            color: white;

            padding: 9px 13px;

            border-radius: 8px;

            font-size: 12px;
        }

        .side-photos {
            display: grid;

            grid-template-rows:
                1fr 1fr;

            gap: 12px;
        }

        .side-photo {
            position: relative;

            overflow: hidden;

            border-radius: 14px;

            background: #111c2e;

            cursor: pointer;
        }

        .side-photo img {
            width: 100%;
            height: 100%;

            object-fit: cover;

            display: block;

            transition: 0.25s;
        }

        .side-photo:hover img {
            transform: scale(1.05);
        }

        .more-photos {
            position: absolute;

            right: 12px;

            bottom: 12px;

            background: rgba(0, 0, 0, 0.75);

            padding: 8px 11px;

            border-radius: 7px;

            font-size: 11px;
        }

        .no-photo {
            width: 100%;
            height: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #64748b;

            font-size: 55px;
        }

        /* =========================
           LIGHTBOX
        ========================= */

        .lightbox {
            display: none;

            position: fixed;

            inset: 0;

            background: rgba(0, 0, 0, 0.94);

            z-index: 1000;

            align-items: center;

            justify-content: center;

            padding: 30px;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-image {
            max-width: 90vw;

            max-height: 82vh;

            object-fit: contain;

            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;

            top: 20px;

            right: 25px;

            width: 45px;
            height: 45px;

            border: none;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.1);

            color: white;

            font-size: 27px;

            cursor: pointer;
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .lightbox-prev,
        .lightbox-next {
            position: absolute;

            top: 50%;

            transform: translateY(-50%);

            width: 48px;
            height: 48px;

            border: none;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.12);

            color: white;

            font-size: 28px;

            cursor: pointer;
        }

        .lightbox-prev {
            left: 25px;
        }

        .lightbox-next {
            right: 25px;
        }

        .lightbox-prev:hover,
        .lightbox-next:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        .lightbox-counter {
            position: absolute;

            bottom: 25px;

            left: 50%;

            transform: translateX(-50%);

            color: #cbd5e1;

            font-size: 13px;

            background: rgba(0, 0, 0, 0.5);

            padding: 8px 12px;

            border-radius: 7px;
        }

        /* =========================
           DETAIL
        ========================= */

        .detail-layout {
            display: grid;

            grid-template-columns:
                1fr 350px;

            gap: 30px;

            align-items: start;
        }

        .detail-card {
            background: #0d1728;

            border: 1px solid #1e293b;

            border-radius: 16px;

            padding: 28px;
        }

        .status {
            display: inline-block;

            padding: 7px 12px;

            border-radius: 999px;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            margin-bottom: 12px;
        }

        .tersedia {
            background: #052e16;

            color: #4ade80;
        }

        .penuh {
            background: #450a0a;

            color: #f87171;
        }

        .title {
            font-size: 30px;

            margin-bottom: 10px;
        }

        .address {
            color: #94a3b8;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 25px;
        }

        /* =========================
           INFO
        ========================= */

        .info-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 14px;

            margin-bottom: 30px;
        }

        .info-box {
            background: #111c2e;

            border-radius: 11px;

            padding: 17px;
        }

        .info-label {
            color: #64748b;

            font-size: 11px;

            margin-bottom: 7px;

            text-transform: uppercase;
        }

        .info-value {
            font-size: 15px;

            font-weight: 700;
        }

        .price {
            color: #60a5fa;

            font-size: 20px;

            font-weight: 700;
        }

        /* =========================
           SECTION
        ========================= */

        .section {
            margin-top: 25px;

            padding-top: 25px;

            border-top: 1px solid #1e293b;
        }

        .section h2 {
            font-size: 19px;

            margin-bottom: 12px;
        }

        .section p {
            color: #94a3b8;

            font-size: 14px;

            line-height: 1.8;

            white-space: pre-line;
        }

        /* =========================
           RENT
        ========================= */

        .rent-card {
            position: sticky;

            top: 100px;
        }

        .rent-card h2 {
            font-size: 20px;

            margin-bottom: 10px;
        }

        .rent-card > p {
            color: #94a3b8;

            font-size: 13px;

            line-height: 1.6;

            margin-bottom: 22px;
        }

        .rent-price {
            background: #111c2e;

            padding: 18px;

            border-radius: 11px;

            margin-bottom: 18px;
        }

        .rent-price small {
            display: block;

            color: #64748b;

            margin-bottom: 5px;
        }

        .rent-price strong {
            color: #60a5fa;

            font-size: 24px;
        }

        .btn-rent {
            display: block;

            width: 100%;

            text-align: center;

            text-decoration: none;

            background: #2563eb;

            color: white;

            padding: 14px;

            border-radius: 10px;

            font-weight: 700;

            font-size: 14px;

            transition: 0.2s;
        }

        .btn-rent:hover {
            background: #1d4ed8;
        }

        .btn-disabled {
            background: #334155;

            color: #94a3b8;

            cursor: not-allowed;
        }

        .btn-disabled:hover {
            background: #334155;
        }

        .login-note {
            color: #64748b;

            text-align: center;

            font-size: 11px;

            margin-top: 10px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .navbar {
                padding: 0 20px;
            }

            .nav-menu a:not(.login-btn) {
                display: none;
            }

            .container {
                padding: 25px 20px 50px;
            }

            .gallery {
                height: 350px;
            }

            .detail-layout {
                grid-template-columns: 1fr;
            }

            .rent-card {
                position: static;
            }

        }

        @media (max-width: 600px) {

            .gallery {
                display: block;

                height: 280px;
            }

            .main-photo {
                height: 100%;
            }

            .side-photos {
                display: none;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .title {
                font-size: 25px;
            }

            .detail-card {
                padding: 20px;
            }

            .lightbox {
                padding: 15px;
            }

            .lightbox-prev {
                left: 10px;
            }

            .lightbox-next {
                right: 10px;
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
        href="index.php"
        class="logo"
    >

        <img
            src="assets/images/logo-kosfinder.png"
            alt="KosFinder"
        >

        <span>
            KosFinder
        </span>

    </a>


    <div class="nav-menu">

        <a href="index.php">
            Beranda
        </a>

        <a href="kos.php">
            Cari Kos
        </a>

        <?php if (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'pelanggan'
        ): ?>

            <a href="pelanggan/dashboard.php">
                Dashboard
            </a>

        <?php endif; ?>


        <?php if (!isset($_SESSION['id_user'])): ?>

            <a
                href="login.php"
                class="login-btn"
            >
                Login
            </a>

        <?php else: ?>

            <a href="logout.php">
                Logout
            </a>

        <?php endif; ?>

    </div>

</nav>


<!-- =========================
     CONTENT
========================= -->

<div class="container">

    <a
        href="kos.php"
        class="back"
    >
        ← Kembali ke daftar kos
    </a>


    <!-- =========================
         GALLERY
    ========================= -->

    <div class="gallery">


        <!-- FOTO UTAMA -->

        <div
            class="main-photo"
            onclick="openLightbox(0)"
        >

            <?php if (count($fotos) > 0): ?>

                <img
                    src="assets/images/<?= htmlspecialchars($fotos[0]); ?>"
                    alt="<?= htmlspecialchars($kos['nama_kos']); ?>"
                >

                <div class="photo-overlay">
                    🔍 Klik untuk memperbesar
                </div>

                <div class="photo-count">
                    📷 <?= count($fotos); ?> foto
                </div>

            <?php else: ?>

                <div class="no-photo">
                    🏠
                </div>

            <?php endif; ?>

        </div>


        <!-- FOTO SAMPING -->

        <div class="side-photos">

            <?php if (isset($fotos[1])): ?>

                <div
                    class="side-photo"
                    onclick="openLightbox(1)"
                >

                    <img
                        src="assets/images/<?= htmlspecialchars($fotos[1]); ?>"
                        alt="Foto kos 2"
                    >

                </div>

            <?php else: ?>

                <div class="side-photo">

                    <div class="no-photo">
                        🏠
                    </div>

                </div>

            <?php endif; ?>


            <?php if (isset($fotos[2])): ?>

                <div
                    class="side-photo"
                    onclick="openLightbox(2)"
                >

                    <img
                        src="assets/images/<?= htmlspecialchars($fotos[2]); ?>"
                        alt="Foto kos 3"
                    >

                    <?php if (count($fotos) > 3): ?>

                        <div class="more-photos">
                            +<?= count($fotos) - 3; ?> foto lainnya
                        </div>

                    <?php endif; ?>

                </div>

            <?php else: ?>

                <div class="side-photo">

                    <div class="no-photo">
                        🏠
                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- =========================
         DETAIL
    ========================= -->

    <div class="detail-layout">


        <div class="detail-card">

            <span
                class="status <?= $status_tersedia
                    ? 'tersedia'
                    : 'penuh'; ?>"
            >

                <?= htmlspecialchars(
                    $kos['status']
                ); ?>

            </span>


            <h1 class="title">

                <?= htmlspecialchars(
                    $kos['nama_kos']
                ); ?>

            </h1>


            <div class="address">

                📍

                <?= htmlspecialchars(
                    $kos['alamat']
                ); ?>

                <br>

                <?= htmlspecialchars(
                    $kos['kota']
                ); ?>

            </div>


            <div class="info-grid">

                <div class="info-box">

                    <div class="info-label">
                        Harga
                    </div>

                    <div class="price">

                        Rp <?= number_format(
                            $kos['harga'],
                            0,
                            ',',
                            '.'
                        ); ?>

                    </div>

                    <small style="color:#64748b;">
                        / bulan
                    </small>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        Tipe Kamar
                    </div>

                    <div class="info-value">

                        <?= htmlspecialchars(
                            $kos['tipe_kamar']
                        ); ?>

                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        Kamar
                    </div>

                    <div class="info-value">

                        <?= (int) $kos['jumlah_kamar']; ?>

                        kamar

                    </div>

                </div>

            </div>


            <div class="section">

                <h2>
                    Fasilitas
                </h2>

                <p><?= !empty($kos['fasilitas'])
                    ? htmlspecialchars($kos['fasilitas'])
                    : 'Informasi fasilitas belum tersedia.'; ?></p>

            </div>


            <div class="section">

                <h2>
                    Deskripsi
                </h2>

                <p><?= !empty($kos['deskripsi'])
                    ? htmlspecialchars($kos['deskripsi'])
                    : 'Belum ada deskripsi untuk kos ini.'; ?></p>

            </div>

        </div>


        <!-- =========================
             RENT CARD
        ========================= -->

        <div class="detail-card rent-card">

            <h2>
                Tertarik dengan kos ini?
            </h2>

            <p>
                Ajukan sewa sekarang dan tunggu
                konfirmasi dari pengelola kos.
            </p>


            <div class="rent-price">

                <small>
                    Harga sewa
                </small>

                <strong>

                    Rp <?= number_format(
                        $kos['harga'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </strong>

                <small>
                    per bulan
                </small>

            </div>


            <?php if ($status_tersedia): ?>

                <?php if (
                    isset($_SESSION['id_user']) &&
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === 'pelanggan'
                ): ?>

                    <a
                        href="ajukan_sewa.php?id=<?= $id_kos; ?>"
                        class="btn-rent"
                    >
                        Ajukan Sewa
                    </a>

                <?php elseif (!isset($_SESSION['id_user'])): ?>

                    <a
                        href="login.php?redirect=ajukan_sewa&id=<?= $id_kos; ?>"
                        class="btn-rent"
                    >
                        Login untuk Ajukan Sewa
                    </a>

                    <div class="login-note">
                        Silakan login terlebih dahulu.
                    </div>

                <?php else: ?>

                    <div class="btn-rent btn-disabled">
                        Akun Admin
                    </div>

                <?php endif; ?>

            <?php else: ?>

                <div class="btn-rent btn-disabled">
                    Kos Sudah Penuh
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<!-- =========================
     LIGHTBOX
========================= -->

<div
    class="lightbox"
    id="lightbox"
>

    <button
        class="lightbox-close"
        onclick="closeLightbox()"
        aria-label="Tutup"
    >
        ×
    </button>


    <button
        class="lightbox-prev"
        onclick="previousPhoto()"
        aria-label="Foto sebelumnya"
    >
        ‹
    </button>


    <img
        class="lightbox-image"
        id="lightboxImage"
        src=""
        alt="Foto kos"
    >


    <button
        class="lightbox-next"
        onclick="nextPhoto()"
        aria-label="Foto berikutnya"
    >
        ›
    </button>


    <div
        class="lightbox-counter"
        id="lightboxCounter"
    >
        1 / <?= count($fotos); ?>
    </div>

</div>


<script>

    const photos = <?= json_encode(
        $foto_urls,
        JSON_UNESCAPED_SLASHES
    ); ?>;

    let currentPhoto = 0;


    function openLightbox(index) {

        if (photos.length === 0) {
            return;
        }

        currentPhoto = index;

        updateLightbox();

        document
            .getElementById('lightbox')
            .classList
            .add('active');

        document.body.classList.add(
            'no-scroll'
        );
    }


    function closeLightbox() {

        document
            .getElementById('lightbox')
            .classList
            .remove('active');

        document.body.classList.remove(
            'no-scroll'
        );
    }


    function updateLightbox() {

        document
            .getElementById('lightboxImage')
            .src = photos[currentPhoto];

        document
            .getElementById('lightboxCounter')
            .textContent =
                (currentPhoto + 1) +
                ' / ' +
                photos.length;
    }


    function nextPhoto() {

        if (photos.length === 0) {
            return;
        }

        currentPhoto =
            (currentPhoto + 1) %
            photos.length;

        updateLightbox();
    }


    function previousPhoto() {

        if (photos.length === 0) {
            return;
        }

        currentPhoto =
            (currentPhoto - 1 + photos.length) %
            photos.length;

        updateLightbox();
    }


    /* Keyboard */

    document.addEventListener(
        'keydown',
        function(event) {

            const lightbox =
                document.getElementById(
                    'lightbox'
                );

            if (
                !lightbox.classList.contains(
                    'active'
                )
            ) {
                return;
            }

            if (event.key === 'Escape') {
                closeLightbox();
            }

            if (event.key === 'ArrowRight') {
                nextPhoto();
            }

            if (event.key === 'ArrowLeft') {
                previousPhoto();
            }

        }
    );


    /* Klik area hitam untuk menutup */

    document
        .getElementById('lightbox')
        .addEventListener(
            'click',
            function(event) {

                if (
                    event.target === this
                ) {
                    closeLightbox();
                }

            }
        );

</script>

</body>

</html>