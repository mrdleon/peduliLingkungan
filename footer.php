</main>
    <!-- Footer Modern -->
    <footer class="bg-white border-t border-slate-100 pt-16 pb-8">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Kolom 1: Brand -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
                            <i class="fa-solid fa-leaf text-sm"></i>
                        </div>
                        <span class="font-heading font-bold text-xl text-primary">EcoReport</span>
                    </div>
                    <p class="text-slate-500 leading-relaxed mb-6 max-w-md">
                        Platform berbasis komunitas untuk melaporkan, memantau, dan membersihkan tumpukan sampah liar demi lingkungan Indonesia yang lebih sehat dan lestari.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all"><i class="fa-brands fa-facebook-f"></i></a>
                    </div>
                </div>

                <!-- Kolom 2: Link -->
                <div>
                    <h4 class="font-heading font-bold text-slate-800 mb-6">Tautan Cepat</h4>
                    <ul class="space-y-3">
                        <li><a href="<?php echo BASE_URL; ?>index.php" class="text-slate-500 hover:text-primary transition-colors">Beranda</a></li>
                        <li><a href="<?php echo BASE_URL; ?>index.php#peta" class="text-slate-500 hover:text-primary transition-colors">Peta Laporan</a></li>
                        <li><a href="#" class="text-slate-500 hover:text-primary transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="text-slate-500 hover:text-primary transition-colors">Kontak</a></li>
                    </ul>
                </div>

                 <!-- Kolom 3: Kontak -->
                 <div>
                    <h4 class="font-heading font-bold text-slate-800 mb-6">Hubungi Kami</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 text-slate-500">
                            <i class="fa-solid fa-location-dot mt-1 text-primary"></i>
                            <span>Jl. Lingkungan Bersih No. 123, Jakarta Selatan, Indonesia</span>
                        </li>
                        <li class="flex items-center gap-3 text-slate-500">
                            <i class="fa-solid fa-envelope text-primary"></i>
                            <span>halo@ecoreport.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-slate-100 pt-8 text-center">
                <p class="text-slate-500 text-sm">
                    &copy; <?php echo date('Y'); ?> EcoReport. Dibuat dengan <i class="fa-solid fa-heart text-danger mx-1"></i> untuk Bumi.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>