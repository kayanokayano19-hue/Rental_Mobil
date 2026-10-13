<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$nama_user = $_SESSION["nama"] ?? $_SESSION["username"];
$nama_safe = mysqli_real_escape_string($kon, $nama_user);
$pesan = "";

$query_user = mysqli_query($kon, "SELECT password FROM tb_user WHERE id_user = '$user_id' LIMIT 1");
$data_user = mysqli_fetch_assoc($query_user);
$password_user = $data_user['password'] ?? '';
$query_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nama = '$nama_safe' LIMIT 1");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);

if (isset($_POST['simpan'])) {
    $nik = mysqli_real_escape_string($kon, trim($_POST['nik_pelanggan']));
    $email = mysqli_real_escape_string($kon, trim($_POST['email']));
    $no_hp = mysqli_real_escape_string($kon, trim($_POST['no_hp']));
    $alamat = mysqli_real_escape_string($kon, trim($_POST['alamat']));
    $ktp_sql = "";

    if (isset($_FILES['ktp']) && $_FILES['ktp']['error'] === UPLOAD_ERR_OK) {
        $ktp = file_get_contents($_FILES['ktp']['tmp_name']);
        $ktp_sql = " ,ktp_pelanggan='" . mysqli_real_escape_string($kon, $ktp) . "'";
    }

    if ($data_pelanggan) {
        $id_pelanggan = (int)$data_pelanggan['id_pelanggan'];
        $sql = "UPDATE tb_pelanggan SET nik_pelanggan='$nik', email='$email', no_hp='$no_hp', alamat='$alamat'$ktp_sql WHERE id_pelanggan='$id_pelanggan'";
    } else {
        $sql = "INSERT INTO tb_pelanggan (nik_pelanggan, nama, email, password, no_hp, alamat, saldo, status_verifikasi, ktp_pelanggan) VALUES ('$nik', '$nama_safe', '$email', '" . mysqli_real_escape_string($kon, $password_user) . "', '$no_hp', '$alamat', 0, 'Belum Verifikasi', '')";
        if ($ktp_sql !== '') {
            $ktp = file_get_contents($_FILES['ktp']['tmp_name']);
            $ktp_safe = mysqli_real_escape_string($kon, $ktp);
            $sql = "INSERT INTO tb_pelanggan (nik_pelanggan, nama, email, password, no_hp, alamat, saldo, status_verifikasi, ktp_pelanggan) VALUES ('$nik', '$nama_safe', '$email', '" . mysqli_real_escape_string($kon, $password_user) . "', '$no_hp', '$alamat', 0, 'Belum Verifikasi', '$ktp_safe')";
        }
    }

    if (mysqli_query($kon, $sql)) {
        $pesan = "Data pelanggan berhasil disimpan.";
        $query_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nama = '$nama_safe' LIMIT 1");
        $data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
    } else {
        $pesan = "Gagal menyimpan data: " . mysqli_error($kon);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data Pelanggan</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="customer" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h2>Lengkapi Data Pelanggan</h2>
<p>Nama Akun: <b><?php echo htmlspecialchars($nama_user); ?></b></p>
<?php if ($pesan): ?><p><b><?php echo htmlspecialchars($pesan); ?></b></p><?php endif; ?>
<form method="POST" enctype="multipart/form-data">
    <label>Nama Akun</label><br>
    <input type="text" value="<?php echo htmlspecialchars($nama_user); ?>" readonly><br><br>
    <label>NIK</label><br>
    <input type="text" name="nik_pelanggan" value="<?php echo htmlspecialchars($data_pelanggan['nik_pelanggan'] ?? ''); ?>" required><br><br>
    <label>Email</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($data_pelanggan['email'] ?? ''); ?>" required><br><br>
    <label>No HP</label><br>
    <input type="text" name="no_hp" value="<?php echo htmlspecialchars($data_pelanggan['no_hp'] ?? ''); ?>" required><br><br>
    <label>Alamat</label><br>
    <textarea name="alamat" rows="4" required><?php echo htmlspecialchars($data_pelanggan['alamat'] ?? ''); ?></textarea><br><br>
    <label>Foto KTP</label><br>
    <input type="file" name="ktp" accept="image/jpeg,image/png,image/webp"><br><br>
    <button type="submit" name="simpan">Simpan Data</button>
</form>
<br>
<a href="user_dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
