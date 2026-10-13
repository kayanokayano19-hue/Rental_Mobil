<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])){header('Location: ../../../login.php');exit();}
if(!in_array(strtolower($_SESSION['role']??''),['admin','petugas'],true)){header('Location: ../../dashboard/user_dashboard.php');exit();}
include_once("../../../config/koneksi.php");
$id=(int)($_GET['id_rental']??0);
$sql="SELECT r.*,p.nama nama_pelanggan,p.nik_pelanggan,p.alamat alamat_pelanggan,p.email email_pelanggan,m.merk,m.tipe,m.harga_rental harga_mobil,m.foto foto_mobil FROM tb_rental r LEFT JOIN tb_pelanggan p ON p.id_pelanggan=r.id_pelanggan LEFT JOIN tb_mobil m ON m.plat_mobil=r.plat_mobil WHERE r.id_rental=$id LIMIT 1";
$res=mysqli_query($kon,$sql); $data=$res?mysqli_fetch_assoc($res):null;
if(!$data){echo 'Detail transaksi tidak ditemukan.';exit;}
function rupiah($n){return 'Rp '.number_format((float)$n,0,',','.');}
function tanggal($v){$t=strtotime($v);return $t?date('d M Y, H:i',$t).' WIB':$v;}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Detail Transaksi Rental</title><link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg"><script src="../../assets/app.js?v=2040" defer></script>
</head><body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<h1>Detail Transaksi Rental</h1><p>ID Transaksi: <strong>#<?php echo $data['id_rental']; ?></strong></p>
<p><a href="../../rental/invoice.php?id=<?php echo $data['id_rental']; ?>" target="_blank">Invoice</a> | <a href="updaterental.php?id_rental=<?php echo $data['id_rental']; ?>">Edit</a> | <a href="../../dashboard/dashboard.php?menu=rental">Kembali</a></p>
<h2>Status Transaksi</h2><p><strong><?php echo htmlspecialchars($data['status_rental']); ?></strong></p>
<h3>Informasi Pelanggan</h3><p><strong><?php echo htmlspecialchars($data['nama_pelanggan']??'-'); ?></strong></p><p>NIK: <?php echo htmlspecialchars($data['nik_pelanggan']??'-'); ?></p><p>Email: <?php echo htmlspecialchars($data['email_pelanggan']??'-'); ?></p><p>Alamat: <?php echo htmlspecialchars($data['alamat_pelanggan']??'-'); ?></p>
<h3>Informasi Kendaraan</h3><p><strong><?php echo htmlspecialchars(trim(($data['merk']??'').' '.($data['tipe']??''))); ?></strong></p><p>Nomor Plat: <strong><?php echo htmlspecialchars($data['plat_mobil']); ?></strong></p><p><?php echo rupiah($data['harga_rental']); ?> / hari</p><p>Waktu Pinjam<br><strong><?php echo tanggal($data['waktu_pinjam']); ?></strong></p><p>Waktu Kembali<br><strong><?php echo tanggal($data['waktu_kembali']); ?></strong></p><h3>Total Tagihan Rental</h3><p><strong><?php echo rupiah($data['total_harga']); ?></strong></p>
<h3>Foto Kendaraan</h3><?php if(!empty($data['foto_mobil'])): ?><img src="data:image/jpeg;base64,<?php echo base64_encode($data['foto_mobil']); ?>" alt="Foto mobil" width="400"><?php else: ?>Foto mobil belum tersedia.<?php endif; ?>
</body></html>
