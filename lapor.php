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

<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="font-heading font-bold text-3xl md:text-4xl text-slate-800 mb-4">
                Buat Laporan Sampah Baru
            </h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Bantu kami memetakan masalah sampah di sekitarmu. Tandai lokasi di peta dan isi detail laporannya.
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="mb-8 bg-red-50 border-l-4 border-danger p-4 rounded-lg flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-danger mt-0.5"></i>
                <div>
                    <h3 class="text-red-800 font-medium">Terjadi Kesalahan</h3>
                    <p class="text-red-700 text-sm"><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!empty($sukses)): ?>
            <div class="mb-8 bg-emerald-50 border-l-4 border-success p-4 rounded-lg flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-success mt-0.5"></i>
                <div>
                    <h3 class="text-emerald-800 font-medium">Berhasil!</h3>
                    <p class="text-emerald-700 text-sm"><?php echo $sukses; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 bg-white p-2 rounded-3xl shadow-elevated border border-slate-100 h-fit sticky top-24">
                <div class="relative">
                    <div id="map-lapor" class="w-full h-[500px] lg:h-[600px] rounded-2xl z-0"></div>
                    <div class="absolute top-4 left-4 right-4 bg-white/90 backdrop-blur-sm px-4 py-3 rounded-xl shadow-sm border border-slate-200 text-sm text-slate-600 z-[400]">
                        <i class="fa-solid fa-circle-info text-primary mr-2"></i>
                        Klik pada peta untuk menandai lokasi tumpukan sampah secara akurat.
                    </div>
                </div>
                <div class="px-4 py-3 bg-slate-50 rounded-b-2xl mt-2 text-sm text-slate-500 flex items-center justify-between">
                    <span>Koordinat Terpilih:</span>
                    <span id="lokasi-terpilih" class="font-mono font-medium bg-white px-2 py-1 rounded border border-slate-200">Belum ada</span>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white p-6 md:p-8 rounded-3xl shadow-xl border border-slate-100">
                    <h3 class="font-heading font-bold text-xl text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-primary"></i>
                        Detail Laporan
                    </h3>

                    <form action="lapor.php" method="post" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Foto Kondisi Sampah <span class="text-danger">*</span>
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-primary transition-colors bg-slate-50">
                                <div class="space-y-1 text-center">
                                    <i class="fa-solid fa-image text-4xl text-slate-400 mb-3"></i>
                                    <div class="flex text-sm text-slate-600 justify-center">
                                        <label for="foto" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-hover focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary px-2">
                                            <span>Upload file</span>
                                            <input id="foto" name="foto" type="file" class="sr-only" accept="image/*" required onchange="previewImage(this)">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG, GIF up to 5MB</p>
                                </div>
                            </div>
                            <div id="image-preview" class="hidden mt-4 rounded-xl overflow-hidden border border-slate-200">
                                <img src="" alt="Preview" class="w-full h-48 object-cover">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jenis_sampah" class="block text-sm font-medium text-slate-700 mb-2">
                                    Jenis Sampah
                                </label>
                                <select id="jenis_sampah" name="jenis_sampah" required class="block w-full px-4 py-3 rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-slate-50">
                                    <option value="Campuran">Campuran</option>
                                    <option value="Plastik">Plastik</option>
                                    <option value="Organik">Organik</option>
                                    <option value="B3">B3 (Berbahaya)</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label for="tingkat_keparahan" class="block text-sm font-medium text-slate-700 mb-2">
                                    Tingkat Keparahan
                                </label>
                                <select id="tingkat_keparahan" name="tingkat_keparahan" required class="block w-full px-4 py-3 rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-slate-50">
                                    <option value="Sedikit">Sedikit (1-2 kantong)</option>
                                    <option value="Sedang">Sedang (1 gerobak)</option>
                                    <option value="Parah">Parah (> 1 meter)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="catatan" class="block text-sm font-medium text-slate-700 mb-2">
                                Catatan Tambahan
                            </label>
                            <textarea id="catatan" name="catatan" rows="4" class="block w-full px-4 py-3 rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-slate-50" placeholder="Deskripsikan lokasi lebih detail, bau, atau akses jalan..."></textarea>
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-full shadow-sm text-lg font-heading font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all hover:scale-[1.02]">
                            <i class="fa-solid fa-paper-plane"></i>
                            Kirim Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inisialisasi Peta
    var mapLapor = L.map('map-lapor', {
        scrollWheelZoom: false // Agar tidak mengganggu scroll halaman saat mengisi form
    }).setView([-6.2088, 106.8456], 13);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        maxZoom: 20
    }).addTo(mapLapor);

    // Coba dapatkan lokasi pengguna saat ini
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            mapLapor.setView([lat, lng], 15);
             // Opsional: Langsung set marker di lokasi user
             // setMarker(lat, lng);
        }, function() {
            console.log("Geolocation tidak diizinkan atau error.");
        });
    }

    var marker;
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var lokasiTerpilih = document.getElementById('lokasi-terpilih');

    function setMarker(lat, lng) {
        if (marker) {
            mapLapor.removeLayer(marker);
        }
        marker = L.marker([lat, lng], {draggable: true}).addTo(mapLapor);
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
        lokasiTerpilih.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        
        // Update saat marker di-drag
        marker.on('dragend', function(e) {
            var position = marker.getLatLng();
            latInput.value = position.lat.toFixed(8);
            lngInput.value = position.lng.toFixed(8);
            lokasiTerpilih.textContent = `${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}`;
        });
    }

    mapLapor.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });

    // Fungsi Preview Image sederhana
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