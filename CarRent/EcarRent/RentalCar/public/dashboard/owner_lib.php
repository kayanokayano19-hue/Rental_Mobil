<?php
/* Bagian bersama untuk semua halaman portal owner. Pasang: include 'owner_lib.php'; di baris atas. */
include_once(__DIR__ . '/../../config/guard.php');
include_once(__DIR__ . '/../../config/koneksi.php');
include_once(__DIR__ . '/../../config/aturan.php');
include_once(__DIR__ . '/../../config/ulasan.php');
wajib_peran(['owner']);

function owner_data($kon) {
    $stmt = mysqli_prepare($kon, 'SELECT * FROM tb_pemilik_mobil WHERE id_user = ? LIMIT 1');
    if (!$stmt) { return null; }
    $uid = (int)($_SESSION['user_id'] ?? 0);
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}
function owner_escape($v) { return esc($v); }
function owner_rp($v) { return fmt_rp($v); }

$owner = owner_data($kon);
$owner_id = (int)($owner['id_pemilik'] ?? 0);
$nama = $owner['nama'] ?? ($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Owner');

define('SQL_RENTAL_SELESAI', "LOWER(TRIM(r.status_rental)) IN ('selesai','dikembalikan')");
define('SQL_RENTAL_AKTIF', "LOWER(TRIM(r.status_rental)) NOT IN ('dibatalkan','batal','ditolak')");

/* Semua mobil milik owner beserta jumlah sewa dan omzet selesai. */
function owner_daftar_mobil($kon, $owner_id) {
    $sql = "SELECT m.*,
        (SELECT COUNT(*) FROM tb_rental r WHERE r.plat_mobil = m.plat_mobil AND " . SQL_RENTAL_AKTIF . ") AS jml_sewa,
        (SELECT COALESCE(SUM(r.total_harga),0) FROM tb_rental r WHERE r.plat_mobil = m.plat_mobil AND " . SQL_RENTAL_SELESAI . ") AS omzet
        FROM tb_mobil m WHERE m.id_pemilik = ? ORDER BY m.plat_mobil";
    $res = jalankan($kon, $sql, 'i', [$owner_id]);
    $out = [];
    while ($res && ($m = mysqli_fetch_assoc($res))) { $out[] = $m; }
    return $out;
}

function owner_badge_persetujuan($s) {
    $k = strtolower((string)$s);
    $kelas = $k === 'disetujui' ? 'ok' : ($k === 'ditolak' ? 'bad' : 'warn');
    return '<span class="badge badge--' . $kelas . '">' . esc($s) . '</span>';
}

/* Kartu satu mobil untuk portal owner. */
function owner_kartu_mobil($m, $kon) {
    $uri = foto_uri($m['foto'] ?? '');
    $disetujui = strtolower($m['status_persetujuan']) === 'disetujui';
    $tersedia = mobil_tersedia($m['status']);
    $nama_mobil = trim($m['merk'] . ' ' . $m['tipe']);
    ob_start(); ?>
    <article class="ocar glass">
        <div class="ocar__img">
            <?php if ($uri): ?><img src="<?php echo $uri; ?>" alt="<?php echo esc($nama_mobil); ?>" loading="lazy"><?php else: ?><span>Tanpa foto</span><?php endif; ?>
        </div>
        <div class="ocar__body">
            <div class="ocar__top">
                <span class="plat"><?php echo esc($m['plat_mobil']); ?></span>
                <span class="ocar__badges">
                    <?php echo owner_badge_persetujuan($m['status_persetujuan']); ?>
                    <?php if ($disetujui): ?><span class="badge badge--<?php echo $tersedia ? 'ok' : 'info'; ?>"><?php echo $tersedia ? 'Tersedia' : 'Disewa'; ?></span><?php endif; ?>
                </span>
            </div>
            <h3><?php echo esc($nama_mobil); ?> <small>(<?php echo esc($m['tahun']); ?>)</small></h3>
            <div class="car__price"><?php echo fmt_rp($m['harga_rental']); ?><small>per hari</small></div>
            <?php echo label_rating($kon, $m['plat_mobil']); ?>
            <dl class="ocar__nums">
                <div><dt>Disewa</dt><dd><?php echo (int)$m['jml_sewa']; ?> kali</dd></div>
                <div><dt>Pendapatan Anda</dt><dd><?php echo fmt_rp(bagian_pemilik($m['omzet'])); ?></dd></div>
            </dl>
            <?php if (strtolower($m['status_persetujuan']) === 'menunggu'): ?><p class="muted">Menunggu ditinjau admin. Belum tampil ke pelanggan.</p><?php endif; ?>
            <?php if (strtolower($m['status_persetujuan']) === 'ditolak'): ?><p class="muted">Pengajuan ditolak. Perbarui data lalu hubungi admin.</p><?php endif; ?>
            <div class="actions">
                <a class="act act--view" href="owner_detail_mobil.php?plat=<?php echo urlencode($m['plat_mobil']); ?>">Detail</a>
                <a class="act act--edit" href="owner_edit_mobil.php?plat=<?php echo urlencode($m['plat_mobil']); ?>">Ubah tarif &amp; kondisi</a>
            </div>
        </div>
    </article>
    <?php return ob_get_clean();
}

/* Pendapatan pemilik per bulan (6 bulan) dari rental selesai. */
function owner_deret_pendapatan($kon, $owner_id) {
    $hasil = [];
    $res = jalankan($kon, "SELECT DATE_FORMAT(r.waktu_pinjam, '%Y-%m') AS ym, SUM(r.total_harga) AS omzet FROM tb_rental r INNER JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil WHERE m.id_pemilik = ? AND " . SQL_RENTAL_SELESAI . " AND r.waktu_pinjam >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH) GROUP BY ym", 'i', [$owner_id]);
    while ($res && ($x = mysqli_fetch_assoc($res))) { $hasil[$x['ym']] = bagian_pemilik($x['omzet']); }
    return deret_enam_bulan($hasil);
}

function owner_grafik($deret, $judul, $sub) {
    $maks = 0; $jml = 0;
    foreach ($deret as $d) { $maks = max($maks, $d['nilai']); $jml += $d['nilai']; }
    ob_start(); ?>
    <section class="chart glass" aria-label="<?php echo esc($judul); ?>">
        <div class="chart__head">
            <div><h3><?php echo esc($judul); ?></h3><p><?php echo esc($sub); ?></p></div>
            <div class="chart__sum"><b><?php echo fmt_rp($jml); ?></b><small>total 6 bulan</small></div>
        </div>
        <div class="bars" role="img" aria-label="Grafik batang pendapatan per bulan">
            <?php foreach ($deret as $d): $t = $maks > 0 ? max(3, round($d['nilai'] / $maks * 100)) : 3; ?>
            <div class="bar<?php echo $d['nilai'] <= 0 ? ' bar--zero' : ''; ?>">
                <span class="bar__val"><?php echo $d['nilai'] > 0 ? fmt_ringkas($d['nilai']) : '-'; ?></span>
                <i style="--h:<?php echo (int)$t; ?>%"></i>
                <small><?php echo esc($d['label']); ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php return ob_get_clean();
}
