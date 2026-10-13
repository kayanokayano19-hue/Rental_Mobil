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

if (!$data_pelanggan) {
    flash_go('Silakan lengkapi data pelanggan terlebih dahulu!', 'lengkapi_pelanggan.php');
    exit();
}

$id_pelanggan = $data_pelanggan['id_pelanggan'];
$plat_mobil = $_GET['plat_mobil'] ?? '';
$plat_safe = mysqli_real_escape_string($kon, $plat_mobil);
$query_mobil = mysqli_query($kon, "SELECT * FROM tb_mobil WHERE plat_mobil = '$plat_safe' AND status_persetujuan = 'Disetujui' LIMIT 1");
$mobil = mysqli_fetch_assoc($query_mobil);

if (!$mobil) {
    echo "Data mobil tidak ditemukan!";
    exit();
}

$status_clean = strtolower(trim($mobil['status']));
if (in_array($status_clean, ['disewa', 'di sewa'])) {
    flash_go('Mobil ini sedang disewa!', 'detailmobil.php?plat_mobil=' . urlencode($plat_mobil));
    exit();
}

$harga_default = (float)($mobil['harga_rental'] ?? 0);
$pesan = "";

if (isset($_POST['proses_sewa'])) {
    $waktu_pinjam = $_POST['waktu_pinjam'];
    $waktu_kembali = $_POST['waktu_kembali'];
    $harga_rental = (float)$_POST['harga_rental'];

    if ($harga_rental <= 0) {
        $pesan = "Harga rental tidak valid.";
    } else {
        $tgl1 = new DateTime($waktu_pinjam);
        $tgl2 = new DateTime($waktu_kembali);

        if ($tgl2 <= $tgl1) {
            $pesan = "Waktu kembali harus lebih besar dari waktu pinjam.";
        } else {
            $durasi_detik = $tgl2->getTimestamp() - $tgl1->getTimestamp();
            $selisih = (int)ceil($durasi_detik / 86400);
            if ($selisih < 1) {
                $selisih = 1;
            }

            $total_harga = $harga_rental * $selisih;
            $saldo = (int)$data_pelanggan['saldo'];

            if ($saldo < $total_harga) {
                $pesan = "Saldo tidak cukup. Saldo Anda Rp " . number_format($saldo, 0, ',', '.') . ", sedangkan total sewa Rp " . number_format($total_harga, 0, ',', '.') . ".";
            } else {
                mysqli_begin_transaction($kon);
                $sql_rental = "INSERT INTO tb_rental (id_pelanggan, plat_mobil, waktu_pinjam, waktu_kembali, harga_rental, total_harga, status_rental) VALUES ('$id_pelanggan', '$plat_safe', '$waktu_pinjam', '$waktu_kembali', '$harga_rental', '$total_harga', 'Proses')";
                $result_rental = mysqli_query($kon, $sql_rental);
                $result_mobil = mysqli_query($kon, "UPDATE tb_mobil SET status = 'Disewa' WHERE plat_mobil = '$plat_safe' AND status NOT IN ('Disewa', 'Di Sewa')");
                $mobil_berubah = mysqli_affected_rows($kon);
                $result_saldo = mysqli_query($kon, "UPDATE tb_pelanggan SET saldo = saldo - $total_harga WHERE id_pelanggan = '$id_pelanggan' AND saldo >= $total_harga");
                $saldo_berubah = mysqli_affected_rows($kon);

                if ($result_rental && $result_mobil && $mobil_berubah > 0 && $result_saldo && $saldo_berubah > 0) {
                    mysqli_commit($kon);
                    flash_go('Pemesanan berhasil!', 'riwayat.php');
                    exit();
                } else {
                    mysqli_rollback($kon);
                    $pesan = "Pemesanan gagal diproses. Silakan coba lagi.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Sewa Mobil</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<a class="btn secondary" href="detailmobil.php?plat_mobil=<?php echo urlencode($plat_mobil); ?>">Kembali ke detail mobil</a>
<div class="page-head page-head--spaced"><div><h1>Atur jadwal sewa</h1><p>Pilih waktu pinjam dan kembali. Total dihitung otomatis.</p></div></div>
<?php if ($pesan): ?><p class="alert alert--bad"><?php echo htmlspecialchars($pesan); ?></p><?php endif; ?>
<div class="booking">
    <form method="POST">
        <div class="grid2">
            <div><label for="wp">Waktu pinjam</label><input type="datetime-local" id="wp" name="waktu_pinjam" required></div>
            <div><label for="wk">Waktu kembali</label><input type="datetime-local" id="wk" name="waktu_kembali" required></div>
        </div>
        <input type="hidden" name="harga_rental" value="<?php echo $harga_default; ?>">
        <div class="sum glass">
            <div><span>Durasi</span><strong id="durasi_sewa">-</strong></div>
            <div><span>Perkiraan total</span><strong id="total_sewa">Rp 0</strong></div>
        </div>
        <button type="submit" name="proses_sewa" class="block">Konfirmasi sewa</button>
    </form>
    <aside class="glass booking__car">
        <h3><?php echo htmlspecialchars($mobil['merk'] . ' ' . $mobil['tipe']); ?></h3>
        <table class="kv">
            <tr><td>Plat</td><td><?php echo htmlspecialchars($mobil['plat_mobil']); ?></td></tr>
            <tr><td>Tahun</td><td><?php echo htmlspecialchars($mobil['tahun']); ?></td></tr>
            <tr><td>Kondisi</td><td><?php echo htmlspecialchars($mobil['kondisi_mobil']); ?></td></tr>
            <tr><td>Tarif</td><td>Rp <?php echo number_format($harga_default, 0, ',', '.'); ?> / hari</td></tr>
            <tr><td>Penyewa</td><td><?php echo htmlspecialchars($nama_user); ?></td></tr>
            <tr><td>Saldo</td><td>Rp <?php echo number_format((int)$data_pelanggan['saldo'], 0, ',', '.'); ?></td></tr>
        </table>
    </aside>
</div>
</body>
</html>
