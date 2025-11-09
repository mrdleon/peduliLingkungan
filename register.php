<?php
require_once 'config.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username atau email sudah ada
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $error = "Username atau email sudah terdaftar.";
            } else {
                // Username/email aman, lanjut insert
                $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
                    // Hash password untuk keamanan
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $hashed_password);
                    
                    if (mysqli_stmt_execute($stmt_insert)) {
                        header("location: login.php?status=registered");
                        exit;
                    } else {
                        $error = "Terjadi kesalahan. Coba lagi nanti.";
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    mysqli_close($conn);
}
?>

<?php include 'header.php'; ?>

<div class="min-h-[calc(100vh-80px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full space-y-8 bg-white p-8 md:p-10 rounded-3xl shadow-xl border border-slate-100">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 text-primary mb-6">
                <i class="fa-solid fa-user-plus text-2xl"></i>
            </div>
            <h2 class="font-heading font-bold text-3xl text-slate-800">
                Buat Akun Baru
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                Bergabunglah dengan komunitas peduli lingkungan hari ini.
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-danger p-4 rounded-md flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-danger"></i>
                <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="register.php" method="post">
            <div class="rounded-md space-y-4">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-slate-400"></i>
                        </div>
                        <input id="username" name="username" type="text" required class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-slate-300 placeholder-slate-400 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all" placeholder="Username">
                    </div>
                </div>
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-slate-400"></i>
                        </div>
                        <input id="email" name="email" type="email" required class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-slate-300 placeholder-slate-400 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all" placeholder="Alamat Email">
                    </div>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-slate-300 placeholder-slate-400 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all" placeholder="Password">
                    </div>
                </div>
                <div>
                    <label for="confirm_password" class="sr-only">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        <input id="confirm_password" name="confirm_password" type="password" required class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-slate-300 placeholder-slate-400 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all" placeholder="Konfirmasi Password">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent font-heading font-bold rounded-full text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all hover:scale-[1.02] hover:shadow-lg">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-arrow-right text-primary-hover group-hover:text-white transition-colors"></i>
                    </span>
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="text-sm text-slate-600">
                Sudah punya akun? 
                <a href="login.php" class="font-medium text-accent hover:text-accent-hover transition-colors">
                    Login di sini
                </a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>