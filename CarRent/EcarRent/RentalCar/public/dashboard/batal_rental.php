<?php
/* Pelanggan membatalkan pesanannya sendiri selama masih berstatus Proses (belum dikonfirmasi admin). */
include_once(__DIR__ . '/../../config/guard.php');
include_once(__DIR__ . '/../../config/koneksi.php');
wajib_peran(['user']);

$balik = 'riwayat.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) { header('Location: ' . $balik . '?pesan=gagal'); exit(); }

$id_rental = (int)($_POST['id_rental'] ?? 0);
$p = pelanggan_saat_ini($kon);
if ($id_rental <= 0 || !$p) { header('Location: ' . $balik . '?pesan=gagal'); exit(); }

mysqli_begin_transaction($kon);
try {
    $stmt = mysqli_prepare($kon, 'SELECT plat_mobil, status_rental, total_harga FROM tb_rental WHERE id_rental = ? AND id_pelanggan = ? FOR UPDATE');
    mysqli_stmt_bind_param($stmt, 'ii', $id_rental, $p['id_pelanggan']);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$r) { throw new Exception('tidak ditemukan'); }
    if (strtolower(trim($r['status_rental'])) !== 'proses') {
        mysqli_rollback($kon);
        header('Location: ' . $balik . '?pesan=tidak');
        exit();
    }

    $stmt = mysqli_prepare($kon, "UPDATE tb_rental SET status_rental = 'Dibatalkan' WHERE id_rental = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_rental);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($kon, "UPDATE tb_mobil SET status = 'Tersedia' WHERE plat_mobil = ?");
    mysqli_stmt_bind_param($stmt, 's', $r['plat_mobil']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $refund = (float)$r['total_harga'];
    $stmt = mysqli_prepare($kon, 'UPDATE tb_pelanggan SET saldo = saldo + ? WHERE id_pelanggan = ?');
    mysqli_stmt_bind_param($stmt, 'di', $refund, $p['id_pelanggan']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($kon);
    header('Location: ' . $balik . '?pesan=batal');
} catch (Throwable $e) {
    mysqli_rollback($kon);
    header('Location: ' . $balik . '?pesan=gagal');
}
exit();
