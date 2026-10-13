<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$nama_user = $_SESSION["nama"] ?? $_SESSION["username"];
$nama_safe = mysqli_real_escape_string($kon, $nama_user);
$query_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nama = '$nama_safe' LIMIT 1");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$pesan = "";

if (!$data_pelanggan) {
    header("Location: lengkapi_pelanggan.php");
    exit();
}

if (isset($_POST['isi_saldo'])) {
    $nominal = (int)$_POST['nominal'];
    if ($nominal <= 0) {
        $pesan = "Nominal saldo harus lebih dari 0.";
    } else {
        $id_pelanggan = $data_pelanggan['id_pelanggan'];
        $sql = "UPDATE tb_pelanggan SET saldo = saldo + $nominal WHERE id_pelanggan = '$id_pelanggan'";
        if (mysqli_query($kon, $sql)) {
            $pesan = "Saldo berhasil ditambahkan.";
            $query_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE id_pelanggan = '$id_pelanggan' LIMIT 1");
            $data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
        } else {
            $pesan = "Gagal menambah saldo: " . mysqli_error($kon);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Saldo</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<div class="page-head"><div><h1>Isi saldo</h1><p>Saldo dipakai untuk membayar sewa. Isi sesuai kebutuhan.</p></div></div>
<section class="stats">
    <div class="stat glass stat--hi"><span>Saldo saat ini</span><b>Rp <?php echo number_format((int)$data_pelanggan['saldo'], 0, ',', '.'); ?></b></div>
</section>
<?php if ($pesan): ?><p class="alert <?php echo (stripos($pesan, 'berhasil') !== false) ? 'alert--ok' : 'alert--bad'; ?>"><?php echo htmlspecialchars($pesan); ?></p><?php endif; ?>
<form method="POST">
    <label for="nominal">Nominal isi saldo</label>
    <input type="number" id="nominal" name="nominal" min="1" placeholder="Contoh: 500000" required>
    <div class="quick" data-quick="nominal" aria-label="Pilihan cepat"></div>
    <button type="submit" name="isi_saldo">Isi saldo sekarang</button>
</form>
</body>
</html>
