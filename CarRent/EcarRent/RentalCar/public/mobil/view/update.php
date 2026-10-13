<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once("../../../config/koneksi.php");
include_once("../Controler/updatemobil.php");

$mobilController = new MobilController($kon);

$plat_mobil_param = $_GET['plat_mobil'] ?? '';
$data = $mobilController->getDataMobil($plat_mobil_param);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

$pesan = "";

if (isset($_POST['update'])) {
    $plat_mobil        = $_POST['plat_mobil'];
    $id_pemilik        = $_POST['id_pemilik'] ?? '';
    $merk              = $_POST['merk'];
    $tipe              = $_POST['tipe'];
    $status            = $_POST['status'];
    $tahun             = $_POST['tahun'];
    $kondisi_mobil     = $_POST['kondisi_mobil'];
    $riwayat_kerusakan = $_POST['riwayat_kerusakan'];
    $harga_rental      = (float)($_POST['harga_rental'] ?? 0);

    $pesan = $mobilController->updateDataMobil($plat_mobil, $id_pemilik, $merk, $tipe, $status, $tahun, $kondisi_mobil, $riwayat_kerusakan, $harga_rental);

    flash_go($pesan, '../../dashboard/dashboard.php', '../../assets/app.js?v=2040');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Mobil</title>
    <link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg">
    <script src="../../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <a href="../../dashboard/dashboard.php">Home</a>
    <br/><br/>
    
    
    <form name="update_data" method="POST" action="" enctype="multipart/form-data">
        <table border="0" cellpadding="5">
            <tr>
                <td>Plat Mobil</td>
                <td>:</td>
                <td>
                    <input type="text" name="plat_mobil" value="<?php echo htmlspecialchars($data['plat_mobil']); ?>" readonly>
                </td>
            </tr>
            <tr>
                <td>ID Pemilik</td>
                <td>:</td>
                <td>
                    <select name="id_pemilik" required>
                        <option value="">-- Pilih Pemilik Mobil --</option>
                        <?php
                        $query_pemilik = mysqli_query($kon, "SELECT id_pemilik, nama FROM tb_pemilik_mobil");
                        while ($p = mysqli_fetch_assoc($query_pemilik)) {
                            $selected = ($p['id_pemilik'] == $data['id_pemilik']) ? "selected" : "";
                            echo "<option value='" . $p['id_pemilik'] . "' $selected>" . $p['id_pemilik'] . " - " . $p['nama'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Merk</td>
                <td>:</td>
                <td><input type="text" name="merk" value="<?php echo htmlspecialchars($data['merk']); ?>" required></td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td>:</td>
                <td><input type="text" name="tipe" value="<?php echo htmlspecialchars($data['tipe']); ?>" required></td>
            </tr>
            <tr>
                <td>Status</td>
                <td>:</td>
                <td><input type="text" name="status" value="<?php echo htmlspecialchars($data['status']); ?>" required></td>
            </tr>
            <tr>
                <td>Tahun</td>
                <td>:</td>
                <td><input type="number" name="tahun" value="<?php echo htmlspecialchars($data['tahun']); ?>" required></td>
            </tr>
            <tr>
                <td>Harga Rental / Hari (Rp)</td>
                <td>:</td>
                <td><input type="number" name="harga_rental" min="0" step="1000" inputmode="numeric" value="<?php echo htmlspecialchars((int)$data['harga_rental']); ?>" required></td>
            </tr>
            <tr>
                <td>Kondisi Mobil</td>
                <td>:</td>
                <td><textarea name="kondisi_mobil" required><?php echo htmlspecialchars($data['kondisi_mobil']); ?></textarea></td>
            </tr>
            <tr>
                <td>Riwayat Kerusakan</td>
                <td>:</td>
                <td><textarea name="riwayat_kerusakan" required><?php echo htmlspecialchars($data['riwayat_kerusakan']); ?></textarea></td>
            </tr>
            <tr>
                <td>Foto Saat Ini</td>
                <td>:</td>
                <td>
                    <?php 
                    if (!empty($data['foto'])) {
                        echo "<img src='data:image/jpeg;base64," . base64_encode($data['foto']) . "' alt='Foto Mobil' width='150'><br>";
                    } else {
                        echo "Tidak ada foto<br>";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Ganti Foto Baru</td>
                <td>:</td>
                <td><input type="file" name="foto"> <small></small></td>
            </tr>
            <tr>
                <td colspan="3">
                    <br>
                    <button type="submit" name="update">Simpan Perubahan</button>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>