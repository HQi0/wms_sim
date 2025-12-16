<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_wms_baru"; // Pastikan nama database sesuai dengan yang kita buat tadi

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>