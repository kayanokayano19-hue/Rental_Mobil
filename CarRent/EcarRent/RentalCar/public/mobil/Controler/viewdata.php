<?php
include_once("../../../config/koneksi.php");

class MobilController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function getMobilData($plat_mobil) {
        $result = mysqli_query($this->kon, "SELECT * FROM tb_mobil WHERE plat_mobil='$plat_mobil'");
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_array($result);
        }
        return null;
    }
}

$mobilController = new MobilController($kon);
$plat_mobil_param = $_GET['plat_mobil'] ?? '';
$mobilData = $mobilController->getMobilData($plat_mobil_param);

if ($mobilData) {
    $plat_mobil        = $mobilData['plat_mobil'];
    $id_pemilik        = $mobilData['id_pemilik'];
    $merk              = $mobilData['merk'];
    $tipe              = $mobilData['tipe'];
    $status            = $mobilData['status'];
    $tahun             = $mobilData['tahun'];
    $kondisi_mobil     = $mobilData['kondisi_mobil'];
    $riwayat_kerusakan = $mobilData['riwayat_kerusakan'];
    $foto              = $mobilData['foto'];
}
?>
