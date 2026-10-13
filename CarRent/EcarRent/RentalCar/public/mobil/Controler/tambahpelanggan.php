<?php
include_once("../../../config/koneksi.php");

class MobilController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function tambahDataPelanggan($data) {
        $nik = mysqli_real_escape_string($this->kon, trim($data['nik_pelanggan']));
        $nama = mysqli_real_escape_string($this->kon, trim($data['nama']));
        $username = mysqli_real_escape_string($this->kon, trim($data['username']));
        $email = mysqli_real_escape_string($this->kon, trim($data['email']));
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $no_hp = mysqli_real_escape_string($this->kon, trim($data['no_hp']));
        $alamat = mysqli_real_escape_string($this->kon, trim($data['alamat']));
        $saldo = (float)$data['saldo'];
        $status = mysqli_real_escape_string($this->kon, trim($data['status_verifikasi']));
        $ktp = !empty($_FILES['ktp']['tmp_name']) ? file_get_contents($_FILES['ktp']['tmp_name']) : '';

        $cek = mysqli_query($this->kon, "SELECT id_user FROM tb_user WHERE username = '$username' LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) return "Username sudah digunakan.";

        mysqli_begin_transaction($this->kon);
        $stmt1 = mysqli_prepare($this->kon, "INSERT INTO tb_user (username, password, nama, role) VALUES (?, ?, ?, 'User')");
        $stmt2 = mysqli_prepare($this->kon, "INSERT INTO tb_pelanggan (nik_pelanggan, nama, email, password, no_hp, alamat, saldo, status_verifikasi, ktp_pelanggan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt1 || !$stmt2) {
            mysqli_rollback($this->kon);
            return "Gagal menyiapkan data: " . mysqli_error($this->kon);
        }
        $role = 'User';
        mysqli_stmt_bind_param($stmt1, 'ssss', $username, $password, $nama, $role);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_bind_param($stmt2, 'ssssssdss', $nik, $nama, $email, $password, $no_hp, $alamat, $saldo, $status, $ktp);
        $ok = mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt1);
        mysqli_stmt_close($stmt2);
        if ($ok) { mysqli_commit($this->kon); return "Data pelanggan dan akun berhasil disimpan."; }
        mysqli_rollback($this->kon);
        return "Gagal menyimpan data pelanggan: " . mysqli_error($this->kon);
    }
}
?>