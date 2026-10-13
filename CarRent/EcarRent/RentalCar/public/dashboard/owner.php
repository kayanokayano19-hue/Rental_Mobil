<?php
include 'owner_lib.php';

$mobil = $owner_id ? owner_daftar_mobil($kon, $owner_id) : [];
$disetujui = array_filter($mobil, function ($m) { return strtolower($m['status_persetujuan']) === 'disetujui'; });
$menunggu = count($mobil) - count($disetujui);
$total_mobil = count($disetujui);
$tersedia = count(array_filter($disetujui, function ($m) { return mobil_tersedia($m['status']); }));
$disewa = $total_mobil - $tersedia;

$rental = 0; $pendapatan = 0; $terbaru = false; $deret = [];
if ($owner_id) {
    $rental = (int)satu_nilai($kon, 'SELECT COUNT(*) FROM tb_rental r INNER JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil WHERE m.id_pemilik = ?', 'i', [$owner_id]);
    $omzet = (float)satu_nilai($kon, 'SELECT COALESCE(SUM(r.total_harga),0) FROM tb_rental r INNER JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil WHERE m.id_pemilik = ? AND ' . SQL_RENTAL_SELESAI, 'i', [$owner_id]);
    $pendapatan = bagian_pemilik($omzet);
    $deret = owner_deret_pendapatan($kon, $owner_id);
    $terbaru = jalankan($kon, 'SELECT r.id_rental, r.plat_mobil, r.status_rental, r.waktu_pinjam, r.total_harga, p.nama AS nama_pelanggan, m.merk, m.tipe FROM tb_rental r INNER JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil LEFT JOIN tb_pelanggan p ON p.id_pelanggan = r.id_pelanggan WHERE m.id_pemilik = ? ORDER BY r.id_rental DESC LIMIT 5', 'i', [$owner_id]);
}
$owner_page = 'home'; $owner_title = 'Home Pemilik'; include 'owner_header.php';
?>
<div class="page-head">
    <div>
        <h1>Halo, <?php echo esc($nama); ?></h1>
        <p>Begini kondisi armada dan pendapatan Anda saat ini.</p>
    </div>
    <a class="btn" href="owner_tambah_mobil.php">+ Ajukan mobil baru</a>
</div>
<?php if (!$owner_id): ?><div class="alert alert--bad">Akun owner belum terhubung dengan data pemilik mobil. Hubungi admin untuk menghubungkan akun.</div><?php else: ?>

<?php if ($menunggu > 0): ?><p class="alert" role="status"><?php echo $menunggu; ?> pengajuan mobil Anda belum disetujui admin. <a href="owner_armada.php">Lihat statusnya</a></p><?php endif; ?>

<section class="stats">
    <div class="stat glass stat--hi"><span>Pendapatan Anda</span><b><?php echo fmt_rp($pendapatan); ?></b><small>Setelah komisi <?php echo (int)(KOMISI_PLATFORM * 100); ?>%, dari rental selesai</small></div>
    <div class="stat glass"><span>Unit aktif</span><b><?php echo $total_mobil; ?> unit</b><small><?php echo $tersedia; ?> siap sewa &middot; <?php echo $disewa; ?> disewa</small></div>
    <div class="stat glass"><span>Total transaksi</span><b><?php echo $rental; ?> kali</b></div>
    <div class="stat glass<?php echo $menunggu ? ' stat--alert' : ''; ?>"><span>Menunggu persetujuan</span><b><?php echo $menunggu; ?> unit</b></div>
</section>

<?php echo owner_grafik($deret, 'Pendapatan 6 bulan terakhir', 'Bagian Anda dari rental yang sudah selesai.'); ?>

<div class="panel-head"><div><h2>Armada Anda</h2><p>Ringkasan tiap unit. Buka halaman Armada untuk daftar lengkap.</p></div><a class="btn secondary" href="owner_armada.php">Semua armada</a></div>
<?php if ($mobil): ?>
<div class="ocar-grid">
    <?php foreach (array_slice($mobil, 0, 3) as $m) { echo owner_kartu_mobil($m, $kon); } ?>
</div>
<?php else: ?>
<div class="empty glass"><h3>Belum ada mobil</h3><p>Ajukan unit pertama Anda. Admin akan meninjau sebelum tampil ke pelanggan.</p><a class="btn" href="owner_tambah_mobil.php">Ajukan mobil</a></div>
<?php endif; ?>

<div class="panel-head"><div><h2>Transaksi terbaru</h2></div><a class="btn secondary" href="owner_transaksi.php">Semua transaksi</a></div>
<div class="table-wrap"><table>
    <tr><th>ID</th><th>Penyewa</th><th>Kendaraan</th><th>Tanggal</th><th>Status</th><th>Total</th></tr>
    <?php if ($terbaru && mysqli_num_rows($terbaru)): while ($r = mysqli_fetch_assoc($terbaru)): ?>
    <tr>
        <td><a href="owner_detail_rental.php?id=<?php echo (int)$r['id_rental']; ?>">#<?php echo (int)$r['id_rental']; ?></a></td>
        <td><?php echo esc($r['nama_pelanggan'] ?? '-'); ?></td>
        <td><?php echo esc(trim($r['merk'] . ' ' . $r['tipe'])); ?><br><span class="muted"><?php echo esc($r['plat_mobil']); ?></span></td>
        <td><?php echo esc(fmt_tgl($r['waktu_pinjam'])); ?></td>
        <td><span class="status"><?php echo esc($r['status_rental']); ?></span></td>
        <td class="num"><?php echo fmt_rp($r['total_harga']); ?></td>
    </tr>
    <?php endwhile; else: ?><tr><td colspan="6">Belum ada transaksi untuk armada Anda.</td></tr><?php endif; ?>
</table></div>
<?php endif; ?>
<?php include 'owner_footer.php'; ?>
