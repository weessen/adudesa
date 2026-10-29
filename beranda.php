<?php
require 'koneksi.php';
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$grafik_data = [];
$max_aduan = 1;

$query_grafik = mysqli_query($conn, "SELECT tanggal, COUNT(id) as total FROM pengaduan WHERE tanggal >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY tanggal ORDER BY tanggal ASC");

$db_data = [];
while($row = mysqli_fetch_assoc($query_grafik)) {
    $db_data[$row['tanggal']] = (int)$row['total'];
    if($row['total'] > $max_aduan) $max_aduan = $row['total'];
}

for ($i = 6; $i >= 0; $i--) {
    $tgl_label = date('Y-m-d', strtotime("-$i days"));
    $tgl_format = date('d M', strtotime("-$i days"));
    
    $total = isset($db_data[$tgl_label]) ? $db_data[$tgl_label] : 0;
    
    $persen_tinggi = ($total / $max_aduan) * 100;
    if($persen_tinggi < 5) $persen_tinggi = 5; 
    
    $grafik_data[] = [
        'label' => $tgl_format,
        'total' => $total,
        'tinggi' => $persen_tinggi
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Layanan Aduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .chart-grid {
            background-image: linear-gradient(to bottom, #f3f4f6 1px, transparent 1px);
            background-size: 100% 25%;
        }
    </style>
</head>
<body class="bg-white font-sans text-gray-800">
    <?php include 'nav.php'; ?>
    <div class="container mx-auto p-10 max-w-6xl">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-16">
            
            <div class="flex flex-col justify-start">
                <h1 class="text-4xl md:text-5xl font-extrabold text-[#0066cc] leading-tight mb-6 tracking-tight">Sampaikan Aspirasi,<br>Wujudkan<br>Perubahan</h1>
                <p class="text-gray-500 mb-8 text-sm md:text-base leading-relaxed pr-8">Laporkan masalah di sekitar Anda dan pantau tindak lanjutnya secara transparan.</p>
                <div class="flex gap-4">
                    <a href="aduan.php" class="bg-[#0080ff] text-white font-bold px-6 py-2.5 rounded shadow hover:bg-blue-600 transition text-sm">Buat Aduan</a>
                    <a href="aduan.php?tab=histori" class="bg-white border-2 border-[#0080ff] text-[#0080ff] font-bold px-6 py-2.5 rounded shadow-sm hover:bg-blue-50 transition text-sm">Lihat Aduan Saya</a>
                </div>
            </div>
            
            <div class="flex flex-col gap-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-[#003366] mb-2">Selamat Datang, Warga!</h2>
                    <p class="text-gray-500 text-sm leading-relaxed">Semoga hari Anda menyenangkan. Silakan sampaikan aspirasi atau pantau aduan Anda di bawah ini.</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold text-[#0080ff] mb-4">Bagaimana Cara Kerjanya?</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="border border-gray-200 p-4 rounded-xl shadow-sm bg-white hover:shadow-md transition">
                            <div class="bg-[#0080ff] text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mb-3">1</div>
                            <h4 class="font-extrabold text-sm mb-2">Tulis Aduan</h4>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Isi formulir dengan jelas, sertakan lokasi, dan lampirkan foto pendukung.</p>
                        </div>
                        <div class="border border-gray-200 p-4 rounded-xl shadow-sm bg-white hover:shadow-md transition">
                            <div class="bg-[#0080ff] text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mb-3">2</div>
                            <h4 class="font-extrabold text-sm mb-2">Verifikasi Petugas</h4>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Tim verifikasi akan meninjau laporan Anda untuk memastikan kelengkapan data.</p>
                        </div>
                        <div class="border border-gray-200 p-4 rounded-xl shadow-sm bg-white hover:shadow-md transition">
                            <div class="bg-[#0080ff] text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mb-3">3</div>
                            <h4 class="font-extrabold text-sm mb-2">Pantau Penyelesaian</h4>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Ikuti status aduan Anda dari verifikasi hingga penyelesaian pekerjaan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-8">
                <h3 class="font-extrabold text-lg text-gray-800">Aktivitas Aduan</h3>
                <span class="border border-gray-200 px-4 py-1 text-xs font-bold rounded-lg text-gray-500 bg-white">7 Hari Terakhir</span>
            </div>
            
            <div class="relative h-64 w-full">
                <div class="absolute left-0 top-0 h-full flex flex-col justify-between text-[11px] font-bold text-gray-400 pb-8 z-10 w-8 text-right pr-2">
                    <span><?php echo $max_aduan; ?></span>
                    <span><?php echo ceil($max_aduan * 0.75); ?></span>
                    <span><?php echo ceil($max_aduan * 0.5); ?></span>
                    <span><?php echo ceil($max_aduan * 0.25); ?></span>
                    <span>0</span>
                </div>
                
                <div class="absolute left-10 right-0 top-0 h-[calc(100%-2rem)] chart-grid border-l border-b border-gray-200 flex items-end justify-between px-2 sm:px-8 pt-2">
                    <?php foreach($grafik_data as $data): ?>
                    <div class="h-full flex flex-col justify-end items-center w-8 sm:w-12 group relative">
                        <div class="absolute -top-8 bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-20">
                            <?php echo $data['total']; ?> Aduan
                        </div>
                        
                        <div class="w-full bg-[#1a8cff] rounded-t-sm transition-all duration-500 group-hover:bg-[#0066cc] group-hover:shadow-md relative" style="height: <?php echo $data['tinggi']; ?>%;">
                            <div class="w-2 h-2 bg-white rounded-full absolute -top-1 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition"></div>
                        </div>
                        
                        <div class="absolute -bottom-8 text-[10px] font-bold text-gray-400 whitespace-nowrap">
                            <?php echo $data['label']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>

    </div>
</body>
</html>