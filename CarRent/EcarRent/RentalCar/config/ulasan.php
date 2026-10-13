<?php
/* Bantuan untuk fitur ulasan & rating. Aman dipakai walau tabel tb_ulasan belum dibuat. */

function ulasan_aktif($kon) {
    static $ada = null;
    if ($ada === null) {
        $r = mysqli_query($kon, "SHOW TABLES LIKE 'tb_ulasan'");
        $ada = ($r && mysqli_num_rows($r) > 0);
    }
    return $ada;
}

/* [plat => ['rata' => 4.5, 'jumlah' => 12]] untuk semua mobil, diambil sekali per halaman. */
function peta_rating($kon) {
    static $peta = null;
    if ($peta !== null) { return $peta; }
    $peta = [];
    if (!ulasan_aktif($kon)) { return $peta; }
    $r = mysqli_query($kon, 'SELECT plat_mobil, AVG(rating) AS rata, COUNT(*) AS jumlah FROM tb_ulasan GROUP BY plat_mobil');
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        $peta[$row['plat_mobil']] = ['rata' => (float)$row['rata'], 'jumlah' => (int)$row['jumlah']];
    }
    return $peta;
}

function ikon_bintang() {
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg>';
}

/* Label kecil "★ 4,8 (12)"; kosong kalau belum ada ulasan. */
function label_rating($kon, $plat) {
    $p = peta_rating($kon)[$plat] ?? null;
    if (!$p || $p['jumlah'] < 1) { return ''; }
    return '<span class="rate" title="Rata-rata ' . number_format($p['rata'], 1, ',', '.') . ' dari ' . (int)$p['jumlah'] . ' ulasan">'
         . ikon_bintang() . '<b>' . number_format($p['rata'], 1, ',', '.') . '</b><small>(' . (int)$p['jumlah'] . ')</small></span>';
}
