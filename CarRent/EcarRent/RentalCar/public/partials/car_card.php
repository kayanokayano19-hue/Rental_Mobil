<?php
/* Kartu mobil untuk halaman pelanggan. Pakai: render_car_card($rowMobil) */
include_once __DIR__ . '/../../config/ulasan.php';
function render_car_card(array $m): void {
    global $kon;
    $status = strtolower(trim($m['status'] ?? ''));
    $tersedia = !in_array($status, ['disewa', 'di sewa'], true);
    $nama = htmlspecialchars(trim(($m['merk'] ?? '') . ' ' . ($m['tipe'] ?? '')));
    $harga = isset($m['harga_rental']) ? 'Rp ' . number_format((float)$m['harga_rental'], 0, ',', '.') : '';
    $plat = htmlspecialchars($m['plat_mobil'] ?? '');
    $url_detail = 'detailmobil.php?plat_mobil=' . urlencode($m['plat_mobil'] ?? '');
    ?>
    <article class="car glass" data-plat="<?php echo $plat; ?>" data-nama="<?php echo $nama; ?>" data-harga="<?php echo (int)($m['harga_rental'] ?? 0); ?>" data-url="<?php echo htmlspecialchars($url_detail); ?>">
        <div class="car__img">
            <?php if (!empty($m['foto'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($m['foto']); ?>" alt="<?php echo $nama; ?>" loading="lazy">
            <?php else: ?>
                <div class="car__ph" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16v-5l2-5h10l2 5v5"/><path d="M3 16h18v3H3z"/><path d="M7.5 13h.01M16.5 13h.01"/></svg></div>
            <?php endif; ?>
            <span class="badge car__tag badge--<?php echo $tersedia ? 'ok' : 'info'; ?>"><?php echo $tersedia ? 'Tersedia' : 'Sedang disewa'; ?></span>
            <span class="plat"><?php echo $plat; ?></span>
        </div>
        <div class="car__body">
            <h3><?php echo $nama; ?></h3>
            <?php if (isset($kon)) { echo label_rating($kon, $m['plat_mobil'] ?? ''); } ?>
            <p class="car__meta">Tahun <?php echo htmlspecialchars($m['tahun'] ?? '-'); ?> &middot; kondisi <?php echo htmlspecialchars($m['kondisi_mobil'] ?? '-'); ?></p>
            <div class="car__foot">
                <?php if ($harga): ?><div class="car__price"><?php echo $harga; ?><small>per hari</small></div><?php else: ?><span></span><?php endif; ?>
                <a class="btn" href="<?php echo htmlspecialchars($url_detail); ?>">Lihat detail</a>
            </div>
        </div>
    </article>
    <?php
}
