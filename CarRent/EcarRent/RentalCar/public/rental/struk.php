<?php
/* Struk rental eCar-Rent — dipakai bersama oleh invoice.php (semua peran) dan owner_invoice.php */
if(!function_exists('sk_e')){
function sk_e($v){return htmlspecialchars((string)($v===null||$v===''?'-':$v),ENT_QUOTES,'UTF-8');}
function sk_rp($n){return 'Rp '.number_format((float)$n,0,',','.');}
function sk_tgl($v){$t=strtotime((string)$v);return $t?date('d/m/Y',$t):'-';}
function sk_jam($v){$t=strtotime((string)$v);return $t?date('H:i',$t).' WIB':'-';}
function sk_mask($v){$v=(string)$v;$n=strlen($v);return $n>8?substr($v,0,4).str_repeat('*',$n-8).substr($v,-4):($v===''?'-':$v);}
function sk_barcode($seed){
    $x=0;$out='';$s=(int)$seed*2654435761%4294967295;
    for($i=0;$i<46;$i++){$s=($s*1103515245+12345)%2147483648;$w=1+($s>>8)%3;$g=1+($s>>12)%2;$out.='<rect x="'.$x.'" y="0" width="'.$w.'" height="46"/>';$x+=$w+$g;}
    return '<svg viewBox="0 0 '.$x.' 46" preserveAspectRatio="none" aria-hidden="true">'.$out.'</svg>';
}
function struk_render($d,$operator=''){
    $tp=strtotime((string)$d['waktu_pinjam']);$tk=strtotime((string)$d['waktu_kembali']);
    $diff=($tp&&$tk&&$tk>$tp)?$tk-$tp:0;
    $hari=$diff?max(1,(int)ceil($diff/86400)):1;
    $durD=(int)floor($diff/86400);$durH=(int)floor(($diff%86400)/3600);$durM=(int)floor(($diff%3600)/60);
    $durasi=$diff?(($durD?$durD.' hari ':'').($durH?$durH.' jam ':'').($durM?$durM.' menit':'')):'-';
    $tarif=(float)($d['harga_rental']??$d['harga_mobil']??0);
    $total=(float)$d['total_harga'];$denda=(float)($d['denda']??0);
    $platform=$total*0.10;$owner=$total*0.90;
    $st=strtolower((string)($d['status_rental']??''));
    $cls=(strpos($st,'selesai')!==false||strpos($st,'kembali')!==false)?'ok':((strpos($st,'batal')!==false||strpos($st,'tolak')!==false)?'bad':'run');
    $kend=trim(($d['merk']??'').' '.($d['tipe']??''));
    $no=str_pad((string)(int)$d['id_rental'],6,'0',STR_PAD_LEFT);
    ob_start(); ?>
<div class="rcpt-stage"><article class="rcpt" aria-label="Struk rental #<?php echo $no; ?>">
  <header class="rcpt__head">
    <svg class="rcpt__logo" viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="13" fill="#111"/><path d="M10 28l3-8c.6-1.6 2-2.5 3.7-2.5h14.6c1.7 0 3.1.9 3.7 2.5l3 8v6h-3.4a3.6 3.6 0 0 1-7.2 0H20.6a3.6 3.6 0 0 1-7.2 0H10zM15.4 26h17.2l-1.5-4.4H16.9z" fill="#ffffff"/></svg>
    <h2>eCar<span>-Rent</span></h2>
    <p>Layanan Rental Mobil</p>
    <div class="rcpt__title">SURAT TANDA TERIMA RENTAL KENDARAAN</div>
  </header>

  <dl class="rcpt__list rcpt__list--meta">
    <div><dt>No. Transaksi</dt><dd>#<?php echo $no; ?></dd></div>
    <div><dt>Dicetak</dt><dd><?php echo date('d/m/Y H:i'); ?></dd></div>
    <div><dt>Operator</dt><dd><?php echo sk_e($operator); ?></dd></div>
  </dl>

  <div class="rcpt__stamp rcpt__stamp--<?php echo $cls; ?>"><?php echo sk_e(strtoupper((string)$d['status_rental'])); ?></div>

  <h3 class="rcpt__sec">Penyewa</h3>
  <dl class="rcpt__list">
    <div><dt>Nama</dt><dd><?php echo sk_e($d['nama_pelanggan']??''); ?></dd></div>
    <div><dt>No. KTP/SIM</dt><dd><?php echo sk_e(sk_mask($d['nik_pelanggan']??'')); ?></dd></div>
    <?php if(!empty($d['no_hp_pelanggan'])): ?><div><dt>No. Telp/HP</dt><dd><?php echo sk_e($d['no_hp_pelanggan']); ?></dd></div><?php endif; ?>
    <?php if(!empty($d['alamat_pelanggan'])): ?><div><dt>Alamat</dt><dd><?php echo sk_e($d['alamat_pelanggan']); ?></dd></div><?php endif; ?>
  </dl>

  <h3 class="rcpt__sec">Kendaraan</h3>
  <dl class="rcpt__list">
    <div><dt>Jenis</dt><dd><?php echo sk_e($kend); ?></dd></div>
    <div><dt>No. Polisi</dt><dd class="rcpt__plate"><?php echo sk_e($d['plat_mobil']??''); ?></dd></div>
    <?php if(!empty($d['tahun'])): ?><div><dt>Tahun</dt><dd><?php echo sk_e($d['tahun']); ?></dd></div><?php endif; ?>
    <?php if(!empty($d['nama_pemilik'])): ?><div><dt>Pemilik</dt><dd><?php echo sk_e($d['nama_pemilik']); ?></dd></div><?php endif; ?>
  </dl>

  <h3 class="rcpt__sec">Waktu Sewa</h3>
  <div class="rcpt__trip">
    <div class="rcpt__pt"><small>DIAMBIL</small><b><?php echo sk_tgl($d['waktu_pinjam']); ?></b><span>Pukul <?php echo sk_jam($d['waktu_pinjam']); ?></span></div>
    <div class="rcpt__road"><i></i></div>
    <div class="rcpt__pt"><small>DIKEMBALIKAN</small><b><?php echo sk_tgl($d['waktu_kembali']); ?></b><span>Pukul <?php echo sk_jam($d['waktu_kembali']); ?></span></div>
  </div>
  <dl class="rcpt__list">
    <div><dt>Durasi</dt><dd><?php echo sk_e($durasi); ?></dd></div>
    <div><dt>Dihitung</dt><dd><?php echo $hari; ?> hari</dd></div>
  </dl>

  <h3 class="rcpt__sec">Rincian Biaya</h3>
  <dl class="rcpt__list">
    <div><dt>Tarif / hari</dt><dd><?php echo sk_rp($tarif); ?></dd></div>
    <div><dt><?php echo $hari; ?> hari x <?php echo sk_rp($tarif); ?></dt><dd><?php echo sk_rp($tarif*$hari); ?></dd></div>
    <?php if($denda>0): ?><div><dt>Denda</dt><dd><?php echo sk_rp($denda); ?></dd></div><?php endif; ?>
  </dl>
  <div class="rcpt__total"><span>TOTAL</span><b><?php echo sk_rp($total); ?></b></div>
  <dl class="rcpt__list rcpt__list--small">
    <div><dt>Biaya platform (10%)</dt><dd><?php echo sk_rp($platform); ?></dd></div>
    <div><dt>Pendapatan pemilik (90%)</dt><dd><?php echo sk_rp($owner); ?></dd></div>
  </dl>

  <h3 class="rcpt__sec">Cek Serah Terima</h3>
  <div class="rcpt__check">
    <div><small>BBM</small><div class="rcpt__fuel"><i></i><i></i><i></i><i></i><i></i><i></i></div><div class="rcpt__fl"><span>E</span><span>F</span></div></div>
    <ul><li>Body mulus</li><li>Ban &amp; velg</li><li>Interior bersih</li><li>STNK &amp; kunci</li></ul>
  </div>

  <p class="rcpt__terms"><b>KETENTUAN:</b> kendaraan tidak boleh dipindahtangankan/dijaminkan; kerusakan, kehilangan, dan pelanggaran hukum selama masa sewa menjadi tanggung jawab penyewa; keterlambatan dikenakan denda.</p>

  <div class="rcpt__sign"><div>Penyewa<br><b><?php echo sk_e($d['nama_pelanggan']??''); ?></b></div><div>Petugas<br><b><?php echo sk_e($operator); ?></b></div></div>

  <div class="rcpt__bar"><?php echo sk_barcode($d['id_rental']); ?><span><?php echo $no; ?></span></div>
  <p class="rcpt__thanks">** TERIMA KASIH **<br>Selamat jalan &amp; berkendara aman</p>
</article></div>
<?php return ob_get_clean(); }
}
