# eCar-Rent v2: catatan perubahan

## Cara memasang
1. Timpa folder proyek lama dengan isi zip ini.
2. Import `RentalCar/database/upgrade.sql` SEKALI ke database yang sama (phpMyAdmin > Import).
   Isinya hanya tabel `tb_ulasan`. Tanpa ini semua halaman tetap jalan, hanya fitur ulasan yang tidak muncul.
3. Kosongkan cache browser (versi aset naik ke `?v=2040`).

> PENTING: kode PHP ditulis tanpa bisa dijalankan di lingkungan saya (tidak ada PHP/MySQL).
> Struktur kode, nama kolom, dan tampilan CSS sudah diperiksa, tetapi alur lengkapnya belum diuji.
> Jalankan daftar uji di bagian bawah sebelum dipakai sungguhan.

## Fitur baru
- **Persetujuan mobil oleh admin.** Tab baru "Pengajuan Mobil": foto, data, tombol Setujui / Tolak.
  Mobil yang belum disetujui tidak tampil dan tidak bisa disewa pelanggan.
- **Denda keterlambatan otomatis.** Dihitung saat admin menekan Selesai, dipotong dari saldo pelanggan.
  Aturannya ada di `config/aturan.php`: toleransi 60 menit, 10% tarif harian per jam, maks 100% per 24 jam.
- **Pembatalan dan refund.** Pelanggan bisa membatalkan pesanan yang masih berstatus Proses. Admin bisa membatalkan kapan saja.
  Keduanya mengembalikan seluruh biaya ke saldo (sebelumnya saldo tidak pernah dikembalikan).
- **Ulasan dan rating.** Setelah rental selesai, pelanggan memberi 1 sampai 5 bintang. Rating tampil di kartu mobil dan halaman detail.
- **Riwayat rental baru** untuk pelanggan: ringkasan, filter status, durasi, peringatan terlambat, tombol nota, batal, ulasan, sewa lagi.
- **Dashboard admin:** ringkasan pendapatan platform, grafik omzet 6 bulan, filter status transaksi (termasuk Terlambat), kolom denda dan bagi hasil.
- **Portal owner dirombak:** grafik pendapatan, kartu armada (foto, rating, jumlah sewa, pendapatan per mobil),
  ubah tarif dan kondisi sendiri, laporan dengan filter bulan dan unduh CSV, ubah profil dan ganti password.

## Bug dan celah yang diperbaiki
- Halaman hapus mobil / pemilik / rental dan beberapa halaman edit admin **bisa dibuka tanpa login**. Sekarang wajib admin.
- Nota (`invoice.php`) bisa dibuka pelanggan mana pun. Sekarang pelanggan hanya melihat notanya, owner hanya mobilnya.
- Perubahan status rental (yang menyentuh saldo) kini hanya lewat POST dengan token CSRF, memakai transaksi database.
- Mobil yang sedang disewa tidak bisa dihapus. Rental yang masih berjalan tidak bisa dihapus (status mobil dan saldo jadi kacau).
- Pemilik yang dibuat admin tidak terhubung ke akun loginnya (`id_user` kosong), sehingga portal owner-nya selalu "belum terhubung".
- Pengajuan mobil owner tanpa foto gagal tersimpan (kolom foto tidak boleh NULL). Sekarang foto wajib, maks 3 MB, tipe divalidasi.
- Markup `owner_laporan.php` rusak (div tidak tertutup) dan kelas CSS `.cards`, `.form-grid`, `.field` tidak pernah ada.
- Tombol Keluar melebar keluar layar di ponsel kecil.
- `session_start()` aman dipanggil dua kali (mencegah notice yang merusak redirect).

## Yang sengaja tidak diubah (disarankan berikutnya)
- Isi saldo masih langsung bertambah tanpa pembayaran nyata. Untuk produksi, pakai payment gateway atau persetujuan admin.
- Pelanggan dikenali lewat **nama** (tidak ada kolom `id_user` di `tb_pelanggan`). Dua pelanggan bernama sama akan tertukar.
  Pencariannya sudah dipusatkan di `pelanggan_saat_ini()` di `config/guard.php` supaya mudah diganti.
- Tautan Hapus dan Verifikasi admin masih GET (sudah wajib admin, tetapi belum pakai CSRF).
- Denda masuk ke platform, bukan ke pemilik. Ubah di `owner_lib.php` / `dashboard.php` bila ingin dibagi.
- `config/koneksi.php` masih user `root` tanpa password. Ganti sebelum online.

## Daftar uji cepat
1. Login owner, ajukan mobil dengan foto: muncul "Menunggu" di armada owner, TIDAK muncul di katalog pelanggan.
2. Login admin, tab Pengajuan Mobil, Setujui: mobil muncul di katalog pelanggan.
3. Pelanggan menyewa, lalu Riwayat > Batalkan pesanan: saldo kembali, mobil Tersedia.
4. Sewa lagi, admin Proses lalu Selesai setelah waktu kembali lewat > 1 jam: denda tercatat, saldo berkurang, nota menampilkan denda.
5. Pelanggan Beri ulasan: bintang tampil di kartu mobil. Coba ulasan kedua untuk rental yang sama: ditolak.
6. Buka `mobil/Controler/hapusmobil.php?plat_mobil=X` tanpa login: harus dialihkan ke login.
7. Pelanggan A membuka `rental/invoice.php?id=` milik pelanggan B: "Invoice tidak ditemukan."
