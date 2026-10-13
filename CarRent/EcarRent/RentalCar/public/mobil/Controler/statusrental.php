<?php
/*
 * Ubah status rental (admin/petugas). Hanya menerima POST dengan token CSRF.
 *   proses -> Diproses  : pesanan dikonfirmasi, mobil tetap Disewa
 *   selesai-> Selesai   : mobil kembali Tersedia, denda keterlambatan dihitung dan dipotong dari saldo
 *   batal  -> Dibatalkan: mobil kembali Tersedia, seluruh biaya dikembalikan ke saldo pelanggan
 */
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once(__DIR__ . '/../../../config/koneksi.php');
include_once(__DIR__ . '/../../../config/aturan.php');

$balik = '../../dashboard/dashboard.php?menu=rental';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    header('Location: ' . $balik . '&pesan=gagal');
    exit();
}

$id_rental = (int)($_POST['id_rental'] ?? 0);
$aksi = strtolower((string)($_POST['aksi'] ?? ''));
if ($id_rental <= 0 || !in_array($aksi, ['proses', 'selesai', 'batal'], true)) {
    header('Location: ' . $balik . '&pesan=gagal');
    exit();
}

mysqli_begin_transaction($kon);
try {
    /* Kunci barisnya supaya dua admin tidak memproses rental yang sama bersamaan */
    $stmt = mysqli_prepare($kon, 'SELECT id_rental, id_pelanggan, plat_mobil, status_rental, waktu_kembali, harga_rental, total_harga FROM tb_rental WHERE id_rental = ? FOR UPDATE');
    mysqli_stmt_bind_param($stmt, 'i', $id_rental);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$r) { throw new Exception('rental tidak ada'); }

    $status_lama = strtolower(trim($r['status_rental']));
    $sudah_akhir = in_array($status_lama, ['selesai', 'dikembalikan', 'dibatalkan', 'batal', 'ditolak'], true);

    if ($aksi === 'proses') {
        if ($sudah_akhir) { throw new Exception('sudah berakhir'); }
        $status_baru = 'Diproses';
        $status_mobil = 'Disewa';
        $denda = 0;
        $refund = 0;
    } elseif ($aksi === 'selesai') {
        if ($sudah_akhir) { throw new Exception('sudah berakhir'); }
        $status_baru = 'Selesai';
        $status_mobil = 'Tersedia';
        $denda = hitung_denda($r['harga_rental'], $r['waktu_kembali']);
        $refund = 0;
    } else {
        if ($sudah_akhir) { throw new Exception('sudah berakhir'); }
        $status_baru = 'Dibatalkan';
        $status_mobil = 'Tersedia';
        $denda = 0;
        $refund = (float)$r['total_harga'];
    }

    $stmt = mysqli_prepare($kon, 'UPDATE tb_rental SET status_rental = ?, denda = ? WHERE id_rental = ?');
    mysqli_stmt_bind_param($stmt, 'sdi', $status_baru, $denda, $id_rental);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($kon, 'UPDATE tb_mobil SET status = ? WHERE plat_mobil = ?');
    mysqli_stmt_bind_param($stmt, 'ss', $status_mobil, $r['plat_mobil']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($refund > 0) {
        $stmt = mysqli_prepare($kon, 'UPDATE tb_pelanggan SET saldo = saldo + ? WHERE id_pelanggan = ?');
        mysqli_stmt_bind_param($stmt, 'di', $refund, $r['id_pelanggan']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    if ($denda > 0) {
        /* Saldo boleh minus: angkanya menjadi tagihan yang harus dilunasi lewat isi saldo */
        $stmt = mysqli_prepare($kon, 'UPDATE tb_pelanggan SET saldo = saldo - ? WHERE id_pelanggan = ?');
        mysqli_stmt_bind_param($stmt, 'di', $denda, $r['id_pelanggan']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($kon);
    $pesan = 'berhasil';
    if ($aksi === 'selesai' && $denda > 0) { $pesan = 'denda'; }
    if ($aksi === 'batal') { $pesan = 'refund'; }
} catch (Throwable $e) {
    mysqli_rollback($kon);
    $pesan = 'gagal';
}

header('Location: ' . $balik . '&pesan=' . $pesan);
exit();
