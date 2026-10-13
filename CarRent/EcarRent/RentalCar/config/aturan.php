<?php
/* Aturan bisnis. Ubah angka di sini, tidak perlu menyentuh halaman lain. */

define('KOMISI_PLATFORM', 0.10);       /* bagian platform dari tiap transaksi; pemilik mendapat sisanya */
define('DENDA_TOLERANSI_MENIT', 60);   /* terlambat sampai sekian menit belum kena denda */
define('DENDA_PERSEN_PER_JAM', 0.10);  /* 10% tarif harian untuk tiap jam keterlambatan */
define('DENDA_MAKS_PER_HARI', 1.00);   /* denda tiap 24 jam tidak lebih dari 100% tarif harian */

/* Lama sewa dalam hari, dibulatkan ke atas, minimal 1 (sama dengan perhitungan di form sewa). */
function jumlah_hari($mulai, $selesai) {
    $a = strtotime((string)$mulai);
    $b = strtotime((string)$selesai);
    if (!$a || !$b || $b <= $a) { return 1; }
    return max(1, (int)ceil(($b - $a) / 86400));
}

/* Denda keterlambatan dalam rupiah (bilangan bulat). */
function hitung_denda($tarif_harian, $waktu_kembali, $waktu_aktual = null) {
    $batas  = strtotime((string)$waktu_kembali);
    $aktual = $waktu_aktual ? strtotime((string)$waktu_aktual) : time();
    if (!$batas || !$aktual) { return 0; }
    $telat_menit = (int)floor(($aktual - $batas) / 60);
    if ($telat_menit <= DENDA_TOLERANSI_MENIT) { return 0; }
    $telat_jam = (int)ceil($telat_menit / 60);
    $hari_penuh = intdiv($telat_jam, 24);
    $sisa_jam = $telat_jam % 24;
    $tarif = (float)$tarif_harian;
    $total = $hari_penuh * DENDA_MAKS_PER_HARI * $tarif
           + min($sisa_jam * DENDA_PERSEN_PER_JAM, DENDA_MAKS_PER_HARI) * $tarif;
    return (int)round($total);
}

function bagian_pemilik($total) { return (float)$total * (1 - KOMISI_PLATFORM); }
function bagian_platform($total) { return (float)$total * KOMISI_PLATFORM; }
