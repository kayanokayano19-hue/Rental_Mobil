<?php
include 'owner_lib.php';
$mobil = $owner_id ? owner_daftar_mobil($kon, $owner_id) : [];
$filter = strtolower(trim($_GET['status'] ?? ''));
$hitung = ['disetujui' => 0, 'menunggu' => 0, 'ditolak' => 0];
foreach ($mobil as $m) { $k = strtolower($m['status_persetujuan']); if (isset($hitung[$k])) { $hitung[$k]++; } }
$tampil = array_filter($mobil, function ($m) use ($filter) { return $filter === '' || strtolower($m['status_persetujuan']) === $filter; });
$notif = ['diajukan' => 'Mobil diajukan. Admin akan meninjau sebelum tampil ke pelanggan.', 'diubah' => 'Data mobil berhasil diperbarui.'];
$pesan = $_GET['pesan'] ?? '';
$owner_page = 'armada'; $owner_title = 'Armada Saya'; include 'owner_header.php';
?>
<div class="page-head">
    <div><h1>Armada saya</h1><p>Semua mobil yang Anda titipkan, lengkap dengan performanya.</p></div>
    <a class="btn" href="owner_tambah_mobil.php">+ Ajukan mobil baru</a>
</div>
<?php if (isset($notif[$pesan])): ?><p class="alert alert--ok" role="status"><?php echo esc($notif[$pesan]); ?></p><?php endif; ?>
<?php if (!$owner_id): ?><div class="alert alert--bad">Akun owner belum terhubung dengan data pemilik mobil.</div><?php else: ?>
<div class="chips" aria-label="Filter status persetujuan">
    <?php foreach (['' => 'Semua (' . count($mobil) . ')', 'disetujui' => 'Disetujui (' . $hitung['disetujui'] . ')', 'menunggu' => 'Menunggu (' . $hitung['menunggu'] . ')', 'ditolak' => 'Ditolak (' . $hitung['ditolak'] . ')'] as $k => $lbl): ?>
        <a class="chip<?php echo $filter === $k ? ' is-on' : ''; ?>" href="?<?php echo $k !== '' ? 'status=' . $k : ''; ?>"><?php echo esc($lbl); ?></a>
    <?php endforeach; ?>
</div>
<?php if ($tampil): ?>
<div class="ocar-grid"><?php foreach ($tampil as $m) { echo owner_kartu_mobil($m, $kon); } ?></div>
<?php else: ?>
<div class="empty glass"><h3>Tidak ada mobil di sini</h3><p>Belum ada unit dengan status ini.</p><a class="btn" href="owner_tambah_mobil.php">Ajukan mobil</a></div>
<?php endif; endif; ?>
<?php include 'owner_footer.php'; ?>
