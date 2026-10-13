<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once("../../../config/koneksi.php");
include_once("../Controler/updatepemilik.php");

$pemilikController = new PemilikController($kon);

$id_pemilik_param = $_GET['id_pemilik'] ?? '';
$data = $pemilikController->getPemilik($id_pemilik_param); 

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

$pesan = "";

if (isset($_POST['update'])) {
    $nama      = $_POST['nama'];
    $email     = $_POST['email'];
    $password  = $_POST['password'];
    $no_hp     = $_POST['no_hp'];
    $alamat    = $_POST['alamat'];

   
    $pesan = $pemilikController->updateDataPemilik($id_pemilik_param, $nama, $email, $password, $no_hp, $alamat);
    flash_go($pesan, '../../dashboard/dashboard.php', '../../assets/app.js?v=2040');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Pemilik</title>
    <link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg">
    <script src="../../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <a href="../../dashboard/dashboard.php">Home</a>
    <br/><br/>
    
    <form name="update_data" method="POST" action="">
        <table border="0" cellpadding="5">    
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
                </td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" required>
                </td>
            </tr>
             <tr>
                <td>password</td>
                <td>:</td>
                <td>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($data['password']); ?>" required>
                </td>
            </tr>
            <tr>
                <td>No HP</td>
                <td>:</td>
                <td>
                    <input type="text" name="no_hp" value="<?php echo htmlspecialchars($data['no_hp']); ?>" required>
                </td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>
                    <textarea name="alamat" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                </td>
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