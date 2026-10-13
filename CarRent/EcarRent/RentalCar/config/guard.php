<?php
/*
 * Pengaman akses dan fungsi bantu yang dipakai bersama.
 * Pasang di baris paling atas halaman di folder public/:
 *     include_once(__DIR__ . '/../../config/guard.php');   // sesuaikan kedalaman folder
 *     wajib_peran(['admin', 'petugas']);
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* Alamat dasar aplikasi, mis. "" atau "/EcarRent/RentalCar", dihitung dari lokasi skrip. */
function app_base() {
    $s = $_SERVER['SCRIPT_NAME'] ?? '';
    $p = strpos($s, '/public/');
    return $p === false ? '' : substr($s, 0, $p);
}

function peran_saat_ini() {
    return strtolower((string)($_SESSION['role'] ?? ''));
}

/* Hentikan halaman kalau belum login atau perannya tidak termasuk daftar. */
function wajib_peran(array $peran) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . app_base() . '/login.php');
        exit();
    }
    if (!in_array(peran_saat_ini(), $peran, true)) {
        /* login.php akan mengarahkan pengguna yang sudah masuk ke dashboard-nya sendiri */
        header('Location: ' . app_base() . '/login.php');
        exit();
    }
}

function wajib_admin() { wajib_peran(['admin', 'petugas']); }

/* ---------- CSRF untuk form POST ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . esc(csrf_token()) . '">';
}
function csrf_valid() {
    $t = $_POST['csrf'] ?? '';
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

/* ---------- Format ---------- */
function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt_rp($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function fmt_tgl($t) {
    $ts = strtotime((string)$t);
    return $ts ? date('d M Y, H:i', $ts) : '-';
}

/* ---------- Data pelanggan yang sedang login ---------- */
/* Dicocokkan lewat nama, sama seperti halaman pelanggan lain. Satu tempat saja kalau nanti diganti ke id_user. */
function pelanggan_saat_ini($kon) {
    $nama = (string)($_SESSION['nama'] ?? $_SESSION['username'] ?? '');
    $stmt = $kon->prepare('SELECT * FROM tb_pelanggan WHERE nama = ? LIMIT 1');
    if (!$stmt) { return null; }
    $stmt->bind_param('s', $nama);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/* ---------- Status mobil & rental ---------- */
function mobil_tersedia($status) {
    return !in_array(strtolower(trim((string)$status)), ['disewa', 'di sewa'], true);
}
function rental_berakhir($status) {
    return in_array(strtolower(trim((string)$status)), ['selesai', 'dikembalikan', 'dibatalkan', 'batal', 'ditolak'], true);
}
/* Potongan SQL: hanya mobil yang sudah disetujui admin yang boleh tampil ke pelanggan. */
function sql_mobil_disetujui($alias = '') {
    $a = $alias === '' ? '' : $alias . '.';
    return "{$a}status_persetujuan = 'Disetujui'";
}

/* ---------- Pembantu tampilan ---------- */
/* Angka ringkas untuk grafik: 1,2 jt / 3,4 M / 850 rb */
function fmt_ringkas($n) {
    $n = (float)$n;
    if ($n >= 1000000000) { return number_format($n / 1000000000, 1, ',', '.') . ' M'; }
    if ($n >= 1000000)    { return number_format($n / 1000000, 1, ',', '.') . ' jt'; }
    if ($n >= 1000)       { return number_format($n / 1000, 0, ',', '.') . ' rb'; }
    return number_format($n, 0, ',', '.');
}

/* Nama bulan singkat untuk label grafik: 1 -> Jan */
function nama_bulan($m) {
    $n = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return $n[(int)$m] ?? '';
}

/* 6 bulan terakhir (termasuk bulan ini) dengan nilai dari $hasil: ['2026-10' => 1500000, ...] */
function deret_enam_bulan(array $hasil) {
    $deret = [];
    for ($i = 5; $i >= 0; $i--) {
        $ts = strtotime(date('Y-m-01') . " -$i month");
        $kunci = date('Y-m', $ts);
        $deret[] = ['kunci' => $kunci, 'label' => nama_bulan(date('n', $ts)), 'nilai' => (float)($hasil[$kunci] ?? 0)];
    }
    return $deret;
}

/* Satu tombol aksi berbentuk form POST kecil (dengan token CSRF). $kelas: view | edit | ok | danger */
function form_aksi($action, array $fields, $label, $kelas = 'view', $konfirmasi = '') {
    $h = '<form class="inline" method="post" action="' . esc($action) . '">' . csrf_field();
    foreach ($fields as $k => $v) {
        $h .= '<input type="hidden" name="' . esc($k) . '" value="' . esc($v) . '">';
    }
    $h .= '<button type="submit" class="act act--' . esc($kelas) . '"'
        . ($konfirmasi !== '' ? ' data-confirm="' . esc($konfirmasi) . '"' : '') . '>' . esc($label) . '</button></form>';
    return $h;
}

/* Jalankan query berparameter dan kembalikan mysqli_result (atau false). */
function jalankan($kon, $sql, $tipe = '', array $param = []) {
    $stmt = mysqli_prepare($kon, $sql);
    if (!$stmt) { return false; }
    if ($tipe !== '') { mysqli_stmt_bind_param($stmt, $tipe, ...$param); }
    if (!mysqli_stmt_execute($stmt)) { return false; }
    return mysqli_stmt_get_result($stmt);
}

/* Satu nilai tunggal dari query berparameter. */
function satu_nilai($kon, $sql, $tipe = '', array $param = [], $bawaan = 0) {
    $r = jalankan($kon, $sql, $tipe, $param);
    $row = $r ? mysqli_fetch_row($r) : null;
    return ($row && $row[0] !== null) ? $row[0] : $bawaan;
}

/* Foto mobil sebagai data-URI, atau '' bila kosong. */
function foto_uri($blob) {
    return ($blob !== null && $blob !== '') ? 'data:image/jpeg;base64,' . base64_encode($blob) : '';
}
