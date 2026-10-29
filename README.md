# 📢 Sistem Informasi Manajemen Pengaduan Desa

Aplikasi berbasis web untuk memudahkan masyarakat dalam menyampaikan aspirasi, kritik, dan laporan kejadian di lingkungan desa. Proyek ini dibangun menggunakan PHP Native.

## 🚀 Yang Digunakan
* **Backend:** PHP Native (Procedural/MySQLi)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Tailwind CSS (via CDN)
* **Ikon:** FontAwesome 6.5.1
* **Fitur UI Tambahan:** Toast Notifications, Modal Pop-up, Custom Scrollbar.

## 🌟 Fitur Utama
Aplikasi ini memiliki 2 hak akses (Role): **Warga** dan **Admin**.

**👨‍💼 Fitur Admin:**
* Dashboard statistik rekapitulasi aduan (Grafik Bar 7 Hari Terakhir).
* **Manajemen Pengguna:** Edit role (Warga/Admin), Nonaktifkan/Aktifkan akun.
* **Manajemen Aduan:** Filter pencarian laporan, menanggapi laporan (Ubah status: Menunggu, Proses, Selesai, Ditolak).
* **Cetak Laporan:** Generate rekap data ke format PDF/Print-ready yang rapi.

**🧑‍🤝‍🧑 Fitur Warga:**
* Membuat laporan aduan lengkap dengan kategori, deskripsi, dan unggah lampiran foto.
* Memantau status laporan pribadi (Aduan Saya) dan membatalkan/menghapus laporan jika masih berstatus *Menunggu*.
* Transparansi Publik: Melihat daftar keluhan dari semua warga.
* Mengirim umpan balik (Feedback) dan penilaian tingkat kepuasan layanan.

---

## 🛠️ Cara Instalasi & Menjalankan Proyek
1. Buka **phpMyAdmin** `http://localhost/phpmyadmin`.
4. Buat database baru dengan nama: `db_pengaduan_desa`.
5. Import file **`db_pengaduan_desa.sql`** yang ada di dalam folder ini ke database tersebut.
6. Akses aplikasi melalui browser dengan mengetikkan: `http://localhost/pengaduan-desa` (sesuaikan dengan nama folder).
7. **Cara mendapatkan Admin:** Silakan registrasi akun baru di halaman login, lalu buka tabel `users` di phpMyAdmin, dan ubah kolom `role` akun kamu menjadi `admin`.

---

## ⚠️ ATURAN KONTRIBUSI & EDIT KODE ⚠️

Bagi siapapun yang akan mengubah, memperbaiki (*bug fix*), atau menambahkan fitur baru di dalam kode sumber, **DIWAJIBKAN** untuk memberikan rincian perubahan yang jelas.
**MENEMUKAN BUG? SILAHKAN LANGSUNG BILANG GRUB WOY**
Jika kamu melakukan modifikasi kode, harap berikan laporan/catatan (*commit message* atau *log* komunikasi) dengan format berikut:

1. **Nama File yang diedit:** (contoh: `admin.php`)
2. **Detail Perubahan:** (contoh: *Mengubah warna tombol hapus dan memperbaiki query pencarian*)
3. **Lokasi Baris (Line):** Sebutkan secara spesifik baris ke berapa sampai ke berapa kode tersebut diubah atau ditambahkan.
   * *Contoh: "Line 145 sampai 160"* atau *"Menambahkan fungsi baru di Line 45"*
4. **Alasan Perubahan:** (Opsional tapi disarankan)

**Tujuan Aturan Ini:** 
Agar jika terjadi *error* atau *conflict* saat menggabungkan kode, kita bisa langsung melacak spesifik di baris mana (*line number*) masalah tersebut berasal tanpa harus membaca ribuan baris kode dari awal.

---

### 📂 Struktur Folder Penting
* `assets/` : Berisi logo, ilustrasi landing page, dan ikon web.
* `uploads/` : Tempat penyimpanan otomatis (direktori fisik) file foto/bukti aduan yang diunggah warga. Pastikan *folder* ini tidak dihapus.
* `koneksi.php` : File konfigurasi *database*. Jika ada perbedaan password *database* lokal, silakan edit file ini.

---
*Diupdate 29/10/26 13:00 WIB.*