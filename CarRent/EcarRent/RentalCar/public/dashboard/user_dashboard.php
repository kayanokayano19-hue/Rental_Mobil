<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once("../../config/koneksi.php");
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}
$username = $_SESSION["username"] ?? '';
$nama_user = $_SESSION["nama"] ?? $username;
$nama_safe = mysqli_real_escape_string($kon, $nama_user);
$query_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nama = '$nama_safe' LIMIT 1");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$jumlah_mobil = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) AS total FROM tb_mobil WHERE status_persetujuan = 'Disetujui'"))['total'] ?? 0;
$jumlah_tersedia = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) AS total FROM tb_mobil WHERE status_persetujuan = 'Disetujui' AND LOWER(status) NOT IN ('disewa','di sewa')"))['total'] ?? 0;
$jumlah_rental = 0;
if ($data_pelanggan) {
    $id_pelanggan = $data_pelanggan['id_pelanggan'];
    $jumlah_rental = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) AS total FROM tb_rental WHERE id_pelanggan = '$id_pelanggan'"))['total'] ?? 0;
}
$query_mobil = mysqli_query($kon, "SELECT * FROM tb_mobil WHERE status_persetujuan = 'Disetujui' ORDER BY CASE WHEN LOWER(status) IN ('disewa','di sewa') THEN 1 ELSE 0 END, plat_mobil ASC LIMIT 6");
include_once(__DIR__ . '/../partials/car_card.php');

/* Rental yang masih berjalan (terbaru) */
$rental_aktif = null;
if ($data_pelanggan) {
    $stmt_ra = $kon->prepare("SELECT r.*, m.merk, m.tipe FROM tb_rental r LEFT JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil WHERE r.id_pelanggan = ? AND LOWER(r.status_rental) NOT IN ('selesai','dikembalikan','dibatalkan','batal','ditolak') ORDER BY r.id_rental DESC LIMIT 1");
    $stmt_ra->bind_param("i", $id_pelanggan);
    $stmt_ra->execute();
    $rental_aktif = $stmt_ra->get_result()->fetch_assoc();
    $stmt_ra->close();
}

/* Merek terbanyak untuk chip pintasan */
$merk_list = [];
$q_merk = mysqli_query($kon, "SELECT merk, COUNT(*) AS c FROM tb_mobil WHERE status_persetujuan = 'Disetujui' AND merk <> '' GROUP BY merk ORDER BY c DESC, merk ASC LIMIT 6");
while ($q_merk && ($rm = mysqli_fetch_assoc($q_merk))) { $merk_list[] = $rm['merk']; }

/* Mobil yang bisa dipilih di kalkulator */
$calc_mobil = mysqli_query($kon, "SELECT plat_mobil, merk, tipe, harga_rental FROM tb_mobil WHERE status_persetujuan = 'Disetujui' AND LOWER(status) NOT IN ('disewa','di sewa') AND harga_rental > 0 ORDER BY merk, tipe");
$saldo_user = (int)($data_pelanggan['saldo'] ?? 0);
function fmt_waktu($t) { $ts = strtotime($t); return $ts ? date('d M Y, H:i', $ts) : '-'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beranda - eCar-Rent</title>
<link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
<script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($nama_user, ENT_QUOTES, 'UTF-8'); ?>">

<section class="hero glass">
    <span class="eyebrow"><i class="dot"></i> <?php echo (int)$jumlah_tersedia; ?> mobil siap jalan sekarang</span>
    <h1>Mau ke mana hari ini, <span class="grad"><?php echo htmlspecialchars(strtok($nama_user, ' ')); ?></span>?</h1>
    <p>Ketik merek, tipe, atau plat nomor. Kami tampilkan mobil yang siap jalan, tanpa antre dan tanpa ribet.</p>
    <form method="GET" action="armada.php">
        <input type="text" name="cari" placeholder="Contoh: Avanza, Innova, BP 1234" aria-label="Cari mobil" required>
        <button type="submit">Cari mobil</button>
    </form>
    <?php if (!empty($merk_list)): ?>
    <div class="chips hero__chips" aria-label="Merek populer">
        <?php foreach ($merk_list as $mk): ?>
            <a class="chip" href="armada.php?cari=<?php echo urlencode($mk); ?>"><?php echo htmlspecialchars($mk); ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="lane" aria-hidden="true"></div>
</section>

<div class="marquee" aria-hidden="true"><div class="marquee__t">
    <div class="marquee__g"><span>Siap jalan</span><span>Tarif transparan</span><span>Akun terverifikasi</span><span>Nota digital</span><span>Tanpa antre</span><span>Hitung mundur sewa</span></div>
    <div class="marquee__g"><span>Siap jalan</span><span>Tarif transparan</span><span>Akun terverifikasi</span><span>Nota digital</span><span>Tanpa antre</span><span>Hitung mundur sewa</span></div>
</div></div>

<section class="stats">
    <div class="stat glass"><span>Total armada</span><b><?php echo (int)$jumlah_mobil; ?> mobil</b></div>
    <div class="stat glass stat--hi" data-pct="<?php echo $jumlah_mobil ? round($jumlah_tersedia / $jumlah_mobil * 100) : 0; ?>"><span>Siap disewa sekarang</span><b><?php echo (int)$jumlah_tersedia; ?> mobil</b></div>
    <div class="stat glass"><span>Rental saya</span><b><?php echo (int)$jumlah_rental; ?> transaksi</b></div>
    <div class="stat glass"><span>Saldo</span><b>Rp <?php echo number_format((int)($data_pelanggan['saldo'] ?? 0), 0, ',', '.'); ?></b><small><a href="isi_saldo.php">Isi saldo</a></small></div>
</section>

<?php if ($rental_aktif):
    $nama_mobil_ra = trim(($rental_aktif['merk'] ?? '') . ' ' . ($rental_aktif['tipe'] ?? ''));
    $iso_mulai = (new DateTime($rental_aktif['waktu_pinjam']))->format('c');
    $iso_kembali = (new DateTime($rental_aktif['waktu_kembali']))->format('c');
?>
<section class="trip glass" data-start="<?php echo htmlspecialchars($iso_mulai); ?>" data-end="<?php echo htmlspecialchars($iso_kembali); ?>" aria-label="Rental yang sedang berjalan">
    <div class="trip__car">
        <span class="plat plat--lg"><?php echo htmlspecialchars($rental_aktif['plat_mobil']); ?></span>
        <h3><?php echo htmlspecialchars($nama_mobil_ra !== '' ? $nama_mobil_ra : 'Mobil rental'); ?></h3>
        <p>Status <span class="status"><?php echo htmlspecialchars($rental_aktif['status_rental']); ?></span></p>
    </div>
    <div class="trip__mid">
        <div class="trip__times">
            <span><small>Ambil</small><b><?php echo htmlspecialchars(fmt_waktu($rental_aktif['waktu_pinjam'])); ?></b></span>
            <span><small>Kembali</small><b><?php echo htmlspecialchars(fmt_waktu($rental_aktif['waktu_kembali'])); ?></b></span>
        </div>
        <div class="trip__bar" role="img" aria-label="Kemajuan masa sewa"><i></i></div>
    </div>
    <div class="trip__left"><small>Sisa waktu sewa</small><span class="trip__count">...</span></div>
    <div class="trip__links">
        <a class="btn secondary" href="riwayat.php">Lihat riwayat</a>
        <a class="btn secondary" href="../rental/invoice.php?id=<?php echo (int)$rental_aktif['id_rental']; ?>" target="_blank" rel="noopener">Lihat invoice</a>
    </div>
</section>
<?php endif; ?>

<?php if (!$data_pelanggan): ?>
    <div class="alert alert--bad">
        <b>Data diri Anda belum lengkap.</b> Lengkapi dulu supaya bisa menyewa mobil.
        <a class="btn btn--inline" href="lengkapi_pelanggan.php">Lengkapi data</a>
    </div>
<?php else: ?>
    <p>Status akun: <span class="status"><?php echo htmlspecialchars($data_pelanggan['status_verifikasi']); ?></span></p>
<?php endif; ?>

<div class="panel-head">
    <div><h2>Armada pilihan</h2><p>Yang tersedia ditampilkan lebih dulu.</p></div>
    <a class="btn secondary" href="armada.php">Lihat semua armada</a>
</div>

<div class="chips" aria-label="Pintasan pencarian">
    <a class="chip chip--live" href="armada.php?status=tersedia">Siap jalan</a>
    <?php foreach ($merk_list as $mk): ?>
        <a class="chip" href="armada.php?cari=<?php echo urlencode($mk); ?>"><?php echo htmlspecialchars($mk); ?></a>
    <?php endforeach; ?>
</div>

<?php if (mysqli_num_rows($query_mobil) > 0): ?>
    <div class="cars">
        <?php while ($mobil = mysqli_fetch_assoc($query_mobil)) { render_car_card($mobil); } ?>
    </div>
<?php else: ?>
    <div class="empty glass"><h3>Armada belum tersedia</h3><p>Mobil baru sedang disiapkan. Cek lagi sebentar lagi.</p></div>
<?php endif; ?>

<div class="panel-head"><div><h2>Rencanakan sewa Anda</h2><p>Hitung biaya dulu, dan simpan mobil yang Anda taksir.</p></div></div>
<div class="duo">
    <section class="calc glass" data-saldo="<?php echo $saldo_user; ?>">
        <h3>Hitung estimasi sewa</h3>
        <p>Pilih mobil dan lama sewa. Total dibandingkan dengan saldo Anda.</p>
        <label for="calcCar">Mobil</label>
        <select id="calcCar">
            <option value="" selected>Pilih mobil yang tersedia</option>
            <?php while ($cm = mysqli_fetch_assoc($calc_mobil)): ?>
                <option value="<?php echo htmlspecialchars($cm['plat_mobil']); ?>" data-harga="<?php echo (int)$cm['harga_rental']; ?>"><?php echo htmlspecialchars(trim($cm['merk'] . ' ' . $cm['tipe'])); ?> (Rp <?php echo number_format((float)$cm['harga_rental'], 0, ',', '.'); ?>/hari)</option>
            <?php endwhile; ?>
        </select>
        <label for="calcDays">Lama sewa</label>
        <div class="calc__days">
            <div class="stepper">
                <button type="button" class="stepbtn" data-step="-1" aria-label="Kurangi satu hari">&minus;</button>
                <input type="number" id="calcDays" min="1" max="60" value="1" inputmode="numeric">
                <button type="button" class="stepbtn" data-step="1" aria-label="Tambah satu hari">+</button>
            </div>
            <span>hari</span>
        </div>
        <div class="calc__out" aria-live="polite"></div>
        <a class="btn block" id="calcGo" href="#" aria-disabled="true">Lanjut ke penyewaan</a>
    </section>

    <section class="favs glass" id="favPanel">
        <h3>Favorit saya</h3>
        <p>Tersimpan di perangkat ini.</p>
        <ul class="favs__list"></ul>
        <p class="favs__empty">Belum ada favorit. Tekan ikon hati pada foto mobil untuk menyimpannya di sini.</p>
    </section>
</div>

<div class="panel-head"><div><h2>Kenapa eCar-Rent</h2><p>Tiga hal yang kami jaga di setiap sewa.</p></div></div>
<section class="tiles">
    <article class="tile glass"><span class="tile__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3 5 14h6l-1 7 8-11h-6z"/></svg></span><h3>Siap jalan</h3><p>Status mobil selalu terbarui, jadi yang Anda lihat tersedia benar-benar bisa dipakai.</p></article>
    <article class="tile glass"><span class="tile__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.3 8-8 9-4.7-1-8-4.5-8-9V6z"/><path d="m9 12 2 2 4-4"/></svg></span><h3>Akun terverifikasi</h3><p>Data pelanggan dicek admin sebelum sewa, demi keamanan pemilik dan penyewa.</p></article>
    <article class="tile glass"><span class="tile__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></svg></span><h3>Tarif yang jelas</h3><p>Harga per hari dan perkiraan total tampil sebelum Anda mengonfirmasi.</p></article>
</section>

<div class="panel-head"><h2>Cara menyewa</h2></div>
<ol class="steps">
    <li>Pilih armada<small>Cari atau telusuri daftar mobil.</small></li>
    <li>Cek detailnya<small>Lihat kondisi, tahun, dan tarif per hari.</small></li>
    <li>Tentukan waktu<small>Isi tanggal pinjam dan kembali.</small></li>
    <li>Konfirmasi<small>Saldo terpotong, rental tercatat.</small></li>
    <li>Pantau<small>Semua transaksi ada di Riwayat.</small></li>
</ol>
</body>
</html>
