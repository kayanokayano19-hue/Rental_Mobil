<?php
include 'owner_lib.php';
$notif = ''; $galat = '';
if ($owner && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $galat = 'Sesi formulir sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif (($_POST['aksi'] ?? '') === 'profil') {
        $no_hp = trim((string)($_POST['no_hp'] ?? ''));
        $alamat = trim((string)($_POST['alamat'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        if ($no_hp === '' || strlen($no_hp) > 15 || $alamat === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $galat = 'Periksa lagi: email harus valid, no HP maksimal 15 karakter, alamat wajib diisi.';
        } else {
            $stmt = mysqli_prepare($kon, 'UPDATE tb_pemilik_mobil SET email = ?, no_hp = ?, alamat = ? WHERE id_pemilik = ?');
            mysqli_stmt_bind_param($stmt, 'sssi', $email, $no_hp, $alamat, $owner_id);
            if (mysqli_stmt_execute($stmt)) { $notif = 'Profil berhasil diperbarui.'; $owner = owner_data($kon); } else { $galat = 'Profil gagal disimpan.'; }
            mysqli_stmt_close($stmt);
        }
    } elseif (($_POST['aksi'] ?? '') === 'password') {
        $lama = (string)($_POST['password_lama'] ?? '');
        $baru = (string)($_POST['password_baru'] ?? '');
        $ulang = (string)($_POST['password_ulang'] ?? '');
        $uid = (int)$_SESSION['user_id'];
        $hash = (string)satu_nilai($kon, 'SELECT password FROM tb_user WHERE id_user = ?', 'i', [$uid], '');
        if ($hash === '' || !password_verify($lama, $hash)) {
            $galat = 'Password lama tidak cocok.';
        } elseif (strlen($baru) < 6) {
            $galat = 'Password baru minimal 6 karakter.';
        } elseif ($baru !== $ulang) {
            $galat = 'Konfirmasi password baru tidak sama.';
        } else {
            $h = password_hash($baru, PASSWORD_DEFAULT);
            mysqli_begin_transaction($kon);
            $s1 = mysqli_prepare($kon, 'UPDATE tb_user SET password = ? WHERE id_user = ?');
            mysqli_stmt_bind_param($s1, 'si', $h, $uid);
            $ok1 = mysqli_stmt_execute($s1); mysqli_stmt_close($s1);
            $s2 = mysqli_prepare($kon, 'UPDATE tb_pemilik_mobil SET password = ? WHERE id_pemilik = ?');
            mysqli_stmt_bind_param($s2, 'si', $h, $owner_id);
            $ok2 = mysqli_stmt_execute($s2); mysqli_stmt_close($s2);
            if ($ok1 && $ok2) { mysqli_commit($kon); $notif = 'Password berhasil diganti.'; } else { mysqli_rollback($kon); $galat = 'Password gagal diganti.'; }
        }
    }
}
$owner_page = 'profil'; $owner_title = 'Profil Mitra'; include 'owner_header.php';
?>
<div class="page-head"><div><h1>Profil mitra</h1><p>Data kontak Anda dan keamanan akun.</p></div><a class="btn secondary" href="owner.php">&larr; Kembali ke ringkasan</a></div>
<?php if ($notif !== ''): ?><p class="alert alert--ok" role="status"><?php echo esc($notif); ?></p><?php endif; ?>
<?php if ($galat !== ''): ?><p class="alert alert--bad" role="alert"><?php echo esc($galat); ?></p><?php endif; ?>
<?php if (!$owner): ?><div class="alert alert--bad">Data pemilik mobil belum terhubung dengan akun ini. Hubungi admin.</div><?php else: ?>
<div class="panel-head"><h2>Data mitra</h2></div>
<form method="post">
    <?php echo csrf_field(); ?><input type="hidden" name="aksi" value="profil">
    <div class="form-grid">
        <div class="field"><label>Nama lengkap</label><input value="<?php echo esc($owner['nama']); ?>" readonly></div>
        <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?php echo esc($owner['email']); ?>" required></div>
        <div class="field"><label for="hp">No HP / WA</label><input id="hp" type="text" name="no_hp" maxlength="15" value="<?php echo esc($owner['no_hp']); ?>" required></div>
        <div class="field full"><label for="alamat">Alamat</label><textarea id="alamat" name="alamat" required><?php echo esc($owner['alamat']); ?></textarea></div>
        <div class="full"><button type="submit">Simpan profil</button></div>
    </div>
</form>
<div class="panel-head"><h2>Ganti password</h2></div>
<form method="post">
    <?php echo csrf_field(); ?><input type="hidden" name="aksi" value="password">
    <div class="form-grid">
        <div class="field full"><label for="pl">Password lama</label><input id="pl" type="password" name="password_lama" autocomplete="current-password" required></div>
        <div class="field"><label for="pb">Password baru</label><input id="pb" type="password" name="password_baru" minlength="6" autocomplete="new-password" required></div>
        <div class="field"><label for="pu">Ulangi password baru</label><input id="pu" type="password" name="password_ulang" minlength="6" autocomplete="new-password" required></div>
        <div class="full"><button type="submit">Ganti password</button></div>
    </div>
</form>
<?php endif; ?>
<?php include 'owner_footer.php'; ?>
