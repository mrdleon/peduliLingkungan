<?php
require_once 'config.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
// Wajib ada ID laporan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$report_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$error = '';
$sukses = '';

// Cek apakah laporan ini valid
$sql_check = "SELECT id, status FROM reports WHERE id = ?";
if($stmt_check = mysqli_prepare($conn, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "i", $report_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if(mysqli_num_rows($result_check) == 0) {
        die("Laporan tidak ditemukan.");
    }
    $report = mysqli_fetch_assoc($result_check);
    if($report['status'] != 'approved') {
        die("Laporan ini tidak bisa ditandai 'bersih' (mungkin sudah bersih atau masih pending).");
    }
    mysqli_stmt_close($stmt_check);
}

// Logika Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_FILES["foto_sesudah"]["name"])) {
        $error = "Foto sesudah (bukti bersih) wajib diunggah.";
    } else {
        // --- Proses Upload Foto ---
        $target_dir = "uploads/";
        $nama_file_unik = 'clean_' . uniqid() . '_' . basename($_FILES["foto_sesudah"]["name"]);
        $target_file = $target_dir . $nama_file_unik;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek adalah gambar
        $check = getimagesize($_FILES["foto_sesudah"]["tmp_name"]);
        if ($check === false) { $error = "File bukan gambar."; $uploadOk = 0; }
        // Cek ukuran
        if ($_FILES["foto_sesudah"]["size"] > 5000000) { $error = "Maks 5MB."; $uploadOk = 0; }
        // Cek format
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") { $error = "Hanya JPG, JPEG, PNG."; $uploadOk = 0; }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["foto_sesudah"]["tmp_name"], $target_file)) {
                // Foto berhasil diupload, update DB
                $sql = "UPDATE reports SET foto_sesudah = ?, status = 'pending_clean', cleaned_by_user_id = ? WHERE id = ?";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sii", $nama_file_unik, $user_id, $report_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $sukses = "Laporan 'Sudah Bersih' berhasil dikirim! Menunggu validasi admin.";
                    } else {
                        $error = "Gagal menyimpan laporan ke database.";
                    }
                    mysqli_stmt_close($stmt);
                }
            } else {
                $error = "Terjadi kesalahan saat mengunggah foto.";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container form-container">
    <h2>Lapor Sudah Bersih (ID: <?php echo $report_id; ?>)</h2>
    <p>Terima kasih telah berpartisipasi! Silakan unggah foto area yang sudah bersih sebagai bukti.</p>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(!empty($sukses)): ?>
        <div class="alert alert-success"><?php echo $sukses; ?></div>
    <?php endif; ?>

    <?php if(empty($sukses)): // Sembunyikan form jika sudah berhasil ?>
    <form action="lapor_bersih.php?id=<?php echo $report_id; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="foto_sesudah">Upload Foto SESUDAH Bersih (Wajib, Maks 5MB)</label>
            <input type="file" name="foto_sesudah" id="foto_sesudah" accept="image/*" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Kirim Bukti Bersih</button>
        </div>
    </form>
    <?php endif; ?>
    <a href="detail_laporan.php?id=<?php echo $report_id; ?>">Kembali ke detail laporan</a>
</div>

<?php include 'footer.php'; ?>