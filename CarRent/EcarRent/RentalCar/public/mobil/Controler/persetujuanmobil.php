<?php
/* Admin/petugas menyetujui atau menolak mobil yang diajukan pemilik. POST + CSRF. */
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once(__DIR__ . '/../../../config/koneksi.php');

$balik = '../../dashboard/dashboard.php?menu=persetujuan';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    header('Location: ' . $balik . '&pesan=gagal');
    exit();
}

$plat = trim((string)($_POST['plat_mobil'] ?? ''));
$aksi = strtolower((string)($_POST['aksi'] ?? ''));
if ($plat === '' || !in_array($aksi, ['setujui', 'tolak'], true)) {
    header('Location: ' . $balik . '&pesan=gagal');
    exit();
}

$stmt = mysqli_prepare($kon, 'SELECT status FROM tb_mobil WHERE plat_mobil = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $plat);
mysqli_stmt_execute($stmt);
$mobil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$mobil) {
    header('Location: ' . $balik . '&pesan=gagal');
    exit();
}
if ($aksi === 'tolak' && !mobil_tersedia($mobil['status'])) {
    header('Location: ' . $balik . '&pesan=mobil_disewa');
    exit();
}

$baru = $aksi === 'setujui' ? 'Disetujui' : 'Ditolak';
$stmt = mysqli_prepare($kon, 'UPDATE tb_mobil SET status_persetujuan = ? WHERE plat_mobil = ?');
mysqli_stmt_bind_param($stmt, 'ss', $baru, $plat);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: ' . $balik . '&pesan=' . ($ok ? ($aksi === 'setujui' ? 'mobil_disetujui' : 'mobil_ditolak') : 'gagal'));
exit();
