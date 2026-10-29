<?php
require 'koneksi.php';
if(isset($_SESSION['user_id'])) {
    header("Location: beranda.php");
    exit;
}

$msg = '';
$msg_type = '';

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        
        $is_act = isset($data['is_active']) ? $data['is_active'] : 1;
        
        if($is_act == 1) {
            if(password_verify($password, $data['password'])) {
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['username'] = $data['username'];
                $_SESSION['role'] = $data['role'];
                header("Location: beranda.php");
                exit;
            } else {
                $msg = 'Password salah';
                $msg_type = 'error';
            }
        } else {
            $msg = 'Akun dinonaktifkan. Hubungi Admin.';
            $msg_type = 'error';
        }
    } else {
        $msg = 'Username tidak ditemukan';
        $msg_type = 'error';
    }
}

if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($cek) > 0) {
        $msg = 'Username sudah digunakan';
        $msg_type = 'error';
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password, role, is_active) VALUES ('$username', '$password', 'warga', 1)");
        $msg = 'Registrasi berhasil, silakan login';
        $msg_type = 'success';
    }
}
$is_register = isset($_GET['action']) && $_GET['action'] == 'register';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth - Layanan Aduan Masyarakat</title>
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
<body class="flex h-screen bg-white relative overflow-hidden">
    
    <?php if($msg !== ''): ?>
    <div class="fixed top-10 right-4 z-50 toast">
        <div class="<?php echo $msg_type === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border-2 border-white">
            <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'; ?> text-xl"></i>
            <span class="font-bold text-sm"><?php echo $msg; ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="hidden md:flex w-1/2 bg-blue-600 flex-col justify-center items-center p-12 relative shadow-[10px_0_15px_-3px_rgba(0,0,0,0.1)] z-10 overflow-hidden">
        
        <div class="absolute bottom-0 left-0 w-full opacity-20 pointer-events-none">
            <svg viewBox="0 0 1440 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill="#ffffff" fill-opacity="1" d="M0,288L48,272C96,256,192,224,288,197.3C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,213.3C1248,181,1344,139,1392,117.3L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>

        <div class="z-10 flex flex-col items-center gap-[15px]">
            <div class="text-center">
                <h1 class="text-4xl lg:text-5xl font-extrabold text-white mb-3 drop-shadow-lg tracking-tight leading-tight">
                    LAYANAN ADUAN<br>MASYARAKAT
                </h1>
                <p class="text-lg text-blue-100 font-medium max-w-md mx-auto leading-relaxed">
                    Sampaikan Aspirasi dan Laporan Anda Secara Cepat, Aman, dan Transparan.
                </p>
            </div>
            
            <div class="w-full max-w-[512px] flex justify-center items-center relative">
                <div class="absolute w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
                <img src="assets/cover.png" alt="Ilustrasi Cover" class="w-full h-auto object-contain drop-shadow-2xl relative z-10 hover:scale-105 transition-transform duration-500">
            </div>

        </div>
        
    </div>

    <div class="w-full md:w-1/2 flex justify-center items-center relative z-20 bg-white">
        <div class="w-96 p-8 md:p-0">
            
            <div class="flex items-center justify-start gap-4 mb-10">
                <img src="assets/icon-megaphone.png" alt="Ikon Aduan" class="h-12 w-12 object-contain" style="filter: brightness(0) saturate(100%) invert(0%) sepia(100%) saturate(7500%) hue-rotate(345deg) brightness(98%) contrast(105%);">
                <div class="flex flex-col text-left leading-tight">
                    <span class="text-green-500 font-extrabold text-lg">Layanan</span>
                    <span class="text-[#0066cc] font-extrabold text-2xl tracking-tight">Aduan Masyarakat</span>
                </div>
            </div>
            
            <?php if($is_register): ?>
            <form method="POST" action="">
                <div class="mb-5">
                    <label class="block text-sm font-extrabold text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="w-full bg-[#f0f4f8] border border-transparent p-4 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-400 transition font-medium text-sm" required>
                </div>
                <div class="mb-8">
                    <label class="block text-sm font-extrabold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full bg-[#f0f4f8] border border-transparent p-4 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-400 transition font-medium text-sm" required>
                </div>
                <button type="submit" name="register" class="w-full bg-[#3b82f6] hover:bg-blue-600 text-white font-extrabold py-4 px-4 rounded-xl mb-6 transition shadow-md text-sm tracking-wider">DAFTAR SEKARANG</button>
                <p class="text-center text-xs font-bold text-gray-500">Sudah Mempunyai Akun? <a href="index.php" class="text-[#3b82f6] hover:underline">Login Sekarang</a></p>
            </form>
            <?php else: ?>
            <form method="POST" action="">
                <div class="mb-5">
                    <label class="block text-sm font-extrabold text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="w-full bg-[#f0f4f8] border border-transparent p-4 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-400 transition font-medium text-sm" required>
                </div>
                <div class="mb-8">
                    <label class="block text-sm font-extrabold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full bg-[#f0f4f8] border border-transparent p-4 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-400 transition font-medium text-sm" required>
                </div>  
                <button type="submit" name="login" class="w-full bg-[#3b82f6] hover:bg-blue-600 text-white font-extrabold py-4 px-4 rounded-xl mb-6 transition shadow-md text-sm tracking-wider uppercase">Masuk ke Sistem</button>
                <p class="text-center text-xs font-bold text-gray-500">Belum Mempunyai Akun? <a href="index.php?action=register" class="text-[#3b82f6] hover:underline">Daftar Sekarang</a></p>
            </form> 
            <?php endif; ?>
        </div>
    </div>
</body>
</html>