<?php
include_once(__DIR__ . '/../../config/guard.php');
include_once(__DIR__ . '/../../config/koneksi.php');
include_once(__DIR__ . '/../../config/aturan.php');
wajib_admin();

$menu = $_GET['menu'] ?? 'pelanggan';
$cari = trim($_GET['cari'] ?? '');
$filter_status = strtolower(trim($_GET['status'] ?? ''));
$menus = [
    'pelanggan'   => 'Pelanggan',
    'pengajuan'   => 'Pengajuan Verifikasi',
    'pemilik'     => 'Pemilik Mobil',
    'mobil'       => 'Armada Mobil',
    'persetujuan' => 'Pengajuan Mobil',
    'rental'      => 'Transaksi Rental',
];
if (!isset($menus[$menu])) { $menu = 'pelanggan'; }
$menu_desc = [
    'pelanggan'   => 'Semua pelanggan yang sudah mendaftar.',
    'pengajuan'   => 'Akun yang menunggu keputusan Anda.',
    'pemilik'     => 'Mitra yang menitipkan mobilnya.',
    'mobil'       => 'Seluruh unit beserta tarif dan statusnya.',
    'persetujuan' => 'Mobil baru dari mitra. Baru tampil ke pelanggan setelah Anda setujui.',
    'rental'      => 'Pantau sewa yang berjalan, denda keterlambatan, dan bagi hasilnya.',
];

/* ---------- Angka ringkasan ---------- */
$sql_menunggu = "LOWER(TRIM(status_verifikasi)) NOT IN ('disetujui','terverifikasi','1','ditolak')";
$total_pelanggan = (int)satu_nilai($kon, 'SELECT COUNT(*) FROM tb_pelanggan');
$total_pemilik   = (int)satu_nilai($kon, 'SELECT COUNT(*) FROM tb_pemilik_mobil');
$total_mobil     = (int)satu_nilai($kon, "SELECT COUNT(*) FROM tb_mobil WHERE status_persetujuan = 'Disetujui'");
$total_pengajuan = (int)satu_nilai($kon, "SELECT COUNT(*) FROM tb_pelanggan WHERE $sql_menunggu");
$total_mobil_menunggu = (int)satu_nilai($kon, "SELECT COUNT(*) FROM tb_mobil WHERE status_persetujuan <> 'Disetujui'");
$rental_berjalan = (int)satu_nilai($kon, "SELECT COUNT(*) FROM tb_rental WHERE LOWER(TRIM(status_rental)) NOT IN ('selesai','dikembalikan','dibatalkan','batal','ditolak')");
$omzet_selesai = (float)satu_nilai($kon, "SELECT COALESCE(SUM(total_harga),0) FROM tb_rental WHERE LOWER(TRIM(status_rental)) IN ('selesai','dikembalikan')");
$denda_total   = (float)satu_nilai($kon, "SELECT COALESCE(SUM(denda),0) FROM tb_rental WHERE LOWER(TRIM(status_rental)) IN ('selesai','dikembalikan')");
$pendapatan_platform = bagian_platform($omzet_selesai) + $denda_total;

/* ---------- Grafik omzet 6 bulan ---------- */
$hasil_bulan = [];
$rb = mysqli_query($kon, "SELECT DATE_FORMAT(waktu_pinjam, '%Y-%m') AS ym, SUM(total_harga) AS omzet FROM tb_rental WHERE LOWER(TRIM(status_rental)) NOT IN ('dibatalkan','batal','ditolak') AND waktu_pinjam >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH) GROUP BY ym");
while ($rb && ($x = mysqli_fetch_assoc($rb))) { $hasil_bulan[$x['ym']] = (float)$x['omzet']; }
$deret = deret_enam_bulan($hasil_bulan);
$maks_bulan = 0; $jumlah_6bln = 0;
foreach ($deret as $d) { $maks_bulan = max($maks_bulan, $d['nilai']); $jumlah_6bln += $d['nilai']; }

/* ---------- Data tabel ---------- */
$data = null;
$like = '%' . $cari . '%';
if ($menu === 'pelanggan' || $menu === 'pengajuan') {
    $where = $menu === 'pengajuan' ? $sql_menunggu : '1=1';
    if ($cari !== '') {
        $data = jalankan($kon, "SELECT * FROM tb_pelanggan WHERE $where AND (nama LIKE ? OR nik_pelanggan LIKE ? OR email LIKE ? OR no_hp LIKE ?) ORDER BY id_pelanggan DESC", 'ssss', [$like, $like, $like, $like]);
    } else {
        $data = jalankan($kon, "SELECT * FROM tb_pelanggan WHERE $where ORDER BY id_pelanggan DESC");
    }
} elseif ($menu === 'pemilik') {
    $dasar = 'SELECT p.*, (SELECT COUNT(*) FROM tb_mobil m WHERE m.id_pemilik = p.id_pemilik) AS jml_mobil FROM tb_pemilik_mobil p';
    if ($cari !== '') {
        $data = jalankan($kon, "$dasar WHERE p.nama LIKE ? OR p.email LIKE ? OR p.no_hp LIKE ? ORDER BY p.id_pemilik DESC", 'sss', [$like, $like, $like]);
    } else {
        $data = jalankan($kon, "$dasar ORDER BY p.id_pemilik DESC");
    }
} elseif ($menu === 'mobil' || $menu === 'persetujuan') {
    $where = $menu === 'persetujuan' ? "m.status_persetujuan <> 'Disetujui'" : "m.status_persetujuan = 'Disetujui'";
    $dasar = 'SELECT m.*, p.nama AS nama_pemilik FROM tb_mobil m LEFT JOIN tb_pemilik_mobil p ON p.id_pemilik = m.id_pemilik';
    if ($cari !== '') {
        $data = jalankan($kon, "$dasar WHERE $where AND (m.plat_mobil LIKE ? OR m.merk LIKE ? OR m.tipe LIKE ? OR m.status LIKE ? OR p.nama LIKE ?) ORDER BY m.plat_mobil ASC", 'sssss', [$like, $like, $like, $like, $like]);
    } else {
        $data = jalankan($kon, "$dasar WHERE $where ORDER BY m.plat_mobil ASC");
    }
} elseif ($menu === 'rental') {
    $dasar = 'SELECT r.*, p.nama AS nama_pelanggan, m.merk, m.tipe FROM tb_rental r LEFT JOIN tb_pelanggan p ON p.id_pelanggan = r.id_pelanggan LEFT JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil';
    $kondisi = ['1=1'];
    $tipe = ''; $param = [];
    if ($filter_status === 'proses')  { $kondisi[] = "LOWER(TRIM(r.status_rental)) IN ('proses','diproses')"; }
    if ($filter_status === 'selesai') { $kondisi[] = "LOWER(TRIM(r.status_rental)) IN ('selesai','dikembalikan')"; }
    if ($filter_status === 'batal')   { $kondisi[] = "LOWER(TRIM(r.status_rental)) IN ('dibatalkan','batal','ditolak')"; }
    if ($filter_status === 'telat')   { $kondisi[] = "LOWER(TRIM(r.status_rental)) NOT IN ('selesai','dikembalikan','dibatalkan','batal','ditolak') AND r.waktu_kembali < NOW()"; }
    if ($cari !== '') {
        $kondisi[] = '(CAST(r.id_rental AS CHAR) LIKE ? OR r.plat_mobil LIKE ? OR p.nama LIKE ? OR r.status_rental LIKE ?)';
        $tipe = 'ssss'; $param = [$like, $like, $like, $like];
    }
    $data = jalankan($kon, $dasar . ' WHERE ' . implode(' AND ', $kondisi) . ' ORDER BY r.id_rental DESC', $tipe, $param);
}

$notif = [
    'berhasil'        => ['ok',  'Perubahan berhasil disimpan.'],
    'gagal'           => ['bad', 'Aksi gagal diproses. Coba lagi.'],
    'denda'           => ['ok',  'Rental diselesaikan. Mobil terlambat dikembalikan, denda sudah dicatat dan dipotong dari saldo pelanggan.'],
    'refund'          => ['ok',  'Rental dibatalkan. Biaya sewa dikembalikan ke saldo pelanggan.'],
    'mobil_disetujui' => ['ok',  'Mobil disetujui dan sekarang tampil di katalog pelanggan.'],
    'mobil_ditolak'   => ['ok',  'Pengajuan mobil ditolak. Pemilik bisa melihat statusnya di portal mitra.'],
    'mobil_disewa'    => ['bad', 'Mobil sedang disewa, jadi belum bisa dihapus atau ditolak.'],
    'rental_aktif'    => ['bad', 'Rental masih berjalan. Selesaikan atau batalkan dulu sebelum menghapus.'],
];
$pesan_aksi = $_GET['pesan'] ?? '';
$link_menu = '?menu=' . urlencode($menu) . ($cari !== '' ? '&cari=' . urlencode($cari) : '');
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administrator eCar-Rent</title><link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg"><script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../" data-user="<?php echo esc($_SESSION['nama'] ?? $_SESSION['username'] ?? ''); ?>">
<main>
    <div class="page-head">
        <div>
            <h1>Ruang kendali admin</h1>
            <p>Ringkasan bisnis dan seluruh data operasional di satu tempat.</p>
        </div>
    </div>

    <section class="stats stats--wide-first">
        <div class="stat glass stat--hi"><span>Pendapatan platform</span><b><?php echo fmt_rp($pendapatan_platform); ?></b><small>Komisi <?php echo (int)(KOMISI_PLATFORM * 100); ?>% dari rental selesai<?php echo $denda_total > 0 ? ' + denda' : ''; ?></small></div>
        <div class="stat glass"><span>Rental berjalan</span><b><?php echo $rental_berjalan; ?></b><small>Belum selesai atau dibatalkan</small></div>
        <div class="stat glass"><span>Unit armada</span><b><?php echo $total_mobil; ?></b><small>Sudah disetujui</small></div>
        <div class="stat glass"><span>Pelanggan</span><b><?php echo $total_pelanggan; ?></b><small><?php echo $total_pengajuan ? $total_pengajuan . ' menunggu verifikasi' : 'Semua terverifikasi'; ?></small></div>
        <div class="stat glass"><span>Mitra pemilik</span><b><?php echo $total_pemilik; ?></b></div>
        <div class="stat glass<?php echo $total_mobil_menunggu ? ' stat--alert' : ''; ?>"><span>Mobil menunggu</span><b><?php echo $total_mobil_menunggu; ?></b><small><?php echo $total_mobil_menunggu ? '<a href="?menu=persetujuan">Tinjau sekarang</a>' : 'Tidak ada antrean'; ?></small></div>
    </section>

    <section class="chart glass" aria-label="Omzet enam bulan terakhir">
        <div class="chart__head">
            <div><h3>Omzet 6 bulan terakhir</h3><p>Total nilai sewa per bulan, tidak termasuk yang dibatalkan.</p></div>
            <div class="chart__sum"><b><?php echo fmt_rp($jumlah_6bln); ?></b><small>total 6 bulan</small></div>
        </div>
        <div class="bars" role="img" aria-label="Grafik batang omzet per bulan">
            <?php foreach ($deret as $d): $t = $maks_bulan > 0 ? max(3, round($d['nilai'] / $maks_bulan * 100)) : 3; ?>
            <div class="bar<?php echo $d['nilai'] <= 0 ? ' bar--zero' : ''; ?>">
                <span class="bar__val"><?php echo $d['nilai'] > 0 ? fmt_ringkas($d['nilai']) : '-'; ?></span>
                <i style="--h:<?php echo (int)$t; ?>%"></i>
                <small><?php echo esc($d['label']); ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <nav class="tabs" aria-label="Bagian data">
        <?php foreach ($menus as $key => $label): ?><a href="?menu=<?php echo $key; ?>"<?php if ($key === $menu) echo ' class="is-active"'; ?>><?php echo esc($label); ?><?php if ($key === 'pengajuan' && $total_pengajuan) echo ' (' . $total_pengajuan . ')'; ?><?php if ($key === 'persetujuan' && $total_mobil_menunggu) echo ' (' . $total_mobil_menunggu . ')'; ?></a><?php endforeach; ?>
    </nav>

    <?php if (isset($notif[$pesan_aksi])): ?><p class="alert alert--<?php echo $notif[$pesan_aksi][0]; ?>" role="status"><?php echo esc($notif[$pesan_aksi][1]); ?></p><?php endif; ?>

    <div class="panel-head">
        <div>
            <h2><?php echo esc($menus[$menu]); ?></h2>
            <p><?php echo esc($menu_desc[$menu]); ?></p>
        </div>
        <form method="get" class="searchbar">
            <input type="hidden" name="menu" value="<?php echo esc($menu); ?>">
            <?php if ($menu === 'rental' && $filter_status !== ''): ?><input type="hidden" name="status" value="<?php echo esc($filter_status); ?>"><?php endif; ?>
            <input type="text" name="cari" value="<?php echo esc($cari); ?>" placeholder="Cari nama, email, plat..." aria-label="Cari data">
            <button type="submit">Cari</button>
        </form>
    </div>

<?php if ($menu === 'pelanggan' || $menu === 'pengajuan'): ?>
    <p><a class="btn" href="../mobil/aset/pelanggan.php">+ Tambah Pelanggan</a></p>
    <div class="table-wrap"><table>
        <tr><th>ID</th><th>Identitas</th><th>Email &amp; NIK</th><th>Alamat</th><th>Saldo</th><th>Status</th><th>Aksi</th></tr>
        <?php if ($data && mysqli_num_rows($data)): while ($r = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td>#<?php echo (int)$r['id_pelanggan']; ?></td>
            <td><strong><?php echo esc($r['nama']); ?></strong><br><?php echo esc($r['no_hp']); ?></td>
            <td><?php echo esc($r['email']); ?><br><?php echo esc($r['nik_pelanggan']); ?></td>
            <td><?php echo nl2br(esc($r['alamat'])); ?></td>
            <td class="num<?php echo (float)$r['saldo'] < 0 ? ' neg' : ''; ?>"><?php echo fmt_rp($r['saldo']); ?></td>
            <td><span class="status"><?php echo esc($r['status_verifikasi']); ?></span></td>
            <td>
                <a href="../mobil/view/viewpel.php?id_pelanggan=<?php echo (int)$r['id_pelanggan']; ?>">Lihat</a>
                <a href="../mobil/view/updatepel.php?id_pelanggan=<?php echo (int)$r['id_pelanggan']; ?>">Edit</a>
                <a href="../mobil/Controler/hapuspelanggan.php?id_pelanggan=<?php echo (int)$r['id_pelanggan']; ?>" data-confirm="Hapus data pelanggan ini?">Hapus</a>
                <?php if ($menu === 'pengajuan'): ?>
                <a href="../mobil/Controler/verifikasi.php?id_pelanggan=<?php echo (int)$r['id_pelanggan']; ?>&amp;aksi=setujui" data-confirm="Verifikasi pelanggan ini?">Verifikasi</a>
                <a href="../mobil/Controler/verifikasi.php?id_pelanggan=<?php echo (int)$r['id_pelanggan']; ?>&amp;aksi=tolak" data-confirm="Tolak pengajuan pelanggan ini?">Tolak</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; else: ?><tr><td colspan="7">Belum ada data yang cocok. Coba kata kunci lain.</td></tr><?php endif; ?>
    </table></div>

<?php elseif ($menu === 'pemilik'): ?>
    <p><a class="btn" href="../mobil/aset/pemilik.php">+ Tambah Pemilik</a></p>
    <div class="table-wrap"><table>
        <tr><th>ID</th><th>Nama</th><th>Email</th><th>No HP</th><th>Alamat</th><th>Armada</th><th>Aksi</th></tr>
        <?php if ($data && mysqli_num_rows($data)): while ($r = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td>#<?php echo (int)$r['id_pemilik']; ?></td>
            <td><strong><?php echo esc($r['nama']); ?></strong></td>
            <td><?php echo esc($r['email']); ?></td>
            <td><?php echo esc($r['no_hp']); ?></td>
            <td><?php echo esc($r['alamat']); ?></td>
            <td><?php echo (int)$r['jml_mobil']; ?> unit</td>
            <td>
                <a href="../mobil/view/viewpem.php?id_pemilik=<?php echo (int)$r['id_pemilik']; ?>">Lihat</a>
                <a href="../mobil/view/updatepem.php?id_pemilik=<?php echo (int)$r['id_pemilik']; ?>">Edit</a>
                <a href="../mobil/Controler/hapuspemilik.php?id_pemilik=<?php echo (int)$r['id_pemilik']; ?>" data-confirm="Hapus pemilik ini beserta mobil dan riwayat sewanya?">Hapus</a>
            </td>
        </tr>
        <?php endwhile; else: ?><tr><td colspan="7">Belum ada data yang cocok. Coba kata kunci lain.</td></tr><?php endif; ?>
    </table></div>

<?php elseif ($menu === 'mobil'): ?>
    <p><a class="btn" href="../mobil/view/tambah.php">+ Tambah Mobil</a></p>
    <div class="table-wrap"><table>
        <tr><th>Plat</th><th>Mobil</th><th>Pemilik</th><th>Tahun</th><th>Harga/Hari</th><th>Status</th><th>Aksi</th></tr>
        <?php if ($data && mysqli_num_rows($data)): while ($r = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><span class="plat"><?php echo esc($r['plat_mobil']); ?></span></td>
            <td><strong><?php echo esc(trim($r['merk'] . ' ' . $r['tipe'])); ?></strong></td>
            <td><?php echo esc($r['nama_pemilik'] ?? '-'); ?></td>
            <td><?php echo esc($r['tahun']); ?></td>
            <td class="num"><?php echo fmt_rp($r['harga_rental'] ?? 0); ?></td>
            <td><span class="status"><?php echo esc($r['status']); ?></span></td>
            <td>
                <a href="../mobil/view/view.php?plat_mobil=<?php echo urlencode($r['plat_mobil']); ?>">Lihat</a>
                <a href="../mobil/view/update.php?plat_mobil=<?php echo urlencode($r['plat_mobil']); ?>">Edit</a>
                <a href="../mobil/Controler/hapusmobil.php?plat_mobil=<?php echo urlencode($r['plat_mobil']); ?>" data-confirm="Hapus mobil ini?">Hapus</a>
            </td>
        </tr>
        <?php endwhile; else: ?><tr><td colspan="7">Data armada tidak ditemukan.</td></tr><?php endif; ?>
    </table></div>

<?php elseif ($menu === 'persetujuan'): ?>
    <?php if ($data && mysqli_num_rows($data)): ?>
    <div class="approve-list">
        <?php while ($r = mysqli_fetch_assoc($data)): $uri = foto_uri($r['foto'] ?? ''); $ditolak = strtolower($r['status_persetujuan']) === 'ditolak'; ?>
        <article class="approve glass">
            <div class="approve__img">
                <?php if ($uri): ?><img src="<?php echo $uri; ?>" alt="<?php echo esc(trim($r['merk'] . ' ' . $r['tipe'])); ?>" loading="lazy"><?php else: ?><span>Tanpa foto</span><?php endif; ?>
            </div>
            <div class="approve__body">
                <div class="approve__top">
                    <span class="plat"><?php echo esc($r['plat_mobil']); ?></span>
                    <span class="badge badge--<?php echo $ditolak ? 'bad' : 'warn'; ?>"><?php echo esc($r['status_persetujuan']); ?></span>
                </div>
                <h3><?php echo esc(trim($r['merk'] . ' ' . $r['tipe'])); ?> <small>(<?php echo esc($r['tahun']); ?>)</small></h3>
                <p class="muted">Diajukan oleh <b><?php echo esc($r['nama_pemilik'] ?? '-'); ?></b> &middot; tarif <b><?php echo fmt_rp($r['harga_rental']); ?></b> per hari</p>
                <dl class="approve__kv">
                    <div><dt>Kondisi</dt><dd><?php echo esc($r['kondisi_mobil']); ?></dd></div>
                    <div><dt>Riwayat kerusakan</dt><dd><?php echo esc($r['riwayat_kerusakan'] !== '' ? $r['riwayat_kerusakan'] : 'Tidak ada'); ?></dd></div>
                </dl>
                <div class="actions">
                    <?php echo form_aksi('../mobil/Controler/persetujuanmobil.php', ['plat_mobil' => $r['plat_mobil'], 'aksi' => 'setujui'], 'Setujui', 'ok', 'Setujui mobil ini? Mobil langsung tampil di katalog pelanggan.'); ?>
                    <?php if (!$ditolak) { echo form_aksi('../mobil/Controler/persetujuanmobil.php', ['plat_mobil' => $r['plat_mobil'], 'aksi' => 'tolak'], 'Tolak', 'danger', 'Tolak pengajuan mobil ini?'); } ?>
                    <a class="act act--view" href="../mobil/view/update.php?plat_mobil=<?php echo urlencode($r['plat_mobil']); ?>">Edit data</a>
                </div>
            </div>
        </article>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty glass"><h3>Tidak ada pengajuan</h3><p>Semua mobil dari mitra sudah ditinjau. Pengajuan baru akan muncul di sini.</p></div>
    <?php endif; ?>

<?php elseif ($menu === 'rental'): ?>
    <div class="chips" aria-label="Filter status">
        <?php foreach (['' => 'Semua', 'proses' => 'Berjalan', 'telat' => 'Terlambat', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $k => $lbl): ?>
            <a class="chip<?php echo $filter_status === $k ? ' is-on' : ''; ?>" href="<?php echo $link_menu . ($k !== '' ? '&amp;status=' . $k : ''); ?>"><?php echo esc($lbl); ?></a>
        <?php endforeach; ?>
    </div>
    <p><a class="btn" href="../mobil/aset/tambahrental.php">+ Buat Rental Baru</a></p>
    <div class="table-wrap"><table>
        <tr><th>ID</th><th>Pelanggan</th><th>Kendaraan</th><th>Status</th><th>Pinjam &amp; Kembali</th><th>Total</th><th>Denda</th><th>Platform <?php echo (int)(KOMISI_PLATFORM * 100); ?>%</th><th>Pemilik</th><th>Ubah status</th><th>Opsi</th></tr>
        <?php if ($data && mysqli_num_rows($data)): while ($r = mysqli_fetch_assoc($data)):
            $st = strtolower(trim($r['status_rental']));
            $akhir = rental_berakhir($st);
            $telat = !$akhir && strtotime($r['waktu_kembali']) < time();
            $denda_est = $telat ? hitung_denda($r['harga_rental'], $r['waktu_kembali']) : 0;
        ?>
        <tr>
            <td>#<?php echo (int)$r['id_rental']; ?></td>
            <td><?php echo esc($r['nama_pelanggan'] ?? '-'); ?></td>
            <td><strong><?php echo esc(trim(($r['merk'] ?? '') . ' ' . ($r['tipe'] ?? ''))); ?></strong><br><span class="muted"><?php echo esc($r['plat_mobil']); ?></span></td>
            <td><span class="status"><?php echo esc($r['status_rental']); ?></span><?php if ($telat): ?> <span class="badge badge--bad">Terlambat</span><?php endif; ?></td>
            <td>Pinjam: <?php echo esc(fmt_tgl($r['waktu_pinjam'])); ?><br>Kembali: <?php echo esc(fmt_tgl($r['waktu_kembali'])); ?></td>
            <td class="num"><?php echo fmt_rp($r['total_harga']); ?></td>
            <td class="num"><?php if ((float)$r['denda'] > 0): ?><?php echo fmt_rp($r['denda']); ?><?php elseif ($denda_est > 0): ?><span class="muted">&asymp; <?php echo fmt_rp($denda_est); ?></span><?php else: ?>-<?php endif; ?></td>
            <td class="num"><?php echo fmt_rp(bagian_platform($r['total_harga'])); ?></td>
            <td class="num"><?php echo fmt_rp(bagian_pemilik($r['total_harga'])); ?></td>
            <td>
                <div class="actions">
                <?php if (!$akhir && $st !== 'diproses') echo form_aksi('../mobil/Controler/statusrental.php', ['id_rental' => $r['id_rental'], 'aksi' => 'proses'], 'Proses', 'ok', 'Konfirmasi pesanan ini?'); ?>
                <?php if (!$akhir) echo form_aksi('../mobil/Controler/statusrental.php', ['id_rental' => $r['id_rental'], 'aksi' => 'selesai'], 'Selesai', 'ok', $telat ? 'Mobil terlambat dikembalikan. Denda dihitung otomatis dan dipotong dari saldo pelanggan. Lanjutkan?' : 'Tandai rental ini selesai dan mobil kembali tersedia?'); ?>
                <?php if (!$akhir) echo form_aksi('../mobil/Controler/statusrental.php', ['id_rental' => $r['id_rental'], 'aksi' => 'batal'], 'Batal', 'danger', 'Batalkan rental ini? Biaya sewa dikembalikan penuh ke saldo pelanggan.'); ?>
                <?php if ($akhir) echo '<span class="muted">-</span>'; ?>
                </div>
            </td>
            <td>
                <a href="../mobil/view/viewrental.php?id_rental=<?php echo (int)$r['id_rental']; ?>">Lihat</a>
                <a href="../rental/invoice.php?id=<?php echo (int)$r['id_rental']; ?>" target="_blank" rel="noopener">Cetak Nota</a>
                <a href="../mobil/view/updaterental.php?id_rental=<?php echo (int)$r['id_rental']; ?>">Edit</a>
                <?php if ($akhir): ?><a href="../mobil/Controler/hapusrental.php?id_rental=<?php echo (int)$r['id_rental']; ?>" data-confirm="Hapus riwayat rental ini secara permanen?">Hapus</a><?php endif; ?>
            </td>
        </tr>
        <?php endwhile; else: ?><tr><td colspan="11">Transaksi tidak ditemukan.</td></tr><?php endif; ?>
    </table></div>
<?php endif; ?>
</main>
</body>
</html>
