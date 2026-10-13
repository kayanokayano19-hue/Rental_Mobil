<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../dashboard/dashboard.php');
    exit();
}
include_once("../../../config/koneksi.php");
include_once("../Controler/tambahpelanggan.php");
$controller = new MobilController($kon);
$pesan = '';
if (isset($_POST['submit'])) {
    $data = [
        'nik_pelanggan' => $_POST['nik_pelanggan'],
        'nama' => $_POST['nama'],
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'no_hp' => $_POST['no_hp'],
        'alamat' => $_POST['alamat'],
        'saldo' => $_POST['saldo'],
        'status_verifikasi' => $_POST['status_verifikasi']
    ];
    $pesan = $controller->tambahDataPelanggan($data);
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tambah Data Pelanggan</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h2>Form Tambah Data Pelanggan</h2>
<?php if ($pesan): ?><p><b><?php echo htmlspecialchars($pesan); ?></b></p><?php endif; ?>
<form method="POST" enctype="multipart/form-data">
<label>NIK KTP:</label><br><input type="text" name="nik_pelanggan" required><br><br>
<label>Nama Lengkap:</label><br><input type="text" name="nama" required><br><br>
<label>Username Login:</label><br><input type="text" name="username" required><br><br>
<label>Email:</label><br><input type="email" name="email" required><br><br>
<label>Password Akun:</label><br><input type="password" name="password" minlength="6" required><br><br>
<label>Nomor Handphone:</label><br><input type="text" name="no_hp" required><br><br>
<label>Alamat Lengkap:</label><br><textarea name="alamat" rows="4" required></textarea><br><br>
<label>Saldo Awal:</label><br><input type="number" name="saldo" min="0" value="0" required><br><br>
<label>Status Verifikasi:</label><br><select name="status_verifikasi" required><option value="Belum Verifikasi">Pending</option><option value="Disetujui">Approved</option><option value="Ditolak">Rejected</option><option value="Blacklist">Blacklist</option></select><br><br>
<label>Foto KTP:</label><br><input type="file" name="ktp" accept="image/*"><br><br>
<button type="submit" name="submit">Simpan Data Pelanggan</button>
</form><br><a href="../../dashboard/dashboard.php?menu=pelanggan">Kembali</a>
</body></html>