<?php
require_once 'config.php';

// Ambil 20 user teratas dengan poin tertinggi (kecuali admin)
$sql = "SELECT username, poin, tgl_daftar 
        FROM users 
        WHERE role = 'user' 
        ORDER BY poin DESC, tgl_daftar ASC 
        LIMIT 20";
$result = mysqli_query($conn, $sql);

// Fungsi untuk mendapatkan inisial dan warna background avatar acak (agar terlihat variatif)
function get_avatar_color($char) {
    $colors = [
        'bg-red-100 text-red-600', 'bg-orange-100 text-orange-600', 'bg-amber-100 text-amber-600',
        'bg-emerald-100 text-emerald-600', 'bg-teal-100 text-teal-600', 'bg-blue-100 text-blue-600',
        'bg-indigo-100 text-indigo-600', 'bg-violet-100 text-violet-600', 'bg-pink-100 text-pink-600'
    ];
    // Gunakan nilai ASCII karakter pertama untuk memilih warna secara konsisten
    $index = ord(strtolower($char)) % count($colors);
    return $colors[$index];
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen bg-slate-50 py-12 px-4 md:px-8">
    <div class="max-w-4xl mx-auto">
        
        <div class="text-center mb-12">
            <span class="inline-block py-1 px-3 rounded-full bg-amber-100 text-amber-700 text-sm font-medium mb-4">
                <i class="fa-solid fa-trophy mr-2"></i> Hall of Fame
            </span>
            <h1 class="font-heading font-bold text-3xl md:text-5xl text-slate-800 mb-4">
                Papan Peringkat
            </h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Apresiasi tertinggi untuk para pahlawan lingkungan yang paling aktif berkontribusi menjaga bumi kita.
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-elevated border border-slate-100 overflow-hidden">
            
            <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-slate-50 border-b border-slate-100 text-sm font-heading font-bold text-slate-500 uppercase tracking-wider">
                <div class="col-span-2 md:col-span-1 text-center">#</div>
                <div class="col-span-7 md:col-span-8">Pengguna</div>
                <div class="col-span-3 text-right">Total Poin</div>
            </div>

            <div class="divide-y divide-slate-50">
                <?php 
                $rank = 1;
                if (mysqli_num_rows($result) > 0):
                    while($row = mysqli_fetch_assoc($result)): 
                        // Tentukan styling khusus untuk Top 3
                        $rank_style = "text-slate-500 font-medium";
                        $row_bg = "hover:bg-slate-50 transition-colors";
                        $medal = "";

                        if ($rank == 1) {
                            $rank_style = "text-amber-500 font-bold text-xl";
                            $row_bg = "bg-amber-50/50 hover:bg-amber-50 transition-colors";
                            $medal = '<i class="fa-solid fa-medal text-amber-400 ml-2" title="Juara 1"></i>';
                        } elseif ($rank == 2) {
                            $rank_style = "text-slate-400 font-bold text-xl"; // Silver color roughly
                            $medal = '<i class="fa-solid fa-medal text-slate-300 ml-2" title="Juara 2"></i>';
                        } elseif ($rank == 3) {
                            $rank_style = "text-orange-700 font-bold text-xl"; // Bronze color roughly
                            $medal = '<i class="fa-solid fa-medal text-orange-700/50 ml-2" title="Juara 3"></i>';
                        }

                        // Cek apakah ini user yang sedang login (highlight diri sendiri)
                        $is_me = (isset($_SESSION['username']) && $_SESSION['username'] == $row['username']);
                        if ($is_me) {
                            $row_bg .= " !bg-primary/5"; // Tambah highlight halus jika itu 'saya'
                        }

                        $initial = strtoupper(substr($row['username'], 0, 1));
                        $avatar_class = get_avatar_color($initial);
                ?>
                
                <div class="grid grid-cols-12 gap-4 px-6 py-5 items-center <?php echo $row_bg; ?>">
                    <div class="col-span-2 md:col-span-1 text-center">
                        <span class="<?php echo $rank_style; ?>"><?php echo $rank; ?></span>
                    </div>

                    <div class="col-span-7 md:col-span-8 flex items-center gap-4">
                        <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center font-bold <?php echo $avatar_class; ?>">
                            <?php echo $initial; ?>
                        </div>
                        <div class="truncate">
                            <span class="font-heading font-bold text-slate-700 <?php echo $is_me ? 'text-primary' : ''; ?>">
                                <?php echo htmlspecialchars($row['username']); ?>
                                <?php echo $is_me ? '<span class="ml-2 text-xs px-2 py-0.5 bg-primary/10 text-primary rounded-full">Anda</span>' : ''; ?>
                            </span>
                            <?php echo $medal; ?>
                        </div>
                    </div>

                    <div class="col-span-3 text-right">
                        <span class="inline-flex items-center gap-1.5 font-heading font-bold text-amber-500 bg-amber-50 px-3 py-1 rounded-full">
                            <i class="fa-solid fa-coins text-sm"></i>
                            <?php echo number_format($row['poin']); ?>
                        </span>
                    </div>
                </div>

                <?php 
                    $rank++;
                    endwhile; 
                else:
                ?>
                    <div class="text-center py-12 text-slate-500">
                        Belum ada data untuk ditampilkan.
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="text-center mt-8 text-slate-500 text-sm">
            Hanya menampilkan 20 pengguna teratas. Terus berkontribusi untuk naik peringkat!
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>