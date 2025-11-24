<?php
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$sukses = '';

// Ambil data user saat ini untuk ditampilkan di form
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// --- PROSES UPDATE DATA ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi dasar
    if (empty($username) || empty($email)) {
        $error = "Username dan Email tidak boleh kosong.";
    } else {
        // 1. Cek apakah Username/Email sudah dipakai orang lain (kecuali diri sendiri)
        $sql_check = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ssi", $username, $email, $user_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username atau Email sudah digunakan pengguna lain.";
        } else {
            // 2. Proses Update
            if (!empty($password)) {
                // Jika user mengisi password baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "sssi", $username, $email, $hashed_password, $user_id);
            } else {
                // Jika password dikosongkan (tidak mau ganti password)
                $sql_update = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ssi", $username, $email, $user_id);
            }

            if (mysqli_stmt_execute($stmt_update)) {
                // Update Session Data (Penting agar nama di header berubah)
                $_SESSION['username'] = $username;
                
                $sukses = "Profil berhasil diperbarui!";
                // Update data variabel $user agar form langsung berubah
                $user['username'] = $username;
                $user['email'] = $email;
            } else {
                $error = "Gagal mengupdate profil.";
            }
            mysqli_stmt_close($stmt_update);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen bg-slate-50 py-12 px-4 md:px-8">
    <div class="max-w-2xl mx-auto">
        
        <div class="mb-8 flex items-center gap-2 text-slate-500 text-sm font-medium">
            <a href="profil.php" class="hover:text-primary transition-colors">Profil Saya</a>
            <i class="fa-solid fa-chevron-right text-xs opacity-50"></i>
            <span class="text-slate-800">Edit Profil</span>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-elevated border border-slate-100">
            <h2 class="font-heading font-bold text-2xl text-slate-800 mb-6 flex items-center gap-3">
                <i class="fa-solid fa-user-pen text-primary"></i> Edit Profil
            </h2>

            <?php if(!empty($error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-danger p-4 rounded-md flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-danger"></i>
                    <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <?php if(!empty($sukses)): ?>
                <div class="mb-6 bg-emerald-50 border-l-4 border-success p-4 rounded-md flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-success"></i>
                    <p class="text-sm text-emerald-700 font-medium"><?php echo $sukses; ?></p>
                </div>
            <?php endif; ?>

            <form action="edit_profil.php" method="post" class="space-y-6">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-slate-400"></i>
                        </div>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="pl-10 w-full px-4 py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-slate-400"></i>
                        </div>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="pl-10 w-full px-4 py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Ganti Password <span class="text-slate-400 font-normal text-xs ml-1">(Biarkan kosong jika tidak ingin mengganti)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        <input type="password" name="password" placeholder="Password Baru" class="pl-10 w-full px-4 py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-hover text-white font-heading font-bold rounded-xl transition-all hover:shadow-lg hover:-translate-y-0.5">
                        Simpan Perubahan
                    </button>
                    <a href="profil.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>