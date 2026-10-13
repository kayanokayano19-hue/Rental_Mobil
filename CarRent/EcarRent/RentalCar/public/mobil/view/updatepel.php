<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])){header('Location: ../../../login.php');exit();}
if(!in_array(strtolower($_SESSION['role']??''),['admin','petugas'],true)){header('Location: ../../dashboard/user_dashboard.php');exit();}
include_once("../../../config/koneksi.php");
$id=(int)($_GET['id_pelanggan']??0);
$stmt=mysqli_prepare($kon,"SELECT * FROM tb_pelanggan WHERE id_pelanggan=? LIMIT 1"); mysqli_stmt_bind_param($stmt,'i',$id); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); $data=mysqli_fetch_assoc($res); mysqli_stmt_close($stmt);
if(!$data){echo 'Data pelanggan tidak ditemukan.';exit;}
if(isset($_POST['update'])){
 $nik=mysqli_real_escape_string($kon,trim($_POST['nik_pelanggan'])); $nama=mysqli_real_escape_string($kon,trim($_POST['nama'])); $email=mysqli_real_escape_string($kon,trim($_POST['email'])); $no=mysqli_real_escape_string($kon,trim($_POST['no_hp'])); $alamat=mysqli_real_escape_string($kon,trim($_POST['alamat'])); $saldo=(float)$_POST['saldo']; $status=mysqli_real_escape_string($kon,trim($_POST['status_verifikasi']));
 if(isset($_FILES['ktp_pelanggan']) && $_FILES['ktp_pelanggan']['error']===UPLOAD_ERR_OK){$ktp=addslashes(file_get_contents($_FILES['ktp_pelanggan']['tmp_name']));$sql="UPDATE tb_pelanggan SET nik_pelanggan='$nik',nama='$nama',email='$email',no_hp='$no',alamat='$alamat',saldo='$saldo',status_verifikasi='$status',ktp_pelanggan='$ktp' WHERE id_pelanggan=$id";}else{$sql="UPDATE tb_pelanggan SET nik_pelanggan='$nik',nama='$nama',email='$email',no_hp='$no',alamat='$alamat',saldo='$saldo',status_verifikasi='$status' WHERE id_pelanggan=$id";}
 if(mysqli_query($kon,$sql)){flash_go('Data pelanggan berhasil diperbarui.','../../dashboard/dashboard.php?menu=pelanggan','../../assets/app.js?v=2040');exit;} $pesan=mysqli_error($kon);
}
$status=(string)$data['status_verifikasi'];
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Data Pelanggan</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h1>Edit Data Pelanggan</h1><p>Perbarui data pelanggan dan status verifikasi.</p><p><a href="viewpel.php?id_pelanggan=<?php echo $id; ?>">Kembali</a></p>
<?php if(!empty($pesan)): ?><p><?php echo htmlspecialchars($pesan); ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data"><p>NIK KTP<br><input type="text" name="nik_pelanggan" value="<?php echo htmlspecialchars($data['nik_pelanggan']); ?>" required></p><p>Nama Lengkap<br><input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required></p><p>Email<br><input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" required></p><p>Nomor HP<br><input type="text" name="no_hp" value="<?php echo htmlspecialchars($data['no_hp']); ?>" required></p><p>Alamat<br><textarea name="alamat" rows="4" required><?php echo htmlspecialchars($data['alamat']); ?></textarea></p><p>Saldo<br><input type="number" name="saldo" min="0" value="<?php echo htmlspecialchars($data['saldo']); ?>" required></p><p>Status Verifikasi<br><select name="status_verifikasi"><option value="Belum Verifikasi" <?php echo $status==='Belum Verifikasi'?'selected':''; ?>>Pending</option><option value="Disetujui" <?php echo in_array(strtolower($status),['disetujui','terverifikasi','1'],true)?'selected':''; ?>>Approved</option><option value="Ditolak" <?php echo in_array(strtolower($status),['ditolak','ditolak'],true)?'selected':''; ?>>Rejected</option><option value="Blacklist" <?php echo $status==='Blacklist'?'selected':''; ?>>Blacklist</option></select></p><p>Foto KTP Baru<br><input type="file" name="ktp_pelanggan" accept="image/*"></p><button type="submit" name="update">Simpan Perubahan</button></form>
</body></html>
