<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    body {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    input, textarea, select {
        -webkit-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
    }

    ::-webkit-scrollbar {
        width: 0px; 
    }
</style>
<nav class="bg-blue-500 text-white p-4 flex justify-between items-center shadow-md">
    <div class="flex items-center gap-3 font-bold text-xl ml-4">
        <img src="assets/icon-megaphone.png" alt="Ikon Aduan" class="h-8 w-8 object-contain">
        <span class="tracking-wide">Layanan Aduan Masyarakat</span>
    </div>
    <div class="flex gap-6 items-center mr-4">
        <a href="beranda.php" class="hover:text-blue-200 transition"><i class="fa-solid fa-house mr-1"></i> Beranda</a>
        <a href="aduan.php" class="hover:text-blue-200 transition"><i class="fa-solid fa-bullhorn mr-1"></i> Aduan</a>
        <a href="feedback.php" class="hover:text-blue-200 transition"><i class="fa-solid fa-comments mr-1"></i> Feedback</a>
        <?php if($role === 'admin'): ?>
            <a href="admin.php" class="hover:text-blue-200 transition"><i class="fa-solid fa-screwdriver-wrench mr-1"></i> Admin Management</a>
        <?php endif; ?>
        <div class="flex items-center gap-3 bg-blue-600 border border-blue-400 px-4 py-1.5 rounded-2xl shadow-inner">
            <span class="text-sm font-semibold"><i class="fa-solid fa-user mr-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="text-red-100 hover:text-red-300 transition ml-2 text-lg">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </div>
</nav>