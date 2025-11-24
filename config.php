<?php
// TENTUKAN 'RUMAH' PROYEK KAMU
define('BASE_URL', '/php/pedulilingkungan/');

// Detail Database
$db_server = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "db_pedulilingkungan";

// Buat koneksi
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Mulai session
session_start();

// --- FITUR COOKIE (AUTO LOGIN) ---
// Cek jika Session kosong TAPI Cookie ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    $cookie_id = $_COOKIE['id'];
    $cookie_key = $_COOKIE['key'];

    // Cari user berdasarkan ID di cookie
    $sql_cookie = "SELECT id, username, role, poin FROM users WHERE id = ?";
    if ($stmt_c = mysqli_prepare($conn, $sql_cookie)) {
        mysqli_stmt_bind_param($stmt_c, "i", $cookie_id);
        mysqli_stmt_execute($stmt_c);
        $result_c = mysqli_stmt_get_result($stmt_c);
        
        if ($row_c = mysqli_fetch_assoc($result_c)) {
            // Cek validasi Key (Hash Username)
            if ($cookie_key === hash('sha256', $row_c['username'])) {
                // Cookie Valid! Buat Session otomatis
                $_SESSION['user_id'] = $row_c['id'];
                $_SESSION['username'] = $row_c['username'];
                $_SESSION['role'] = $row_c['role'];
                $_SESSION['poin'] = $row_c['poin'];
            }
        }
        mysqli_stmt_close($stmt_c);
    }
}
?>