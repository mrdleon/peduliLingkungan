<?php
require_once 'config.php';
$error = '';

// Jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role, poin FROM users WHERE username = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username_db, $hashed_password, $role, $poin);
                if (mysqli_stmt_fetch($stmt)) {
                    // Verifikasi password
                    if (password_verify($password, $hashed_password)) {
                        // Password benar! Mulai session
                        $_SESSION['user_id'] = $id;
                        $_SESSION['username'] = $username_db;
                        $_SESSION['role'] = $role;
                        $_SESSION['poin'] = $poin;

                        header("location: index.php");
                        exit;
                    } else {
                        $error = "Username atau password salah.";
                    }
                }
            } else {
                $error = "Username atau password salah.";
            }
        } else {
            $error = "Terjadi kesalahan. Coba lagi.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>

<?php include 'header.php'; ?>

<div class="min-h-[calc(100vh-80px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full space-y-8 bg-white p-8 md:p-10 rounded-3xl shadow-xl border border-slate-100">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-accent/10 text-accent mb-6">
                <i class="fa-solid fa-right-to-bracket text-2xl"></i>
            </div>
            <h2 class="font-heading font-bold text-3xl text-slate-800">
                Selamat Datang Kembali
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                Silakan login untuk melanjutkan kontribusi Anda.
            </p>
        </div>
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
            <div class="bg-emerald-50 border-l-4 border-success p-4 rounded-md flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-success"></i>
                <p class="text-sm text-emerald-700 font-medium">Registrasi berhasil! Silakan login.</p>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-danger p-4 rounded-md flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-danger"></i>
                <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="login.php" method="post">
            <div class="rounded-md space-y-4">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-slate-400"></i>
                        </div>
                        <input id="username" name="username" type="text" required class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-slate-300 placeholder-slate-400 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent sm:text-sm transition-all" placeholder="Username">
                    </div>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-slate-300 placeholder-slate-400 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent sm:text-sm transition-all" placeholder="Password">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent font-heading font-bold rounded-full text-white bg-accent hover:bg-accent-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all hover:scale-[1.02] hover:shadow-lg">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-key text-accent-hover group-hover:text-white transition-colors"></i>
                    </span>
                    Masuk Sekarang
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="text-sm text-slate-600">
                Belum punya akun? 
                <a href="register.php" class="font-medium text-primary hover:text-primary-hover transition-colors">
                    Daftar gratis di sini
                </a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>