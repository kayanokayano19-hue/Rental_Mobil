<?php
include 'owner_lib.php';
$plat = trim($_GET['plat'] ?? '');
$m = null;
if ($owner_id && $plat !== '') {
    $res = jalankan($kon, "SELECT m.*,
        (SELECT COUNT(*) FROM tb_rental r WHERE r.plat_mobil = m.plat_mobil AND " . SQL_RENTAL_AKTIF . ") AS jml_sewa,
        (SELECT COALESCE(SUM(r.total_harga),0) FROM tb_rental r WHERE r.plat_mobil = m.plat_mobil AND " . SQL_RENTAL_SELESAI . ") AS omzet
        FROM tb_mobil m WHERE m.plat_mobil = ? AND m.id_pemilik = ? LIMIT 1", 'si', [$plat, $owner_id]);
    $m = $res ? mysqli_fetch_assoc($res) : null;
}
$riwayat = $ulasan = [];
if ($m) {
    $res = jalankan($kon, 'SELECT r.id_rental, r.status_rental, r.waktu_pinjam, r.waktu_kembali, r.total_harga, p.nama AS nama_pelanggan FROM tb_rental r LEFT JOIN tb_pelanggan p ON p.id_pelanggan = r.id_pelanggan WHERE r.plat_mobil = ? ORDER BY r.id_rental DESC LIMIT 8', 's', [$plat]);
    while ($res && ($x = mysqli_fetch_assoc($res))) { $riwayat[] = $x; }
    if (ulasan_aktif($kon)) {
        $res = jalankan($kon, 'SELECT u.rating, u.komentar, u.dibuat_pada, p.nama FROM tb_ulasan u LEFT JOIN tb_pelanggan p ON p.id_pelanggan = u.id_pelanggan WHERE u.plat_mobil = ? ORDER BY u.id_ulasan DESC LIMIT 6', 's', [$plat]);
        while ($res && ($x = mysqli_fetch_assoc($res))) { $ulasan[] = $x; }
    }
}
$owner_page = 'armada'; $owner_title = 'Detail Mobil'; include 'owner_header.php';
?>
<div class="page-head">
    <div><h1>Detail mobil</h1><p>Spesifikasi, performa sewa, dan ulasan penyewa.</p></div>
    <div><a class="btn secondary" href="owner_armada.php">&larr; Kembali ke armada</a></div>
</div>
<?php if (!$m): ?><div class="alert alert--bad">Mobil tidak ditemukan atau bukan milik Anda.</div><?php else:
    $uri = foto_uri($m['foto'] ?? ''); $nm = trim($m['merk'] . ' ' . $m['tipe']); ?>
<section class="detail">
    <div class="detail__photo glass"><?php if ($uri): ?><img src="<?php echo $uri; ?>" alt="<?php echo esc($nm); ?>"><?php else: ?><div class="car__ph">Tanpa foto</div><?php endif; ?></div>
    <aside class="detail__book glass">
        <?php echo owner_badge_persetujuan($m['status_persetujuan']); ?>
        <span class="plat plat--lg"><?php echo esc($m['plat_mobil']); ?></span>
        <h1><?php echo esc($nm); ?></h1>
        <div class="car__price"><?php echo fmt_rp($m['harga_rental']); ?><small>per hari</small></div>
        <?php echo label_rating($kon, $m['plat_mobil']); ?>
        <a class="btn block" href="owner_edit_mobil.php?plat=<?php echo urlencode($m['plat_mobil']); ?>">Ubah tarif &amp; kondisi</a>
    </aside>
</section>
<section class="stats">
    <div class="stat glass"><span>Disewa</span><b><?php echo (int)$m['jml_sewa']; ?> kali</b></div>
    <div class="stat glass stat--hi"><span>Pendapatan Anda</span><b><?php echo fmt_rp(bagian_pemilik($m['omzet'])); ?></b><small>Dari rental selesai</small></div>
    <div class="stat glass"><span>Status sekarang</span><b><?php echo mobil_tersedia($m['status']) ? 'Tersedia' : 'Disewa'; ?></b></div>
</section>
<div class="panel-head"><h2>Spesifikasi dan kondisi</h2></div>
<table class="kv">
    <tr><td>Tahun</td><td><?php echo esc($m['tahun']); ?></td></tr>
    <tr><td>Kondisi</td><td><?php echo esc($m['kondisi_mobil']); ?></td></tr>
    <tr><td>Riwayat kerusakan</td><td><?php echo esc($m['riwayat_kerusakan'] !== '' ? $m['riwayat_kerusakan'] : 'Tidak ada'); ?></td></tr>
</table>
<div class="panel-head"><h2>Riwayat sewa terakhir</h2></div>
<div class="table-wrap"><table>
    <tr><th>ID</th><th>Penyewa</th><th>Periode</th><th>Status</th><th>Total</th></tr>
    <?php if ($riwayat): foreach ($riwayat as $r): ?>
    <tr><td><a href="owner_detail_rental.php?id=<?php echo (int)$r['id_rental']; ?>">#<?php echo (int)$r['id_rental']; ?></a></td><td><?php echo esc($r['nama_pelanggan'] ?? '-'); ?></td><td><?php echo esc(fmt_tgl($r['waktu_pinjam'])); ?><br><span class="muted">s/d <?php echo esc(fmt_tgl($r['waktu_kembali'])); ?></span></td><td><span class="status"><?php echo esc($r['status_rental']); ?></span></td><td class="num"><?php echo fmt_rp($r['total_harga']); ?></td></tr>
    <?php endforeach; else: ?><tr><td colspan="5">Mobil ini belum pernah disewa.</td></tr><?php endif; ?>
</table></div>
<?php if ($ulasan): ?>
<div class="panel-head"><h2>Ulasan penyewa</h2></div>
<div class="reviews">
    <?php foreach ($ulasan as $x): $nmx = trim((string)$x['nama']); ?>
    <article class="review glass">
        <div class="review__top"><b><?php echo esc($nmx !== '' ? $nmx : 'Penyewa'); ?></b><span class="review__stars" aria-label="<?php echo (int)$x['rating']; ?> dari 5 bintang"><?php echo str_repeat('&#9733;', (int)$x['rating']) . str_repeat('<span class="off">&#9733;</span>', 5 - (int)$x['rating']); ?></span></div>
        <?php if (trim((string)$x['komentar']) !== ''): ?><p><?php echo nl2br(esc($x['komentar'])); ?></p><?php endif; ?>
        <small class="muted"><?php echo esc(date('d M Y', strtotime($x['dibuat_pada']))); ?></small>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; endif; ?>
<?php include 'owner_footer.php'; ?>
