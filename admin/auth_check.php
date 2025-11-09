<?php
// File ini akan kita 'require' di setiap halaman admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, tendang ke halaman utama
    header("Location: ../index.php");
    exit;
}
// Jika lolos, dia adalah admin
?>