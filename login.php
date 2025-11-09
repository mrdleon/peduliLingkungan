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

<div class="container form-container">
    <h2>Login</h2>
    
    <?php if(isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
        <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
    <?php endif; ?>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Login</button>
        </div>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </form>
</div>

<?php include 'footer.php'; ?>