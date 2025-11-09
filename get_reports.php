<?php
// ... (kode config.php) ...
require_once 'config.php';

// Ambil laporan yang sudah disetujui atau sudah dibersihkan
$sql = "SELECT r.id, r.latitude, r.longitude, r.jenis_sampah, r.foto, 
               r.catatan, r.tgl_lapor, r.status, u.username 
        FROM reports r
        JOIN users u ON r.user_id = u.id
        WHERE r.status = 'approved' OR r.status = 'cleaned'
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