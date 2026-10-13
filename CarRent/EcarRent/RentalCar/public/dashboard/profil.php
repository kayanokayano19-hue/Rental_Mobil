<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_user = $_SESSION['nama'] ?? $username;
$role = $_SESSION['role'] ?? 'user';
$nama_safe = mysqli_real_escape_string($kon, $nama_user);
$query = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nama = '$nama_safe' LIMIT 1");
$data = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pelanggan</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<section class="profile glass">
    <span class="profile__av"><?php echo htmlspecialchars(strtoupper(substr($nama_user, 0, 1))); ?></span>
    <div>
        <h1><?php echo htmlspecialchars($nama_user); ?></h1>
        <p>@<?php echo htmlspecialchars($username); ?></p>
        <span class="status"><?php echo isset($data['status_verifikasi']) && $data['status_verifikasi'] == 1 ? 'Terverifikasi' : (htmlspecialchars($data['status_verifikasi'] ?? '') ?: 'Belum Terverifikasi'); ?></span>
    </div>
    <a class="btn" href="lengkapi_pelanggan.php">Ubah data diri</a>
</section>
<section class="stats">
    <div class="stat glass stat--hi"><span>Saldo</span><b>Rp <?php echo number_format((int)($data['saldo'] ?? 0), 0, ',', '.'); ?></b><small><a href="isi_saldo.php">Isi saldo</a></small></div>
</section>
<table class="kv">
    <tr><td>NIK</td><td><?php echo htmlspecialchars($data['nik_pelanggan'] ?? 'Belum dilengkapi'); ?></td></tr>
    <tr><td>Email</td><td><?php echo htmlspecialchars($data['email'] ?? 'Belum dilengkapi'); ?></td></tr>
    <tr><td>No HP</td><td><?php echo htmlspecialchars($data['no_hp'] ?? 'Belum dilengkapi'); ?></td></tr>
    <tr><td>Alamat</td><td><?php echo htmlspecialchars($data['alamat'] ?? 'Belum dilengkapi'); ?></td></tr>
</table>
</body>
</html>
