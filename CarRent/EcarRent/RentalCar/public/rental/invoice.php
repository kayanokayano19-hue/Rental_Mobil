<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])){header('Location: ../../login.php');exit();}
if(!in_array(strtolower($_SESSION['role']??''),['admin','petugas','owner','user'],true)){header('Location: ../../login.php');exit();}
include_once("../../config/koneksi.php");
include_once(__DIR__."/../../config/guard.php");
include_once(__DIR__."/struk.php");
$id=(int)($_GET['id']??0);
$sql="SELECT r.*,p.nama nama_pelanggan,p.nik_pelanggan,p.email email_pelanggan,p.no_hp no_hp_pelanggan,p.alamat alamat_pelanggan,m.merk,m.tipe,m.tahun,m.harga_rental harga_mobil,m.id_pemilik,pm.nama nama_pemilik FROM tb_rental r LEFT JOIN tb_pelanggan p ON p.id_pelanggan=r.id_pelanggan LEFT JOIN tb_mobil m ON m.plat_mobil=r.plat_mobil LEFT JOIN tb_pemilik_mobil pm ON pm.id_pemilik=m.id_pemilik WHERE r.id_rental=$id LIMIT 1";
$res=mysqli_query($kon,$sql);$d=$res?mysqli_fetch_assoc($res):null;
if(!$d){echo 'Invoice tidak ditemukan.';exit;}
$peran=strtolower($_SESSION['role']??'');
if($peran==='user'){
    $p=pelanggan_saat_ini($kon);
    if(!$p||(int)$p['id_pelanggan']!==(int)$d['id_pelanggan']){echo 'Invoice tidak ditemukan.';exit;}
}elseif($peran==='owner'){
    $st=mysqli_prepare($kon,'SELECT id_pemilik FROM tb_pemilik_mobil WHERE id_user=? LIMIT 1');
    $uid=(int)$_SESSION['user_id'];mysqli_stmt_bind_param($st,'i',$uid);mysqli_stmt_execute($st);
    $po=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    if(!$po||(int)$po['id_pemilik']!==(int)($d['id_pemilik']??0)){echo 'Invoice tidak ditemukan.';exit;}
}
$operator=$_SESSION['nama']??$_SESSION['username']??'';
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Struk Rental #<?php echo (int)$d['id_rental']; ?></title>
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
<link rel="stylesheet" href="../assets/app.css?v=2040">
</head>
<body class="struk-page">
<div class="bar">
  <button class="btn alt" type="button" data-theme-flip aria-label="Ganti tema">Tema</button>
  <button class="btn alt" type="button" data-close>Tutup</button>
  <button class="btn" type="button" data-print>Cetak Struk</button>
</div>
<?php echo struk_render($d,$operator); ?>
<script src="../assets/app.js?v=2040" defer></script>
</body>
</html>
