<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once(__DIR__ . '/../../../config/koneksi.php');

$id_pemilik = (int)($_GET['id_pemilik'] ?? 0);
$pesan = 'gagal';

if ($id_pemilik > 0) {
    /* Menghapus pemilik ikut menghapus mobil dan riwayat sewanya, jadi tahan dulu kalau ada yang sedang disewa. */
    $stmt = mysqli_prepare($kon, "SELECT COUNT(*) AS c FROM tb_mobil WHERE id_pemilik = ? AND LOWER(status) IN ('disewa','di sewa')");
    mysqli_stmt_bind_param($stmt, 'i', $id_pemilik);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ((int)($row['c'] ?? 0) > 0) {
        $pesan = 'mobil_disewa';
    } else {
        $stmt = mysqli_prepare($kon, "DELETE FROM tb_pemilik_mobil WHERE id_pemilik = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id_pemilik);
        $pesan = mysqli_stmt_execute($stmt) ? 'berhasil' : 'gagal';
        mysqli_stmt_close($stmt);
    }
}

header('Location: ../../dashboard/dashboard.php?menu=pemilik&pesan=' . $pesan);
exit();
