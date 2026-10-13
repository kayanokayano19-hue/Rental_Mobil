<?php
include_once("../../../config/koneksi.php");
class PemilikController {
    private $kon;
    public function __construct($connection) { $this->kon = $connection; }
    public function tambahDataPemilik($data) {
        $nama = mysqli_real_escape_string($this->kon, trim($data['nama']));
        $username = mysqli_real_escape_string($this->kon, trim($data['username']));
        $email = mysqli_real_escape_string($this->kon, trim($data['email']));
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $no_hp = mysqli_real_escape_string($this->kon, trim($data['no_hp']));
        $alamat = mysqli_real_escape_string($this->kon, trim($data['alamat']));
        $cek = mysqli_query($this->kon, "SELECT id_user FROM tb_user WHERE username = '$username' LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) return 'Username sudah digunakan.';
        mysqli_begin_transaction($this->kon);
        $stmt1 = mysqli_prepare($this->kon, "INSERT INTO tb_user (username, password, nama, role) VALUES (?, ?, ?, 'owner')");
        $stmt2 = mysqli_prepare($this->kon, "INSERT INTO tb_pemilik_mobil (id_user, nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt1 || !$stmt2) { mysqli_rollback($this->kon); return 'Gagal menyiapkan data: ' . mysqli_error($this->kon); }
        mysqli_stmt_bind_param($stmt1, 'sss', $username, $password, $nama);
        $ok1 = mysqli_stmt_execute($stmt1);
        $id_user = (int)mysqli_insert_id($this->kon);
        mysqli_stmt_bind_param($stmt2, 'isssss', $id_user, $nama, $email, $password, $no_hp, $alamat);
        $ok2 = mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt1); mysqli_stmt_close($stmt2);
        if ($ok1 && $ok2) { mysqli_commit($this->kon); return 'Mitra pemilik dan akun login berhasil disimpan.'; }
        mysqli_rollback($this->kon); return 'Gagal menyimpan pemilik: ' . mysqli_error($this->kon);
    }
}
?>