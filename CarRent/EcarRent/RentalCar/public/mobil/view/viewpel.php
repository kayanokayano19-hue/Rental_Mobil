<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])){header('Location: ../../../login.php');exit();}
if(!in_array(strtolower($_SESSION['role']??''),['admin','petugas'],true)){header('Location: ../../dashboard/user_dashboard.php');exit();}
include_once("../../../config/koneksi.php");
$id=(int)($_GET['id_pelanggan']??0);
$stmt=mysqli_prepare($kon,"SELECT * FROM tb_pelanggan WHERE id_pelanggan=? LIMIT 1");
mysqli_stmt_bind_param($stmt,'i',$id); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); $data=mysqli_fetch_assoc($res); mysqli_stmt_close($stmt);
if(!$data){echo 'Data pelanggan tidak ditemukan.';exit;}
function rp2($n){return 'Rp '.number_format((float)$n,0,',','.');}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Detail Informasi Pelanggan</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h1>Detail Informasi Pelanggan</h1><p>Profil lengkap dan status verifikasi identitas</p>
<p><a href="updatepel.php?id_pelanggan=<?php echo $id; ?>">Edit</a> | <a href="../../dashboard/dashboard.php?menu=pelanggan">Kembali</a></p>
<h2><?php echo htmlspecialchars($data['nama']); ?></h2>
<p><strong>Saldo Dompet</strong><br><strong><?php echo rp2($data['saldo']); ?></strong></p>
<p><strong>Status Verifikasi</strong><br><strong><?php echo htmlspecialchars($data['status_verifikasi']); ?></strong></p>
<p><strong>Nomor NIK KTP</strong><br><?php echo htmlspecialchars($data['nik_pelanggan']); ?></p>
<p><strong>Email</strong><br><?php echo htmlspecialchars($data['email']); ?></p>
<p><strong>Nomor HP</strong><br><?php echo htmlspecialchars($data['no_hp']); ?></p>
<p><strong>Alamat Lengkap</strong><br><?php echo nl2br(htmlspecialchars($data['alamat'])); ?></p>
<h3>Foto KTP Pelanggan</h3>
<?php if(!empty($data['ktp_pelanggan'])): ?><img src="data:image/jpeg;base64,<?php echo base64_encode($data['ktp_pelanggan']); ?>" alt="Foto KTP" width="400"><br><br><?php else: ?>Tidak ada foto KTP.<br><?php endif; ?>
</body></html>
