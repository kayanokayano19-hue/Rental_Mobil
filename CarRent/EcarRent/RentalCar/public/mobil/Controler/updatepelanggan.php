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

class Pelanggan {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }
    
    public function updateDataPelanggan($id_pelanggan, $nik_pelanggan, $nama, $email, $no_hp, $alamat, $saldo, $status_verifikasi, $ktp_pelanggan) {
    
        $sql = "UPDATE tb_pelanggan SET 
                    nik_pelanggan = '$nik_pelanggan', 
                    nama = '$nama', 
                    email = '$email', 
                    no_hp = '$no_hp', 
                    alamat = '$alamat', 
                    saldo = '$saldo', 
                    status_verifikasi = '$status_verifikasi', 
                    ktp_pelanggan = '$ktp_pelanggan' 
                WHERE id_pelanggan = '$id_pelanggan'";

        $result = mysqli_query($this->kon, $sql);

        if ($result) {
            return "Data pelanggan berhasil diperbarui.";
        } else {
            return "Gagal memperbarui data pelanggan: " . mysqli_error($this->kon);
        }
    }

    public function getPelangganData($id_pelanggan) {
        $sql = "SELECT * FROM tb_pelanggan WHERE id_pelanggan = '$id_pelanggan'";
        $ambildata = mysqli_query($this->kon, $sql);

        if ($ambildata && mysqli_num_rows($ambildata) > 0) {
            return mysqli_fetch_array($ambildata);
        } else {
            return null;
        }
    }
}
?>