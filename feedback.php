<?php
require 'koneksi.php';
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

if(isset($_POST['kirim_feedback'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    
    mysqli_query($conn, "INSERT INTO feedback (nama, kategori, isi) VALUES ('$nama', '$kategori', '$isi')");
    
    $_SESSION['msg'] = 'Umpan balik berhasil dikirim. Terima kasih!';
    $_SESSION['msg_type'] = 'success';
    header("Location: feedback.php");
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

$feedbacks = mysqli_query($conn, "SELECT * FROM feedback ORDER BY id DESC LIMIT 6");
$total_feedbacks = mysqli_num_rows($feedbacks);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Feedback - Layanan Aduan</title>
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
</head>
<body class="bg-[#f8fafc] font-sans text-gray-800 relative overflow-x-hidden">
    
    <div class="absolute top-0 left-0 w-full h-96 bg-[#0066cc] z-0 overflow-hidden rounded-b-[4rem] shadow-sm">
        <div class="absolute w-96 h-96 bg-white opacity-5 rounded-full -top-10 -right-20 blur-3xl"></div>
        <div class="absolute w-72 h-72 bg-[#0080ff] opacity-50 rounded-full bottom-10 -left-10 blur-2xl"></div>
    </div>

    <div class="relative z-10">
        <?php include 'nav.php'; ?>
        
        <?php if($msg !== ''): ?>
        <div class="fixed top-20 right-4 z-50 toast">
            <div class="<?php echo $msg_type === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border-2 border-white">
                <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'; ?> text-xl"></i>
                <span class="font-bold text-sm"><?php echo $msg; ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="container mx-auto p-6 md:p-10 max-w-6xl mt-4">
            
            <div class="text-center mb-10">
                <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4 tracking-tight drop-shadow-md">Suara Anda Penting</h1>
                <p class="text-blue-100 font-medium max-w-2xl mx-auto leading-relaxed">Bantu kami untuk terus meningkatkan kualitas pelayanan publik di desa ini dengan memberikan penilaian, kritik membangun, atau saran secara jujur.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <div class="lg:col-span-2">
                    <form method="POST" action="" class="bg-white p-8 md:p-10 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-[#0080ff]"></div>
                        <h2 class="text-2xl font-extrabold text-[#003366] mb-8 flex items-center gap-3"><i class="fa-solid fa-pen-to-square text-[#0080ff]"></i> Tulis Umpan Balik</h2>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-extrabold text-gray-700 mb-2 uppercase tracking-wide"><i class="fa-solid fa-user-tag text-[#0080ff] mr-2"></i>Nama Pengguna</label>
                            <input type="text" name="nama" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" class="w-full bg-gray-100 border border-gray-200 p-4 rounded-xl text-gray-500 font-bold cursor-not-allowed focus:outline-none" required readonly>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-extrabold text-gray-700 mb-2 uppercase tracking-wide"><i class="fa-solid fa-face-smile text-[#0080ff] mr-2"></i>Tingkat Kepuasan</label>
                            <div class="relative">
                                <select name="kategori" class="w-full bg-gray-50 border border-gray-300 p-4 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-[#0080ff] focus:outline-none cursor-pointer font-bold text-gray-700 appearance-none" required>
                                    <option value="" disabled selected>Pilih Tingkat Kepuasan Anda...</option>
                                    <option value="Sangat Puas">Sangat Puas</option>
                                    <option value="Puas">Puas</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Kecewa">Kecewa</option>
                                    <option value="Sangat Kecewa">Sangat Kecewa</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-8">
                            <label class="block text-sm font-extrabold text-gray-700 mb-2 uppercase tracking-wide"><i class="fa-solid fa-comment-dots text-[#0080ff] mr-2"></i>Pesan, Kritik / Saran</label>
                            <textarea name="isi" rows="5" placeholder="Bagikan pengalaman, kritik, atau saran Anda secara detail agar kami bisa berbenah..." class="w-full bg-gray-50 border border-gray-300 p-4 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-[#0080ff] focus:outline-none font-medium text-gray-700 transition" required></textarea>
                        </div>
                        
                        <button type="submit" name="kirim_feedback" class="w-full md:w-auto bg-[#0080ff] hover:bg-blue-600 text-white font-extrabold py-4 px-10 rounded-xl shadow-lg transition transform hover:-translate-y-1 hover:shadow-xl flex justify-center items-center gap-2">
                            <i class="fa-regular fa-paper-plane"></i> Kirim Umpan Balik
                        </button>
                    </form>
                </div>
                
                <div class="lg:col-span-1">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <h2 class="font-extrabold text-xl text-[#003366] mb-6 flex items-center gap-2 border-b-2 border-gray-100 pb-4">
                            <i class="fa-solid fa-star text-yellow-500"></i> Apa Kata Mereka?
                        </h2>
                        
                        <div class="flex flex-col gap-5">
                            <?php if($total_feedbacks == 0): ?>
                                <div class="text-center py-10 text-gray-400">
                                    <i class="fa-solid fa-comment-slash text-4xl mb-3 opacity-50"></i>
                                    <p class="font-bold text-sm">Belum ada umpan balik yang diberikan.</p>
                                </div>
                            <?php else: ?>
                                <?php while($f = mysqli_fetch_assoc($feedbacks)): ?>
                                <div class="border border-gray-100 rounded-2xl p-5 bg-[#f8fafc] hover:bg-white hover:shadow-md hover:border-blue-200 transition duration-300 group">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 text-[#0080ff] flex items-center justify-center font-black text-xs">
                                                <?php echo strtoupper(substr($f['nama'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="font-extrabold text-gray-800 text-sm group-hover:text-[#0080ff] transition"><?php echo htmlspecialchars($f['nama']); ?></div>
                                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5"><?php echo date('d M Y', strtotime($f['waktu'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="bg-white border border-gray-200 rounded-full px-3 py-1 text-[10px] font-extrabold text-gray-600 shadow-sm inline-block mb-3">
                                        <?php echo htmlspecialchars($f['kategori']); ?>
                                    </span>
                                    <p class="text-sm text-gray-600 leading-relaxed font-medium">"<?php echo htmlspecialchars($f['isi']); ?>"</p>
                                </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>