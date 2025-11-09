<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peduli Lingkungan</title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>index.php">PeduliLingkungan</a>
            </div>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>index.php">Peta Laporan</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <li><a href="<?php echo BASE_URL; ?>lapor.php" class="btn-lapor">Lapor Sampah!</a></li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>profil.php" class="nav-poin" title="Lihat Profil Anda">
                            Poin: <?php echo htmlspecialchars($_SESSION['poin'] ?? 0); ?>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/index.php" class="nav-admin">Admin Panel</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo BASE_URL; ?>logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>