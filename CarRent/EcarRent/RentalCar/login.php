<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once("config/koneksi.php");

/* Arahkan ke dashboard sesuai peran */
function tujuan_dashboard($role) {
    $role = strtolower((string)$role);
    if ($role === "owner") return "public/dashboard/owner.php";
    if ($role === "admin" || $role === "petugas") return "public/dashboard/dashboard.php";
    return "public/dashboard/user_dashboard.php";
}

/* Sudah login? langsung masuk */
if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: " . tujuan_dashboard($_SESSION["role"]));
    exit();
}

$batas_gagal = 5;      // percobaan salah maksimal
$jeda_detik  = 300;    // lalu tunggu 5 menit
$error = "";
$username_input = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $gagal  = (int)($_SESSION["login_gagal"] ?? 0);
    $mulai  = (int)($_SESSION["login_gagal_waktu"] ?? 0);
    if ($gagal >= $batas_gagal && (time() - $mulai) < $jeda_detik) {
        $sisa = (int)ceil(($jeda_detik - (time() - $mulai)) / 60);
        $error = "Terlalu banyak percobaan. Coba lagi dalam {$sisa} menit.";
    } else {
        if ($gagal >= $batas_gagal) { $gagal = 0; }

        $stmt = $kon->prepare("SELECT id_user, username, password, nama, role FROM tb_user WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username_input);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row["password"])) {
            session_regenerate_id(true);
            unset($_SESSION["login_gagal"], $_SESSION["login_gagal_waktu"]);
            $_SESSION["user_id"]  = $row["id_user"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["nama"]     = $row["nama"];
            $_SESSION["role"]     = $row["role"];
            $_SESSION["level"]    = $row["role"];
            header("Location: " . tujuan_dashboard($row["role"]));
            exit();
        }

        $_SESSION["login_gagal"] = $gagal + 1;
        $_SESSION["login_gagal_waktu"] = time();
        $error = "Login gagal. Username atau password salah.";
    }
}

/* Jumlah mobil yang siap disewa, tampil di panel kiri */
$siap = 0;
$qs = mysqli_query($kon, "SELECT COUNT(*) AS c FROM tb_mobil WHERE status_persetujuan = 'Disetujui' AND LOWER(status) NOT IN ('disewa','di sewa')");
if ($qs) { $siap = (int)(mysqli_fetch_assoc($qs)['c'] ?? 0); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - eCar-Rent</title>
    <link rel="stylesheet" href="public/assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="public/assets/logo.svg">
    <script src="public/assets/app.js?v=2040" defer></script>
</head>
<body class="auth-page">
<div class="auth">
    <section class="auth__pitch glass">
        <a class="brand" href="login.php"><img class="brand__logo" src="public/assets/logo.svg" alt=""><span><b class="wm">eCar<i>-Rent</i></b><small>Rental mobil</small></span></a>
        <span class="eyebrow"><i class="dot"></i> Rental mobil generasi baru</span>
        <h1>Kunci sudah siap. <span class="grad">Tinggal berangkat.</span></h1>
        <p>Pilih mobil, atur jadwal, dan pantau semuanya dari satu akun. Tanpa antre, tanpa nota yang hilang.</p>
        <div class="chips auth__chips"><span class="chip chip--live">Online 24 jam</span><span class="chip">Nota digital</span><span class="chip">Akun terverifikasi</span></div>
        <div class="auth__live">
            <span class="plat plat--lg" aria-hidden="true"><small>BP</small><?php echo str_pad((string)$siap, 2, "0", STR_PAD_LEFT); ?></span>
            <span><strong><?php echo $siap; ?> mobil</strong> siap disewa sekarang</span>
        </div>
        <div class="lane" aria-hidden="true"></div>
        <ul class="perks">
            <li><b>Pelanggan</b>Cari armada, sewa dalam beberapa langkah, cek riwayat kapan saja.</li>
            <li><b>Pemilik mobil</b>Lihat unit yang disewa dan pendapatan Anda tanpa menunggu laporan.</li>
            <li><b>Admin</b>Verifikasi pelanggan dan atur transaksi dari satu layar.</li>
        </ul>
    </section>

    <section class="auth__card glass">
        <h2>Selamat datang kembali</h2>
        <p>Masuk dengan username dan password Anda.</p>

        <?php if (isset($_GET["baru"]) && $error === ""): ?><p class="alert alert--ok">Akun berhasil dibuat. Silakan masuk.</p><?php endif; ?>
        <?php if ($error !== ""): ?><p class="alert alert--bad" role="alert"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

        <form method="post" action="" data-login>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username_input); ?>" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>

            <label class="check" for="remember"><input type="checkbox" id="remember"> Ingat username saya di perangkat ini</label>

            <button type="submit" class="block">Masuk</button>
        </form>
        <p class="auth__alt">Belum punya akun? <a href="register.php">Daftar sebagai pelanggan</a></p>
    </section>
</div>
</body>
</html>
