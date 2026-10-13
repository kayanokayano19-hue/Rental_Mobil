<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']??''),['admin','petugas'],true)){header('Location: ../../dashboard/dashboard.php');exit();}
include_once("../../../config/koneksi.php");
$pesan='';
if(isset($_POST['simpan'])){
 $id=(int)$_POST['id_pelanggan'];$plat=mysqli_real_escape_string($kon,$_POST['plat_mobil']);$mulai=mysqli_real_escape_string($kon,$_POST['waktu_pinjam']);$kembali=mysqli_real_escape_string($kon,$_POST['waktu_kembali']);
 $q=mysqli_query($kon,"SELECT harga_rental,status FROM tb_mobil WHERE plat_mobil='$plat' AND status_persetujuan='Disetujui' LIMIT 1");$mobil=$q?mysqli_fetch_assoc($q):null;
 $q2=mysqli_query($kon,"SELECT status_verifikasi FROM tb_pelanggan WHERE id_pelanggan=$id LIMIT 1");$pel=$q2?mysqli_fetch_assoc($q2):null;
 $hari=max(1,(int)((new DateTime($mulai))->diff(new DateTime($kembali))->days));
 if(!$mobil||!$pel){$pesan='Pelanggan atau mobil tidak ditemukan.';}elseif(strtolower(trim($pel['status_verifikasi']))!=='disetujui' && strtolower(trim($pel['status_verifikasi']))!=='terverifikasi' && trim($pel['status_verifikasi'])!=='1'){$pesan='Pelanggan belum terverifikasi.';}elseif(strtolower(trim($mobil['status']))!=='tersedia'){$pesan='Mobil sedang tidak tersedia.';}elseif(strtotime($kembali)<=strtotime($mulai)){$pesan='Waktu kembali harus setelah waktu pinjam.';}else{$harga=(float)$mobil['harga_rental'];$total=$hari*$harga;$stmt=mysqli_prepare($kon,"INSERT INTO tb_rental (id_pelanggan,plat_mobil,waktu_pinjam,waktu_kembali,harga_rental,total_harga,status_rental) VALUES (?,?,?,?,?,?,?)");$status='Diproses';mysqli_stmt_bind_param($stmt,'isssdds',$id,$plat,$mulai,$kembali,$harga,$total,$status);$ok=mysqli_stmt_execute($stmt);$newid=mysqli_insert_id($kon);mysqli_stmt_close($stmt);if($ok){mysqli_query($kon,"UPDATE tb_mobil SET status='Disewa' WHERE plat_mobil='$plat'");header("Location: ../../dashboard/dashboard.php?menu=rental");exit();}$pesan=mysqli_error($kon);}
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tambah Data Rental</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h1>Tambah Data Rental</h1><p>Buat reservasi sewa kendaraan baru oleh admin</p><p><a href="../../dashboard/dashboard.php?menu=rental">Kembali</a></p>
<?php if($pesan): ?><p><strong><?php echo htmlspecialchars($pesan); ?></strong></p><?php endif; ?>
<form method="post">
<p>Pelanggan (Terverifikasi)<br><select name="id_pelanggan" required><option value="">-- Pilih Pelanggan --</option><?php $q=mysqli_query($kon,"SELECT id_pelanggan,nama,saldo FROM tb_pelanggan WHERE LOWER(TRIM(status_verifikasi)) IN ('disetujui','terverifikasi','1') ORDER BY nama");while($p=mysqli_fetch_assoc($q)): ?><option value="<?php echo $p['id_pelanggan']; ?>"><?php echo htmlspecialchars($p['nama']); ?> (Saldo: <?php echo 'Rp '.number_format($p['saldo'],0,',','.'); ?>)</option><?php endwhile; ?></select></p>
<p>Mobil Armada<br><select name="plat_mobil" id="plat_mobil" required><option value="">-- Pilih Mobil --</option><?php $q=mysqli_query($kon,"SELECT plat_mobil,merk,tipe,harga_rental FROM tb_mobil WHERE status_persetujuan='Disetujui' AND LOWER(TRIM(status))='tersedia' ORDER BY merk,tipe");while($m=mysqli_fetch_assoc($q)): ?><option value="<?php echo htmlspecialchars($m['plat_mobil']); ?>" data-harga="<?php echo htmlspecialchars($m['harga_rental']); ?>"><?php echo htmlspecialchars($m['plat_mobil'].' - '.$m['merk'].' '.$m['tipe']); ?> (Rp <?php echo number_format($m['harga_rental'],0,',','.'); ?> / hari)</option><?php endwhile; ?></select></p>
<p>Tanggal Mulai<br><input type="date" id="tanggal" required></p>
<p>Jam Pickup<br><select id="jam" required><option value="">-- Pilih Jam Pickup --</option><?php foreach(['14:00','15:00','16:00','17:00','18:00'] as $jam): ?><option value="<?php echo $jam; ?>"><?php echo $jam; ?> WIB</option><?php endforeach; ?></select></p>
<p>Durasi Rental<br><select id="durasi" required><option value="">-- Pilih Durasi --</option><?php for($i=1;$i<=20;$i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?> Hari</option><?php endfor; ?></select></p>
<p>Total Perkiraan<br><strong id="total">Rp 0</strong></p>
<input type="hidden" name="waktu_pinjam" id="waktu_pinjam"><input type="hidden" name="waktu_kembali" id="waktu_kembali">
<p>Pengembalian otomatis dijadwalkan sesuai durasi rental.</p><button type="submit" name="simpan">Tambah Transaksi Rental</button> <a href="../../dashboard/dashboard.php?menu=rental">Batal</a>
</form>
</body></html>
