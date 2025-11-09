<?php
require_once 'config.php';

// Cek apakah user sudah login. Jika belum, tendang ke halaman login.
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$error = '';
$sukses = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $user_id = $_SESSION['user_id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $jenis_sampah = $_POST['jenis_sampah'];
    $tingkat_keparahan = $_POST['tingkat_keparahan'];
    $catatan = $_POST['catatan'];

    // Validasi dasar
    if (empty($latitude) || empty($longitude)) {
        $error = "Lokasi belum dipilih. Silakan klik peta untuk menandai lokasi.";
    } else if (empty($_FILES["foto"]["name"])) {
        $error = "Foto wajib diunggah.";
    } else {
        // --- Proses Upload Foto ---
        $target_dir = "uploads/";
        // Buat nama file unik untuk menghindari konflik
        $nama_file_unik = uniqid() . '_' . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $nama_file_unik;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["foto"]["tmp_name"]);
        if ($check === false) {
            $error = "File bukan gambar.";
            $uploadOk = 0;
        }

        // Cek ukuran file (misal: maks 5MB)
        if ($_FILES["foto"]["size"] > 5000000) {
            $error = "Maaf, ukuran file terlalu besar (Maks 5MB).";
            $uploadOk = 0;
        }

        // Izinkan format tertentu
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Maaf, hanya format JPG, JPEG, & PNG yang diizinkan.";
            $uploadOk = 0;
        }

        // Jika semua OK, coba upload
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                // Foto berhasil diupload, sekarang masukkan data ke DB
                $sql = "INSERT INTO reports (user_id, latitude, longitude, jenis_sampah, tingkat_keparahan, catatan, foto) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iddssss", $user_id, $latitude, $longitude, $jenis_sampah, $tingkat_keparahan, $catatan, $nama_file_unik);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $sukses = "Laporan berhasil dikirim! Terima kasih atas partisipasinya.";
                        // Kosongkan form? (opsional)
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

<div class="container">
    <h2>Buat Laporan Sampah Baru</h2>
    <p>Klik pada peta di bawah untuk menandai lokasi tumpukan sampah.</p>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(!empty($sukses)): ?>
        <div class="alert alert-success"><?php echo $sukses; ?></div>
    <?php endif; ?>

    <div id="map-lapor"></div>
    <p>Lokasi Terpilih: <span id="lokasi-terpilih">Belum ada</span></p>

    <form action="lapor.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="form-group">
            <label for="foto">Upload Foto (Wajib, Maks 5MB)</label>
            <input type="file" name="foto" id="foto" accept="image/*" required>
        </div>

        <div class="form-group">
            <label for="jenis_sampah">Jenis Sampah Dominan</label>
            <select name="jenis_sampah" id="jenis_sampah" required>
                <option value="Campuran">Campuran</option>
                <option value="Plastik">Plastik</option>
                <option value="Organik">Organik</option>
                <option value="B3">B3 (Berbahaya)</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>

        <div class="form-group">
            <label for="tingkat_keparahan">Tingkat Keparahan</label>
            <select name="tingkat_keparahan" id="tingkat_keparahan" required>
                <option value="Sedikit">Sedikit (1-2 kantong)</option>
                <option value="Sedang">Sedang (1 gerobak)</option>
                <option value="Parah">Parah (Menumpuk >1 meter)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="catatan">Catatan Tambahan</label>
            <textarea name="catatan" id="catatan" rows="3" placeholder="Misal: Sampah di selokan, bau menyengat..."></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Kirim Laporan</button>
        </div>
    </form>
</div>

<script>
    // Inisialisasi Peta (Set lokasi default, misal: Jakarta)
    var mapLapor = L.map('map-lapor').setView([-6.2088, 106.8456], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(mapLapor);

    var marker;
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var lokasiTerpilih = document.getElementById('lokasi-terpilih');

    mapLapor.on('click', function(e) {
        // Hapus marker lama jika ada
        if (marker) {
            mapLapor.removeLayer(marker);
        }
        
        // Tambah marker baru
        marker = L.marker(e.latlng).addTo(mapLapor);
        
        // Update hidden input
        latInput.value = e.latlng.lat.toFixed(8);
        lngInput.value = e.latlng.lng.toFixed(8);

        // Update teks
        lokasiTerpilih.textContent = `Lat: ${latInput.value}, Lng: ${lngInput.value}`;
    });
</script>

<?php include 'footer.php'; ?>