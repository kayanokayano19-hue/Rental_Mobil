<?php
if(!isset($owner_page)){$owner_page='home';}
if(!isset($owner_title)){$owner_title='Owner';}
if(!isset($nama)){$nama=$_SESSION['nama']??$_SESSION['username']??'Owner';}
function owner_nav_escape($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo owner_nav_escape($owner_title); ?> - eCar-Rent</title>
<link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
<script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="owner" data-pub="../" data-user="<?php echo owner_nav_escape($nama); ?>">
<main>
