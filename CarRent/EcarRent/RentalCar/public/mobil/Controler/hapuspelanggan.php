<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']??''),['admin','petugas'],true)){header('Location: ../../../login.php');exit();}
include_once("../../../config/koneksi.php");
$id=(int)($_GET['id_pelanggan']??0);
if($id>0){
    $stmt=mysqli_prepare($kon,"DELETE FROM tb_pelanggan WHERE id_pelanggan=?");
    mysqli_stmt_bind_param($stmt,'i',$id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
}
header('Location: ../../dashboard/dashboard.php?menu=pelanggan');exit();
?>
