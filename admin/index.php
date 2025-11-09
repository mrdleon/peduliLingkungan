<?php
require_once '../config.php';
require_once 'auth_check.php'; // Keamanan!

// Ambil laporan 'pending'
$sql_pending = "SELECT r.*, u.username FROM reports r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending' ORDER BY r.tgl_lapor ASC";
$result_pending = mysqli_query($conn, $sql_pending);

// Ambil laporan 'pending_clean' (Fitur 2)
$sql_pending_clean = "SELECT r.*, u.username AS pelapor, u2.username AS pembersih 
                      FROM reports r 
                      JOIN users u ON r.user_id = u.id 
                      LEFT JOIN users u2 ON r.cleaned_by_user_id = u2.id 
                      WHERE r.status = 'pending_clean' ORDER BY r.tgl_lapor ASC";
$result_pending_clean = mysqli_query($conn, $sql_pending_clean);

?>
<?php include '../header.php'; ?>
<link rel="stylesheet" href="../css/admin_style.css">

<div class="container admin-container">
    <h2>Admin Dashboard - Validasi Laporan</h2>
    
    <h3>Laporan Baru (Pending)</h3>
    <?php if (mysqli_num_rows($result_pending) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Info</th>
                    <th>Pelapor</th>
                    <th>Tgl Lapor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result_pending)): ?>
                <tr>
                    <td><img src="../uploads/<?php echo $row['foto']; ?>" alt="Foto"></td>
                    <td>
                        <strong><?php echo $row['jenis_sampah']; ?></strong> (<?php echo $row['tingkat_keparahan']; ?>)<br>
                        <small><?php echo $row['catatan']; ?></small>
                    </td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['tgl_lapor']; ?></td>
                    <td class="action-buttons">
                        <a href="admin_action.php?action=approve&id=<?php echo $row['id']; ?>" class="btn-approve">Approve</a>
                        <a href="admin_action.php?action=reject&id=<?php echo $row['id']; ?>" class="btn-reject">Reject</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak ada laporan baru yang menunggu validasi.</p>
    <?php endif; ?>

    <hr style="margin: 2rem 0;">

    <h3>Laporan "Sudah Bersih" (Pending Clean)</h3>
    <?php if (mysqli_num_rows($result_pending_clean) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Foto Sebelum</th>
                    <th>Foto Sesudah</th>
                    <th>Info</th>
                    <th>Pembersih</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result_pending_clean)): ?>
                <tr>
                    <td><img src="../uploads/<?php echo $row['foto']; ?>" alt="Foto Sebelum"></td>
                    <td><img src="../uploads/<?php echo $row['foto_sesudah']; ?>" alt="Foto Sesudah"></td> <td>
                        <strong><?php echo $row['jenis_sampah']; ?></strong><br>
                        <small>Pelapor Awal: <?php echo $row['pelapor']; ?></small>
                    </td>
                    <td><?php echo $row['pembersih']; ?></td>
                    <td class="action-buttons">
                        <a href="admin_action.php?action=approve_clean&id=<?php echo $row['id']; ?>" class="btn-approve">Approve Clean</a>
                        <a href="admin_action.php?action=reject_clean&id=<?php echo $row['id']; ?>" class="btn-reject">Reject Clean</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak ada laporan bersih yang menunggu validasi.</p>
    <?php endif; ?>

</div>

<?php include '../footer.php'; ?>