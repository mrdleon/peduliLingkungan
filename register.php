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

<div class="container form-container">
    <h2>Registrasi Akun Baru</h2>
    <p>Bergabunglah untuk ikut melaporkan sampah di sekitar Anda.</p>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="register.php" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Register</button>
        </div>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </form>
</div>

<?php include 'footer.php'; ?>