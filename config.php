<?php
define('BASE_URL', '/pedulilingkungan/');
// Mulai session
session_start();

// Detail Database
$db_server = "localhost";
$db_user = "root";
$db_pass = ""; // Kosongkan jika default XAMPP
$db_name = "db_pedulilingkungan";

// Buat koneksi
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>