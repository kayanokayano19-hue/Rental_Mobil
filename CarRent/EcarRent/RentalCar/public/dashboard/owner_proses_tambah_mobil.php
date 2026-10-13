<?php
include 'owner_lib.php';
$balik = 'owner_tambah_mobil.php?galat=';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: owner_tambah_mobil.php'); exit(); }
if (!csrf_valid()) { header('Location: ' . $balik . 'sesi'); exit(); }
if (!$owner_id) { header('Location: owner_tambah_mobil.php'); exit(); }

$plat = strtoupper(trim($_POST['plat_mobil'] ?? ''));
$merk = trim($_POST['merk'] ?? '');
$tipe = trim($_POST['tipe'] ?? '');
$tahun = (int)($_POST['tahun'] ?? 0);
$harga = (float)($_POST['harga_rental'] ?? 0);
$kondisi = trim($_POST['kondisi_mobil'] ?? '');
$riwayat = trim($_POST['riwayat_kerusakan'] ?? '');
if ($plat === '' || strlen($plat) > 20 || $merk === '' || $tipe === '' || $tahun < 1990 || $harga <= 0 || $kondisi === '') {
    header('Location: ' . $balik . 'lengkap'); exit();
}
if ((int)satu_nilai($kon, 'SELECT COUNT(*) FROM tb_mobil WHERE plat_mobil = ?', 's', [$plat]) > 0) {
    header('Location: ' . $balik . 'plat'); exit();
}

/* Foto wajib, gambar asli, maksimal 3 MB. Kolom foto di database tidak boleh NULL. */
$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['foto']['tmp_name'];
    $info = @getimagesize($tmp);
    $boleh = ['image/jpeg', 'image/png', 'image/webp'];
    if (!$info || !in_array($info['mime'], $boleh, true) || $_FILES['foto']['size'] > 3 * 1024 * 1024) {
        header('Location: ' . $balik . 'foto'); exit();
    }
    $foto = file_get_contents($tmp);
} else {
    header('Location: ' . $balik . 'foto'); exit();
}

$status = 'Tersedia';
$approval = 'Menunggu';
$stmt = mysqli_prepare($kon, 'INSERT INTO tb_mobil (plat_mobil, id_pemilik, merk, tipe, status, tahun, kondisi_mobil, riwayat_kerusakan, foto, harga_rental, status_persetujuan) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
if (!$stmt) { header('Location: ' . $balik . 'simpan'); exit(); }
mysqli_stmt_bind_param($stmt, 'sisssisssds', $plat, $owner_id, $merk, $tipe, $status, $tahun, $kondisi, $riwayat, $foto, $harga, $approval);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: ' . ($ok ? 'owner_armada.php?pesan=diajukan' : $balik . 'simpan'));
exit();
