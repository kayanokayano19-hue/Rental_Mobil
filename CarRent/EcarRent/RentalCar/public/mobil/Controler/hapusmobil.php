<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once(__DIR__ . '/../../../config/koneksi.php');

$plat_mobil = trim($_GET['plat_mobil'] ?? '');
$pesan = 'gagal';

if ($plat_mobil !== '') {
    /* Mobil yang sedang disewa tidak boleh dihapus: riwayat dan nota ikut hilang (ON DELETE CASCADE). */
    $stmt = mysqli_prepare($kon, "SELECT status FROM tb_mobil WHERE plat_mobil = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $plat_mobil);
    mysqli_stmt_execute($stmt);
    $mobil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($mobil && mobil_tersedia($mobil['status'])) {
        $stmt = mysqli_prepare($kon, "DELETE FROM tb_mobil WHERE plat_mobil = ?");
        mysqli_stmt_bind_param($stmt, 's', $plat_mobil);
        $pesan = mysqli_stmt_execute($stmt) ? 'berhasil' : 'gagal';
        mysqli_stmt_close($stmt);
    } elseif ($mobil) {
        $pesan = 'mobil_disewa';
    }
}

header('Location: ../../dashboard/dashboard.php?menu=mobil&pesan=' . $pesan);
exit();
