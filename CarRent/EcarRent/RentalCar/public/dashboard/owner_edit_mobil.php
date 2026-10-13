<?php
include 'owner_lib.php';
$plat = trim($_REQUEST['plat'] ?? '');
$m = null;
if ($owner_id && $plat !== '') {
    $res = jalankan($kon, 'SELECT plat_mobil, merk, tipe, tahun, harga_rental, kondisi_mobil, riwayat_kerusakan, status_persetujuan FROM tb_mobil WHERE plat_mobil = ? AND id_pemilik = ? LIMIT 1', 'si', [$plat, $owner_id]);
    $m = $res ? mysqli_fetch_assoc($res) : null;
}
$galat = '';
if ($m && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $harga = (float)($_POST['harga_rental'] ?? -1);
    $kondisi = trim((string)($_POST['kondisi_mobil'] ?? ''));
    $riwayat = trim((string)($_POST['riwayat_kerusakan'] ?? ''));
    if (!csrf_valid()) {
        $galat = 'Sesi formulir sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($harga <= 0 || $kondisi === '') {
        $galat = 'Tarif harus lebih dari 0 dan kondisi mobil wajib diisi.';
    } else {
        $stmt = mysqli_prepare($kon, 'UPDATE tb_mobil SET harga_rental = ?, kondisi_mobil = ?, riwayat_kerusakan = ? WHERE plat_mobil = ? AND id_pemilik = ?');
        mysqli_stmt_bind_param($stmt, 'dsssi', $harga, $kondisi, $riwayat, $plat, $owner_id);
        if (mysqli_stmt_execute($stmt)) { header('Location: owner_armada.php?pesan=diubah'); exit(); }
        $galat = 'Perubahan gagal disimpan. Coba lagi.';
    }
    $m['harga_rental'] = $harga; $m['kondisi_mobil'] = $kondisi; $m['riwayat_kerusakan'] = $riwayat;
}
$owner_page = 'armada'; $owner_title = 'Ubah Data Mobil'; include 'owner_header.php';
?>
<div class="page-head">
    <div><h1>Ubah tarif &amp; kondisi</h1><p>Perubahan tarif berlaku untuk pesanan baru. Rental yang sudah berjalan tidak ikut berubah.</p></div>
    <a class="btn secondary" href="owner_armada.php">&larr; Kembali ke armada</a>
</div>
<?php if (!$m): ?><div class="alert alert--bad">Mobil tidak ditemukan atau bukan milik Anda.</div><?php else: ?>
<?php if ($galat !== ''): ?><p class="alert alert--bad" role="alert"><?php echo esc($galat); ?></p><?php endif; ?>
<form method="post">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="plat" value="<?php echo esc($m['plat_mobil']); ?>">
    <div class="form-grid">
        <div class="field"><label>Mobil</label><input value="<?php echo esc($m['merk'] . ' ' . $m['tipe'] . ' (' . $m['plat_mobil'] . ')'); ?>" readonly></div>
        <div class="field"><label for="harga">Tarif per hari (Rp)</label><input id="harga" type="number" name="harga_rental" min="1" step="1000" value="<?php echo (int)$m['harga_rental']; ?>" required></div>
        <div class="field full"><label for="kondisi">Kondisi mobil</label><textarea id="kondisi" name="kondisi_mobil" required><?php echo esc($m['kondisi_mobil']); ?></textarea></div>
        <div class="field full"><label for="riwayat">Riwayat kerusakan</label><textarea id="riwayat" name="riwayat_kerusakan"><?php echo esc($m['riwayat_kerusakan']); ?></textarea></div>
        <div class="full"><button type="submit">Simpan perubahan</button></div>
    </div>
</form>
<?php endif; ?>
<?php include 'owner_footer.php'; ?>
