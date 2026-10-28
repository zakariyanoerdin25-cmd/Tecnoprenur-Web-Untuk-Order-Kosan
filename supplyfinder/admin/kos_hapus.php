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
| Cek ID Kos
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {
    header("Location: kos.php");
    exit;
}

$id_kos = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Ambil Foto Kos
|--------------------------------------------------------------------------
*/

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

$result = mysqli_stmt_get_result($stmt);

$kos = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| Jika Kos Tidak Ditemukan
|--------------------------------------------------------------------------
*/

if (!$kos) {
    header("Location: kos.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Hapus Data Kos
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM tb_kos
     WHERE id_kos = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_kos
);


if (mysqli_stmt_execute($stmt)) {

    /*
    |--------------------------------------------------------------------------
    | Hapus Foto dari Folder
    |--------------------------------------------------------------------------
    */

    if (!empty($kos['foto'])) {

        $file = "../assets/images/" . $kos['foto'];

        if (file_exists($file)) {
            unlink($file);
        }
    }
}


/*
|--------------------------------------------------------------------------
| Kembali ke Data Kos
|--------------------------------------------------------------------------
*/

header("Location: kos.php");
exit;

?>