<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']??''),['admin','petugas'],true)){header('Location: ../../dashboard/dashboard.php');exit();}
include_once("../../../config/koneksi.php");
$id=(int)($_GET['id_rental']??0);
$q=mysqli_query($kon,"SELECT * FROM tb_rental WHERE id_rental=$id LIMIT 1");$data=$q?mysqli_fetch_assoc($q):null;if(!$data){echo 'Data rental tidak ditemukan.';exit;}
if(isset($_POST['update'])){
 $pel=(int)$_POST['id_pelanggan'];$plat=mysqli_real_escape_string($kon,$_POST['plat_mobil']);$mulai=mysqli_real_escape_string($kon,$_POST['waktu_pinjam']);$kembali=mysqli_real_escape_string($kon,$_POST['waktu_kembali']);
 $m=mysqli_query($kon,"SELECT harga_rental FROM tb_mobil WHERE plat_mobil='$plat' LIMIT 1");$md=$m?mysqli_fetch_assoc($m):null;$harga=$md?(float)$md['harga_rental']:(float)$data['harga_rental'];$hari=max(1,(int)((new DateTime($mulai))->diff(new DateTime($kembali))->days));$total=$hari*$harga;
 if(strtotime($kembali)<=strtotime($mulai)){$pesan='Waktu kembali harus setelah waktu pinjam.';}else{$stmt=mysqli_prepare($kon,"UPDATE tb_rental SET id_pelanggan=?,plat_mobil=?,waktu_pinjam=?,waktu_kembali=?,harga_rental=?,total_harga=? WHERE id_rental=?");mysqli_stmt_bind_param($stmt,'isssddi',$pel,$plat,$mulai,$kembali,$harga,$total,$id);$ok=mysqli_stmt_execute($stmt);mysqli_stmt_close($stmt);if($ok){header('Location: ../../dashboard/dashboard.php?menu=rental');exit;}$pesan=mysqli_error($kon);}
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Data Rental</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h1>Edit Data Rental</h1><p>Perbarui data sewa untuk transaksi <strong>#<?php echo $id; ?></strong></p><p><a href="../../dashboard/dashboard.php?menu=rental">Kembali</a></p><?php if(!empty($pesan)): ?><p><strong><?php echo htmlspecialchars($pesan); ?></strong></p><?php endif; ?>
<form method="post">
<p>Pelanggan<br><select name="id_pelanggan" required><?php $q=mysqli_query($kon,"SELECT id_pelanggan,nama FROM tb_pelanggan ORDER BY nama");while($p=mysqli_fetch_assoc($q)): ?><option value="<?php echo $p['id_pelanggan']; ?>" <?php echo $p['id_pelanggan']==$data['id_pelanggan']?'selected':''; ?>><?php echo htmlspecialchars($p['nama']); ?></option><?php endwhile; ?></select></p>
<p>Mobil yang Disewa<br><select name="plat_mobil" id="plat_mobil" required><option value="">-- Pilih Mobil --</option><?php $q=mysqli_query($kon,"SELECT plat_mobil,merk,tipe,harga_rental,status FROM tb_mobil ORDER BY merk,tipe");while($m=mysqli_fetch_assoc($q)): ?><option value="<?php echo htmlspecialchars($m['plat_mobil']); ?>" data-harga="<?php echo htmlspecialchars($m['harga_rental']); ?>" <?php echo $m['plat_mobil']==$data['plat_mobil']?'selected':''; ?>><?php echo htmlspecialchars($m['plat_mobil'].' - '.$m['merk'].' '.$m['tipe']); ?> (Rp <?php echo number_format($m['harga_rental'],0,',','.'); ?> / hari)</option><?php endwhile; ?></select></p>
<p>Waktu Pinjam<br><input type="datetime-local" name="waktu_pinjam" id="waktu_pinjam" value="<?php echo date('Y-m-d\TH:i',strtotime($data['waktu_pinjam'])); ?>" required></p>
<p>Waktu Kembali<br><input type="datetime-local" name="waktu_kembali" id="waktu_kembali" value="<?php echo date('Y-m-d\TH:i',strtotime($data['waktu_kembali'])); ?>" required></p>
<p>Tarif dan total dihitung ulang otomatis berdasarkan mobil dan selisih hari.</p><p>Total Baru: <strong id="total">Rp <?php echo number_format($data['total_harga'],0,',','.'); ?></strong></p><button type="submit" name="update">Simpan Perubahan</button> <a href="../../dashboard/dashboard.php?menu=rental">Batal</a>
</form>
</body></html>
