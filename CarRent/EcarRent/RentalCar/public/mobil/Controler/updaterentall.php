<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}


if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../../dashboard/dashboard.php');
    exit();
}

include_once("../../../config/koneksi.php");

class rental {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function updateDataRental($id_rental, $plat_mobil, $waktu_pinjam, $waktu_kembali, $harga_rental, $total_harga) {
        $sql = "UPDATE tb_rental SET 
                    plat_mobil = '$plat_mobil', 
                    waktu_pinjam = '$waktu_pinjam', 
                    waktu_kembali = '$waktu_kembali', 
                    harga_rental = '$harga_rental', 
                    total_harga = '$total_harga' 
                WHERE id_rental = '$id_rental'";

        $result = mysqli_query($this->kon, $sql);

        if ($result) {
            return "Data rental berhasil diperbarui.";
        } else {
            return "Gagal memperbarui data rental: " . mysqli_error($this->kon);
        }
    }

    public function getRentalData($id_rental) {
        $sql = "SELECT * FROM tb_rental WHERE id_rental = '$id_rental'";
        $ambildata = mysqli_query($this->kon, $sql);

        if ($ambildata && mysqli_num_rows($ambildata) > 0) {
            return mysqli_fetch_array($ambildata);
        } else {
            return null;
        }
    }
}
?>