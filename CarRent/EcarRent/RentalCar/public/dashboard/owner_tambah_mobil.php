<?php
include 'owner_lib.php';
$galat = $_GET['galat'] ?? '';
$galat_teks = [
    'lengkap' => 'Data pengajuan belum lengkap. Periksa kembali isian Anda.',
    'plat'    => 'Plat mobil sudah terdaftar.',
    'foto'    => 'File foto tidak valid atau lebih dari 3 MB. Pakai JPG, PNG, atau WebP.',
    'simpan'  => 'Pengajuan gagal disimpan. Coba lagi.',
    'sesi'    => 'Sesi formulir sudah kedaluwarsa. Coba kirim ulang.',
];
$owner_page = 'tambah'; $owner_title = 'Ajukan Mobil Baru'; include 'owner_header.php';
?>
<div class="page-head"><div><h1>Ajukan mobil baru</h1><p>Isi data kendaraan. Pengajuan berstatus <strong>Menunggu</strong> sampai admin menyetujui, setelah itu tampil di katalog pelanggan.</p></div><a class="btn secondary" href="owner_armada.php">&larr; Kembali ke armada</a></div>
<?php if (isset($galat_teks[$galat])): ?><p class="alert alert--bad" role="alert"><?php echo esc($galat_teks[$galat]); ?></p><?php endif; ?>
<?php if (!$owner): ?><div class="alert alert--bad">Akun owner belum terhubung dengan data pemilik mobil.</div><?php else: ?>
<form method="post" action="owner_proses_tambah_mobil.php" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="form-grid">
        <div class="field"><label for="plat">Plat mobil</label><input id="plat" type="text" name="plat_mobil" placeholder="BP 1234 AB" required></div>
        <div class="field"><label for="merk">Merk</label><input id="merk" type="text" name="merk" required></div>
        <div class="field"><label for="tipe">Tipe</label><input id="tipe" type="text" name="tipe" required></div>
        <div class="field"><label for="tahun">Tahun</label><input id="tahun" type="number" name="tahun" min="1990" max="2100" required></div>
        <div class="field"><label for="harga">Tarif per hari (Rp)</label><input id="harga" type="number" name="harga_rental" min="1" step="1000" required></div>
        <div class="field"><label for="foto">Foto mobil (maks. 3 MB)</label><input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" required></div>
        <div class="field full"><label for="kondisi">Kondisi mobil</label><textarea id="kondisi" name="kondisi_mobil" required placeholder="Mis. mesin halus, AC dingin, ban baru"></textarea></div>
        <div class="field full"><label for="riwayat">Riwayat kerusakan</label><textarea id="riwayat" name="riwayat_kerusakan" placeholder="Kosongkan bila tidak ada"></textarea></div>
        <div class="full"><button type="submit">Ajukan mobil</button></div>
    </div>
</form>
<?php endif; ?>
<?php include 'owner_footer.php'; ?>
