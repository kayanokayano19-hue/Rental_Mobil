<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once("../../config/koneksi.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$nama_user = $_SESSION["nama"] ?? $username;
$role = strtolower($_SESSION["role"] ?? 'user');

$nama_safe = mysqli_real_escape_string($kon, $nama_user);
$query_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nama = '$nama_safe' LIMIT 1");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$id_pelanggan = $data_pelanggan['id_pelanggan'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Rental Mobil</title>
</head>
<body>

<h2>Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h2>
<p>Role Anda: <b><?php echo strtoupper(htmlspecialchars($role)); ?></b></p>

<?php if ($data_pelanggan): ?>
<p>
    Saldo Anda:
    <b>Rp <?php echo number_format($data_pelanggan['saldo'], 0, ',', '.'); ?></b> |
    Status Verifikasi:
    <b><?php echo ($data_pelanggan['status_verifikasi'] == 1) ? 'Terverifikasi' : 'Belum Terverifikasi'; ?></b>
</p>
<?php endif; ?>

<a href="../../logout.php">Logout</a>
<hr>

<h3>Katalog Mobil</h3>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Plat Nomor</th>
            <th>Merk</th>
            <th>Tipe</th>
            <th>Tahun</th>
            <th>Kondisi</th>
            <th>Status</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query_mobil = mysqli_query($kon, "SELECT * FROM tb_mobil");

        if (mysqli_num_rows($query_mobil) > 0) {
            while ($mobil = mysqli_fetch_assoc($query_mobil)) {
                $plat = $mobil['plat_mobil'];
                $status_mobil = $mobil['status'];
        ?>
        <tr>
            <td><?php echo htmlspecialchars($mobil['plat_mobil']); ?></td>
            <td><?php echo htmlspecialchars($mobil['merk']); ?></td>
            <td><?php echo htmlspecialchars($mobil['tipe']); ?></td>
            <td><?php echo htmlspecialchars($mobil['tahun']); ?></td>
            <td><?php echo htmlspecialchars($mobil['kondisi_mobil']); ?></td>
            <td><b><?php echo htmlspecialchars($status_mobil); ?></b></td>
            <td>
                <?php if (!empty($mobil['foto'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($mobil['foto']); ?>" width="100">
                <?php else: ?>
                    Tidak ada foto
                <?php endif; ?>
            </td>
            <td>
                <?php
                $status_clean = strtolower(trim($status_mobil));
                if ($status_clean === 'disewa' || $status_clean === 'di sewa') {
                    echo "<span style='color:red;'>Sudah Disewa</span>";
                } else {
                    echo "<a href='sewamobil.php?plat_mobil=" . urlencode($plat) . "'>Sewa Mobil Ini</a>";
                }
                ?>
            </td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='8' align='center'>Tidak ada data mobil.</td></tr>";
        }
        ?>
    </tbody>
</table>

<br>
<hr>

<h3>Riwayat Rental Saya</h3>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID Rental</th>
            <th>Plat Mobil</th>
            <th>Waktu Pinjam</th>
            <th>Waktu Kembali</th>
            <th>Total Harga</th>
            <th>Status Rental</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query_riwayat = mysqli_query($kon, "SELECT * FROM tb_rental WHERE id_pelanggan = '$id_pelanggan' ORDER BY id_rental DESC");

        if (mysqli_num_rows($query_riwayat) > 0) {
            while ($rental = mysqli_fetch_assoc($query_riwayat)) {
        ?>
        <tr>
            <td><?php echo htmlspecialchars($rental['id_rental']); ?></td>
            <td><?php echo htmlspecialchars($rental['plat_mobil']); ?></td>
            <td><?php echo htmlspecialchars($rental['waktu_pinjam']); ?></td>
            <td><?php echo htmlspecialchars($rental['waktu_kembali']); ?></td>
            <td>Rp <?php echo number_format($rental['total_harga'], 0, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars($rental['status_rental']); ?></td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='6' align='center'>Belum ada riwayat rental.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>