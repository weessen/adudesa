<?php
require 'koneksi.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: beranda.php");
    exit;
}

if(isset($_POST['update_pengaduan'])) {
    $aduan_id = (int)$_POST['aduan_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $tanggapan = mysqli_real_escape_string($conn, $_POST['tanggapan']);
    
    mysqli_query($conn, "UPDATE pengaduan SET status='$status', tanggapan='$tanggapan' WHERE id='$aduan_id'");
    
    $_SESSION['msg'] = 'Status dan tanggapan aduan berhasil diperbarui.';
    $_SESSION['msg_type'] = 'success';
    header("Location: admin.php?tab=aduan");
    exit;
}

if(isset($_POST['hapus_pengaduan'])) {
    $aduan_id = (int)$_POST['aduan_id'];
    
    $cek_file = mysqli_query($conn, "SELECT bukti FROM pengaduan WHERE id='$aduan_id'");
    if(mysqli_num_rows($cek_file) > 0) {
        $file_data = mysqli_fetch_assoc($cek_file);
        if(!empty($file_data['bukti']) && file_exists('uploads/'.$file_data['bukti'])) {
            unlink('uploads/'.$file_data['bukti']);
        }
    }
    
    mysqli_query($conn, "DELETE FROM pengaduan WHERE id='$aduan_id'");
    $_SESSION['msg'] = 'Data aduan dan lampiran berhasil dihapus secara permanen.';
    $_SESSION['msg_type'] = 'success';
    header("Location: admin.php?tab=aduan");
    exit;
}

if(isset($_POST['update_user'])) {
    $target_user_id = (int)$_POST['target_user_id'];
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if($target_user_id !== $_SESSION['user_id']) {
        mysqli_query($conn, "UPDATE users SET username='$new_username', role='$new_role', is_active='$is_active' WHERE id='$target_user_id'");
        $_SESSION['msg'] = 'Data pengguna berhasil diperbarui.';
        $_SESSION['msg_type'] = 'success';
    } else {
        $_SESSION['msg'] = 'Anda tidak dapat mengubah data akun Anda sendiri dari sini.';
        $_SESSION['msg_type'] = 'error';
    }
    header("Location: admin.php?tab=user");
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

$active_tab = isset($_GET['tab']) && $_GET['tab'] == 'aduan' ? 'aduan' : 'user';
$search_query = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

$total_warga = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='warga'"))['c'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='admin'"))['c'];
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

$total_aduan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM pengaduan"))['c'];

$sql_aduan = "SELECT p.*, u.username FROM pengaduan p JOIN users u ON p.user_id = u.id";
if($search_query !== '') {
    $sql_aduan .= " WHERE p.isi LIKE '%$search_query%' OR p.kategori LIKE '%$search_query%' OR u.username LIKE '%$search_query%'";
}
$sql_aduan .= " ORDER BY p.id DESC";
$pengaduan_list = mysqli_query($conn, $sql_aduan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
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
        .modal, #modal-tanggapi {
            transition: opacity 0.25s ease;
        }
        body.modal-active {
            overflow-x: hidden;
            overflow-y: hidden;
        }
    </style>
    <script>
        function openAdminTab(tabName) {
            document.getElementById('content-user').classList.add('hidden');
            document.getElementById('content-aduan').classList.add('hidden');
            
            document.getElementById('tab-user').className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer';
            document.getElementById('tab-aduan').className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer';
            
            document.getElementById('content-' + tabName).classList.remove('hidden');
            document.getElementById('tab-' + tabName).className = 'py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-blue-600 border-blue-600 transition cursor-pointer';
            
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('tab', tabName);
            window.history.replaceState(null, null, "?" + urlParams.toString());
        }

        // Edit Pengguna
        function toggleModal(userId, username, role, isActive) {
            const body = document.querySelector('body');
            const modal = document.querySelector('.modal');
            modal.classList.toggle('opacity-0');
            modal.classList.toggle('pointer-events-none');
            body.classList.toggle('modal-active');
            
            if(userId) {
                document.getElementById('edit_user_id').value = userId;
                document.getElementById('edit_username').value = username;
                document.getElementById('edit_role').value = role;
                document.getElementById('edit_is_active').checked = (isActive == 1);
            }
        }

        // Tanggapi Aduan
        function openTanggapiModal(id, isi, bukti, status, tanggapan) {
            const body = document.querySelector('body');
            const modal = document.getElementById('modal-tanggapi');
            
            document.getElementById('tanggapi_aduan_id').value = id;
            document.getElementById('hapus_aduan_id').value = id;
            
            document.getElementById('detail_isi').innerHTML = isi ? isi.replace(/\n/g, '<br>') : '';
            
            const buktiContainer = document.getElementById('detail_bukti');
            if(bukti && bukti.trim() !== '') {
                buktiContainer.innerHTML = `
                    <a href="uploads/${bukti}" target="_blank" class="block w-full h-32 bg-gray-200 rounded-lg overflow-hidden border-2 border-white shadow-sm hover:opacity-80 transition relative group">
                        <img src="uploads/${bukti}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition"><i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i></div>
                    </a>
                `;
            } else {
                buktiContainer.innerHTML = '<div class="w-full h-12 bg-white border border-dashed border-gray-300 rounded-lg flex items-center justify-center text-xs text-gray-400 font-bold">Tidak ada bukti</div>';
            }
            
            document.getElementById('tanggapi_status').value = status;
            document.getElementById('tanggapi_pesan').value = tanggapan || '';
            document.getElementById('delete-confirm-modal').checked = false; // Reset checkbox hapus
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            body.classList.add('modal-active');
        }

        function closeTanggapiModal() {
            const body = document.querySelector('body');
            const modal = document.getElementById('modal-tanggapi');
            modal.classList.add('opacity-0', 'pointer-events-none');
            body.classList.remove('modal-active');
        }

        window.onload = function() {
            openAdminTab('<?php echo $active_tab; ?>');
        }
    </script>
</head>
<body class="bg-gray-100 relative">
    <?php include 'nav.php'; ?>
    
    <?php if($msg !== ''): ?>
    <div class="fixed top-20 right-4 z-50 toast">
        <div class="<?php echo $msg_type === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border-2 border-white">
            <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'; ?> text-xl"></i>
            <span class="font-bold text-sm"><?php echo $msg; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mx-auto p-8 max-w-7xl">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-gray-100 rounded-2xl p-6 flex items-center gap-5 shadow-sm">
                <div class="bg-blue-100 w-16 h-16 flex items-center justify-center rounded-full text-blue-600 text-2xl"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">Total Warga</div>
                    <div class="text-3xl font-extrabold text-gray-700"><?php echo $total_warga; ?></div>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-6 flex items-center gap-5 shadow-sm">
                <div class="bg-purple-100 w-16 h-16 flex items-center justify-center rounded-full text-purple-600 text-2xl"><i class="fa-solid fa-user-shield"></i></div>
                <div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">Total Admin</div>
                    <div class="text-3xl font-extrabold text-gray-700"><?php echo $total_admin; ?></div>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-6 flex items-center gap-5 shadow-sm">
                <div class="bg-orange-100 w-16 h-16 flex items-center justify-center rounded-full text-orange-600 text-2xl"><i class="fa-solid fa-file-lines"></i></div>
                <div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wide">Total Aduan</div>
                    <div class="text-3xl font-extrabold text-gray-700"><?php echo $total_aduan; ?></div>
                </div>
            </div>
        </div>

        <div class="flex border-b-2 border-gray-200 mb-6 gap-2">
            <button onclick="openAdminTab('user')" id="tab-user" class="py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-blue-600 border-blue-600 transition cursor-pointer">Manajemen Pengguna</button>
            <button onclick="openAdminTab('aduan')" id="tab-aduan" class="py-3 px-6 font-extrabold text-sm uppercase tracking-wider border-b-4 text-gray-500 border-transparent hover:text-blue-500 transition cursor-pointer">Manajemen Aduan</button>
        </div>

        <div id="content-user" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hidden transition-all duration-300">
            <div class="bg-blue-600 p-5 text-white font-extrabold text-lg tracking-wide"><i class="fa-solid fa-users-gear mr-2"></i> Daftar Pengguna Sistem</div>
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 border-b-2 border-gray-200">
                            <th class="p-4 font-bold text-sm">No</th>
                            <th class="p-4 font-bold text-sm">Username</th>
                            <th class="p-4 font-bold text-sm text-center">Hak Akses (Role)</th>
                            <th class="p-4 font-bold text-sm text-center">Status</th>
                            <th class="p-4 font-bold text-sm text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $no=1; while($u = mysqli_fetch_assoc($users)): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-extrabold text-gray-500"><?php echo str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                            <td class="p-4 font-bold text-gray-800"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td class="p-4 text-center">
                                <?php if($u['role'] == 'admin'): ?>
                                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider">Admin</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider">Warga</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <?php $is_act = isset($u['is_active']) ? $u['is_active'] : 1; ?>
                                <?php if($is_act == 1): ?>
                                    <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-extrabold border border-green-200"><i class="fa-solid fa-check mr-1"></i> Aktif</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-extrabold border border-red-200"><i class="fa-solid fa-ban mr-1"></i> Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <?php if($u['id'] == $_SESSION['user_id']): ?>
                                    <span class="text-xs font-bold text-gray-400 italic">Akun Anda</span>
                                <?php else: ?>
                                    <button onclick="toggleModal(<?php echo $u['id']; ?>, '<?php echo addslashes($u['username']); ?>', '<?php echo $u['role']; ?>', <?php echo $is_act; ?>)" class="bg-blue-100 hover:bg-blue-600 text-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="content-aduan" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hidden transition-all duration-300">
            <div class="bg-orange-500 p-5 text-white flex justify-between items-center">
                <div class="font-extrabold text-lg tracking-wide"><i class="fa-solid fa-list-check mr-2"></i> Tindak Lanjut Pengaduan</div>
                <div class="flex gap-3">
                    <form method="GET" action="" class="flex items-center">
                        <input type="hidden" name="tab" value="aduan">
                        <input type="text" name="cari" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari laporan..." class="px-3 py-1.5 rounded-l-lg text-sm text-gray-800 focus:outline-none w-48">
                        <button type="submit" class="bg-orange-700 hover:bg-orange-800 text-white px-3 py-1.5 rounded-r-lg transition"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                    <a href="cetak.php" target="_blank" class="bg-gray-800 hover:bg-black text-white px-4 py-1.5 rounded-lg text-sm font-bold transition flex items-center shadow-md"><i class="fa-solid fa-print mr-2"></i> Cetak PDF</a>
                </div>
            </div>
            
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 border-b-2 border-gray-200">
                            <th class="p-4 font-bold text-sm">Tgl</th>
                            <th class="p-4 font-bold text-sm">Pelapor</th>
                            <th class="p-4 font-bold text-sm">Kategori</th>
                            <th class="p-4 font-bold text-sm">Keluhan Singkat</th>
                            <th class="p-4 font-bold text-sm">Status</th>
                            <th class="p-4 font-bold text-sm text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(mysqli_num_rows($pengaduan_list) == 0): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 font-bold">Tidak ada data aduan ditemukan.</td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php while($a = mysqli_fetch_assoc($pengaduan_list)): ?>
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="p-4 text-sm font-bold text-gray-500 whitespace-nowrap"><?php echo htmlspecialchars($a['tanggal']); ?></td>
                            <td class="p-4 text-sm font-black text-gray-800"><?php echo htmlspecialchars($a['username']); ?></td>
                            <td class="p-4 text-sm font-bold text-blue-600"><?php echo htmlspecialchars($a['kategori']); ?></td>
                            <td class="p-4 text-sm text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($a['isi']); ?>"><?php echo htmlspecialchars($a['isi']); ?></td>
                            <td class="p-4">
                                <?php 
                                    $sColor = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                    $sText = 'Menunggu';
                                    if($a['status'] == 'proses') { $sColor = 'bg-blue-100 text-blue-700 border-blue-200'; $sText = 'Proses'; }
                                    if($a['status'] == 'selesai') { $sColor = 'bg-green-100 text-green-700 border-green-200'; $sText = 'Selesai'; }
                                    if($a['status'] == 'ditolak') { $sColor = 'bg-red-100 text-red-700 border-red-200'; $sText = 'Ditolak'; }
                                ?>
                                <span class="<?php echo $sColor; ?> border px-3 py-1 rounded-full text-xs font-extrabold uppercase shadow-sm inline-block"><?php echo htmlspecialchars($sText); ?></span>
                            </td>
                            <td class="p-4 text-center">
                                <button type="button" onclick='openTanggapiModal(<?php echo $a['id']; ?>, <?php echo htmlspecialchars(json_encode($a['isi']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($a['bukti']), ENT_QUOTES, 'UTF-8'); ?>, "<?php echo $a['status']; ?>", <?php echo htmlspecialchars(json_encode($a['tanggapan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)' class="bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-lg text-xs font-bold cursor-pointer transition shadow-md whitespace-nowrap"><i class="fa-solid fa-pen-to-square mr-1"></i> Tanggapi</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL EDIT PENGGUNA -->
    <div class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
        <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50" onclick="toggleModal()"></div>
        
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-2xl shadow-2xl z-50 overflow-y-auto">
            <div class="modal-content py-6 text-left px-8">
                
                <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-6">
                    <p class="text-xl font-extrabold text-gray-800"><i class="fa-solid fa-user-pen text-blue-500 mr-2"></i>Edit Pengguna</p>
                    <div class="modal-close cursor-pointer z-50 hover:bg-gray-200 p-2 rounded-full transition" onclick="toggleModal()">
                        <i class="fa-solid fa-xmark text-gray-500 text-lg"></i>
                    </div>
                </div>

                <form method="POST" action="" class="space-y-5">
                    <input type="hidden" name="target_user_id" id="edit_user_id">
                    
                    <div>
                        <label class="block text-sm font-extrabold text-gray-700 mb-2">Username</label>
                        <input type="text" name="new_username" id="edit_username" class="w-full bg-gray-50 border border-gray-300 p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-sm text-gray-800" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-extrabold text-gray-700 mb-2">Hak Akses (Role)</label>
                        <select name="new_role" id="edit_role" class="w-full bg-gray-50 border border-gray-300 p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-sm text-gray-800 cursor-pointer">
                            <option value="warga">Warga</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        <label for="edit_is_active" class="text-sm font-extrabold text-gray-700 cursor-pointer">Status Akun Aktif (Bisa Login)</label>
                    </div>

                    <div class="flex justify-end pt-4 gap-3">
                        <button type="button" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition" onclick="toggleModal()">Batal</button>
                        <button type="submit" name="update_user" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl shadow-md transition transform hover:-translate-y-0.5">Simpan Perubahan</button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div id="modal-tanggapi" class="opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
        <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50" onclick="closeTanggapiModal()"></div>
        
        <div class="modal-container bg-orange-50 w-11/12 md:max-w-4xl mx-auto rounded-2xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]">
            <div class="modal-content text-left">
                
                <div class="flex justify-between items-center bg-orange-500 p-5 rounded-t-2xl text-white">
                    <p class="text-lg font-extrabold tracking-wide"><i class="fa-solid fa-list-check mr-2"></i> Tindak Lanjut Pengaduan</p>
                    <div class="cursor-pointer z-50 hover:bg-orange-600 p-2 rounded-full transition" onclick="closeTanggapiModal()">
                        <i class="fa-solid fa-xmark text-white text-lg"></i>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        
                        <div class="md:w-1/3">
                            <h4 class="font-extrabold text-orange-800 mb-2 border-b border-orange-200 pb-2">Detail Laporan</h4>
                            <p id="detail_isi" class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-orange-100 mb-4"></p>
                            
                            <h4 class="font-extrabold text-orange-800 mb-2">Lampiran Bukti</h4>
                            <div id="detail_bukti"></div>
                            
                            <div class="mt-6 pt-4 border-t border-orange-200">
                                <label for="delete-confirm-modal" class="text-red-600 text-xs font-extrabold cursor-pointer hover:underline flex items-center gap-1"><i class="fa-solid fa-trash"></i> Hapus Data Ini Permanen</label>
                                <input type="checkbox" id="delete-confirm-modal" class="peer/delete hidden">
                                <div class="hidden peer-checked/delete:flex gap-2 items-center bg-red-100 border border-red-200 p-3 rounded-lg mt-2 shadow-inner">
                                    <span class="text-xs text-red-800 font-bold">Lanjutkan hapus?</span>
                                    <form method="POST" action="" class="m-0">
                                        <input type="hidden" name="aduan_id" id="hapus_aduan_id">
                                        <button type="submit" name="hapus_pengaduan" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-bold transition">Ya, Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md:w-2/3 bg-white p-5 rounded-xl shadow-sm border border-orange-100 h-fit">
                            <h4 class="font-extrabold text-gray-800 mb-4"><i class="fa-solid fa-reply mr-2 text-orange-500"></i>Form Tanggapan Admin</h4>
                            <form method="POST" action="" class="space-y-4">
                                <input type="hidden" name="aduan_id" id="tanggapi_aduan_id">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Ubah Status Laporan</label>
                                    <select name="status" id="tanggapi_status" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-lg text-sm font-bold focus:ring-2 focus:ring-orange-500 focus:outline-none cursor-pointer">
                                        <option value="pending">MENUNGGU (Pending / Belum Disetujui)</option>
                                        <option value="proses">PROSES (Sedang Ditangani / Dikerjakan)</option>
                                        <option value="selesai">SELESAI (Masalah Tuntas)</option>
                                        <option value="ditolak">DITOLAK (Laporan Tidak Valid / Dibatalkan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Pesan / Tindak Lanjut</label>
                                    <textarea name="tanggapan" id="tanggapi_pesan" rows="4" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none" placeholder="Ketikkan balasan atau hasil tindak lanjut petugas di sini..."></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeTanggapiModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-bold transition">Batal</button>
                                    <button type="submit" name="update_pengaduan" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg text-sm font-extrabold shadow-md transition transform hover:-translate-y-0.5">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>