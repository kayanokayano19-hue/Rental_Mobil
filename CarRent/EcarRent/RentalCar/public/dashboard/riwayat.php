<?php
include_once(__DIR__ . '/../../config/guard.php');
include_once(__DIR__ . '/../../config/koneksi.php');
include_once(__DIR__ . '/../../config/aturan.php');
include_once(__DIR__ . '/../../config/ulasan.php');
wajib_peran(['user']);

$p = pelanggan_saat_ini($kon);
$id_pelanggan = (int)($p['id_pelanggan'] ?? 0);
$filter = strtolower(trim($_GET['status'] ?? ''));

$ada_ulasan = ulasan_aktif($kon);
$sql = 'SELECT r.*, m.merk, m.tipe' . ($ada_ulasan ? ', (SELECT COUNT(*) FROM tb_ulasan u WHERE u.id_rental = r.id_rental) AS sudah_ulas' : ', 0 AS sudah_ulas')
     . ' FROM tb_rental r LEFT JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil WHERE r.id_pelanggan = ? ORDER BY r.id_rental DESC';
$hasil = jalankan($kon, $sql, 'i', [$id_pelanggan]);
$semua = [];
while ($hasil && ($row = mysqli_fetch_assoc($hasil))) { $semua[] = $row; }

function kelompok_status($s) {
    $s = strtolower(trim($s));
    if (in_array($s, ['selesai', 'dikembalikan'], true)) { return 'selesai'; }
    if (in_array($s, ['dibatalkan', 'batal', 'ditolak'], true)) { return 'batal'; }
    return 'jalan';
}
$jumlah = ['jalan' => 0, 'selesai' => 0, 'batal' => 0];
$total_belanja = 0;
foreach ($semua as $r) {
    $k = kelompok_status($r['status_rental']);
    $jumlah[$k]++;
    if ($k !== 'batal') { $total_belanja += (float)$r['total_harga'] + (float)$r['denda']; }
}
$tampil = array_filter($semua, function ($r) use ($filter) {
    return $filter === '' || kelompok_status($r['status_rental']) === $filter;
});

$notif = [
    'batal'   => ['ok', 'Pesanan dibatalkan. Seluruh biaya sudah kembali ke saldo Anda.'],
    'tidak'   => ['bad', 'Pesanan ini tidak bisa dibatalkan lagi karena sudah dikonfirmasi admin.'],
    'ulasan'  => ['ok', 'Terima kasih, ulasan Anda tersimpan.'],
    'gagal'   => ['bad', 'Aksi gagal diproses. Coba lagi.'],
];
$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Rental</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
    <link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo esc($_SESSION['nama'] ?? $_SESSION['username'] ?? ''); ?>">
<div class="page-head">
    <div><h1>Riwayat rental</h1><p>Semua perjalanan Anda, dari yang berjalan sampai yang sudah selesai.</p></div>
    <a class="btn" href="armada.php">Sewa mobil lagi</a>
</div>

<?php if (isset($notif[$pesan])): ?><p class="alert alert--<?php echo $notif[$pesan][0]; ?>" role="status"><?php echo esc($notif[$pesan][1]); ?></p><?php endif; ?>

<section class="stats">
    <div class="stat glass"><span>Total sewa</span><b><?php echo count($semua); ?></b></div>
    <div class="stat glass"><span>Sedang berjalan</span><b><?php echo $jumlah['jalan']; ?></b></div>
    <div class="stat glass"><span>Selesai</span><b><?php echo $jumlah['selesai']; ?></b></div>
    <div class="stat glass stat--hi"><span>Total pengeluaran</span><b><?php echo fmt_rp($total_belanja); ?></b><small>Tidak termasuk yang dibatalkan</small></div>
</section>

<div class="chips" aria-label="Filter status">
    <?php foreach (['' => 'Semua (' . count($semua) . ')', 'jalan' => 'Berjalan (' . $jumlah['jalan'] . ')', 'selesai' => 'Selesai (' . $jumlah['selesai'] . ')', 'batal' => 'Dibatalkan (' . $jumlah['batal'] . ')'] as $k => $lbl): ?>
        <a class="chip<?php echo $filter === $k ? ' is-on' : ''; ?>" href="?<?php echo $k !== '' ? 'status=' . $k : ''; ?>"><?php echo esc($lbl); ?></a>
    <?php endforeach; ?>
</div>

<?php if ($tampil): ?>
<div class="rides">
    <?php foreach ($tampil as $r):
        $k = kelompok_status($r['status_rental']);
        $st = strtolower(trim($r['status_rental']));
        $telat = $k === 'jalan' && strtotime($r['waktu_kembali']) < time();
        $hari = jumlah_hari($r['waktu_pinjam'], $r['waktu_kembali']);
        $nama_mobil = trim(($r['merk'] ?? '') . ' ' . ($r['tipe'] ?? ''));
    ?>
    <article class="ride glass ride--<?php echo $k; ?>">
        <header class="ride__top">
            <div>
                <span class="plat"><?php echo esc($r['plat_mobil']); ?></span>
                <h3><?php echo esc($nama_mobil !== '' ? $nama_mobil : 'Mobil'); ?></h3>
            </div>
            <div class="ride__state">
                <span class="status"><?php echo esc($r['status_rental']); ?></span>
                <?php if ($telat): ?><span class="badge badge--bad">Terlambat</span><?php endif; ?>
            </div>
        </header>
        <div class="ride__trip">
            <div><small>Ambil</small><b><?php echo esc(fmt_tgl($r['waktu_pinjam'])); ?></b></div>
            <i aria-hidden="true"></i>
            <div><small>Kembali</small><b><?php echo esc(fmt_tgl($r['waktu_kembali'])); ?></b></div>
        </div>
        <dl class="ride__nums">
            <div><dt>Durasi</dt><dd><?php echo $hari; ?> hari</dd></div>
            <div><dt>Tarif/hari</dt><dd><?php echo fmt_rp($r['harga_rental']); ?></dd></div>
            <div><dt>Total sewa</dt><dd><?php echo fmt_rp($r['total_harga']); ?></dd></div>
            <?php if ((float)$r['denda'] > 0): ?><div class="is-bad"><dt>Denda telat</dt><dd><?php echo fmt_rp($r['denda']); ?></dd></div><?php endif; ?>
        </dl>
        <?php if ($telat): ?><p class="ride__warn">Waktu kembali sudah lewat. Segera kembalikan mobil agar denda tidak bertambah (<?php echo (int)(DENDA_PERSEN_PER_JAM * 100); ?>% tarif harian per jam setelah toleransi <?php echo (int)DENDA_TOLERANSI_MENIT; ?> menit).</p><?php endif; ?>
        <footer class="actions">
            <a class="act act--view" href="../rental/invoice.php?id=<?php echo (int)$r['id_rental']; ?>" target="_blank" rel="noopener">Lihat nota</a>
            <?php if ($st === 'proses') echo form_aksi('batal_rental.php', ['id_rental' => $r['id_rental']], 'Batalkan pesanan', 'danger', 'Batalkan pesanan ini? Seluruh biaya dikembalikan ke saldo Anda.'); ?>
            <?php if ($k === 'selesai' && $ada_ulasan && !(int)$r['sudah_ulas']): ?><a class="act act--ok" href="beri_ulasan.php?id_rental=<?php echo (int)$r['id_rental']; ?>">Beri ulasan</a><?php endif; ?>
            <?php if ($k === 'selesai' && (int)$r['sudah_ulas']): ?><span class="muted">Ulasan terkirim</span><?php endif; ?>
            <?php if ($k !== 'jalan'): ?><a class="act act--edit" href="detailmobil.php?plat_mobil=<?php echo urlencode($r['plat_mobil']); ?>">Sewa lagi</a><?php endif; ?>
        </footer>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty glass"><h3>Belum ada rental di sini</h3><p><?php echo $filter === '' ? 'Pilih mobil pertama Anda di menu Armada.' : 'Tidak ada rental dengan status ini.'; ?></p><a class="btn" href="armada.php">Lihat armada</a></div>
<?php endif; ?>
</body>
</html>
