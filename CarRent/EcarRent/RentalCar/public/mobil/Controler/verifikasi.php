<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit();
}
if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../dashboard/dashboard.php?menu=pengajuan');
    exit();
}
include_once('../../../config/koneksi.php');
$id_pelanggan = (int)($_GET['id_pelanggan'] ?? 0);
$aksi = strtolower($_GET['aksi'] ?? '');
if ($id_pelanggan <= 0) {
    header('Location: ../../dashboard/dashboard.php?menu=pengajuan&pesan=gagal');
    exit();
}
if ($aksi === 'setujui') {
    $status = 'Disetujui';
} elseif ($aksi === 'tolak') {
    $status = 'Ditolak';
} elseif ($aksi === 'reset') {
    $status = 'Belum Verifikasi';
} else {
    header('Location: ../../dashboard/dashboard.php?menu=pengajuan&pesan=gagal');
    exit();
}
$stmt = mysqli_prepare($kon, 'UPDATE tb_pelanggan SET status_verifikasi = ? WHERE id_pelanggan = ?');
mysqli_stmt_bind_param($stmt, 'si', $status, $id_pelanggan);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
header('Location: ../../dashboard/dashboard.php?menu=pengajuan&pesan=' . ($ok ? 'berhasil' : 'gagal'));
exit();
?>
