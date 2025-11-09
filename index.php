<?php include 'header.php'; ?>

<div class="container">
    <h2>Peta Sebaran Laporan Sampah</h2>
    <p>Lihat laporan sampah yang telah dikirim oleh pengguna. Mari kita bersihkan bersama!</p>
    
    <div id="map-utama"></div>
</div>

<script>
    var mapUtama = L.map('map-utama').setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(mapUtama);

    // --- Ikon Kustom ---
    var ikonSampah = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        shadowSize: [41, 41]
    });

    var ikonBersih = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        shadowSize: [41, 41]
    });
    // --- Akhir Ikon Kustom ---

    fetch('get_reports.php')
        .then(response => response.json())
        .then(reports => {
            reports.forEach(report => {
                var lat = report.latitude;
                var lng = report.longitude;
                var ikon;
                var statusText;

                if (report.status === 'cleaned') {
                    ikon = ikonBersih;
                    statusText = "SUDAH BERSIH";
                } else {
                    ikon = ikonSampah;
                    statusText = "PERLU DIBERSIHKAN";
                }
                
                var popupContent = `
                    <img src="uploads/${report.foto}" alt="Foto Sampah" style="width: 100%; max-width: 200px; border-radius: 5px;">
                    <h4>${report.jenis_sampah}</h4>
                    <p style="background-color: ${report.status === 'cleaned' ? '#d1e7dd' : '#f8d7da'}; padding: 5px; border-radius: 5px; text-align: center; font-weight: bold;">
                        ${statusText}
                    </p>
                    <p>Pelapor: <b>${report.username}</b></p>
                    <small>Dilaporkan: ${new Date(report.tgl_lapor).toLocaleString('id-ID')}</small>
                    <hr style="margin: 8px 0;">
                    
                    <a href="detail_laporan.php?id=${report.id}" style="display: block; width: 100%; text-align: center; padding: 8px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 5px;">
                        Lihat Detail & Komentar
                    </a>
                `;

                L.marker([lat, lng], {icon: ikon})
                 .addTo(mapUtama)
                 .bindPopup(popupContent);
            });
        })
        .catch(error => {
            console.error('Error mengambil data laporan:', error);
        });
</script>

<?php include 'footer.php'; ?>