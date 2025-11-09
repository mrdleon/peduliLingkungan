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
$sql_check = "SELECT id, status, jenis_sampah, foto FROM reports WHERE id = ?";
if($stmt_check = mysqli_prepare($conn, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "i", $report_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if(mysqli_num_rows($result_check) == 0) {
        die("Laporan tidak ditemukan.");
    }
    $report = mysqli_fetch_assoc($result_check);
    if($report['status'] != 'approved') {
        // Tampilan error yang lebih bagus nanti
        $fatal_error = "Laporan ini tidak bisa ditandai 'bersih' (mungkin sudah bersih atau masih pending).";
    }
    mysqli_stmt_close($stmt_check);
}

// Logika Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($fatal_error)) {
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

<div class="min-h-[calc(100vh-80px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-xl w-full">
        
        <?php if(isset($fatal_error)): ?>
            <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-100 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 text-danger rounded-full mb-4">
                    <i class="fa-solid fa-ban text-2xl"></i>
                </div>
                <h2 class="font-heading font-bold text-2xl text-slate-800 mb-2">Akses Ditolak</h2>
                <p class="text-slate-600 mb-6"><?php echo $fatal_error; ?></p>
                <a href="<?php echo BASE_URL; ?>detail_laporan.php?id=<?php echo $report_id; ?>" class="btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                    Kembali ke Detail Laporan
                </a>
            </div>
        <?php else: ?>

            <div class="bg-white p-8 md:p-10 rounded-3xl shadow-xl border border-slate-100">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 mb-4">
                        <i class="fa-solid fa-broom text-3xl"></i>
                    </div>
                    <h2 class="font-heading font-bold text-2xl md:text-3xl text-slate-800">
                        Lapor Sudah Bersih
                    </h2>
                    <p class="mt-2 text-slate-500">
                        Terima kasih telah beraksi! Unggah bukti foto area yang telah dibersihkan.
                    </p>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 flex items-center gap-4">
                    <img src="uploads/<?php echo $report['foto']; ?>" alt="Foto Sebelum" class="w-16 h-16 object-cover rounded-lg grayscale opacity-75">
                    <div>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Membersihkan Laporan:</span>
                        <h3 class="font-heading font-semibold text-slate-800"><?php echo $report['jenis_sampah']; ?> (ID: <?php echo $report_id; ?>)</h3>
                    </div>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-danger p-4 rounded-md flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-danger"></i>
                        <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <?php if(!empty($sukses)): ?>
                    <div class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full mb-6 animate-bounce">
                            <i class="fa-solid fa-check text-4xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-2xl text-slate-800 mb-2">Laporan Terkirim!</h3>
                        <p class="text-slate-600 mb-8"><?php echo $sukses; ?></p>
                        <a href="<?php echo BASE_URL; ?>detail_laporan.php?id=<?php echo $report_id; ?>" class="inline-flex items-center justify-center px-6 py-3 border border-transparent font-heading font-bold rounded-full text-white bg-primary hover:bg-primary-hover transition-all">
                            Kembali ke Detail Laporan
                        </a>
                    </div>
                <?php else: ?>
                    <form action="lapor_bersih.php?id=<?php echo $report_id; ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Foto Bukti "Sesudah" Dibersihkan <span class="text-danger">*</span>
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-emerald-500 transition-colors bg-emerald-50/50">
                                <div class="space-y-1 text-center">
                                    <i class="fa-solid fa-camera-rotate text-4xl text-emerald-500/50 mb-3"></i>
                                    <div class="flex text-sm text-slate-600 justify-center">
                                        <label for="foto_sesudah" class="relative cursor-pointer bg-white rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-emerald-500 px-2">
                                            <span>Upload foto bersih</span>
                                            <input id="foto_sesudah" name="foto_sesudah" type="file" class="sr-only" accept="image/*" required onchange="previewImage(this)">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG, GIF up to 5MB</p>
                                </div>
                            </div>
                            <div id="image-preview" class="hidden mt-4 rounded-xl overflow-hidden border border-emerald-200">
                                <img src="" alt="Preview" class="w-full h-48 object-cover">
                            </div>
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-full shadow-sm text-lg font-heading font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all hover:scale-[1.02]">
                            <i class="fa-solid fa-sparkles"></i>
                            Kirim Bukti Bersih
                        </button>
                    </form>
                    
                    <div class="text-center mt-6">
                        <a href="<?php echo BASE_URL; ?>detail_laporan.php?id=<?php echo $report_id; ?>" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Batal
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function previewImage(input) {
        var preview = document.getElementById('image-preview');
        var previewImg = preview.querySelector('img');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('hidden');
        }
    }
</script>

<?php include 'footer.php'; ?>