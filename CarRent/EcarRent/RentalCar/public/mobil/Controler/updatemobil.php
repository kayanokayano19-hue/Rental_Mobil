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

class MobilController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }
    
    public function updateDataMobil($plat_mobil, $id_pemilik, $merk, $tipe, $status, $tahun, $kondisi_mobil, $riwayat_kerusakan, $harga_rental) {
        if (!empty($_FILES['foto']['tmp_name'])) {
            $foto = addslashes(file_get_contents($_FILES['foto']['tmp_name']));
            $sql = "UPDATE tb_mobil SET 
                        id_pemilik = '$id_pemilik', 
                        merk = '$merk', 
                        tipe = '$tipe', 
                        status = '$status', 
                        tahun = '$tahun', 
                        kondisi_mobil = '$kondisi_mobil', 
                        riwayat_kerusakan = '$riwayat_kerusakan',
                        harga_rental = '$harga_rental',
                        foto = '$foto' 
                    WHERE plat_mobil = '$plat_mobil'";
        } else {
            $sql = "UPDATE tb_mobil SET 
                        id_pemilik = '$id_pemilik', 
                        merk = '$merk', 
                        tipe = '$tipe', 
                        status = '$status', 
                        tahun = '$tahun', 
                        kondisi_mobil = '$kondisi_mobil', 
                        riwayat_kerusakan = '$riwayat_kerusakan',
                        harga_rental = '$harga_rental'
                    WHERE plat_mobil = '$plat_mobil'";
        }    
        $result = mysqli_query($this->kon, $sql);

        if ($result) {
            return "Data mobil berhasil diperbarui.";
        } else {
            return "Gagal memperbarui data mobil: " . mysqli_error($this->kon);
        }
    }

    public function getDataMobil($plat_mobil) {
        $sql = "SELECT * FROM tb_mobil WHERE plat_mobil = '$plat_mobil'";
        $ambildata = mysqli_query($this->kon, $sql);

        if ($ambildata && mysqli_num_rows($ambildata) > 0) {
            return mysqli_fetch_array($ambildata);
        } else {
            return null;
        }
    }
}
?>