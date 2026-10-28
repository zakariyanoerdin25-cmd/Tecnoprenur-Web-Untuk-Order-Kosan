<?php
include 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT p.*, k.nama_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori=k.id_kategori WHERE p.id_produk=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    die("Produk tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk['nama_produk']); ?> - SupplyFinder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="brand"><span>SF</span> SupplyFinder</a>
        <div class="nav-links"><a href="produk.php">Produk</a><a href="login.php" class="nav-login">Login</a></div>
    </div>
</nav>

<section class="section">
    <div class="container detail-card">
        <div class="detail-image"><span>PRODUK</span></div>
        <div class="detail-content">
            <span class="product-category"><?= htmlspecialchars($produk['nama_kategori'] ?? 'Produk'); ?></span>
            <h1><?= htmlspecialchars($produk['nama_produk']); ?></h1>
            <p><?= nl2br(htmlspecialchars($produk['deskripsi'] ?? '')); ?></p>
            <h3>Spesifikasi</h3>
            <p><?= nl2br(htmlspecialchars($produk['spesifikasi'] ?? '-')); ?></p>
            <p><strong>Satuan:</strong> <?= htmlspecialchars($produk['satuan'] ?? '-'); ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($produk['status']); ?></p>
            <a href="login.php" class="login-button detail-button">Minta Penawaran</a>
        </div>
    </div>
</section>
</body>
</html>
