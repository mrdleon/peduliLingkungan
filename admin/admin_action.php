<?php
require_once '../config.php';
require_once 'auth_check.php'; // Pastikan hanya admin yang akses

if (isset($_GET['id']) && isset($_GET['action'])) {
    $report_id = (int)$_GET['id'];
    $action = $_GET['action'];

    // 1. Ambil Data Laporan Dulu (untuk tahu siapa yang harus dapat poin)
    $sql_info = "SELECT user_id, cleaned_by_user_id FROM reports WHERE id = ?";
    $stmt_info = mysqli_prepare($conn, $sql_info);
    mysqli_stmt_bind_param($stmt_info, "i", $report_id);
    mysqli_stmt_execute($stmt_info);
    $result_info = mysqli_stmt_get_result($stmt_info);
    $report_data = mysqli_fetch_assoc($result_info);
    mysqli_stmt_close($stmt_info);

    if (!$report_data) {
        // Jika laporan tidak ada, balik ke index
        header("Location: index.php");
        exit;
    }

    // 2. Tentukan Aksi
    switch ($action) {
        // --- VALIDASI LAPORAN BARU ---
        case 'approve':
            // Ubah status jadi approved
            $sql = "UPDATE reports SET status = 'approved' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $report_id);
            mysqli_stmt_execute($stmt);
            
            // Beri POIN ke PELAPOR (misal: +10 poin)
            $sql_poin = "UPDATE users SET poin = poin + 10 WHERE id = ?";
            $stmt_poin = mysqli_prepare($conn, $sql_poin);
            mysqli_stmt_bind_param($stmt_poin, "i", $report_data['user_id']);
            mysqli_stmt_execute($stmt_poin);
            break;

        case 'reject':
            // Ubah status jadi rejected
            $sql = "UPDATE reports SET status = 'rejected' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $report_id);
            mysqli_stmt_execute($stmt);
            break;

        // --- VALIDASI KEBERSIHAN ---
        case 'approve_clean':
            // Ubah status jadi cleaned
            $sql = "UPDATE reports SET status = 'cleaned' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $report_id);
            mysqli_stmt_execute($stmt);

            // Beri POIN ke PEMBERSIH (misal: +20 poin, lebih besar karena aksi nyata)
            if (!empty($report_data['cleaned_by_user_id'])) {
                $sql_poin = "UPDATE users SET poin = poin + 20 WHERE id = ?";
                $stmt_poin = mysqli_prepare($conn, $sql_poin);
                mysqli_stmt_bind_param($stmt_poin, "i", $report_data['cleaned_by_user_id']);
                mysqli_stmt_execute($stmt_poin);
            }
            break;

        case 'reject_clean':
            // Kembalikan status jadi approved (masih kotor), dan hapus bukti foto/data pembersih
            // Kita set cleaned_by_user_id jadi NULL dan foto_sesudah jadi NULL
            $sql = "UPDATE reports SET status = 'approved', foto_sesudah = NULL, cleaned_by_user_id = NULL WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $report_id);
            mysqli_stmt_execute($stmt);
            break;
    }
}

// Kembali ke dashboard admin
header("Location: index.php");
exit;
?>