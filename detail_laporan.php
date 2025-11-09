<?php
require_once 'config.php';

// Cek ID Laporan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$report_id = (int)$_GET['id'];

// --- PROSES TAMBAH KOMENTAR (Harus diletakkan sebelum header) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['komentar'])) {
    if (!isset($_SESSION['user_id'])) {
        $comment_error = "Silakan login untuk berkomentar.";
    } elseif (empty(trim($_POST['komentar']))) {
        $comment_error = "Komentar tidak boleh kosong.";
    } else {
        $komentar = trim($_POST['komentar']);
        $user_id = $_SESSION['user_id'];

        $sql_insert_comment = "INSERT INTO comments (report_id, user_id, komentar) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql_insert_comment)) {
            mysqli_stmt_bind_param($stmt, "iis", $report_id, $user_id, $komentar);
            if (mysqli_stmt_execute($stmt)) {
                // Redirect agar tidak resubmit saat refresh
                header("Location: detail_laporan.php?id=$report_id#komentar-section");
                exit;
            } else {
                $comment_error = "Gagal mengirim komentar.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// --- AMBIL DATA LAPORAN ---
$sql = "SELECT r.*, u_pelapor.username AS pelapor, u_pembersih.username AS pembersih 
        FROM reports r 
        LEFT JOIN users u_pelapor ON r.user_id = u_pelapor.id 
        LEFT JOIN users u_pembersih ON r.cleaned_by_user_id = u_pembersih.id 
        WHERE r.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $report_id);
mysqli_stmt_execute($stmt);
$report = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$report) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// --- AMBIL DATA KOMENTAR ---
$sql_comments = "SELECT c.*, u.username, u.role 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.report_id = ? 
                 ORDER BY c.tgl_komentar DESC";
$stmt_c = mysqli_prepare($conn, $sql_comments);
mysqli_stmt_bind_param($stmt_c, "i", $report_id);
mysqli_stmt_execute($stmt_c);
$result_comments = mysqli_stmt_get_result($stmt_c);
$total_comments = mysqli_num_rows($result_comments);


// --- FUNGSI BANTUAN TIME AGO (untuk komentar) ---
if (!function_exists('time_ago_comment')) {
    function time_ago_comment($timestamp) {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes = round($seconds / 60 );
        $hours   = round($seconds / 3600);
        $days    = round($seconds / 86400);
        if($seconds <= 60) { return "Baru saja"; }
        else if($minutes <=60) { return ($minutes==1) ? "1 menit lalu" : "$minutes menit lalu"; }
        else if($hours <=24) { return ($hours==1) ? "1 jam lalu" : "$hours jam lalu"; }
        else { return date('d M Y, H:i', $time_ago); }
    }
}

// UI Helpers
$status_badge = ''; $status_color = ''; $action_allowed = false;
switch ($report['status']) {
    case 'approved': $status_badge = '<span class="px-4 py-2 rounded-full bg-rose-100 text-rose-700 font-bold flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> Perlu Dibersihkan</span>'; $status_color = 'rose'; $action_allowed = true; break;
    case 'cleaned': $status_badge = '<span class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> Sudah Dibersihkan</span>'; $status_color = 'emerald'; break;
    case 'pending_clean': $status_badge = '<span class="px-4 py-2 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center gap-2"><i class="fa-solid fa-clock"></i> Verifikasi Kebersihan</span>'; $status_color = 'amber'; break;
    default: $status_badge = '<span class="px-4 py-2 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center gap-2"><i class="fa-solid fa-hourglass"></i> Menunggu Admin</span>'; $status_color = 'slate';
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen bg-slate-50 py-12 px-4 md:px-8">
    <div class="max-w-6xl mx-auto">
        
        <div class="mb-8 flex items-center gap-2 text-slate-500 text-sm font-medium">
            <a href="index.php" class="hover:text-primary transition-colors">Beranda</a>
            <i class="fa-solid fa-chevron-right text-xs opacity-50"></i>
            <span class="text-slate-800">Detail Laporan #<?php echo $report['id']; ?></span>
        </div>

        <div class="grid lg:grid-cols-5 gap-8">
            
            <div class="lg:col-span-3 space-y-8">
                <div class="bg-white rounded-3xl p-2 shadow-elevated border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-6 left-6 z-10">
                         <span class="px-4 py-2 rounded-xl bg-black/50 text-white font-bold backdrop-blur-md border border-white/20">KONDISI AWAL</span>
                    </div>
                    <img src="uploads/<?php echo $report['foto']; ?>" alt="Kondisi Awal" class="w-full h-[400px] object-cover rounded-2xl transition-transform duration-700 group-hover:scale-105">
                </div>

                <?php if (!empty($report['foto_sesudah'])): ?>
                <div class="bg-white rounded-3xl p-2 shadow-elevated border-2 border-emerald-400 relative overflow-hidden group">
                    <div class="absolute top-6 left-6 z-10">
                         <span class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold shadow-lg">SUDAH DIBERSIHKAN 🎉</span>
                    </div>
                    <img src="uploads/<?php echo $report['foto_sesudah']; ?>" alt="Kondisi Sesudah" class="w-full h-[400px] object-cover rounded-2xl transition-transform duration-700 group-hover:scale-105">
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="font-heading font-bold text-lg text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-primary"></i> Lokasi Laporan
                    </h3>
                    <div id="mini-map" class="w-full h-64 rounded-2xl z-0"></div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8">
                
                <div class="bg-white rounded-3xl p-8 shadow-elevated border border-slate-100">
                    <div class="mb-6"><?php echo $status_badge; ?></div>
                    <h1 class="font-heading font-bold text-3xl text-slate-900 mb-2 leading-tight">Laporan <?php echo $report['jenis_sampah']; ?></h1>
                    <p class="text-slate-500 flex items-center gap-2 mb-6"><i class="fa-regular fa-calendar"></i> Dilaporkan pada <?php echo date('d F Y, H:i', strtotime($report['tgl_lapor'])); ?> WIB</p>
                    <hr class="border-slate-100 my-6">
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0"><i class="fa-solid fa-user"></i></div>
                            <div><h4 class="text-sm font-medium text-slate-500">Pelapor</h4><p class="text-slate-800 font-semibold"><?php echo htmlspecialchars($report['pelapor']); ?></p></div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-<?php echo $status_color; ?>-50 flex items-center justify-center text-<?php echo $status_color; ?>-500 flex-shrink-0"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div><h4 class="text-sm font-medium text-slate-500">Tingkat Keparahan</h4><p class="text-slate-800 font-semibold"><?php echo $report['tingkat_keparahan']; ?></p></div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0"><i class="fa-solid fa-align-left"></i></div>
                            <div><h4 class="text-sm font-medium text-slate-500">Catatan Pelapor</h4><p class="text-slate-800 leading-relaxed"><?php echo !empty($report['catatan']) ? nl2br(htmlspecialchars($report['catatan'])) : '<span class="italic text-slate-400">Tidak ada catatan.</span>'; ?></p></div>
                        </div>
                        <?php if ($report['status'] == 'cleaned' && !empty($report['pembersih'])): ?>
                        <div class="flex items-start gap-4 bg-emerald-50 p-4 rounded-2xl border border-emerald-100 mt-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0"><i class="fa-solid fa-medal"></i></div>
                            <div><h4 class="text-sm font-medium text-emerald-700">Pahlawan Kebersihan</h4><p class="text-emerald-900 font-bold text-lg"><?php echo htmlspecialchars($report['pembersih']); ?></p></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-8">
                        <?php if ($action_allowed): ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="lapor_bersih.php?id=<?php echo $report['id']; ?>" class="group relative w-full flex justify-center items-center gap-3 py-4 px-6 border border-transparent font-heading font-bold rounded-2xl text-white text-lg bg-accent hover:bg-accent-hover transition-all hover:shadow-xl hover:scale-[1.02] overflow-hidden">
                                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                                    <i class="fa-solid fa-broom text-2xl"></i><span>Saya Sudah Bersihkan Ini!</span>
                                </a>
                                <p class="text-center text-sm text-slate-500 mt-3">Klik jika Anda telah membersihkan lokasi ini untuk klaim poin.</p>
                            <?php else: ?>
                                <div class="bg-slate-50 p-6 rounded-2xl text-center border border-slate-200"><p class="text-slate-600 mb-4 font-medium">Ingin membantu?</p><a href="login.php" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-full font-semibold transition-all">Login untuk Beraksi</a></div>
                            <?php endif; ?>
                        <?php elseif ($report['status'] == 'cleaned'): ?>
                             <div class="w-full py-4 px-6 bg-emerald-50 text-emerald-700 font-heading font-bold rounded-2xl text-center border border-emerald-100 flex items-center justify-center gap-3 cursor-default"><i class="fa-solid fa-check-double text-2xl"></i><span>Misi Selesai! Area Bersih.</span></div>
                        <?php elseif ($report['status'] == 'pending_clean'): ?>
                            <div class="w-full py-4 px-6 bg-amber-50 text-amber-700 font-heading font-bold rounded-2xl text-center border border-amber-100 flex items-center justify-center gap-3 cursor-default"><i class="fa-solid fa-hourglass-half text-2xl animate-pulse"></i><span>Sedang Diverifikasi Admin...</span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="komentar-section" class="bg-white rounded-3xl p-8 shadow-elevated border border-slate-100">
                    <h3 class="font-heading font-bold text-xl text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fa-regular fa-comments text-primary"></i>
                        Diskusi (<?php echo $total_comments; ?>)
                    </h3>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="detail_laporan.php?id=<?php echo $report_id; ?>" method="post" class="mb-8 flex gap-4 items-start">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold flex-shrink-0">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                            <div class="flex-grow">
                                <textarea name="komentar" rows="3" required class="w-full px-4 py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none" placeholder="Tulis komentar..."></textarea>
                                <div class="mt-2 flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-hover text-white font-semibold rounded-full text-sm transition-all">Kirim</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="bg-slate-50 p-4 rounded-xl text-center text-slate-500 text-sm mb-8">
                            <a href="login.php" class="text-primary font-medium hover:underline">Login</a> untuk ikut berdiskusi.
                        </div>
                    <?php endif; ?>

                    <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php if ($total_comments > 0): ?>
                            <?php while($comment = mysqli_fetch_assoc($result_comments)): ?>
                                <div class="flex gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold flex-shrink-0 border border-slate-200">
                                        <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="bg-slate-50 px-4 py-3 rounded-2xl rounded-tl-none border border-slate-100 relative">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-heading font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($comment['username']); ?></span>
                                                <?php if($comment['role'] == 'admin'): ?>
                                                    <span class="bg-red-100 text-red-600 text-[10px] px-1.5 py-0.5 rounded-full font-bold">ADMIN</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-slate-700 text-sm"><?php echo nl2br(htmlspecialchars($comment['komentar'])); ?></p>
                                        </div>
                                        <span class="text-xs text-slate-400 ml-2 mt-1 inline-block"><?php echo time_ago_comment($comment['tgl_komentar']); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-slate-400 italic py-4">Belum ada komentar.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    var lat = <?php echo $report['latitude']; ?>;
    var lng = <?php echo $report['longitude']; ?>;
    var miniMap = L.map('mini-map', { zoomControl: false, scrollWheelZoom: false, dragging: false, doubleClickZoom: false, boxZoom: false }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { attribution: '' }).addTo(miniMap);
    var iconUrl = '<?php echo ($report['status'] == 'cleaned') ? "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png" : "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png"; ?>';
    L.marker([lat, lng], {icon: L.icon({ iconUrl: iconUrl, shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] }) }).addTo(miniMap);
</script>
<style type="text/tailwindcss">
    @keyframes shimmer { 100% { transform: translateX(100%); } }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<?php include 'footer.php'; ?>