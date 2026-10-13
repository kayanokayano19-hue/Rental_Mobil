<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }include_once("../../config/koneksi.php");
if(!isset($_SESSION['user_id'])){header('Location: ../../login.php');exit();}if(strtolower($_SESSION['role']??'')!=='owner'){header('Location: user_dashboard.php');exit();}
function owner_data($kon){$user_id=(int)($_SESSION['user_id']??0);if(!$user_id)return null;$stmt=mysqli_prepare($kon,"SELECT * FROM tb_pemilik_mobil WHERE id_user=? LIMIT 1");if(!$stmt)return null;mysqli_stmt_bind_param($stmt,'i',$user_id);mysqli_stmt_execute($stmt);$res=mysqli_stmt_get_result($stmt);$data=$res?mysqli_fetch_assoc($res):null;mysqli_stmt_close($stmt);return $data;}
function owner_escape($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}function owner_rp($v){return 'Rp '.number_format((float)$v,0,',','.');}
$owner=owner_data($kon);$owner_id=(int)($owner['id_pemilik']??0);$id=(int)($_GET['id']??0);$rental=null;if($owner_id&&$id){$stmt=mysqli_prepare($kon,"SELECT r.*,p.nama nama_pelanggan,p.email email_pelanggan,p.no_hp no_hp_pelanggan,p.nik_pelanggan,p.alamat alamat_pelanggan,m.merk,m.tipe,m.tahun,m.plat_mobil FROM tb_rental r LEFT JOIN tb_pelanggan p ON p.id_pelanggan=r.id_pelanggan INNER JOIN tb_mobil m ON m.plat_mobil=r.plat_mobil WHERE r.id_rental=? AND m.id_pemilik=? LIMIT 1");if($stmt){mysqli_stmt_bind_param($stmt,'ii',$id,$owner_id);mysqli_stmt_execute($stmt);$res=mysqli_stmt_get_result($stmt);$rental=$res?mysqli_fetch_assoc($res):null;mysqli_stmt_close($stmt);}}
include_once __DIR__.'/../rental/struk.php';
if($rental){$rental['nama_pemilik']=$owner['nama']??($_SESSION['nama']??'');}
$owner_page='invoice';$owner_title='Invoice Rental';include 'owner_header.php';
?>
<div class="page-head"><div><h1>Invoice Rental</h1><p>Detail transaksi dari armada milik Anda.</p></div><div><a class="btn secondary no-print" href="owner_laporan.php">← Kembali ke Laporan Pendapatan</a> <button class="btn no-print" data-print>Cetak / Save as PDF</button></div></div>
<div class="section"><?php if(!$rental): ?><div class="notice alert alert--bad">Invoice tidak ditemukan atau bukan transaksi dari armada Anda.</div><?php else: echo struk_render($rental,$_SESSION['nama']??$_SESSION['username']??''); endif; ?></div>
<?php include 'owner_footer.php'; ?>
