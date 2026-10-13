<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') { header('Location: ../../dashboard/dashboard.php'); exit(); }
include_once("../../../config/koneksi.php");
include_once("../Controler/tambahpemilik.php");
$controller = new PemilikController($kon); $pesan = '';
if (isset($_POST['submit'])) { $pesan = $controller->tambahDataPemilik($_POST); }
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tambah Mitra Pemilik</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h2>Form Tambah Mitra Pemilik Mobil</h2>
<?php if ($pesan): ?><p><b><?php echo htmlspecialchars($pesan); ?></b></p><?php endif; ?>
<form method="POST">
<label>Nama Lengkap:</label><br><input type="text" name="nama" required><br><br>
<label>Username Login:</label><br><input type="text" name="username" required><br><br>
<label>Alamat Email:</label><br><input type="email" name="email" required><br><br>
<label>Password Akun:</label><br><input type="password" name="password" minlength="6" required><br><br>
<label>Nomor Handphone/WhatsApp:</label><br><input type="text" name="no_hp" required><br><br>
<label>Alamat Lengkap:</label><br><textarea name="alamat" rows="4" required></textarea><br><br>
<button type="submit" name="submit">Simpan Mitra Pemilik</button>
</form><br><a href="../../dashboard/dashboard.php?menu=pemilik">Kembali</a>
</body></html>