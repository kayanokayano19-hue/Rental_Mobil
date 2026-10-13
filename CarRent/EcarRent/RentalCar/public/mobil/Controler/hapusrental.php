<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once(__DIR__ . '/../../../config/koneksi.php');

$id_rental = (int)($_GET['id_rental'] ?? 0);
$pesan = 'gagal';

if ($id_rental > 0) {
    $stmt = mysqli_prepare($kon, "SELECT status_rental FROM tb_rental WHERE id_rental = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id_rental);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    /* Rental yang masih berjalan harus diselesaikan atau dibatalkan dulu supaya saldo dan status mobil benar. */
    if ($r && rental_berakhir($r['status_rental'])) {
        $stmt = mysqli_prepare($kon, "DELETE FROM tb_rental WHERE id_rental = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id_rental);
        $pesan = mysqli_stmt_execute($stmt) ? 'berhasil' : 'gagal';
        mysqli_stmt_close($stmt);
    } elseif ($r) {
        $pesan = 'rental_aktif';
    }
}

header('Location: ../../dashboard/dashboard.php?menu=rental&pesan=' . $pesan);
exit();
