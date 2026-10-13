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

class PemilikController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function updateDataPemilik($id_pemilik, $nama, $email, $password, $no_hp, $alamat) {
        $stmt = mysqli_prepare($this->kon, "UPDATE tb_pemilik_mobil SET nama = ?, email = ?, password = ?, no_hp = ?, alamat = ? WHERE id_pemilik = ?");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssi", $nama, $email, $password, $no_hp, $alamat, $id_pemilik);
            $execute = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($execute) {
                return "Data pemilik berhasil diperbarui.";
            } else {
                return "Gagal memperbarui data: " . mysqli_error($this->kon);
            }
        } else {
            return "Gagal menyiapkan statement: " . mysqli_error($this->kon);
        }
    }

    public function getPemilik($id_pemilik) {
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
?>