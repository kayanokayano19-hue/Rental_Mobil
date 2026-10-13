<?php
include_once("../../../config/koneksi.php");

class PemilikController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

   public function getPemilikData($id_pemilik) {
        $stmt = mysqli_prepare($this->kon, "SELECT * FROM tb_pemilik_mobil WHERE id_pemilik = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_pemilik);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = null;
            if ($result && mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_array($result);
            }
            
            mysqli_stmt_close($stmt); 
            return $data;
        }
        return null;
    }
}

$controller = new PemilikController($kon);
$id_pemilik_param = $_GET['id_pemilik'] ?? '';
$pemilikData = $controller->getPemilikData($id_pemilik_param);

$nama = "";
$email = "";
$password = "";
$no_hp = "";
$alamat = "";

if ($pemilikData) {
    $nama     = $pemilikData['nama'];
    $email    = $pemilikData['email'];
    $password = $pemilikData['password'];
    $no_hp    = $pemilikData['no_hp'];
    $alamat   = $pemilikData['alamat'];
}
?>