<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once("../../../config/koneksi.php");
include_once("../Controler/viewdata.php");
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Data Mobil</title>
    <link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg">
    <script src="../../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <a href="../../dashboard/dashboard.php">Home</a>
    <br/><br/>
    <table class="kv">
            <tr>
                <td>Plat Mobil</td>
                <td><?php echo isset($plat_mobil) ? htmlspecialchars($plat_mobil) : ''; ?></td>
            </tr>
            <tr>
                <td>ID Pemilik</td>
                <td><?php echo isset($id_pemilik) ? htmlspecialchars($id_pemilik) : ''; ?></td>
            </tr>
            <tr>
                <td>Merk</td>
                <td><?php echo isset($merk) ? htmlspecialchars($merk) : ''; ?></td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td><?php echo isset($tipe) ? htmlspecialchars($tipe) : ''; ?></td>
            </tr>
            <tr>
                <td>Status</td>
                <td><?php echo isset($status) ? htmlspecialchars($status) : ''; ?></td>
            </tr>
            <tr>
                <td>Tahun</td>
                <td><?php echo isset($tahun) ? htmlspecialchars($tahun) : ''; ?></td>
            </tr>
            <tr>
                <td>Kondisi Mobil</td>
                <td><?php echo isset($kondisi_mobil) ? htmlspecialchars($kondisi_mobil) : ''; ?></td>
            </tr>
            <tr>
                <td>Riwayat Kerusakan</td>
                <td><?php echo isset($riwayat_kerusakan) ? htmlspecialchars($riwayat_kerusakan) : ''; ?></td>
            </tr>
            <tr>
                <td>Foto</td>
                <td>
                    <?php 
                    if (!empty($foto)) {
                        echo "<img src='data:image/jpeg;base64," . base64_encode($foto) . "' alt='Foto Mobil' width='250'>";
                    } else {
                        echo "Tidak ada foto";
                    }
                    ?>
                </td>
            </tr>
        </table>
</body>
</html>