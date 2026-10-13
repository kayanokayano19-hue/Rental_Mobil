<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])){header('Location: ../../../login.php');exit();}
if(strtolower($_SESSION['role']??'')!=='admin'){header('Location: ../../dashboard/dashboard.php');exit();}
include_once("../../../config/koneksi.php");
include_once("../Controler/tambahmobil.php");
$controller=new MobilController($kon);$pesan='';
if(isset($_POST['submit'])){
$data=['plat_mobil'=>$_POST['plat_mobil'],'id_pemilik'=>$_POST['id_pemilik'],'merk'=>$_POST['merk'],'tipe'=>$_POST['tipe'],'status'=>$_POST['status'],'tahun'=>$_POST['tahun'],'kondisi_mobil'=>$_POST['kondisi_mobil'],'riwayat_kerusakan'=>$_POST['riwayat_kerusakan'],'harga_rental'=>$_POST['harga_rental']];
$pesan=$controller->tambahDataMobil($data);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Armada Mobil - eCar-Rent</title>
<link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg">
<script src="../../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

<div class="page-head">
    <div>
        <h1>Tambah armada mobil</h1>
        <p>Tambahkan kendaraan baru ke dalam sistem eCar-Rent.</p>
    </div>
    <a class="btn secondary" href="../../dashboard/dashboard.php?menu=mobil">Kembali ke armada</a>
</div>

<?php if ($pesan): ?>
    <p class="alert <?php echo (stripos($pesan, 'berhasil') !== false) ? 'alert--ok' : 'alert--bad'; ?>" role="status"><?php echo htmlspecialchars($pesan); ?></p>
<?php endif; ?>

<form class="form-card" method="POST" enctype="multipart/form-data">
    <div class="grid2">
        <div class="wide">
            <label for="plat_mobil">Plat nomor kendaraan</label>
            <input type="text" id="plat_mobil" name="plat_mobil" placeholder="Contoh: BP 1234 AB" autocapitalize="characters" required>
        </div>
        <div class="wide">
            <label for="id_pemilik">Mitra pemilik mobil</label>
            <select id="id_pemilik" name="id_pemilik" required>
                <option value="">-- Pilih pemilik --</option>
                <?php $q = mysqli_query($kon, "SELECT id_pemilik, nama FROM tb_pemilik_mobil ORDER BY nama"); while ($p = mysqli_fetch_assoc($q)): ?>
                    <option value="<?php echo (int)$p['id_pemilik']; ?>"><?php echo htmlspecialchars($p['nama']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="merk">Merk kendaraan</label>
            <input type="text" id="merk" name="merk" placeholder="Contoh: Toyota" required>
        </div>
        <div>
            <label for="tipe">Tipe / model</label>
            <input type="text" id="tipe" name="tipe" placeholder="Contoh: Avanza" required>
        </div>
        <div>
            <label for="tahun">Tahun produksi</label>
            <input type="number" id="tahun" name="tahun" min="1900" max="2100" inputmode="numeric" placeholder="2022" required>
        </div>
        <div>
            <label for="harga_rental">Tarif sewa harian (Rp)</label>
            <input type="number" id="harga_rental" name="harga_rental" min="0" inputmode="numeric" placeholder="350000" required>
        </div>
        <div class="wide">
            <label for="status">Status kendaraan</label>
            <select id="status" name="status" required>
                <option value="Tersedia">Tersedia</option>
                <option value="Disewa">Disewa</option>
                <option value="Maintenance">Maintenance</option>
            </select>
        </div>
        <div class="wide">
            <label for="kondisi_mobil">Kondisi kendaraan</label>
            <textarea id="kondisi_mobil" name="kondisi_mobil" rows="3" placeholder="Contoh: Bersih, AC dingin, ban baru" required></textarea>
        </div>
        <div class="wide">
            <label for="riwayat_kerusakan">Riwayat kerusakan</label>
            <textarea id="riwayat_kerusakan" name="riwayat_kerusakan" rows="3">-</textarea>
        </div>
        <div class="wide">
            <label for="foto">Foto kendaraan</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-preview="#fotoPreview" required>
            <span class="file-note">Format JPG, PNG, atau WebP.</span>
            <img id="fotoPreview" class="file-preview" alt="Pratinjau foto kendaraan">
        </div>
        <div class="form-actions">
            <button class="btn" type="submit" name="submit">Ajukan pendaftaran mobil</button>
            <a class="btn secondary" href="../../dashboard/dashboard.php?menu=mobil">Batal</a>
        </div>
    </div>
</form>
</body>
</html>
