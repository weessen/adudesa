<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_pengaduan_desa");
if (!$conn) {
    die("Koneksi gagal");
}
?>