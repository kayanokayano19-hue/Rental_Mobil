<?php
include_once("../../../config/koneksi.php");

class pelanggan {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function getPelangganData($id_pelanggan) {
        $result = mysqli_query($this->kon, "SELECT * FROM tb_pelanggan WHERE id_pelanggan='$id_pelanggan'");
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_array($result);
        }
        return null;
    }
}

$pelanggan = new Pelanggan($kon);
$id_pelanggan_param = $_GET['id_pelanggan'] ?? '';
$pelangganData = $pelanggan->getPelangganData($id_pelanggan_param);

if ($pelangganData) {
    $nik_pelanggan        = $pelangganData['nik_pelanggan'];
    $nama                 = $pelangganData['nama'];
    $email                = $pelangganData['email'];
    $no_hp                = $pelangganData['no_hp'];
    $alamat               = $pelangganData['alamat'];
    $saldo                = $pelangganData['saldo'];
    $status_verifikasi    = $pelangganData['status_verifikasi'];
    $ktp_pelanggan        = $pelangganData['ktp_pelanggan'];
}
?>
