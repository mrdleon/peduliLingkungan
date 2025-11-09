<?php
require_once '../config.php';
require_once 'auth_check.php'; // Keamanan!

if (isset($_GET['id']) && isset($_GET['action'])) {
    $report_id = (int)$_GET['id'];
    $action = $_GET['action'];

    $user_id_pelapor = 0;
    $user_id_pembersih = 0;

    // Ambil user_id dari pelapor/pembersih
    $sql_get_user = "SELECT user_id, cleaned_by_user_id FROM reports WHERE id = ?";
    if($stmt_get_user = mysqli_prepare($conn, $sql_get_user)) {
        mysqli_stmt_bind_param($stmt_get_user, "i", $report_id);
        mysqli_stmt_execute($stmt_get_user);
        mysqli_stmt_bind_result($stmt_get_user, $user_id_pelapor, $user_id_pembersih);
        mysqli_stmt_fetch($stmt_get_user);
        mysqli_stmt_close($stmt_get_user);
    }


    $sql = "";
    $poin_user_id = 0;
    $poin_to_add = 0;

    switch ($action) {
        case 'approve':
            $sql = "UPDATE reports SET status = 'approved' WHERE id = ?";
            // --- GAMIFIKASI (Fitur 4) ---
            // Beri 10 poin untuk pelapor yang laporannya valid
            $poin_user_id = $user_id_pelapor;
            $poin_to_add = 10;
            break;
        case 'reject':
            $sql = "UPDATE reports SET status = 'rejected' WHERE id = ?";
            break;
        case 'approve_clean':
            $sql = "UPDATE reports SET status = 'cleaned' WHERE id = ?";
            // --- GAMIFIKASI (Fitur 4) ---
            // Beri 25 poin untuk user yang bersih-bersih
            $poin_user_id = $user_id_pembersih;
            $poin_to_add = 25; 
            break;
        case 'reject_clean':
            // Kembalikan statusnya ke 'approved'
            $sql = "UPDATE reports SET status = 'approved', foto_sesudah = NULL, cleaned_by_user_id = NULL WHERE id = ?";
            break;
    }

    // Eksekusi perubahan status
    if (!empty($sql)) {
        if ($stmt_status = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt_status, "i", $report_id);
            mysqli_stmt_execute($stmt_status);
            mysqli_stmt_close($stmt_status);
        }
    }

    // Eksekusi penambahan poin (Gamifikasi)
    if ($poin_user_id > 0 && $poin_to_add > 0) {
        $sql_poin = "UPDATE users SET poin = poin + ? WHERE id = ?";
        if ($stmt_poin = mysqli_prepare($conn, $sql_poin)) {
            mysqli_stmt_bind_param($stmt_poin, "ii", $poin_to_add, $poin_user_id);
            mysqli_stmt_execute($stmt_poin);
            mysqli_stmt_close($stmt_poin);
        }
    }
}

// Redirect kembali ke dashboard admin
header("Location: index.php");
exit;
?>