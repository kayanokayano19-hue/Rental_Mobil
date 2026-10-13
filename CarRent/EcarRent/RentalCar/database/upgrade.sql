-- eCar-Rent: tambahan database untuk versi 2
-- Jalankan SEKALI di database yang sama setelah db.sql (phpMyAdmin > Import).
-- Aman dijalankan ulang karena memakai IF NOT EXISTS.

-- Ulasan dan rating mobil. Satu rental hanya boleh punya satu ulasan.
CREATE TABLE IF NOT EXISTS `tb_ulasan` (
  `id_ulasan` int(11) NOT NULL AUTO_INCREMENT,
  `id_rental` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `plat_mobil` varchar(20) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `komentar` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ulasan`),
  UNIQUE KEY `uniq_ulasan_rental` (`id_rental`),
  KEY `idx_ulasan_plat` (`plat_mobil`),
  KEY `idx_ulasan_pelanggan` (`id_pelanggan`),
  CONSTRAINT `fk_ulasan_rental` FOREIGN KEY (`id_rental`) REFERENCES `tb_rental` (`id_rental`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ulasan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
