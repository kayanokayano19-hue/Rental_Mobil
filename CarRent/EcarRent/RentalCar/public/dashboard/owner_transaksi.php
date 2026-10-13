<?php
include 'owner_lib.php';
$filter = strtolower(trim($_GET['status'] ?? ''));
$cari = trim($_GET['cari'] ?? '');
$data = false;
if ($owner_id) {
    $kondisi = ['m.id_pemilik = ?']; $tipe = 'i'; $param = [$owner_id];
    if ($filter === 'jalan')   { $kondisi[] = "LOWER(TRIM(r.status_rental)) NOT IN ('selesai','dikembalikan','dibatalkan','batal','ditolak')"; }
    if ($filter === 'selesai') { $kondisi[] = SQL_RENTAL_SELESAI; }
    if ($filter === 'batal')   { $kondisi[] = "LOWER(TRIM(r.status_rental)) IN ('dibatalkan','batal','ditolak')"; }
    if ($cari !== '') {
        $like = '%' . $cari . '%';
        $kondisi[] = '(r.plat_mobil LIKE ? OR p.nama LIKE ? OR CAST(r.id_rental AS CHAR) LIKE ?)';
        $tipe .= 'sss'; array_push($param, $like, $like, $like);
    }
    $data = jalankan($kon, 'SELECT r.*, p.nama AS nama_pelanggan, m.merk, m.tipe FROM tb_rental r INNER JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil LEFT JOIN tb_pelanggan p ON p.id_pelanggan = r.id_pelanggan WHERE ' . implode(' AND ', $kondisi) . ' ORDER BY r.id_rental DESC', $tipe, $param);
}
$link = '?' . ($cari !== '' ? 'cari=' . urlencode($cari) . '&amp;' : '');
$owner_page = 'transaksi'; $owner_title = 'Riwayat Transaksi'; include 'owner_header.php';
?>
<div class="page-head"><div><h1>Riwayat transaksi</h1><p>Semua penyewaan dari armada milik Anda.</p></div><a class="btn secondary" href="owner_laporan.php">Laporan pendapatan</a></div>
<?php if (!$owner_id): ?><div class="alert alert--bad">Akun owner belum terhubung dengan data pemilik mobil.</div><?php else: ?>
<div class="panel-head">
    <div class="chips" aria-label="Filter status">
        <?php foreach (['' => 'Semua', 'jalan' => 'Berjalan', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $k => $lbl): ?>
            <a class="chip<?php echo $filter === $k ? ' is-on' : ''; ?>" href="<?php echo $link . ($k !== '' ? 'status=' . $k : ''); ?>"><?php echo esc($lbl); ?></a>
        <?php endforeach; ?>
    </div>
    <form method="get" class="searchbar">
        <?php if ($filter !== ''): ?><input type="hidden" name="status" value="<?php echo esc($filter); ?>"><?php endif; ?>
        <input type="text" name="cari" value="<?php echo esc($cari); ?>" placeholder="Cari plat, penyewa, ID..." aria-label="Cari transaksi">
        <button type="submit">Cari</button>
    </form>
</div>
<div class="table-wrap"><table>
    <tr><th>ID</th><th>Penyewa</th><th>Kendaraan</th><th>Periode</th><th>Status</th><th>Total sewa</th><th>Bagian Anda</th><th>Aksi</th></tr>
    <?php if ($data && mysqli_num_rows($data)): while ($r = mysqli_fetch_assoc($data)): ?>
    <tr>
        <td>#<?php echo (int)$r['id_rental']; ?></td>
        <td><?php echo esc($r['nama_pelanggan'] ?? '-'); ?></td>
        <td><strong><?php echo esc(trim($r['merk'] . ' ' . $r['tipe'])); ?></strong><br><span class="muted"><?php echo esc($r['plat_mobil']); ?></span></td>
        <td><?php echo esc(fmt_tgl($r['waktu_pinjam'])); ?><br><span class="muted">s/d <?php echo esc(fmt_tgl($r['waktu_kembali'])); ?></span></td>
        <td><span class="status"><?php echo esc($r['status_rental']); ?></span></td>
        <td class="num"><?php echo fmt_rp($r['total_harga']); ?></td>
        <td class="num"><?php echo fmt_rp(bagian_pemilik($r['total_harga'])); ?></td>
        <td><a href="owner_detail_rental.php?id=<?php echo (int)$r['id_rental']; ?>">Detail</a> <a href="owner_invoice.php?id=<?php echo (int)$r['id_rental']; ?>">Invoice</a></td>
    </tr>
    <?php endwhile; else: ?><tr><td colspan="8">Tidak ada transaksi yang cocok.</td></tr><?php endif; ?>
</table></div>
<?php endif; ?>
<?php include 'owner_footer.php'; ?>
