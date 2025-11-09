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

<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-10">
            <h2 class="font-heading font-bold text-3xl text-slate-800 flex items-center gap-3">
                <i class="fa-solid fa-shield-halved text-primary"></i>
                Admin Dashboard
            </h2>
            <p class="text-slate-600 mt-2">Validasi laporan masuk dan aktivitas pembersihan dari komunitas.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-elevated border border-slate-100 overflow-hidden mb-12">
            <div class="px-6 py-5 border-b border-slate-100 bg-red-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-xl text-slate-800">Laporan Baru (Pending)</h3>
                    <p class="text-sm text-slate-500">Menunggu persetujuan untuk ditampilkan di peta publik.</p>
                </div>
            </div>

            <div class="p-6">
                <?php if (mysqli_num_rows($result_pending) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider">Foto Kondisi</th>
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider">Detail Laporan</th>
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider">Pelapor & Waktu</th>
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider text-right">Aksi Validasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php while($row = mysqli_fetch_assoc($result_pending)): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-4 align-top w-48">
                                        <img src="../uploads/<?php echo $row['foto']; ?>" alt="Foto Sampah" class="w-40 h-28 object-cover rounded-xl shadow-sm border border-slate-200">
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-heading font-bold text-slate-800 text-lg"><?php echo $row['jenis_sampah']; ?></span>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                <?php echo $row['tingkat_keparahan']; ?>
                                            </span>
                                        </div>
                                        <p class="text-slate-600 text-sm bg-slate-50 p-3 rounded-lg border border-slate-100 italic">
                                            "<?php echo !empty($row['catatan']) ? htmlspecialchars($row['catatan']) : 'Tidak ada catatan.'; ?>"
                                        </p>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fa-regular fa-user text-slate-400 text-sm"></i>
                                            <span class="font-medium text-primary"><?php echo $row['username']; ?></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-slate-500">
                                            <i class="fa-regular fa-clock text-slate-400"></i>
                                            <span><?php echo date('d M Y, H:i', strtotime($row['tgl_lapor'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 align-middle text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="admin_action.php?action=approve&id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors" onclick="return confirm('Setujui laporan ini?')">
                                                <i class="fa-solid fa-check"></i> Terima
                                            </a>
                                            <a href="admin_action.php?action=reject&id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1.5 bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors" onclick="return confirm('Tolak laporan ini?')">
                                                <i class="fa-solid fa-xmark"></i> Tolak
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                        <i class="fa-regular fa-circle-check text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500 font-medium">Semua bersih! Tidak ada laporan baru yang menunggu.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-elevated border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-emerald-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-success">
                    <i class="fa-solid fa-broom"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-xl text-slate-800">Laporan "Sudah Bersih" (Pending Clean)</h3>
                    <p class="text-sm text-slate-500">Verifikasi bukti foto sebelum memberikan poin kepada pembersih.</p>
                </div>
            </div>

            <div class="p-6">
                <?php if (mysqli_num_rows($result_pending_clean) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider">Perbandingan Foto</th>
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider">Info Laporan</th>
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider">Pahlawan Kebersihan</th>
                                    <th class="py-4 px-4 font-heading font-semibold text-sm text-slate-500 uppercase tracking-wider text-right">Aksi Validasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php while($row = mysqli_fetch_assoc($result_pending_clean)): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-4 align-top w-[400px]">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <span class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm">Sebelum</span>
                                                <img src="../uploads/<?php echo $row['foto']; ?>" alt="Sebelum" class="w-40 h-28 object-cover rounded-xl shadow-sm border border-red-200">
                                            </div>
                                            <i class="fa-solid fa-arrow-right text-slate-300 text-xl"></i>
                                            <div class="relative">
                                                <span class="absolute top-2 left-2 bg-emerald-600/80 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm">Sesudah</span>
                                                <img src="../uploads/<?php echo $row['foto_sesudah']; ?>" alt="Sesudah" class="w-40 h-28 object-cover rounded-xl shadow-sm border-2 border-emerald-400">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <span class="font-heading font-bold text-slate-800 text-lg block mb-1"><?php echo $row['jenis_sampah']; ?></span>
                                        <div class="text-sm text-slate-500">
                                            Dilaporkan oleh: <span class="font-medium text-primary"><?php echo $row['pelapor']; ?></span>
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            pada: <?php echo date('d M Y', strtotime($row['tgl_lapor'])); ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 text-amber-700 rounded-lg w-fit font-medium">
                                            <i class="fa-solid fa-medal"></i>
                                            <?php echo $row['pembersih']; ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 align-middle text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <a href="admin_action.php?action=approve_clean&id=<?php echo $row['id']; ?>" class="w-full inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm" onclick="return confirm('Konfirmasi bersih? User akan dapat poin.')">
                                                <i class="fa-solid fa-check-double"></i> Konfirmasi Bersih
                                            </a>
                                            <a href="admin_action.php?action=reject_clean&id=<?php echo $row['id']; ?>" class="w-full inline-flex items-center justify-center gap-1.5 bg-white hover:bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg font-medium text-sm transition-colors" onclick="return confirm('Tolak bukti bersih ini?')">
                                                <i class="fa-solid fa-rotate-left"></i> Tolak & Reset
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                        <i class="fa-solid fa-hands-bubbles text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500 font-medium">Belum ada laporan yang perlu diverifikasi kebersihannya.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include '../footer.php'; ?>