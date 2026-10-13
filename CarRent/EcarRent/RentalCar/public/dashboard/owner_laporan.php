<?php
include 'owner_lib.php';

/* Filter bulan: ?bulan=2026-10 atau kosong untuk semua */
$bulan = trim($_GET['bulan'] ?? '');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) { $bulan = ''; }

$rows = [];
if ($owner_id) {
    $kondisi = ['m.id_pemilik = ?', SQL_RENTAL_AKTIF]; $tipe = 'i'; $param = [$owner_id];
    if ($bulan !== '') { $kondisi[] = "DATE_FORMAT(r.waktu_pinjam, '%Y-%m') = ?"; $tipe .= 's'; $param[] = $bulan; }
    $res = jalankan($kon, 'SELECT r.id_rental, r.plat_mobil, r.status_rental, r.waktu_pinjam, r.total_harga, p.nama AS nama_pelanggan, m.merk, m.tipe FROM tb_rental r INNER JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil LEFT JOIN tb_pelanggan p ON p.id_pelanggan = r.id_pelanggan WHERE ' . implode(' AND ', $kondisi) . ' ORDER BY r.id_rental DESC', $tipe, $param);
    while ($res && ($x = mysqli_fetch_assoc($res))) { $rows[] = $x; }
}

$ringkas = ['total' => count($rows), 'omzet_selesai' => 0.0, 'menunggu' => 0.0];
foreach ($rows as $r) {
    if (in_array(strtolower(trim($r['status_rental'])), ['selesai', 'dikembalikan'], true)) { $ringkas['omzet_selesai'] += (float)$r['total_harga']; }
    else { $ringkas['menunggu'] += bagian_pemilik($r['total_harga']); }
}
$potongan = bagian_platform($ringkas['omzet_selesai']);
$bersih = bagian_pemilik($ringkas['omzet_selesai']);

/* Unduh CSV (sebelum ada output HTML) */
if (($_GET['unduh'] ?? '') === 'csv' && $owner_id) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="laporan-pendapatan' . ($bulan !== '' ? '-' . $bulan : '') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID Rental', 'Tanggal', 'Penyewa', 'Kendaraan', 'Plat', 'Status', 'Total sewa', 'Potongan platform', 'Pendapatan pemilik']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id_rental'], $r['waktu_pinjam'], $r['nama_pelanggan'], trim($r['merk'] . ' ' . $r['tipe']), $r['plat_mobil'], $r['status_rental'], (int)$r['total_harga'], (int)bagian_platform($r['total_harga']), (int)bagian_pemilik($r['total_harga'])]);
    }
    fclose($out);
    exit();
}

$deret = $owner_id ? owner_deret_pendapatan($kon, $owner_id) : [];
$owner_page = 'laporan'; $owner_title = 'Laporan Pendapatan'; include 'owner_header.php';
$qs = $bulan !== '' ? 'bulan=' . urlencode($bulan) . '&amp;' : '';
?>
<div class="page-head">
    <div><h1>Laporan pendapatan</h1><p>Rincian transaksi dan pembagian hasil antara Anda dan platform.</p></div>
    <div class="actions no-print">
        <a class="btn secondary" href="?<?php echo $qs; ?>unduh=csv">Unduh CSV</a>
        <button class="btn" type="button" data-print>Cetak / PDF</button>
    </div>
</div>
<?php if (!$owner_id): ?><div class="alert alert--bad">Akun owner belum terhubung dengan data pemilik mobil.</div><?php else: ?>

<form method="get" class="searchbar no-print">
    <label for="bulan" class="sr">Bulan</label>
    <input id="bulan" type="month" name="bulan" value="<?php echo esc($bulan); ?>">
    <button type="submit">Terapkan</button>
    <?php if ($bulan !== ''): ?><a class="btn secondary" href="owner_laporan.php">Semua waktu</a><?php endif; ?>
</form>

<section class="stats">
    <div class="stat glass"><span>Transaksi</span><b><?php echo $ringkas['total']; ?></b><small>Tidak termasuk yang dibatalkan</small></div>
    <div class="stat glass"><span>Total sewa selesai</span><b><?php echo fmt_rp($ringkas['omzet_selesai']); ?></b></div>
    <div class="stat glass"><span>Komisi platform <?php echo (int)(KOMISI_PLATFORM * 100); ?>%</span><b><?php echo fmt_rp($potongan); ?></b></div>
    <div class="stat glass stat--hi"><span>Pendapatan bersih</span><b><?php echo fmt_rp($bersih); ?></b><small>Akan bertambah <?php echo fmt_rp($ringkas['menunggu']); ?> saat rental berjalan selesai</small></div>
</section>

<?php echo owner_grafik($deret, 'Pendapatan 6 bulan terakhir', 'Bagian Anda dari rental yang sudah selesai.'); ?>

<div class="panel-head"><h2>Detail laporan<?php echo $bulan !== '' ? ' &middot; ' . esc(nama_bulan(substr($bulan, 5, 2)) . ' ' . substr($bulan, 0, 4)) : ''; ?></h2></div>
<div class="table-wrap"><table>
    <tr><th>ID</th><th>Penyewa</th><th>Kendaraan</th><th>Status</th><th>Total sewa</th><th>Platform <?php echo (int)(KOMISI_PLATFORM * 100); ?>%</th><th>Pendapatan Anda</th><th class="no-print">Invoice</th></tr>
    <?php if ($rows): foreach ($rows as $r): ?>
    <tr>
        <td>#<?php echo (int)$r['id_rental']; ?></td>
        <td><?php echo esc($r['nama_pelanggan'] ?? '-'); ?></td>
        <td><?php echo esc(trim($r['merk'] . ' ' . $r['tipe'])); ?><br><span class="muted"><?php echo esc($r['plat_mobil']); ?></span></td>
        <td><span class="status"><?php echo esc($r['status_rental']); ?></span></td>
        <td class="num"><?php echo fmt_rp($r['total_harga']); ?></td>
        <td class="num"><?php echo fmt_rp(bagian_platform($r['total_harga'])); ?></td>
        <td class="num"><?php echo fmt_rp(bagian_pemilik($r['total_harga'])); ?></td>
        <td class="no-print"><a href="owner_invoice.php?id=<?php echo (int)$r['id_rental']; ?>">Invoice</a></td>
    </tr>
    <?php endforeach; else: ?><tr><td colspan="8">Belum ada data laporan untuk periode ini.</td></tr><?php endif; ?>
</table></div>
<?php endif; ?>
<?php include 'owner_footer.php'; ?>
