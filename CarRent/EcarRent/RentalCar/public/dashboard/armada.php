<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once("../../config/koneksi.php");
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}
$role = strtolower($_SESSION['role'] ?? 'user');
if ($role === 'owner') { header('Location: owner_armada.php'); exit(); }
if ($role === 'admin' || $role === 'petugas') { header('Location: dashboard.php?menu=mobil'); exit(); }
$cari = trim($_GET['cari'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$sort = $_GET['sort'] ?? 'nama';
$where = ["status_persetujuan = 'Disetujui'"];
if ($cari !== '') {
    $cari_safe = mysqli_real_escape_string($kon, $cari);
    $where[] = "(plat_mobil LIKE '%$cari_safe%' OR merk LIKE '%$cari_safe%' OR tipe LIKE '%$cari_safe%')";
}
if ($status_filter === 'tersedia') {
    $where[] = "LOWER(status) NOT IN ('disewa','di sewa')";
} elseif ($status_filter === 'disewa') {
    $where[] = "LOWER(status) IN ('disewa','di sewa')";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$order_sql = 'merk ASC, tipe ASC';
if ($sort === 'tahun_baru') $order_sql = 'tahun DESC, merk ASC';
if ($sort === 'tahun_lama') $order_sql = 'tahun ASC, merk ASC';
if ($sort === 'status') $order_sql = "CASE WHEN LOWER(status) IN ('disewa','di sewa') THEN 1 ELSE 0 END, merk ASC";
$query_mobil = mysqli_query($kon, "SELECT * FROM tb_mobil $where_sql ORDER BY $order_sql");
include_once(__DIR__ . '/../partials/car_card.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Armada - eCar-Rent</title>
<link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
<script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

<div class="page-head">
    <div>
        <h1>Pilih mobil yang pas</h1>
        <p><?php echo (int)mysqli_num_rows($query_mobil); ?> mobil cocok dengan pencarian Anda.</p>
    </div>
</div>

<form method="GET" action="armada.php">
    <div class="f"><label for="cari">Cari</label><input type="text" id="cari" name="cari" value="<?php echo htmlspecialchars($cari); ?>" placeholder="Merek, tipe, atau plat nomor"></div>
    <div class="f"><label for="status">Status</label>
        <select name="status" id="status">
            <option value="">Semua</option>
            <option value="tersedia" <?php echo $status_filter === 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
            <option value="disewa" <?php echo $status_filter === 'disewa' ? 'selected' : ''; ?>>Sedang disewa</option>
        </select>
    </div>
    <div class="f"><label for="sort">Urutkan</label>
        <select name="sort" id="sort">
            <option value="nama" <?php echo $sort === 'nama' ? 'selected' : ''; ?>>Nama mobil</option>
            <option value="tahun_baru" <?php echo $sort === 'tahun_baru' ? 'selected' : ''; ?>>Tahun terbaru</option>
            <option value="tahun_lama" <?php echo $sort === 'tahun_lama' ? 'selected' : ''; ?>>Tahun terlama</option>
            <option value="status" <?php echo $sort === 'status' ? 'selected' : ''; ?>>Tersedia lebih dulu</option>
        </select>
    </div>
    <button type="submit">Tampilkan</button>
    <a class="btn secondary" href="armada.php">Atur ulang</a>
</form>

<div class="chips">
    <button type="button" class="chip" id="favOnly" aria-pressed="false">Hanya favorit saya <b>0</b></button>
</div>
<div class="empty glass" id="favEmpty" hidden>
    <h3>Belum ada favorit di daftar ini</h3>
    <p>Tekan ikon hati pada foto mobil untuk menyimpannya, lalu pilih lagi &quot;Hanya favorit saya&quot;.</p>
</div>

<?php if (mysqli_num_rows($query_mobil) > 0): ?>
    <div class="cars">
        <?php while ($mobil = mysqli_fetch_assoc($query_mobil)) { render_car_card($mobil); } ?>
    </div>
<?php else: ?>
    <div class="empty glass">
        <h3>Belum ada mobil yang cocok</h3>
        <p>Coba kata kunci lain, atau atur ulang filter untuk melihat semua armada.</p>
        <a class="btn" href="armada.php">Lihat semua armada</a>
    </div>
<?php endif; ?>
</body>
</html>
