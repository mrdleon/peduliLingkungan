<?php
require_once 'config.php';

// Ambil laporan yang approved, cleaned, ATAU pending_clean
$sql = "SELECT r.id, r.latitude, r.longitude, r.jenis_sampah, r.foto, 
               r.catatan, r.tgl_lapor, r.status, u.username 
        FROM reports r
        JOIN users u ON r.user_id = u.id
        WHERE r.status IN ('approved', 'cleaned', 'pending_clean')
        ORDER BY r.tgl_lapor DESC";

$result = mysqli_query($conn, $sql);
$reports = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reports[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode($reports);
mysqli_close($conn);
?>