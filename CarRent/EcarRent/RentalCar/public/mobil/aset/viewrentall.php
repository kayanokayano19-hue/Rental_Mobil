<?php
include_once("../../../config/koneksi.php");

class rental {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function getRentalData($id_rental) {
        $result = mysqli_query($this->kon, "SELECT * FROM tb_rental WHERE id_rental='$id_rental'");
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_array($result);
        }
        return null;
    }
}

$rental = new Rental($kon);
$id_rental_param = $_GET['id_rental'] ?? '';
$rentalData = $rental->getRentalData($id_rental_param);

if ($rentalData) {
    $id_rental            = $rentalData['id_rental'];
    $plat_mobil           = $rentalData['plat_mobil'];
    $waktu_pinjam         = $rentalData['waktu_pinjam'];
    $waktu_kembali       = $rentalData['waktu_kembali'];
    $harga_rental         = $rentalData['harga_rental'];
    $total_harga          = $rentalData['total_harga'];
}
?>
