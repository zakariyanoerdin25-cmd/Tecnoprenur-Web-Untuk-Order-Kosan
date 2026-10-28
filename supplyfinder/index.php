<?php

session_start();

require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| DATA KOS TERBARU
|--------------------------------------------------------------------------
*/

$query_kos = mysqli_query(
    $conn,
    "SELECT *
     FROM tb_kos
     WHERE status = 'tersedia'
     ORDER BY id_kos DESC
     LIMIT 6"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>KosFinder - Cari Kos Jadi Lebih Mudah</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        /* =====================================================
           GLOBAL
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #020817;
            color: white;
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
            position: relative;
            z-index: 1000;

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

        /* LOGO - HANYA GAMBAR */

        .brand {
            display: flex;

            align-items: center;

            text-decoration: none;
        }

        .brand img {
            width: 145px;
            height: 58px;

            object-fit: contain;

            display: block;
        }


        /* MENU */

        .nav-links {
            display: flex;

            align-items: center;

            gap: 28px;
        }

        .nav-links a {
            color: #dce7f7;

            text-decoration: none;

            font-size: 14px;

            transition: 0.2s;
        }

        .nav-links a:hover {
            color: #078cff;
        }

        .nav-links .active {
            color: white;

            border-bottom:
                2px solid
                #078cff;

            padding-bottom: 8px;
        }

        .login-btn {
            border:
                1px solid
                rgba(255,255,255,0.8);

            padding: 10px 22px;

            border-radius: 25px;
        }

        .register-btn {
            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white !important;

            padding: 11px 23px;

            border-radius: 25px;

            font-weight: 600;
        }


        /* =====================================================
           HERO
        ===================================================== */

        .hero-kosfinder {
            position: relative;

            min-height: 650px;

            overflow: hidden;

            background:
                radial-gradient(
                    circle at 70% 45%,
                    rgba(0,117,255,0.20),
                    transparent 35%
                ),
                linear-gradient(
                    120deg,
                    #020817,
                    #03142c 55%,
                    #020a19
                );
        }


        .hero-kosfinder::before {
            content: "";

            position: absolute;

            width: 700px;
            height: 700px;

            right: -300px;
            top: -250px;

            background:
                radial-gradient(
                    circle,
                    rgba(0,128,255,0.35),
                    transparent 70%
                );

            filter: blur(20px);
        }


        /* =====================================================
           BACKGROUND SHAPE
        ===================================================== */

        .blue-shape {
            position: absolute;

            border:
                1px solid
                rgba(0,136,255,0.5);

            background:
                linear-gradient(
                    135deg,
                    rgba(0,110,255,0.20),
                    rgba(0,80,180,0.03)
                );

            transform: rotate(35deg);

            border-radius: 35px;
        }

        .shape-one {
            width: 250px;
            height: 250px;

            left: -180px;
            top: -70px;
        }

        .shape-two {
            width: 180px;
            height: 180px;

            right: -80px;
            bottom: -80px;
        }

        .shape-three {
            width: 130px;
            height: 130px;

            left: -60px;
            bottom: 30px;

            opacity: 0.7;
        }


        /* =====================================================
           HERO CONTAINER
        ===================================================== */

        .hero-container {
            position: relative;

            z-index: 2;

            min-height: 650px;

            display: grid;

            grid-template-columns:
                1.05fr
                0.95fr;

            align-items: center;

            gap: 30px;
        }


        /* =====================================================
           HERO LEFT
        ===================================================== */

        .hero-left {
            padding: 50px 0;
        }

        .hero-small-title {
            color: #0b9cff;

            font-size: 13px;

            font-weight: 700;

            letter-spacing: 2px;

            margin-bottom: 10px;
        }

        .hero-left h1 {
            margin: 0;

            font-size: 64px;

            line-height: 1;

            font-weight: 800;

            letter-spacing: -2px;
        }

        .hero-left h1 span {
            color: #078cff;
        }

        .hero-left h2 {
            font-size: 28px;

            margin: 14px 0;

            color: white;
        }

        .hero-left p {
            max-width: 580px;

            color: #b9c9dd;

            font-size: 16px;

            line-height: 1.7;

            margin-bottom: 28px;
        }


        /* =====================================================
           SEARCH HERO
        ===================================================== */

        .hero-search {
            width: 100%;

            max-width: 620px;

            height: 62px;

            background: white;

            border-radius: 35px;

            display: flex;

            align-items: center;

            padding: 7px;

            box-shadow:
                0 0 30px
                rgba(0,136,255,0.30);
        }

        .search-icon {
            color: #168cff;

            font-size: 20px;

            margin-left: 17px;
        }

        .hero-search input {
            flex: 1;

            border: none;

            outline: none;

            padding: 0 15px;

            font-size: 14px;

            color: #222;

            min-width: 0;
        }

        .hero-search button {
            border: none;

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white;

            height: 48px;

            padding: 0 25px;

            border-radius: 28px;

            font-weight: 600;

            cursor: pointer;

            white-space: nowrap;
        }

        .hero-search button:hover {
            transform: scale(1.02);
        }


        /* =====================================================
           HERO FEATURES
        ===================================================== */

        .hero-features {
            display: flex;

            gap: 25px;

            margin-top: 30px;

            flex-wrap: wrap;
        }

        .hero-feature {
            display: flex;

            align-items: center;

            gap: 9px;
        }

        .feature-icon {
            width: 38px;
            height: 38px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                rgba(0,120,255,0.15);

            border:
                1px solid
                rgba(0,140,255,0.5);

            font-size: 16px;
        }

        .hero-feature strong {
            display: block;

            font-size: 12px;

            color: white;
        }

        .hero-feature small {
            display: block;

            font-size: 11px;

            color: #8fa8c5;
        }


        /* =====================================================
           HERO RIGHT
        ===================================================== */

        .hero-right {
            position: relative;

            height: 550px;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        /* LOGO BESAR */

        .hero-logo-glow {
            position: absolute;

            z-index: 3;

            left: 10px;
            top: 75px;

            width: 230px;
            height: 230px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(0,126,255,0.22),
                    transparent 70%
                );

            display: flex;

            align-items: center;

            justify-content: center;
        }

        .hero-logo-glow img {
            width: 185px;
            height: 185px;

            object-fit: contain;

            filter:
                drop-shadow(
                    0 0 25px
                    rgba(0,130,255,0.5)
                );
        }


        /* =====================================================
           VISUAL RUMAH
        ===================================================== */

        .hero-house {
            position: absolute;

            right: -30px;

            top: 65px;

            width: 440px;

            height: 400px;

            border-radius: 35px;

            background:
                linear-gradient(
                    145deg,
                    #0a6eea,
                    #002f83
                );

            transform:
                perspective(700px)
                rotateY(-8deg);

            box-shadow:
                0 20px 70px
                rgba(0,100,255,0.3);

            overflow: hidden;
        }

        .hero-house::before {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            left: 70px;
            top: 70px;

            background:
                linear-gradient(
                    135deg,
                    #132a49,
                    #071325
                );

            clip-path:
                polygon(
                    50% 0,
                    100% 35%,
                    100% 100%,
                    0 100%,
                    0 35%
                );
        }

        .hero-house::after {
            content: "";

            position: absolute;

            width: 230px;
            height: 100px;

            left: 100px;
            bottom: 65px;

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0051b9
                );

            clip-path:
                polygon(
                    0 100%,
                    50% 0,
                    100% 100%
                );

            opacity: 0.9;
        }


        /* WINDOWS */

        .house-window {
            position: absolute;

            z-index: 4;

            width: 50px;
            height: 50px;

            border-radius: 5px;

            background:
                linear-gradient(
                    135deg,
                    #0c1729,
                    #172941
                );

            border:
                1px solid
                rgba(0,150,255,0.4);
        }

        .window-one {
            top: 170px;
            left: 145px;
        }

        .window-two {
            top: 170px;
            left: 205px;
        }

        .window-three {
            top: 230px;
            left: 145px;
        }

        .window-four {
            top: 230px;
            left: 205px;
        }


        /* =====================================================
           TRUSTED CARD
        ===================================================== */

        .trusted-card {
            position: absolute;

            z-index: 8;

            bottom: 45px;

            right: 0;

            width: 280px;

            padding: 13px 18px;

            border-radius: 40px;

            background:
                rgba(255,255,255,0.96);

            color: #162238;

            display: flex;

            align-items: center;

            gap: 12px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.25);
        }

        .trusted-avatar {
            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: #e7f1ff;

            display: flex;

            align-items: center;

            justify-content: center;
        }

        .trusted-card strong {
            display: block;

            font-size: 13px;
        }

        .trusted-card small {
            display: block;

            color: #667085;

            font-size: 11px;

            margin-top: 3px;
        }

        .trusted-card > span {
            margin-left: auto;

            color: #087cff;

            font-size: 20px;
        }


        /* =====================================================
           KOS TERBARU
        ===================================================== */

        .kos-section {
            padding: 80px 0;

            background:
                linear-gradient(
                    180deg,
                    #03152d,
                    #020b1c
                );
        }

        .section-header {
            text-align: center;

            max-width: 700px;

            margin:
                0 auto 40px;
        }

        .eyebrow {
            color: #078cff;

            font-size: 13px;

            font-weight: 700;

            letter-spacing: 1px;
        }

        .section-header h2 {
            font-size: 34px;

            margin: 10px 0;

            color: white;
        }

        .section-header p {
            color: #9fb2ca;

            line-height: 1.7;
        }


        /* KOS GRID */

        .kos-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(270px, 1fr)
                );

            gap: 25px;
        }

        .kos-card {
            background:
                linear-gradient(
                    145deg,
                    #0a213f,
                    #061326
                );

            border:
                1px solid
                rgba(0,133,255,0.35);

            border-radius: 15px;

            overflow: hidden;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,0.20);

            transition: 0.25s;
        }

        .kos-card:hover {
            transform:
                translateY(-6px);

            border-color:
                rgba(0,150,255,0.7);

            box-shadow:
                0 15px 35px
                rgba(0,90,255,0.18);
        }

        .kos-image {
            width: 100%;

            height: 210px;

            object-fit: cover;

            display: block;
        }

        .kos-no-image {
            width: 100%;

            height: 210px;

            background: #10233b;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #8da0b8;
        }

        .kos-content {
            padding: 20px;
        }

        .kos-content h3 {
            margin:
                0 0 10px;

            font-size: 20px;

            color: white;
        }

        .kos-location {
            color: #9db1ca;

            font-size: 14px;

            margin-bottom: 12px;
        }

        .kos-price {
            color: #0795ff;

            font-weight: 700;

            font-size: 19px;

            margin-bottom: 12px;
        }

        .kos-price small {
            color: #9db1ca;

            font-size: 12px;

            font-weight: normal;
        }

        .kos-type {
            display: inline-block;

            padding: 6px 10px;

            background:
                rgba(0,130,255,0.12);

            color: #38a8ff;

            border:
                1px solid
                rgba(0,140,255,0.25);

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 15px;
        }

        .detail-button {
            display: block;

            text-align: center;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #078cff,
                    #0067e0
                );

            color: white;

            padding: 11px;

            border-radius: 8px;

            font-weight: 600;
        }

        .detail-button:hover {
            box-shadow:
                0 6px 18px
                rgba(0,120,255,0.30);
        }


        /* =====================================================
           TENTANG
        ===================================================== */

        .about-section {
            padding: 85px 0;

            background:
                radial-gradient(
                    circle at 10% 50%,
                    rgba(0,110,255,0.12),
                    transparent 25%
                ),
                linear-gradient(
                    180deg,
                    #020b1c,
                    #03152d
                );
        }

        .about-logo {
            display: block;

            width: 190px;
            height: 190px;

            object-fit: contain;

            margin:
                0 auto 35px;

            filter:
                drop-shadow(
                    0 0 25px
                    rgba(0,130,255,0.35)
                );
        }

        .about-text {
            max-width: 850px;

            margin:
                0 auto 50px;

            text-align: center;

            color: #aebed1;

            line-height: 1.8;

            font-size: 16px;
        }


        /* =====================================================
           VISI MISI
        ===================================================== */

        .vision-mission {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 25px;

            max-width: 900px;

            margin:
                0 auto 75px;
        }

        .about-box {
            background:
                rgba(5,28,55,0.75);

            border:
                1px solid
                rgba(0,135,255,0.35);

            padding: 30px;

            border-radius: 16px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.15);
        }

        .about-icon {
            font-size: 40px;

            margin-bottom: 10px;
        }

        .about-box h3 {
            font-size: 23px;

            margin: 10px 0;

            color: white;
        }

        .about-box p {
            color: #9fb2ca;

            line-height: 1.7;
        }


        /* =====================================================
           PENDIRI
        ===================================================== */

        .founders-header {
            text-align: center;

            margin-bottom: 35px;
        }

        .founders-header h2 {
            font-size: 34px;

            margin: 10px 0;

            color: white;
        }

        .founders-header p {
            color: #9fb2ca;
        }

        .founders-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;

            max-width: 950px;

            margin: auto;
        }

        .founder-card {
            background:
                linear-gradient(
                    145deg,
                    rgba(6,35,68,0.9),
                    rgba(3,20,40,0.9)
                );

            padding: 35px 20px;

            border-radius: 16px;

            text-align: center;

            border:
                1px solid
                rgba(0,133,255,0.4);

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.18);

            transition: 0.2s;
        }

        .founder-card:hover {
            transform:
                translateY(-6px);

            border-color:
                rgba(0,155,255,0.8);
        }

        .founder-photo {
            width: 120px;
            height: 120px;

            margin:
                0 auto 20px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    #0a68c8,
                    #061a35
                );

            border:
                2px solid
                rgba(0,150,255,0.7);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 50px;
        }

        .founder-card h3 {
            font-size: 23px;

            margin:
                0 0 8px;

            color: white;
        }

        .founder-card p {
            color: #91a7c0;

            margin: 0;
        }


        /* =====================================================
           CTA
        ===================================================== */

        .cta {
            padding: 70px 0;

            background:
                linear-gradient(
                    135deg,
                    #0069db,
                    #003f9b
                );

            color: white;

            text-align: center;
        }

        .cta h2 {
            font-size: 34px;

            margin:
                0 0 15px;
        }

        .cta p {
            opacity: 0.9;

            margin-bottom: 25px;
        }

        .cta-button {
            display: inline-block;

            padding:
                13px 25px;

            background: white;

            color: #0069db;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 700;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            background: #010713;

            color: white;

            padding:
                50px 0 20px;
        }

        .footer-content {
            display: grid;

            grid-template-columns:
                2fr 1fr 1fr;

            gap: 40px;
        }

        .footer-logo {
            display: flex;

            align-items: center;

            margin-bottom: 15px;
        }

        .footer-logo img {
            width: 145px;
            height: 55px;

            object-fit: contain;
        }

        .footer p {
            color: #8999ad;

            line-height: 1.7;
        }

        .footer h4 {
            margin-top: 0;
        }

        .footer a {
            display: block;

            color: #8999ad;

            text-decoration: none;

            margin-bottom: 10px;
        }

        .footer a:hover {
            color: #0795ff;
        }

        .copyright {
            border-top:
                1px solid
                #1c2a3b;

            margin-top: 35px;

            padding-top: 20px;

            text-align: center;

            color: #66768b;

            font-size: 14px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 950px) {

            .hero-container {
                grid-template-columns: 1fr;

                text-align: center;
            }

            .hero-left p,
            .hero-search {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-features {
                justify-content: center;
            }

            .hero-right {
                height: 420px;

                margin-top: -30px;
            }

            .hero-logo-glow {
                left: 10%;
            }

        }


        @media (max-width: 700px) {

            .nav-content {
                flex-direction: column;

                padding: 15px 0;

                gap: 15px;
            }

            .nav-links {
                flex-wrap: wrap;

                justify-content: center;

                gap: 15px;
            }

            .brand img {
                width: 135px;
                height: 55px;
            }

            .hero-left h1 {
                font-size: 48px;
            }

            .hero-left h2 {
                font-size: 23px;
            }

            .hero-search {
                height: auto;

                padding: 8px;

                flex-wrap: wrap;
            }

            .hero-search input {
                height: 45px;
            }

            .hero-search button {
                width: 100%;
            }

            .hero-right {
                height: 350px;
            }

            .hero-house {
                width: 290px;
                height: 280px;

                right: 0;
            }

            .hero-logo-glow {
                width: 140px;
                height: 140px;

                left: 0;
                top: 80px;
            }

            .hero-logo-glow img {
                width: 105px;
                height: 105px;
            }

            .trusted-card {
                width: 230px;

                right: 0;

                bottom: 20px;
            }

            .vision-mission {
                grid-template-columns: 1fr;
            }

            .founders-grid {
                grid-template-columns: 1fr;

                max-width: 400px;
            }

            .footer-content {
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


        <!-- LOGO ASLI
             TIDAK ADA TEKS TAMBAHAN
        -->

        <a
            href="index.php"
            class="brand"
        >

            <img
                src="assets/images/logo-kosfinder.png"
                alt="KosFinder"
            >

        </a>


        <!-- MENU -->

        <div class="nav-links">


            <a
                href="index.php"
                class="active"
            >
                Beranda
            </a>


            <a href="kos.php">
                Cari Kos
            </a>


            <a href="#tentang">
                Tentang Kami
            </a>


            <?php if (
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'admin'
            ): ?>

                <a href="admin/dashboard.php">
                    Dashboard
                </a>

                <a
                    href="logout.php"
                    class="login-btn"
                >
                    Logout
                </a>


            <?php elseif (
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'pelanggan'
            ): ?>

                <a href="pelanggan/dashboard.php">
                    Dashboard
                </a>

                <a
                    href="logout.php"
                    class="login-btn"
                >
                    Logout
                </a>


            <?php else: ?>

                <a
                    href="login.php"
                    class="login-btn"
                >
                    Login
                </a>

                <a
                    href="register.php"
                    class="register-btn"
                >
                    Daftar
                </a>

            <?php endif; ?>


        </div>

    </div>

</nav>


<!-- =====================================================
     HERO
===================================================== -->

<section class="hero-kosfinder">


    <div class="blue-shape shape-one"></div>

    <div class="blue-shape shape-two"></div>

    <div class="blue-shape shape-three"></div>


    <div class="container hero-container">


        <!-- LEFT -->

        <div class="hero-left">


            <div class="hero-small-title">
                SELAMAT DATANG DI
            </div>


            <h1>
                Kos<span>Finder</span>
            </h1>


            <h2>
                Cari Kos Jadi Lebih Mudah
            </h2>


            <p>

                Temukan kos yang sesuai dengan kebutuhan,
                lokasi, fasilitas, dan budget Anda dengan
                cepat dan nyaman bersama KosFinder.

            </p>


            <!-- SEARCH -->

            <form
                action="kos.php"
                method="GET"
                class="hero-search"
            >

                <div class="search-icon">
                    🔍
                </div>


                <input
                    type="text"
                    name="q"
                    placeholder="Cari nama kos, kota, atau alamat..."
                >


                <button type="submit">

                    Cari Kos

                    <span>
                        →
                    </span>

                </button>

            </form>


            <!-- FEATURES -->

            <div class="hero-features">


                <div class="hero-feature">

                    <div class="feature-icon">
                        🏠
                    </div>

                    <div>

                        <strong>
                            Banyak Pilihan
                        </strong>

                        <small>
                            Kos
                        </small>

                    </div>

                </div>


                <div class="hero-feature">

                    <div class="feature-icon">
                        📍
                    </div>

                    <div>

                        <strong>
                            Lokasi
                        </strong>

                        <small>
                            Strategis
                        </small>

                    </div>

                </div>


                <div class="hero-feature">

                    <div class="feature-icon">
                        🛡️
                    </div>

                    <div>

                        <strong>
                            Aman &
                        </strong>

                        <small>
                            Terpercaya
                        </small>

                    </div>

                </div>


                <div class="hero-feature">

                    <div class="feature-icon">
                        ⚡
                    </div>

                    <div>

                        <strong>
                            Proses Mudah
                        </strong>

                        <small>
                            dan Cepat
                        </small>

                    </div>

                </div>


            </div>

        </div>


        <!-- RIGHT -->

        <div class="hero-right">


            <div class="hero-logo-glow">

                <img
                    src="assets/images/logo-kosfinder.png"
                    alt="Logo KosFinder"
                >

            </div>


            <div class="hero-house">

                <div class="house-window window-one"></div>

                <div class="house-window window-two"></div>

                <div class="house-window window-three"></div>

                <div class="house-window window-four"></div>

            </div>


            <div class="trusted-card">

                <div class="trusted-avatar">
                    🏠
                </div>

                <div>

                    <strong>
                        KosFinder
                    </strong>

                    <small>
                        Temukan kos pilihanmu
                    </small>

                </div>

                <span>
                    →
                </span>

            </div>


        </div>


    </div>

</section>


<!-- =====================================================
     KOS TERBARU
===================================================== -->

<section class="kos-section">

    <div class="container">


        <div class="section-header">

            <span class="eyebrow">
                PILIHAN KOS
            </span>

            <h2>
                Kos Terbaru
            </h2>

            <p>
                Temukan berbagai pilihan kos yang
                tersedia untuk Anda.
            </p>

        </div>


        <div class="kos-grid">


            <?php if (
                mysqli_num_rows($query_kos) > 0
            ): ?>


                <?php while (
                    $kos = mysqli_fetch_assoc($query_kos)
                ): ?>


                    <div class="kos-card">


                        <?php if (
                            !empty($kos['foto'])
                        ): ?>

                            <img
                                src="assets/images/<?= htmlspecialchars($kos['foto']); ?>"
                                alt="<?= htmlspecialchars($kos['nama_kos']); ?>"
                                class="kos-image"
                            >

                        <?php else: ?>

                            <div class="kos-no-image">
                                🏠 Tidak ada foto
                            </div>

                        <?php endif; ?>


                        <div class="kos-content">


                            <h3>
                                <?= htmlspecialchars(
                                    $kos['nama_kos']
                                ); ?>
                            </h3>


                            <div class="kos-location">

                                📍

                                <?= htmlspecialchars(
                                    $kos['kota']
                                ); ?>

                            </div>


                            <div class="kos-price">

                                Rp

                                <?= number_format(
                                    $kos['harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                                <small>
                                    / bulan
                                </small>

                            </div>


                            <div class="kos-type">

                                <?= htmlspecialchars(
                                    $kos['tipe_kamar']
                                ); ?>

                            </div>


                            <a
                                href="detail_kos.php?id=<?= $kos['id_kos']; ?>"
                                class="detail-button"
                            >
                                Lihat Detail
                            </a>


                        </div>

                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div
                    style="
                        grid-column:1/-1;
                        text-align:center;
                        padding:60px 20px;
                    "
                >

                    <h3>
                        Belum ada kos tersedia
                    </h3>

                    <p style="color:#9fb2ca;">
                        Admin belum menambahkan data kos.
                    </p>

                </div>


            <?php endif; ?>


        </div>


        <div
            style="
                text-align:center;
                margin-top:35px;
            "
        >

            <a
                href="kos.php"
                class="detail-button"
                style="
                    display:inline-block;
                    padding:12px 25px;
                "
            >
                Lihat Semua Kos
            </a>

        </div>


    </div>

</section>


<!-- =====================================================
     TENTANG KOSFINDER
===================================================== -->

<section
    class="about-section"
    id="tentang"
>


    <div class="container">


        <div class="section-header">

            <span class="eyebrow">
                TENTANG KOSFINDER
            </span>

            <h2>
                Cari Kos Jadi Lebih Mudah
            </h2>

        </div>


        <img
            src="assets/images/logo-kosfinder.png"
            alt="Logo KosFinder"
            class="about-logo"
        >


        <p class="about-text">

            KosFinder adalah platform pencarian kos
            yang membantu calon penghuni menemukan
            tempat tinggal yang sesuai dengan kebutuhan,
            lokasi, fasilitas, dan anggaran.

            Kami hadir untuk membuat proses pencarian
            kos menjadi lebih mudah, cepat, dan nyaman.

        </p>


        <!-- VISI MISI -->

        <div class="vision-mission">


            <div class="about-box">

                <div class="about-icon">
                    🎯
                </div>

                <h3>
                    Visi
                </h3>

                <p>

                    Menjadi platform pencarian kos yang
                    terpercaya, mudah digunakan, dan
                    membantu masyarakat menemukan tempat
                    tinggal yang sesuai.

                </p>

            </div>


            <div class="about-box">

                <div class="about-icon">
                    🚀
                </div>

                <h3>
                    Misi
                </h3>

                <p>

                    Menyediakan informasi kos yang jelas,
                    memudahkan proses pencarian dan
                    pengajuan sewa, serta memberikan
                    pengalaman terbaik bagi pengguna.

                </p>

            </div>


        </div>


        <!-- PENDIRI -->

        <div class="founders">


            <div class="founders-header">

                <span class="eyebrow">
                    TIM KAMI
                </span>

                <h2>
                    Pendiri KosFinder
                </h2>

                <p>

                    KosFinder dibangun oleh tiga orang
                    pendiri yang memiliki visi untuk
                    membuat pencarian kos menjadi
                    lebih mudah.

                </p>

            </div>


            <div class="founders-grid">


                <!-- JEKI -->

                <div class="founder-card">

                    <div class="founder-photo">
                        👤
                    </div>

                    <h3>
                        Jeki
                    </h3>

                    <p>
                        Pendiri KosFinder
                    </p>

                </div>


                <!-- DHANA -->

                <div class="founder-card">

                    <div class="founder-photo">
                        👤
                    </div>

                    <h3>
                        Dhana
                    </h3>

                    <p>
                        Pendiri KosFinder
                    </p>

                </div>


                <!-- FIKI -->

                <div class="founder-card">

                    <div class="founder-photo">
                        👤
                    </div>

                    <h3>
                        Fiki
                    </h3>

                    <p>
                        Pendiri KosFinder
                    </p>

                </div>


            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     CTA
===================================================== -->

<section class="cta">

    <div class="container">

        <h2>
            Siap Menemukan Kos Impianmu?
        </h2>

        <p>
            Mulai cari kos yang sesuai kebutuhan Anda sekarang.
        </p>

        <a
            href="kos.php"
            class="cta-button"
        >
            🔍 Cari Kos Sekarang
        </a>

    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="container">


        <div class="footer-content">


            <!-- INFORMASI -->

            <div>

                <div class="footer-logo">

                    <img
                        src="assets/images/logo-kosfinder.png"
                        alt="KosFinder"
                    >

                </div>


                <p>

                    Platform pencarian kos yang membantu
                    Anda menemukan tempat tinggal yang
                    nyaman dan sesuai kebutuhan.

                </p>

            </div>


            <!-- MENU -->

            <div>

                <h4>
                    Menu
                </h4>

                <a href="index.php">
                    Beranda
                </a>

                <a href="kos.php">
                    Cari Kos
                </a>

                <a href="#tentang">
                    Tentang Kami
                </a>

            </div>


            <!-- AKUN -->

            <div>

                <h4>
                    Akun
                </h4>


                <?php if (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === 'pelanggan'
                ): ?>

                    <a href="pelanggan/dashboard.php">
                        Dashboard
                    </a>

                    <a href="pelanggan/profil.php">
                        Profil
                    </a>

                    <a href="logout.php">
                        Logout
                    </a>


                <?php elseif (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === 'admin'
                ): ?>

                    <a href="admin/dashboard.php">
                        Dashboard Admin
                    </a>

                    <a href="logout.php">
                        Logout
                    </a>


                <?php else: ?>

                    <a href="login.php">
                        Login
                    </a>

                    <a href="register.php">
                        Daftar
                    </a>

                <?php endif; ?>


            </div>


        </div>


        <div class="copyright">

            © <?= date('Y'); ?>
            KosFinder.
            Semua hak dilindungi.

        </div>


    </div>

</footer>


</body>

</html>