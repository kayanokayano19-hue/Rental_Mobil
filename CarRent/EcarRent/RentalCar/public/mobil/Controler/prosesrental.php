<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once("../../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_POST['simpan'])) {
    $id_pelanggan  = mysqli_real_escape_string($kon, $_POST['id_pelanggan']);
    $plat_mobil    = mysqli_real_escape_string($kon, $_POST['plat_mobil']);
    $waktu_pinjam  = $_POST['waktu_pinjam'];
    $waktu_kembali = $_POST['waktu_kembali'];
    $harga_rental  = floatval($_POST['harga_rental']);

    $tgl1 = new DateTime($waktu_pinjam);
    $tgl2 = new DateTime($waktu_kembali);
    $selisih = $tgl1->diff($tgl2);
    
    $jumlah_hari = $selisih->days;
    
    if ($jumlah_hari == 0) {
        $jumlah_hari = 1;
    }

    $total_harga = $jumlah_hari * $harga_rental;

    $query = "INSERT INTO tb_rental (id_pelanggan, plat_mobil, waktu_pinjam, waktu_kembali, harga_rental, total_harga, status_rental) 
              VALUES ('$id_pelanggan', '$plat_mobil', '$waktu_pinjam', '$waktu_kembali', '$harga_rental', '$total_harga', 'Diproses')";
    
    $eksekusi = mysqli_query($kon, $query);

   if ($eksekusi) {
        header("Location: ../../dashboard/dashboard.php?pesan=berhasil");
        exit();
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($kon);
    }
} else {
    header("Location: ../view/dashboard.php");
    exit();
}
?>