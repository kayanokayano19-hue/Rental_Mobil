<?php
include_once("../../../config/koneksi.php");

class MobilController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    
    public function tambahDataMobil($data) {
        $plat_mobil        = $data['plat_mobil'];
        $id_pemilik        = $data['id_pemilik'];
        $merk              = $data['merk'];
        $tipe              = $data['tipe'];
        $status            = $data['status'];
        $tahun             = $data['tahun'];
        $kondisi_mobil     = $data['kondisi_mobil'];
        $riwayat_kerusakan = $data['riwayat_kerusakan'];
        $harga_rental      = (float)$data['harga_rental'];
        
        $foto_biner        = file_get_contents($_FILES['foto']['tmp_name']);

        $stmt = mysqli_prepare($this->kon, "INSERT INTO tb_mobil (plat_mobil, id_pemilik, merk, tipe, status, tahun, kondisi_mobil, riwayat_kerusakan, foto, harga_rental) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $null = NULL;
            mysqli_stmt_bind_param($stmt, "sisssissbd", $plat_mobil, $id_pemilik, $merk, $tipe, $status, $tahun, $kondisi_mobil, $riwayat_kerusakan, $null, $harga_rental);
            
            mysqli_stmt_send_long_data($stmt, 8, $foto_biner);
            
            $execute = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($execute) {
                return "Data berhasil disimpan.";
            } else {
                return "Gagal menyimpan data: " . mysqli_error($this->kon);
            }
        } else {
            return "Gagal menyiapkan statement: " . mysqli_error($this->kon);
        }
    }
} 
?>