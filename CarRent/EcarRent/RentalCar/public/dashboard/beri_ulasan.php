<?php
include_once(__DIR__ . '/../../config/guard.php');
include_once(__DIR__ . '/../../config/koneksi.php');
include_once(__DIR__ . '/../../config/ulasan.php');
wajib_peran(['user']);

$p = pelanggan_saat_ini($kon);
$id_rental = (int)($_REQUEST['id_rental'] ?? 0);
if (!$p || $id_rental <= 0 || !ulasan_aktif($kon)) { header('Location: riwayat.php?pesan=gagal'); exit(); }

$r = null;
$res = jalankan($kon, 'SELECT r.id_rental, r.plat_mobil, r.status_rental, m.merk, m.tipe FROM tb_rental r LEFT JOIN tb_mobil m ON m.plat_mobil = r.plat_mobil WHERE r.id_rental = ? AND r.id_pelanggan = ? LIMIT 1', 'ii', [$id_rental, $p['id_pelanggan']]);
if ($res) { $r = mysqli_fetch_assoc($res); }
if (!$r || !in_array(strtolower(trim($r['status_rental'])), ['selesai', 'dikembalikan'], true)) { header('Location: riwayat.php?pesan=gagal'); exit(); }

$sudah = (int)satu_nilai($kon, 'SELECT COUNT(*) FROM tb_ulasan WHERE id_rental = ?', 'i', [$id_rental]);
if ($sudah > 0) { header('Location: riwayat.php'); exit(); }

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $komentar = trim((string)($_POST['komentar'] ?? ''));
    if (!csrf_valid()) {
        $galat = 'Sesi formulir sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($rating < 1 || $rating > 5) {
        $galat = 'Pilih jumlah bintang dari 1 sampai 5.';
    } else {
        $komentar = mb_substr($komentar, 0, 500);
        $stmt = mysqli_prepare($kon, 'INSERT INTO tb_ulasan (id_rental, id_pelanggan, plat_mobil, rating, komentar) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iisis', $id_rental, $p['id_pelanggan'], $r['plat_mobil'], $rating, $komentar);
        if (mysqli_stmt_execute($stmt)) { header('Location: riwayat.php?pesan=ulasan'); exit(); }
        $galat = 'Ulasan gagal disimpan. Coba lagi.';
    }
}
$nama_mobil = trim(($r['merk'] ?? '') . ' ' . ($r['tipe'] ?? ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Ulasan</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
    <link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo esc($_SESSION['nama'] ?? $_SESSION['username'] ?? ''); ?>">
<a class="btn secondary" href="riwayat.php">Kembali ke riwayat</a>
<div class="page-head"><div><h1>Bagaimana perjalanannya?</h1><p>Ulasan Anda membantu penyewa lain memilih <?php echo esc($nama_mobil !== '' ? $nama_mobil : 'mobil ini'); ?> (<?php echo esc($r['plat_mobil']); ?>).</p></div></div>
<?php if ($galat !== ''): ?><p class="alert alert--bad" role="alert"><?php echo esc($galat); ?></p><?php endif; ?>
<form method="post" class="review-form">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id_rental" value="<?php echo $id_rental; ?>">
    <fieldset class="stars">
        <legend class="sr">Penilaian</legend>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" id="s<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required><label for="s<?php echo $i; ?>" title="<?php echo $i; ?> bintang"><?php echo ikon_bintang(); ?><span class="sr"><?php echo $i; ?> bintang</span></label>
        <?php endfor; ?>
    </fieldset>
    <label for="komentar">Ceritakan pengalaman Anda (opsional)</label>
    <textarea id="komentar" name="komentar" rows="4" maxlength="500" placeholder="Kondisi mobil, kebersihan, kenyamanan..."></textarea>
    <button type="submit">Kirim ulasan</button>
</form>
</body>
</html>
