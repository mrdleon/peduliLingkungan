<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- KEAMANAN: Blokir Admin dari Profil ---
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Ambil Data User Lengkap
$sql_user = "SELECT username, email, poin, tgl_daftar, role FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

// 2. Hitung Statistik Laporan User Ini
$sql_stats = "SELECT 
                COUNT(*) as total_laporan,
                SUM(CASE WHEN status = 'cleaned' THEN 1 ELSE 0 END) as total_dibersihkan,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as total_pending
              FROM reports WHERE user_id = ?";
$stmt_stats = mysqli_prepare($conn, $sql_stats);
mysqli_stmt_bind_param($stmt_stats, "i", $user_id);
mysqli_stmt_execute($stmt_stats);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stats));
mysqli_stmt_close($stmt_stats);

// 3. Ambil Riwayat Laporan Terakhir
$sql_history = "SELECT * FROM reports WHERE user_id = ? ORDER BY tgl_lapor DESC LIMIT 5";
$stmt_history = mysqli_prepare($conn, $sql_history);
mysqli_stmt_bind_param($stmt_history, "i", $user_id);
mysqli_stmt_execute($stmt_history);
$result_history = mysqli_stmt_get_result($stmt_history);

// --- Gamifikasi Sederhana ---
$poin = $user['poin'];
$level = "Pemula Lingkungan";
$badge_icon = "fa-seedling";
$badge_color = "text-emerald-500 bg-emerald-100";

if ($poin >= 500) {
    $level = "Pahlawan Bumi";
    $badge_icon = "fa-tree";
    $badge_color = "text-primary bg-primary/10";
} elseif ($poin >= 100) {
    $level = "Penjaga Alam";
    $badge_icon = "fa-leaf";
    $badge_color = "text-accent bg-accent/10";
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 md:px-8 max-w-5xl">
        
        <div class="bg-white rounded-3xl p-8 shadow-elevated mb-8 flex flex-col md:flex-row items-center md:items-start gap-8 border border-slate-100 relative overflow-hidden">
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

            <div class="flex-shrink-0 relative z-10">
                <div class="w-32 h-32 bg-gradient-to-br from-primary to-emerald-400 rounded-full flex items-center justify-center shadow-lg ring-4 ring-white">
                    <span class="font-heading font-bold text-5xl text-white">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </span>
                </div>
                <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1.5 shadow-md">
                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fa-solid fa-trophy text-xs"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex-grow text-center md:text-left relative z-10">
                <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2">
                    <h1 class="font-heading font-bold text-3xl text-slate-800"><?php echo htmlspecialchars($user['username']); ?></h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium <?php echo $badge_color; ?> mx-auto md:mx-0 w-fit border border-white/50 shadow-sm">
                        <i class="fa-solid <?php echo $badge_icon; ?>"></i>
                        <?php echo $level; ?>
                    </span>
                </div>
                
                <div class="space-y-1 mb-6">
                    <p class="text-slate-500 flex items-center justify-center md:justify-start gap-2">
                        <i class="fa-solid fa-envelope opacity-60"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="text-sm text-slate-400 flex items-center justify-center md:justify-start gap-2">
                        <i class="fa-regular fa-calendar opacity-60"></i>
                        Bergabung sejak <?php echo date('d F Y', strtotime($user['tgl_daftar'])); ?>
                    </p>
                </div>

                <a href="edit_profil.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-50 hover:border-slate-300 hover:text-primary transition-all shadow-sm text-sm">
                    <i class="fa-solid fa-pen-to-square"></i> 
                    Edit Profil
                </a>
            </div>

            <div class="flex-shrink-0 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 text-center border border-amber-100 min-w-[180px] relative z-10">
                <span class="block text-amber-700 font-bold text-sm uppercase tracking-wider mb-1">Total Poin</span>
                <div class="font-heading font-bold text-4xl text-amber-500 flex items-center justify-center gap-2 drop-shadow-sm">
                    <i class="fa-solid fa-coins"></i>
                    <?php echo number_format($user['poin']); ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 transition-all hover:-translate-y-1 hover:shadow-md group">
                <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <h4 class="text-3xl font-heading font-bold text-slate-800"><?php echo $stats['total_laporan']; ?></h4>
                    <p class="text-slate-500 text-sm font-medium">Total Laporan</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 transition-all hover:-translate-y-1 hover:shadow-md group">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-broom"></i>
                </div>
                <div>
                    <h4 class="text-3xl font-heading font-bold text-slate-800"><?php echo $stats['total_dibersihkan']; ?></h4>
                    <p class="text-slate-500 text-sm font-medium">Laporan Bersih</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 transition-all hover:-translate-y-1 hover:shadow-md group">
                <div class="w-14 h-14 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <h4 class="text-3xl font-heading font-bold text-slate-800"><?php echo $stats['total_pending']; ?></h4>
                    <p class="text-slate-500 text-sm font-medium">Menunggu Tindakan</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-elevated border border-slate-100">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-heading font-bold text-2xl text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Riwayat Aktivitas
                </h3>
                <a href="lapor.php" class="inline-flex items-center gap-2 text-primary font-bold hover:text-primary-hover hover:underline transition-all">
                    <i class="fa-solid fa-plus-circle"></i> Buat Laporan Baru
                </a>
            </div>

            <?php if (mysqli_num_rows($result_history) > 0): ?>
                <div class="space-y-4">
                    <?php while($row = mysqli_fetch_assoc($result_history)): ?>
                        <div class="flex flex-col md:flex-row gap-5 p-4 rounded-2xl border border-slate-100 hover:border-primary/30 hover:bg-slate-50/50 transition-all group bg-white">
                            <div class="flex-shrink-0 md:w-48 h-32 relative overflow-hidden rounded-xl">
                                <img src="uploads/<?php echo $row['foto']; ?>" alt="Foto Laporan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <?php if($row['status'] == 'cleaned'): ?>
                                    <div class="absolute top-2 right-2 bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">BERSIH</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-grow flex flex-col justify-between py-1">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <?php if($row['status'] == 'cleaned'): ?>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 flex items-center gap-1">
                                                <i class="fa-solid fa-check-circle"></i> Selesai
                                            </span>
                                        <?php elseif($row['status'] == 'approved'): ?>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 flex items-center gap-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Kotor
                                            </span>
                                        <?php elseif($row['status'] == 'pending_clean'): ?>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 flex items-center gap-1">
                                                <i class="fa-solid fa-spinner fa-spin"></i> Verifikasi
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 flex items-center gap-1">
                                                <i class="fa-solid fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="text-slate-300 text-sm">|</span>
                                        <span class="text-slate-500 text-xs font-medium">
                                            <?php echo date('d M Y, H:i', strtotime($row['tgl_lapor'])); ?>
                                        </span>
                                    </div>
                                    
                                    <h4 class="font-heading font-bold text-lg text-slate-800 mb-1 group-hover:text-primary transition-colors">
                                        <?php echo $row['jenis_sampah']; ?> 
                                        <span class="text-slate-400 font-normal text-sm ml-1">(<?php echo $row['tingkat_keparahan']; ?>)</span>
                                    </h4>
                                    <p class="text-slate-500 text-sm line-clamp-1 italic">
                                        "<?php echo !empty($row['catatan']) ? htmlspecialchars($row['catatan']) : 'Tidak ada catatan.'; ?>"
                                    </p>
                                </div>
                                
                                <div class="mt-4 md:mt-0 flex justify-end">
                                    <a href="detail_laporan.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1 text-sm font-bold text-slate-600 hover:text-primary transition-colors">
                                        Lihat Detail <i class="fa-solid fa-arrow-right text-xs mt-0.5"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16 px-4 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white text-slate-300 rounded-full mb-4 shadow-sm">
                        <i class="fa-solid fa-camera text-3xl"></i>
                    </div>
                    <h4 class="font-heading font-medium text-xl text-slate-600 mb-2">Belum Ada Aktivitas</h4>
                    <p class="text-slate-500 mb-8 max-w-md mx-auto">Jejak langkahmu dalam menyelamatkan bumi akan muncul di sini.</p>
                    <a href="lapor.php" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-3 rounded-full font-bold transition-all shadow-lg shadow-primary/20 hover:-translate-y-1">
                        <i class="fa-solid fa-plus"></i>
                        Mulai Lapor
                    </a>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<?php include 'footer.php'; ?>