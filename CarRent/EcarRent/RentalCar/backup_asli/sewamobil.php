<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$nama_user = $_SESSION["nama"] ?? '';

$query_pelanggan = mysqli_query($kon, "SELECT id_pelanggan FROM tb_pelanggan WHERE nama = '$nama_user' LIMIT 1");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);

if (!$data_pelanggan) {
    echo "<script>
        alert('Data pelanggan untuk akun ini belum ditemukan!');
        window.location.href = 'user_dashboard.php';
    </script>";
    exit();
}

$id_pelanggan = $data_pelanggan['id_pelanggan'];
$plat_mobil = $_GET['plat_mobil'] ?? '';

$query_mobil = mysqli_query($kon, "SELECT * FROM tb_mobil WHERE plat_mobil = '$plat_mobil'");
$mobil = mysqli_fetch_assoc($query_mobil);

if (!$mobil) {
    echo "Data mobil tidak ditemukan!";
    exit();
}

$pesan = "";

if (isset($_POST['proses_sewa'])) {
    $waktu_pinjam = $_POST['waktu_pinjam'];
    $waktu_kembali = $_POST['waktu_kembali'];
    $harga_rental = $_POST['harga_rental'];

    $tgl1 = new DateTime($waktu_pinjam);
    $tgl2 = new DateTime($waktu_kembali);
    $selisih = $tgl1->diff($tgl2)->days;

    if ($selisih <= 0) {
        $selisih = 1;
    }

    $total_harga = $harga_rental * $selisih;

    $sql_rental = "INSERT INTO tb_rental (id_pelanggan, plat_mobil, waktu_pinjam, waktu_kembali, total_harga, status_rental)
                   VALUES ('$id_pelanggan', '$plat_mobil', '$waktu_pinjam', '$waktu_kembali', '$total_harga', 'Proses')";

    $result_rental = mysqli_query($kon, $sql_rental);

    if ($result_rental) {
        mysqli_query($kon, "UPDATE tb_mobil SET status = 'Disewa' WHERE plat_mobil = '$plat_mobil'");

        echo "<script>
            alert('Berhasil memesan mobil! Status mobil berubah menjadi Disewa.');
            window.location.href = 'user_dashboard.php';
        </script>";
        exit();
    } else {
        $pesan = "Gagal memproses pesanan: " . mysqli_error($kon);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pemesanan Mobil</title>
</head>
<body>

<h2>Form Sewa Mobil</h2>

<?php if (!empty($pesan)): ?>
    <p style="color:red;"><b><?php echo $pesan; ?></b></p>
<?php endif; ?>

<table border="0" cellpadding="5">
    <tr>
        <td><strong>Plat Nomor</strong></td>
        <td>: <?php echo htmlspecialchars($mobil['plat_mobil']); ?></td>
    </tr>
    <tr>
        <td><strong>Merk & Tipe</strong></td>
        <td>: <?php echo htmlspecialchars($mobil['merk'] . " - " . $mobil['tipe']); ?></td>
    </tr>
    <tr>
        <td><strong>Tahun</strong></td>
        <td>: <?php echo htmlspecialchars($mobil['tahun']); ?></td>
    </tr>
    <tr>
        <td><strong>Status Saat Ini</strong></td>
        <td>: <b><?php echo htmlspecialchars($mobil['status']); ?></b></td>
    </tr>
</table>

<br>
<hr>
<br>

<form action="" method="POST">
    <table border="0" cellpadding="5">
        <tr>
            <td><label>Waktu Pinjam</label></td>
            <td>:</td>
            <td><input type="datetime-local" name="waktu_pinjam" required></td>
        </tr>
        <tr>
            <td><label>Waktu Kembali</label></td>
            <td>:</td>
            <td><input type="datetime-local" name="waktu_kembali" required></td>
        </tr>
        <tr>
            <td><label>Harga Rental (Per Hari)</label></td>
            <td>:</td>
            <td><input type="number" name="harga_rental" value="150000" required></td>
        </tr>
        <tr>
            <td colspan="3">
                <br>
                <button type="submit" name="proses_sewa">Konfirmasi Sewa</button>
            </td>
        </tr>
    </table>
</form>

<br>
<a href="user_dashboard.php">Kembali ke Dashboard</a>

</body>
</html>