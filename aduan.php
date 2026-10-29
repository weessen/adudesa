<?php
require 'koneksi.php';
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

if(isset($_POST['buat_laporan'])) {
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $user_id = $_SESSION['user_id'];
    $file_name = '';
    
    if(isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $_FILES['bukti']['name']);
        move_uploaded_file($_FILES['bukti']['tmp_name'], 'uploads/' . $file_name);
    }
    
    mysqli_query($conn, "INSERT INTO pengaduan (user_id, kategori, tanggal, isi, bukti) VALUES ('$user_id', '$kategori', '$tanggal', '$isi', '$file_name')");
    
    $_SESSION['msg'] = 'Laporan berhasil dibuat dan sedang diproses.';
    $_SESSION['msg_type'] = 'success';
    header("Location: aduan.php?tab=histori");
    exit;
}

if(isset($_POST['hapus_aduan_pribadi'])) {
    $aduan_id = (int)$_POST['aduan_id'];
    $user_id = $_SESSION['user_id'];
    
    $cek_file = mysqli_query($conn, "SELECT bukti, status FROM pengaduan WHERE id='$aduan_id' AND user_id='$user_id'");
    if(mysqli_num_rows($cek_file) > 0) {
        $file_data = mysqli_fetch_assoc($cek_file);
        
        if($file_data['status'] === 'pending') {
            if(!empty($file_data['bukti']) && file_exists('uploads/'.$file_data['bukti'])) {
                unlink('uploads/'.$file_data['bukti']);
            }
            mysqli_query($conn, "DELETE FROM pengaduan WHERE id='$aduan_id' AND user_id='$user_id'");
            $_SESSION['msg'] = 'Data aduan Anda berhasil dihapus secara permanen.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['msg'] = 'Gagal menghapus: Laporan sudah diproses atau ditolak.';
            $_SESSION['msg_type'] = 'error';
        }
    } else {
        $_SESSION['msg'] = 'Aduan tidak ditemukan atau Anda tidak memiliki akses.';
        $_SESSION['msg_type'] = 'error';
    }
    header("Location: aduan.php?tab=histori");
    exit;
}

$msg = '';
$msg_type = '';
if(isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'];
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pilih';
$user_id = $_SESSION['user_id'];

$histori = mysqli_query($conn, "SELECT * FROM pengaduan WHERE user_id='$user_id' ORDER BY id DESC");
$total_histori = mysqli_num_rows($histori);

$semua_aduan = mysqli_query($conn, "SELECT p.*, u.username FROM pengaduan p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC");
$total_semua = mysqli_num_rows($semua_aduan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aduan - Layanan Aduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .toast {
            animation: slideInRight 0.5s ease-out forwards, fadeOut 0.5s ease-in 4.5s forwards;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
    </style>
    <script>
        function openTab(tabName) {
            document.getElementById('content-pilih').classList.add('hidden');
            document.getElementById('content-histori').classList.add('hidden');
            document.getElementById('content-semua').classList.add('hidden');
            document.getElementById('content-form').classList.add('hidden');
            
            document.getElementById('tab-pilih').className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer';
            document.getElementById('tab-histori').className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer';
            document.getElementById('tab-semua').className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer';
            
            if(tabName !== 'form') {
                document.getElementById('tab-form-container').style.display = 'none';
                let formAduan = document.getElementById('form_aduan');
                if(formAduan) formAduan.reset();
            } else {
                document.getElementById('tab-form').className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-blue-600 border-blue-600 transition cursor-pointer';
            }

            document.getElementById('content-' + tabName).classList.remove('hidden');
            let activeTabBtn = document.getElementById('tab-' + tabName);
            if(activeTabBtn && tabName !== 'form') {
                activeTabBtn.className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-blue-600 border-blue-600 transition cursor-pointer';
            }
            
            window.history.replaceState(null, null, "?tab=" + tabName);
        }

        function setKategori(kat) {
            document.getElementById('form_kategori').value = kat;
            document.getElementById('label_kategori').innerText = kat.toUpperCase();
            document.getElementById('tab-form-container').style.display = 'block';
            openTab('form');
        }

        window.onload = function() {
            var initialTab = '<?php echo $active_tab; ?>';
            if (initialTab === 'form' && document.getElementById('tab-form-container').style.display === 'none') {
                initialTab = 'pilih';
            }
            openTab(initialTab);
        };
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'nav.php'; ?>
    
    <?php if($msg !== ''): ?>
    <div class="fixed top-20 right-4 z-50 toast">
        <div class="<?php echo $msg_type === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border-2 border-white">
            <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'; ?> text-xl"></i>
            <span class="font-bold text-sm"><?php echo $msg; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mx-auto p-8 max-w-5xl">
        
        <div class="flex flex-wrap justify-center border-b-2 border-gray-200 mb-8 gap-2">
            <button onclick="openTab('pilih')" id="tab-pilih" class="py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-blue-600 border-blue-600 transition cursor-pointer">Buat Aduan</button>
            <button onclick="openTab('histori')" id="tab-histori" class="py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer">Aduan Saya <span class="bg-gray-200 text-gray-700 ml-2 px-2 py-0.5 rounded-full text-xs"><?php echo $total_histori; ?></span></button>
            <button onclick="openTab('semua')" id="tab-semua" class="py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer">Semua Aduan <span class="bg-gray-200 text-gray-700 ml-2 px-2 py-0.5 rounded-full text-xs"><?php echo $total_semua; ?></span></button>
            <div id="tab-form-container" style="display: none;">
                 <button onclick="openTab('form')" id="tab-form" class="py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer">Formulir</button>
            </div>
        </div>

        <div id="content-pilih" class="transition-all duration-300">
            <div class="bg-blue-600 text-white p-5 rounded-2xl shadow-lg text-center mb-4 font-extrabold text-xl tracking-wide">
                Layanan Pelaporan Pengaduan Masyarakat
            </div>
            <p class="text-sm font-semibold mb-8 text-center text-gray-500">Pilih salah satu kategori laporan di bawah ini untuk memulai pengaduan Anda.</p>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-12">
                <button onclick="setKategori('Bencana Alam')" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center gap-4 hover:border-blue-500 hover:shadow-xl transition group">
                    <img src="assets/Bencana.png" class="rounded-2xl w-16 h-16 object-contain group-hover:scale-110 transition duration-300" alt="Bencana">
                    <span class="font-extrabold text-gray-700 group-hover:text-blue-600 transition">BENCANA ALAM</span>
                </button>
                <button onclick="setKategori('Kesehatan')" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center gap-4 hover:border-blue-500 hover:shadow-xl transition group">
                    <img src="assets/PMI.png" class="rounded-2xl w-16 h-16 object-contain group-hover:scale-110 transition duration-300" alt="Kesehatan">
                    <span class="font-extrabold text-gray-700 group-hover:text-blue-600 transition">KESEHATAN</span>
                </button>
                <button onclick="setKategori('Kriminalitas')" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center gap-4 hover:border-blue-500 hover:shadow-xl transition group">
                    <img src="assets/Kriminalitas.png" class="rounded-2xl w-16 h-16 object-contain group-hover:scale-110 transition duration-300" alt="Kriminalitas">
                    <span class="font-extrabold text-gray-700 group-hover:text-blue-600 transition">KRIMINALITAS</span>
                </button>
                <button onclick="setKategori('Konflik Sosial')" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center gap-4 hover:border-blue-500 hover:shadow-xl transition group">
                    <img src="assets/Konflik.png" class="rounded-2xl w-16 h-16 object-contain group-hover:scale-110 transition duration-300" alt="Konflik Sosial">
                    <span class="font-extrabold text-gray-700 group-hover:text-blue-600 transition">KONFLIK SOSIAL</span>
                </button>
                <button onclick="setKategori('Pendidikan')" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center gap-4 hover:border-blue-500 hover:shadow-xl transition group">
                    <img src="assets/Pendidikan.png" class="rounded-2xl w-16 h-16 object-contain group-hover:scale-110 transition duration-300" alt="Pendidikan">
                    <span class="font-extrabold text-gray-700 group-hover:text-blue-600 transition">PENDIDIKAN</span>
                </button>
                <button onclick="setKategori('Lainnya')" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center gap-4 hover:border-blue-500 hover:shadow-xl transition group">
                    <img src="assets/Lainnya.png" class="rounded-2xl w-16 h-16 object-contain group-hover:scale-110 transition duration-300" alt="Lainnya">
                    <span class="font-extrabold text-gray-700 group-hover:text-blue-600 transition">LAINNYA</span>
                </button>
            </div>
        </div>

        <div id="content-form" class="hidden transition-all duration-300">
            <div id="form_section" class="bg-white border border-gray-100 p-10 rounded-2xl shadow-xl mb-16 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-blue-500"></div>
                <h2 class="text-center text-2xl font-extrabold mb-2 text-gray-800">FORMULIR PENGADUAN</h2>
                <div class="text-center text-sm text-gray-400 font-bold mb-8 border-b-2 border-gray-50 pb-4 tracking-widest">
                    KATEGORI TERPILIH<br><span id="label_kategori" class="text-blue-600 text-xl mt-1 block font-black">LAINNYA</span>
                </div>
                
                <form id="form_aduan" method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="kategori" id="form_kategori" value="Lainnya">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-regular fa-calendar text-blue-500 mr-2"></i>Tanggal Kejadian</label>
                        <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" class="w-full bg-gray-50 border border-gray-300 p-4 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 focus:outline-none transition font-medium" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-align-left text-blue-500 mr-2"></i>Detail Keluhan</label>
                        <textarea name="isi" rows="5" class="w-full bg-gray-50 border border-gray-300 p-4 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 focus:outline-none transition font-medium" placeholder="Jelaskan detail kejadian, lokasi, dan informasi terkait..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-image text-blue-500 mr-2"></i>Unggah Bukti Foto (Opsional)</label>
                        <input type="file" name="bukti" class="w-full bg-gray-50 border border-gray-300 p-3 rounded-xl focus:outline-none cursor-pointer file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition" accept="image/*">
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="submit" name="buat_laporan" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-4 px-6 rounded-xl transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"><i class="fa-solid fa-paper-plane mr-2"></i>Kirim Laporan</button>
                        <button type="button" onclick="openTab('pilih')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-extrabold py-4 px-6 rounded-xl transition shadow">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="content-histori" class="hidden transition-all duration-300">
            <div class="flex flex-col gap-6">
                <?php while($row = mysqli_fetch_assoc($histori)): ?>
                <div class="flex flex-col md:flex-row border-2 border-gray-100 rounded-2xl shadow-md bg-white overflow-hidden hover:shadow-lg transition">
                    <div class="md:w-64 h-48 md:h-auto bg-gray-100 flex items-center justify-center relative shrink-0">
                        <?php if(!empty($row['bukti']) && file_exists('uploads/'.$row['bukti'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['bukti']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="text-gray-400 font-bold text-center flex flex-col items-center">
                                <i class="fa-solid fa-image-slash text-4xl mb-2"></i>
                                <span class="text-sm">Tidak Ada Bukti</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 p-6 flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-black tracking-wider uppercase mb-2 inline-block"><?php echo htmlspecialchars($row['kategori']); ?></span>
                                <div class="text-xs text-gray-500 font-bold"><i class="fa-regular fa-calendar mr-1"></i> <?php echo htmlspecialchars($row['tanggal']); ?></div>
                            </div>
                            <div class="flex gap-2 items-center">
                                <?php 
                                    $statusColor = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                    $statusText = 'Menunggu';
                                    if($row['status'] == 'proses') { $statusColor = 'bg-blue-100 text-blue-700 border-blue-200'; $statusText = 'Proses'; }
                                    if($row['status'] == 'selesai') { $statusColor = 'bg-green-100 text-green-700 border-green-200'; $statusText = 'Selesai'; }
                                    if($row['status'] == 'ditolak') { $statusColor = 'bg-red-100 text-red-700 border-red-200'; $statusText = 'Ditolak'; }
                                ?>
                                <span class="border <?php echo $statusColor; ?> px-4 py-1.5 rounded-full uppercase text-xs tracking-wider font-extrabold shadow-sm"><i class="fa-solid fa-circle-info mr-1"></i> <?php echo htmlspecialchars($statusText); ?></span>
                                
                                <?php if($row['status'] === 'pending'): ?>
                                <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus laporan ini secara permanen?');" class="m-0">
                                    <input type="hidden" name="aduan_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="hapus_aduan_pribadi" class="bg-white border border-gray-200 text-gray-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 px-3 py-1.5 rounded-full transition shadow-sm" title="Batalkan Laporan">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl text-sm text-gray-700 leading-relaxed border border-gray-100 mb-4 flex-1">
                            <?php echo nl2br(htmlspecialchars($row['isi'])); ?>
                        </div>
                        <?php if(!empty($row['tanggapan'])): ?>
                            <div class="bg-gray-50 border-l-4 <?php echo ($row['status'] == 'ditolak') ? 'border-red-500 bg-red-50' : 'border-green-500 bg-green-50'; ?> p-4 rounded-r-xl">
                                <div class="text-xs font-black <?php echo ($row['status'] == 'ditolak') ? 'text-red-800' : 'text-green-800'; ?> mb-1"><i class="fa-solid fa-reply mr-1"></i> TANGGAPAN PETUGAS:</div>
                                <div class="text-sm font-medium <?php echo ($row['status'] == 'ditolak') ? 'text-red-900' : 'text-green-900'; ?>"><?php echo nl2br(htmlspecialchars($row['tanggapan'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if($total_histori == 0): ?>
                    <div class="text-center flex flex-col items-center justify-center text-gray-400 font-bold py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                        <i class="fa-solid fa-folder-open text-6xl mb-4 text-gray-300"></i>
                        Belum ada laporan aduan yang Anda buat.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="content-semua" class="hidden transition-all duration-300">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg shadow-sm">
                <p class="text-sm text-blue-800 font-bold"><i class="fa-solid fa-earth-asia mr-2"></i> Transparansi Publik: Berikut adalah daftar seluruh aduan yang telah masuk ke sistem.</p>
            </div>
            
            <div class="flex flex-col gap-6">
                <?php while($row = mysqli_fetch_assoc($semua_aduan)): ?>
                <div class="flex flex-col md:flex-row border border-gray-200 rounded-2xl shadow-sm bg-white overflow-hidden hover:shadow-md transition">
                    <div class="flex-1 p-6 flex flex-col">
                        <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-4">
                            <div>
                                <div class="text-xs text-gray-400 font-bold mb-1"><i class="fa-solid fa-user mr-1"></i> Pelapor: <span class="text-gray-700"><?php echo htmlspecialchars($row['username']); ?></span></div>
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-xs font-black tracking-wider uppercase inline-block"><?php echo htmlspecialchars($row['kategori']); ?></span>
                                <span class="text-xs text-gray-400 font-bold ml-2"><i class="fa-regular fa-calendar mr-1"></i> <?php echo htmlspecialchars($row['tanggal']); ?></span>
                            </div>
                            <?php 
                                $statusColor = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                $statusText = 'Menunggu';
                                if($row['status'] == 'proses') { $statusColor = 'bg-blue-100 text-blue-700 border-blue-200'; $statusText = 'Proses'; }
                                if($row['status'] == 'selesai') { $statusColor = 'bg-green-100 text-green-700 border-green-200'; $statusText = 'Selesai'; }
                                if($row['status'] == 'ditolak') { $statusColor = 'bg-red-100 text-red-700 border-red-200'; $statusText = 'Ditolak'; }
                            ?>
                            <span class="border <?php echo $statusColor; ?> px-3 py-1 rounded-full uppercase text-[10px] tracking-wider font-extrabold shadow-sm"><?php echo htmlspecialchars($statusText); ?></span>
                        </div>
                        <div class="text-sm text-gray-700 leading-relaxed mb-4 flex-1">
                            <?php echo nl2br(htmlspecialchars($row['isi'])); ?>
                        </div>
                        
                        <?php if(!empty($row['tanggapan'])): ?>
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl mt-auto">
                                <div class="text-xs font-black text-gray-600 mb-1"><i class="fa-solid fa-reply mr-1"></i> TANGGAPAN PETUGAS/ADMIN:</div>
                                <div class="text-sm text-gray-800 font-medium"><?php echo nl2br(htmlspecialchars($row['tanggapan'])); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="text-xs text-gray-400 font-bold italic mt-auto"><i class="fa-solid fa-clock mr-1"></i> Belum ada tanggapan dari admin.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if($total_semua == 0): ?>
                    <div class="text-center flex flex-col items-center justify-center text-gray-400 font-bold py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                        <i class="fa-solid fa-folder-open text-6xl mb-4 text-gray-300"></i>
                        Sistem belum menerima aduan apapun.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>