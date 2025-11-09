<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoReport - Wujudkan Lingkungan Bersih</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#40916C', // Hijau Daun
                        'primary-hover': '#2D6A4F',
                        accent: '#F48C06',  // Oranye
                        'accent-hover': '#E85D04',
                        success: '#2A9D8F',
                        warning: '#FFD60A',
                        danger: '#E63946',
                        dark: '#1B4332',
                        light: '#F8F9FA',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style type="text/tailwindcss">
        @layer utilities {
            .shadow-elevated {
                box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 10px -5px rgba(0, 0, 0, 0.05);
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="container mx-auto px-4 md:px-8 h-20 flex items-center justify-between">
            <a href="<?php echo BASE_URL; ?>index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <span class="font-heading font-bold text-2xl text-primary">EcoReport</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="<?php echo BASE_URL; ?>index.php" class="font-medium text-slate-600 hover:text-primary transition-colors">Beranda</a>
                <a href="<?php echo BASE_URL; ?>index.php#peta" class="font-medium text-slate-600 hover:text-primary transition-colors">Peta</a>
                <a href="<?php echo BASE_URL; ?>leaderboard.php" class="font-medium text-slate-600 hover:text-primary transition-colors">Leaderboard</a>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        
                        <a href="<?php echo BASE_URL; ?>admin/index.php" class="hidden md:flex items-center gap-2 px-4 py-2 bg-red-50 text-danger rounded-full font-semibold border border-red-100 transition-all hover:bg-red-100">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Admin Panel</span>
                        </a>
                        <div class="relative group ml-2">
                            <button class="flex items-center gap-2 font-medium text-slate-700 hover:text-primary transition-colors py-2">
                                <i class="fa-regular fa-user-circle text-xl"></i>
                                <span class="hidden md:inline"><?php echo htmlspecialchars(explode(' ', $_SESSION['username'])[0]); ?> (Admin)</span>
                                <i class="fa-solid fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform"></i>
                            </button>
                            <div class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-elevated border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right scale-95 group-hover:scale-100 z-50">
                                <div class="py-2">
                                    <a href="<?php echo BASE_URL; ?>admin/index.php" class="flex md:hidden items-center gap-2 px-4 py-2 text-sm text-danger hover:bg-red-50">
                                        <i class="fa-solid fa-shield-halved w-4"></i> Admin Panel
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>

                        <a href="<?php echo BASE_URL; ?>profil.php" class="hidden md:flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 rounded-full text-sm font-medium border border-amber-200 transition-all hover:bg-amber-100" title="Lihat Profil Anda">
                            <i class="fa-solid fa-coins text-amber-500"></i>
                            <span><?php echo htmlspecialchars($_SESSION['poin'] ?? 0); ?> Poin</span>
                        </a>

                        <div class="relative group ml-2">
                            <button class="flex items-center gap-2 font-medium text-slate-700 hover:text-primary transition-colors py-2">
                                <i class="fa-regular fa-user-circle text-xl"></i>
                                <span class="hidden md:inline"><?php echo htmlspecialchars(explode(' ', $_SESSION['username'])[0]); ?></span>
                                <i class="fa-solid fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform"></i>
                            </button>
                            <div class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-elevated border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right scale-95 group-hover:scale-100 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-2 border-b border-slate-100 md:hidden">
                                        <span class="block text-xs text-slate-500">Poin Anda</span>
                                        <span class="block font-bold text-amber-600"><?php echo htmlspecialchars($_SESSION['poin'] ?? 0); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>profil.php" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <i class="fa-solid fa-id-badge text-slate-400 w-4"></i> Profil Saya
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>lapor.php" class="md:hidden flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <i class="fa-solid fa-plus text-slate-400 w-4"></i> Buat Laporan
                                    </a>
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <a href="<?php echo BASE_URL; ?>logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-danger hover:bg-red-50">
                                        <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="<?php echo BASE_URL; ?>lapor.php" class="hidden md:flex items-center gap-2 bg-accent hover:bg-accent-hover text-white px-5 py-2.5 rounded-full font-semibold transition-all hover:shadow-lg hover:-translate-y-0.5 ml-4">
                            <i class="fa-solid fa-plus"></i>
                            <span>Lapor</span>
                        </a>

                    <?php endif; ?>
                
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php" class="font-medium text-slate-700 hover:text-primary px-4 py-2">Masuk</a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-full font-semibold transition-all hover:shadow-lg hover:-translate-y-0.5">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="min-h-screen">