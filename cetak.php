<?php
require 'koneksi.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: beranda.php");
    exit;
}

$query = "SELECT p.*, u.username FROM pengaduan p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #2563eb; }
        * { scrollbar-width: thin; scrollbar-color: #3b82f6 #f1f5f9; }
        
        @media print {
            @page { margin: 1cm; }
            body { 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 p-8 print:p-0 print:bg-white" onload="window.print()">
    
    <div class="max-w-6xl mx-auto bg-white p-10 rounded-3xl shadow-sm border border-gray-200 print:shadow-none print:border-none print:p-0 print:rounded-none">
        
        <div class="print:hidden mb-10 flex justify-end gap-3">
            <button onclick="window.print()" class="bg-[#0080ff] hover:bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-md transition flex items-center"><i class="fa-solid fa-print mr-2"></i> Cetak Dokumen</button>
            <button onclick="window.close()" class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-md transition flex items-center"><i class="fa-solid fa-xmark mr-2"></i> Tutup Halaman</button>
        </div>
        
        <div class="text-center border-b-4 border-[#0066cc] pb-6 mb-8">
            <h2 class="text-3xl font-extrabold text-[#003366] tracking-tight mb-2 uppercase">Rekap Data Pengaduan</h2>
            <p class="text-gray-500 font-bold tracking-wide">Sistem Pengaduan Masyarakat Desa</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border border-gray-300 print:border-gray-800">
                <thead>
                    <tr class="bg-[#f0f4f8] text-[#003366] border-b-2 border-[#0066cc] print:border-gray-800">
                        <th class="p-4 border border-gray-300 print:border-gray-800 font-extrabold text-xs uppercase tracking-wider text-center w-12">No</th>
                        <th class="p-4 border border-gray-300 print:border-gray-800 font-extrabold text-xs uppercase tracking-wider">Tanggal</th>
                        <th class="p-4 border border-gray-300 print:border-gray-800 font-extrabold text-xs uppercase tracking-wider">Pelapor</th>
                        <th class="p-4 border border-gray-300 print:border-gray-800 font-extrabold text-xs uppercase tracking-wider">Kategori</th>
                        <th class="p-4 border border-gray-300 print:border-gray-800 font-extrabold text-xs uppercase tracking-wider w-1/3">Keluhan</th>
                        <th class="p-4 border border-gray-300 print:border-gray-800 font-extrabold text-xs uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 border border-gray-300 print:border-gray-800 text-center font-bold text-gray-500 text-sm"><?php echo $no++; ?></td>
                        <td class="p-3 border border-gray-300 print:border-gray-800 text-sm font-semibold text-gray-700 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td class="p-3 border border-gray-300 print:border-gray-800 text-sm font-black text-gray-800"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="p-3 border border-gray-300 print:border-gray-800 text-sm font-bold text-[#0080ff] print:text-gray-700"><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td class="p-3 border border-gray-300 print:border-gray-800 text-sm text-gray-600 leading-relaxed"><?php echo htmlspecialchars($row['isi']); ?></td>
                        <td class="p-3 border border-gray-300 print:border-gray-800 text-center">
                            <?php 
                                $sColor = 'text-yellow-700 bg-yellow-100 border-yellow-300';
                                $sText = 'Menunggu';
                                if($row['status'] == 'proses') { $sColor = 'text-[#003366] bg-blue-100 border-blue-300'; $sText = 'Proses'; }
                                if($row['status'] == 'selesai') { $sColor = 'text-green-700 bg-green-100 border-green-300'; $sText = 'Selesai'; }
                                if($row['status'] == 'ditolak') { $sColor = 'text-red-700 bg-red-100 border-red-300'; $sText = 'Ditolak'; }
                            ?>
                            <span class="px-3 py-1 rounded-md text-[10px] font-extrabold uppercase border <?php echo $sColor; ?> inline-block print:border-gray-800 print:bg-transparent print:text-gray-800">
                                <?php echo htmlspecialchars($sText); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>