<?php 
include 'header.php'; 

// --- FUNGSI BANTUAN: Time Ago ---
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60 );
    $hours           = round($seconds / 3600);
    $days          = round($seconds / 86400);
    $weeks          = round($seconds / 604800);
    $months          = round($seconds / 2629440);
    $years          = round($seconds / 31553280);
    if($seconds <= 60) { return "Baru saja"; }
    else if($minutes <=60) { return ($minutes==1) ? "1 menit lalu" : "$minutes menit lalu"; }
    else if($hours <=24) { return ($hours==1) ? "1 jam lalu" : "$hours jam lalu"; }
    else if($days <= 7) { return ($days==1) ? "Kemarin" : "$days hari lalu"; }
    else if($weeks <= 4.3) { return ($weeks==1) ? "1 minggu lalu" : "$weeks minggu lalu"; }
    else if($months <=12) { return ($months==1) ? "1 bulan lalu" : "$months bulan lalu"; }
    else { return ($years==1) ? "1 tahun lalu" : "$years tahun lalu"; }
}

// --- UPDATE QUERY: Tambah Subquery untuk hitung komentar ---
$sql_recent = "SELECT r.*, u.username,
               (SELECT COUNT(*) FROM comments WHERE report_id = r.id) AS jumlah_komentar
               FROM reports r 
               JOIN users u ON r.user_id = u.id 
               WHERE r.status IN ('approved', 'pending_clean')
               ORDER BY r.tgl_lapor DESC 
               LIMIT 5";
$result_recent = mysqli_query($conn, $sql_recent);
?>

<section class="relative pt-20 pb-32 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-primary/90 to-primary"></div>
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-96 h-96 bg-accent/30 rounded-full blur-3xl mix-blend-soft-light animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-success/30 rounded-full blur-3xl mix-blend-soft-light"></div>
    </div>

    <div class="container mx-auto px-4 md:px-8 relative z-10 text-center">
        <div class="max-w-4xl mx-auto">
            <span class="inline-block py-1 px-3 rounded-full bg-white/10 text-white text-sm font-medium mb-6 backdrop-blur-sm border border-white/20">
                <i class="fa-solid fa-earth-asia mr-2"></i> Bersama Jaga Bumi Kita
            </span>
            <h1 class="font-heading font-bold text-5xl md:text-7xl text-white mb-8 leading-tight tracking-tight">
                Laporkan Sampah,<br/>
                Wujudkan Lingkungan Bersih
            </h1>
            <p class="text-lg md:text-xl text-white/90 mb-10 leading-relaxed max-w-2xl mx-auto">
                Bergabunglah dengan ribuan pengguna lain untuk melaporkan tumpukan sampah liar dan menciptakan perubahan nyata di lingkungan sekitar kita.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#peta" class="group flex items-center gap-3 bg-accent hover:bg-accent-hover text-white px-8 py-4 rounded-full font-heading font-bold text-lg transition-all hover:shadow-xl hover:-translate-y-1 w-full sm:w-auto justify-center">
                    <i class="fa-solid fa-map-location-dot text-xl group-hover:scale-110 transition-transform"></i>
                    <span>Lihat Peta Laporan</span>
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="lapor.php" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-full font-heading font-bold text-lg backdrop-blur-md border border-white/30 transition-all hover:shadow-xl hover:-translate-y-1 w-full sm:w-auto justify-center">
                    <i class="fa-solid fa-camera text-xl"></i>
                    <span>Buat Laporan Baru</span>
                </a>
                <?php else: ?>
                <a href="register.php" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-full font-heading font-bold text-lg backdrop-blur-md border border-white/30 transition-all hover:shadow-xl hover:-translate-y-1 w-full sm:w-auto justify-center">
                    <i class="fa-solid fa-user-plus text-xl"></i>
                    <span>Daftar Sekarang</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none rotate-180">
        <svg class="relative block w-full h-[100px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="#F8FAFC" opacity=".25"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="#F8FAFC" opacity=".5"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="#F8FAFC"></path>
        </svg>
    </div>
</section>

<section id="peta" class="py-20 bg-white">
    <div class="container mx-auto px-4 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-primary font-bold tracking-wider uppercase text-sm mb-2 block">Pantau Langsung</span>
            <h2 class="font-heading font-bold text-3xl md:text-5xl text-slate-800 mb-6">Peta Sebaran Laporan</h2>
            <p class="text-slate-600 text-lg">
                Lihat titik-titik lokasi sampah. Klik pin untuk melihat detail.
            </p>
        </div>

        <div class="bg-white p-2 rounded-3xl shadow-elevated border border-slate-100">
            <div id="map-utama" class="w-full h-[500px] md:h-[600px] rounded-2xl z-10 relative"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-6 mt-8">
            <div class="flex items-center gap-2 bg-red-50 px-4 py-2 rounded-full text-danger font-medium border border-red-100">
                <img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png" class="h-6">
                <span>Perlu Dibersihkan</span>
            </div>
            <div class="flex items-center gap-2 bg-emerald-50 px-4 py-2 rounded-full text-success font-medium border border-emerald-100">
                <img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png" class="h-6">
                <span>Sudah Bersih</span>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="container mx-auto px-4 md:px-8 max-w-5xl">
        <div class="flex items-center justify-between mb-8">
            <h2 class="font-heading font-bold text-3xl text-slate-800">Laporan Terbaru</h2>
        </div>

        <div class="grid gap-4">
            <?php if (mysqli_num_rows($result_recent) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_recent)): ?>
                    <?php
                        $severity_bg = ($row['tingkat_keparahan'] == 'Parah') ? 'bg-red-100 text-red-600' : (($row['tingkat_keparahan'] == 'Sedang') ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                        
                        // Badge Status
                        $status_badge = '';
                        if ($row['status'] == 'pending_clean') {
                             $status_badge = '<span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 flex items-center gap-1"><i class="fa-solid fa-clock"></i> Verifikasi Bersih</span>';
                        } else {
                             $status_badge = '<span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Menunggu Tindakan</span>';
                        }

                        $lokasi_display = !empty($row['catatan']) ? htmlspecialchars(substr($row['catatan'], 0, 60)) . (strlen($row['catatan']) > 60 ? '...' : '') : 'Lokasi di Peta';
                    ?>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="flex-1">
                                <div class="flex items-start gap-3 mb-3">
                                    <i class="fa-solid fa-map-pin text-primary mt-1"></i>
                                    <h3 class="font-heading font-semibold text-lg text-slate-800 line-clamp-1"><?php echo $lokasi_display; ?></h3>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="px-3 py-1 rounded-full font-medium <?php echo $severity_bg; ?>"><?php echo $row['tingkat_keparahan']; ?></span>
                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-medium"><?php echo $row['jenis_sampah']; ?></span>
                                    <span class="text-slate-400">•</span>
                                    <span class="text-slate-500"><?php echo time_ago($row['tgl_lapor']); ?></span>
                                    <span class="text-slate-400">•</span>
                                    <span class="font-medium text-primary"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between md:justify-end gap-4 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <?php echo $status_badge; ?>
                                
                                <a href="detail_laporan.php?id=<?php echo $row['id']; ?>#komentar" class="flex items-center gap-1 text-slate-500 hover:text-primary transition-colors text-sm font-medium" title="Lihat Komentar">
                                    <i class="fa-regular fa-comment-dots text-lg"></i>
                                    <span><?php echo $row['jumlah_komentar']; ?></span>
                                </a>

                                <a href="detail_laporan.php?id=<?php echo $row['id']; ?>" class="px-5 py-2 rounded-xl bg-slate-50 text-sm font-bold text-slate-700 hover:bg-primary hover:text-white transition-all">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                    <i class="fa-regular fa-folder-open text-4xl text-slate-300 mb-3"></i>
                    <p class="text-slate-500 font-medium">Belum ada laporan aktif saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    var mapUtama = L.map('map-utama', { scrollWheelZoom: false }).setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap, &copy; CARTO', maxZoom: 20
    }).addTo(mapUtama);

    var ikonSampah = L.icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });
    var ikonBersih = L.icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });

    fetch('get_reports.php')
        .then(response => response.json())
        .then(reports => {
            reports.forEach(report => {
                var ikon = (report.status === 'cleaned') ? ikonBersih : ikonSampah;
                var statusText = (report.status === 'cleaned') ? 
                    '<span class="text-emerald-600 font-bold">SUDAH BERSIH</span>' : 
                    ((report.status === 'pending_clean') ? '<span class="text-amber-600 font-bold">VERIFIKASI BERSIH</span>' : '<span class="text-red-600 font-bold">PERLU DIBERSIHKAN</span>');

                var popupContent = `
                    <div class="min-w-[200px]">
                        <img src="uploads/${report.foto}" class="w-full h-32 object-cover rounded-lg mb-2">
                        <div class="mb-2 text-xs">${statusText}</div>
                        <h4 class="font-bold text-slate-800 line-clamp-2">${report.catatan || report.jenis_sampah}</h4>
                        <a href="detail_laporan.php?id=${report.id}" class="block mt-3 text-center bg-primary text-white py-1.5 rounded-md text-sm font-medium">Lihat Detail</a>
                    </div>
                `;
                L.marker([report.latitude, report.longitude], {icon: ikon}).addTo(mapUtama).bindPopup(popupContent);
            });
        });
</script>

<?php include 'footer.php'; ?>