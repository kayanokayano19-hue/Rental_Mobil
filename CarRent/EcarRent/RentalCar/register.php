<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once("config/koneksi.php");

$pesan = "";
$v = ["nama" => "", "username" => "", "nik" => "", "email" => "", "no_hp" => "", "alamat" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $v["nama"]     = trim($_POST["nama"] ?? "");
    $v["username"] = trim($_POST["username"] ?? "");
    $v["nik"]      = trim($_POST["nik_pelanggan"] ?? "");
    $v["email"]    = trim($_POST["email"] ?? "");
    $v["no_hp"]    = trim($_POST["no_hp"] ?? "");
    $v["alamat"]   = trim($_POST["alamat"] ?? "");
    $password      = $_POST["password"] ?? "";
    $password2     = $_POST["password2"] ?? "";

    if (in_array("", $v, true) || $password === "") {
        $pesan = "Semua kolom wajib diisi.";
    } elseif (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter.";
    } elseif ($password !== $password2) {
        $pesan = "Konfirmasi password belum sama.";
    } elseif (!preg_match('/^\d{16,30}$/', $v["nik"])) {
        $pesan = "NIK harus berupa angka, minimal 16 digit.";
    } elseif (!filter_var($v["email"], FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email belum benar.";
    } elseif (!preg_match('/^[0-9+\-\s]{8,16}$/', $v["no_hp"])) {
        $pesan = "Nomor HP belum benar. Gunakan angka, 8 sampai 16 karakter.";
    } else {
        try {
            $s1 = $kon->prepare("SELECT id_user FROM tb_user WHERE username = ? LIMIT 1");
            $s1->bind_param("s", $v["username"]);
            $s1->execute();
            $ada_user = $s1->get_result()->num_rows > 0;
            $s1->close();

            $s2 = $kon->prepare("SELECT id_pelanggan FROM tb_pelanggan WHERE nik_pelanggan = ? LIMIT 1");
            $s2->bind_param("s", $v["nik"]);
            $s2->execute();
            $ada_nik = $s2->get_result()->num_rows > 0;
            $s2->close();

            $s3 = $kon->prepare("SELECT id_pelanggan FROM tb_pelanggan WHERE nama = ? LIMIT 1");
            $s3->bind_param("s", $v["nama"]);
            $s3->execute();
            $ada_nama = $s3->get_result()->num_rows > 0;
            $s3->close();

            if ($ada_user) {
                $pesan = "Username sudah digunakan, silakan pilih yang lain.";
            } elseif ($ada_nik) {
                $pesan = "NIK sudah terdaftar.";
            } elseif ($ada_nama) {
                $pesan = "Nama pelanggan sudah terdaftar.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = "User";
                $kon->begin_transaction();
                try {
                    $iu = $kon->prepare("INSERT INTO tb_user (username, password, nama, role) VALUES (?, ?, ?, ?)");
                    $iu->bind_param("ssss", $v["username"], $hash, $v["nama"], $role);
                    $iu->execute();
                    $iu->close();

                    $ip = $kon->prepare("INSERT INTO tb_pelanggan (nik_pelanggan, nama, email, password, no_hp, alamat, saldo, status_verifikasi, ktp_pelanggan) VALUES (?, ?, ?, ?, ?, ?, 0, 'Belum Verifikasi', '')");
                    $ip->bind_param("ssssss", $v["nik"], $v["nama"], $v["email"], $hash, $v["no_hp"], $v["alamat"]);
                    $ip->execute();
                    $ip->close();

                    $kon->commit();
                    header("Location: login.php?baru=1");
                    exit();
                } catch (Throwable $e) {
                    $kon->rollback();
                    $pesan = "Pendaftaran gagal disimpan. Coba lagi sebentar lagi.";
                }
            }
        } catch (Throwable $e) {
            $pesan = "Pendaftaran gagal diproses. Coba lagi sebentar lagi.";
        }
    }
}

$siap = 0;
$qs = mysqli_query($kon, "SELECT COUNT(*) AS c FROM tb_mobil WHERE LOWER(status) NOT IN ('disewa','di sewa')");
if ($qs) { $siap = (int)(mysqli_fetch_assoc($qs)['c'] ?? 0); }
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - eCar-Rent</title>
    <link rel="stylesheet" href="public/assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="public/assets/logo.svg">
    <script src="public/assets/app.js?v=2040" defer></script>
</head>
<body class="auth-page">
<div class="auth">
    <section class="auth__pitch glass">
        <a class="brand" href="login.php"><img class="brand__logo" src="public/assets/logo.svg" alt=""><span><b class="wm">eCar<i>-Rent</i></b><small>Rental mobil</small></span></a>
        <span class="eyebrow"><i class="dot"></i> Daftar cuma semenit</span>
        <h1>Satu formulir, lalu <span class="grad">mobil pilihan Anda menunggu.</span></h1>
        <p>Isi data diri sekali saja. Setelah akun diverifikasi admin, Anda bisa langsung menyewa.</p>
        <div class="auth__live">
            <span class="plat plat--lg" aria-hidden="true"><small>BP</small><?php echo str_pad((string)$siap, 2, "0", STR_PAD_LEFT); ?></span>
            <span><strong><?php echo $siap; ?> mobil</strong> siap disewa sekarang</span>
        </div>
        <div class="lane" aria-hidden="true"></div>
        <ul class="perks">
            <li><b>Cukup sekali isi</b>Data pelanggan dibuat bersamaan dengan akun login.</li>
            <li><b>NIK dipakai untuk verifikasi</b>Admin mencocokkannya sebelum akun bisa menyewa.</li>
            <li><b>Saldo bisa diisi nanti</b>Daftar dulu, top up kapan pun lewat menu Isi saldo.</li>
        </ul>
    </section>

    <section class="auth__card glass">
        <h2>Buat akun pelanggan</h2>
        <p>Semua kolom wajib diisi.</p>

        <?php if ($pesan !== ""): ?>
            <p class="alert alert--bad" role="alert"><?php echo h($pesan); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="grid2">
                <div><label for="nama">Nama lengkap</label><input type="text" id="nama" name="nama" value="<?php echo h($v["nama"]); ?>" autocomplete="name" required></div>
                <div><label for="username">Username</label><input type="text" id="username" name="username" value="<?php echo h($v["username"]); ?>" autocomplete="username" autocapitalize="none" spellcheck="false" required></div>
                <div><label for="password">Password</label><input type="password" id="password" name="password" minlength="6" autocomplete="new-password" data-strength required></div>
                <div><label for="password2">Ulangi password</label><input type="password" id="password2" name="password2" minlength="6" autocomplete="new-password" data-match="#password" required></div>
                <div><label for="nik">NIK</label><input type="text" id="nik" name="nik_pelanggan" value="<?php echo h($v["nik"]); ?>" minlength="16" maxlength="30" inputmode="numeric" data-digits data-count="16" autocomplete="off" required></div>
                <div><label for="no_hp">No HP</label><input type="tel" id="no_hp" name="no_hp" value="<?php echo h($v["no_hp"]); ?>" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx" required></div>
                <div class="wide"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo h($v["email"]); ?>" autocomplete="email" required></div>
                <div class="wide"><label for="alamat">Alamat</label><textarea id="alamat" name="alamat" rows="3" autocomplete="street-address" required><?php echo h($v["alamat"]); ?></textarea></div>
            </div>
            <button type="submit" class="block">Buat akun</button>
        </form>
        <p class="auth__alt">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </section>
</div>
</body>
</html>
