<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}
$plat_mobil = $_GET['plat_mobil'] ?? '';
$plat_safe = mysqli_real_escape_string($kon, $plat_mobil);
$query = mysqli_query($kon, "SELECT m.*, p.nama AS nama_pemilik FROM tb_mobil m LEFT JOIN tb_pemilik_mobil p ON m.id_pemilik = p.id_pemilik WHERE m.plat_mobil = '$plat_safe' AND m.status_persetujuan = 'Disetujui' LIMIT 1");
$mobil = mysqli_fetch_assoc($query);
if (!$mobil) {
    echo "Data mobil tidak ditemukan!";
    exit();
}
$status = strtolower(trim($mobil['status']));
$tersedia = !in_array($status, ['disewa', 'di sewa']);
$harga_rental = (float)($mobil['harga_rental'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Mobil - Rental Mobil</title>
<link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
<script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
<a class="btn secondary" href="armada.php">Kembali ke armada</a>
<section class="detail">
    <div class="detail__photo glass">
        <?php if (!empty($mobil['foto'])): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($mobil['foto']); ?>" alt="<?php echo htmlspecialchars($mobil['merk'] . ' ' . $mobil['tipe']); ?>">
        <?php else: ?>
            <div class="car__ph" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16v-5l2-5h10l2 5v5"/><path d="M3 16h18v3H3z"/><path d="M7.5 13h.01M16.5 13h.01"/></svg></div>
        <?php endif; ?>
    </div>
    <aside class="detail__book glass">
        <span class="badge badge--<?php echo $tersedia ? 'ok' : 'info'; ?>"><?php echo $tersedia ? 'Tersedia' : 'Sedang disewa'; ?></span>
        <span class="plat plat--lg"><?php echo htmlspecialchars($mobil['plat_mobil']); ?></span>
        <h1><?php echo htmlspecialchars($mobil['merk'] . ' ' . $mobil['tipe']); ?></h1>
        <div class="car__price">Rp <?php echo number_format($harga_rental, 0, ',', '.'); ?><small>per hari</small></div>
        <?php if ($tersedia): ?>
            <a class="btn block" href="sewamobil.php?plat_mobil=<?php echo urlencode($mobil['plat_mobil']); ?>">Sewa mobil ini</a>
            <p class="detail__hint">Anda bisa mengatur tanggal dan melihat total sebelum konfirmasi.</p>
        <?php else: ?>
            <p class="alert">Mobil ini sedang dipakai penyewa lain. Lihat armada lain yang siap jalan.</p>
            <a class="btn secondary block" href="armada.php?status=tersedia">Lihat yang tersedia</a>
        <?php endif; ?>
    </aside>
</section>
<div class="panel-head"><h2>Spesifikasi dan kondisi</h2></div>
<table class="kv">
<tr><td>Plat nomor</td><td><?php echo htmlspecialchars($mobil['plat_mobil']); ?></td></tr>
<tr><td>Pemilik</td><td><?php echo htmlspecialchars($mobil['nama_pemilik'] ?? '-'); ?></td></tr>
<tr><td>Tahun</td><td><?php echo htmlspecialchars($mobil['tahun']); ?></td></tr>
<tr><td>Kondisi</td><td><?php echo htmlspecialchars($mobil['kondisi_mobil']); ?></td></tr>
<tr><td>Riwayat kerusakan</td><td><?php echo htmlspecialchars($mobil['riwayat_kerusakan'] ?: 'Tidak ada'); ?></td></tr>
</table>
<?php
include_once(__DIR__ . '/../../config/guard.php');
include_once(__DIR__ . '/../../config/ulasan.php');
if (ulasan_aktif($kon)):
    $rev = jalankan($kon, 'SELECT u.rating, u.komentar, u.dibuat_pada, p.nama FROM tb_ulasan u LEFT JOIN tb_pelanggan p ON p.id_pelanggan = u.id_pelanggan WHERE u.plat_mobil = ? ORDER BY u.id_ulasan DESC LIMIT 10', 's', [$mobil['plat_mobil']]);
    $daftar = [];
    while ($rev && ($x = mysqli_fetch_assoc($rev))) { $daftar[] = $x; }
?>
<div class="panel-head"><h2>Ulasan penyewa</h2><?php echo label_rating($kon, $mobil['plat_mobil']); ?></div>
<?php if ($daftar): ?>
<div class="reviews">
    <?php foreach ($daftar as $x): $nm = trim((string)$x['nama']); ?>
    <article class="review glass">
        <div class="review__top"><b><?php echo esc($nm !== '' ? $nm : 'Penyewa'); ?></b><span class="review__stars" aria-label="<?php echo (int)$x['rating']; ?> dari 5 bintang"><?php echo str_repeat('&#9733;', (int)$x['rating']) . str_repeat('<span class="off">&#9733;</span>', 5 - (int)$x['rating']); ?></span></div>
        <?php if (trim((string)$x['komentar']) !== ''): ?><p><?php echo nl2br(esc($x['komentar'])); ?></p><?php endif; ?>
        <small class="muted"><?php echo esc(date('d M Y', strtotime($x['dibuat_pada']))); ?></small>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty glass"><p>Belum ada ulasan untuk mobil ini.</p></div>
<?php endif; endif; ?>
</body>
</html>
