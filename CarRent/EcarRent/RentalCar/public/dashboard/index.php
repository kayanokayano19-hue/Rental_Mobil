<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$role = strtolower($_SESSION["role"] ?? 'user');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama - Dashboard</title>
    <link rel="stylesheet" href="../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <script src="../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <h2>Selamat datang, Ini halaman Dashboard <?php echo htmlspecialchars($username); ?> (Role: <?php echo htmlspecialchars($role); ?>)!</h2> 
    
    <form method="GET" action=""><br>
        <label for="cari">Cari : </label>
        <input type="text" name="cari" id="cari" value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
        <input type="submit" value="Cari">
    </form>

    <!-- ================= DATA MOBIL (Bisa dilihat Admin, Petugas, & User) ================= -->
    <h1>Data Mobil</h1>
    <?php if ($role == 'admin'): ?>
        <a href="../mobil/view/tambah.php">Tambah Data Mobil</a>
    <?php endif; ?>
    <br><br>

    <?php
    if (isset($_GET['cari']) && $_GET['cari'] != '') {
        $cari = mysqli_real_escape_string($kon, $_GET['cari']);
        $ambildata = mysqli_query($kon, "SELECT * FROM tb_mobil WHERE plat_mobil LIKE '%".$cari."%' OR merk LIKE '%".$cari."%' OR tipe LIKE '%".$cari."%'");
    } else {
        $ambildata = mysqli_query($kon, "SELECT * FROM tb_mobil ORDER BY plat_mobil ASC");
    }
    ?>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Plat Nomor</th>
                <th>ID Pemilik</th>
                <th>Merk</th>
                <th>Tipe</th>
                <th>Status</th>
                <th>Tahun</th>
                <th>Kondisi Mobil</th>
                <th>Riwayat Kerusakan</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($ambildata) > 0) {
                while ($row = mysqli_fetch_array($ambildata)) {
                    $plat = $row['plat_mobil'];
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['plat_mobil']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['id_pemilik']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['merk']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tipe']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tahun']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['kondisi_mobil']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['riwayat_kerusakan']) . "</td>";

                    if (!empty($row['foto'])) {
                        echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['foto']) . "' alt='Foto Mobil' width='100'></td>";
                    } else {
                        echo "<td>Tidak ada foto</td>";
                    }

                    echo "<td>
                            <a href='../mobil/view/view.php?plat_mobil=$plat'>View</a>";
                    
                    if ($role == 'admin') {
                        echo " | <a href='../mobil/view/update.php?plat_mobil=$plat'>Edit</a>";
                    }
                    
                    if ($role !== 'user') {
                        echo " | <a href='../mobil/Controler/hapusmobil.php?plat_mobil=$plat' data-confirm=\"Yakin ingin menghapus data mobil ini?\">Hapus</a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10' align='center'>Data tidak ditemukan.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <br>

    <?php if ($role !== 'user'): ?>
        <h1>Data Pelanggan</h1>
        <?php if ($role == 'admin'): ?>
            <a href="../mobil/aset/pelanggan.php">Tambah Data Pelanggan</a><br>
        <?php endif; ?>
        <br>
        <?php
        if (isset($_GET['cari']) && $_GET['cari'] != '') {
            $cari = mysqli_real_escape_string($kon, $_GET['cari']);
            $ambildata_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan WHERE nik_pelanggan LIKE '%".$cari."%' OR nama LIKE '%".$cari."%' OR email LIKE '%".$cari."%'");
        } else {
            $ambildata_pelanggan = mysqli_query($kon, "SELECT * FROM tb_pelanggan ORDER BY nik_pelanggan ASC");
        }
        ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>id_pelanggan</th>
                    <th>nik pelanggan</th>
                    <th>Nama</th>
                    <th>Email</th>  
                    <th>No HP</th>
                    <th>Alamat</th>
                    <th>Saldo</th>
                    <th>Status Verifikasi</th>
                    <th>KTP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($ambildata_pelanggan) > 0) {
                    while ($row = mysqli_fetch_array($ambildata_pelanggan)) {
                        $id_pelanggan = $row['id_pelanggan']; 
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($id_pelanggan) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nik_pelanggan']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['no_hp']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['saldo']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status_verifikasi']) . "</td>";
                        
                        if (!empty($row['ktp_pelanggan'])) {
                            echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['ktp_pelanggan']) . "' alt='KTP Pelanggan' width='100'></td>";
                        } else {
                            echo "<td>Tidak ada foto</td>";
                        }

                        echo "<td>
                                <a href='../mobil/view/viewpel.php?id_pelanggan=$id_pelanggan'>View</a>";
                        
                        if ($role == 'admin') {
                            echo " | <a href='../mobil/view/updatepel.php?id_pelanggan=$id_pelanggan'>Edit</a>";
                        }
                        
                        echo " | <a href='../mobil/Controler/hapuspelanggan.php?id_pelanggan=$id_pelanggan' data-confirm=\"Yakin ingin menghapus data pelanggan ini?\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' align='center'>Data tidak ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <br><br>
    <?php endif; ?>

    <?php if ($role !== 'user'): ?>
        <h1>Data Pemilik</h1>
        <?php if ($role == 'admin'): ?>
            <a href="../mobil/aset/pemilik.php">Tambah Data Pemilik</a><br>
        <?php endif; ?>
        <br>
        <?php
        if (isset($_GET['cari']) && $_GET['cari'] != '') {
            $cari = mysqli_real_escape_string($kon, $_GET['cari']);
            $data_pemilik = mysqli_query($kon, "SELECT * FROM tb_pemilik_mobil WHERE nama LIKE '%".$cari."%' OR email LIKE '%".$cari."%'");
        } else {
            $data_pemilik = mysqli_query($kon, "SELECT * FROM tb_pemilik_mobil");
        }
        ?>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($data_pemilik) > 0) {
                    while ($row = mysqli_fetch_array($data_pemilik)) {
                        $id_pemilik = $row['id_pemilik']; 
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['no_hp']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";

                        echo "<td>
                                <a href='../mobil/view/viewpem.php?id_pemilik=$id_pemilik'>View</a>";
                        
                        if ($role == 'admin') {
                            echo " | <a href='../mobil/view/updatepem.php?id_pemilik=$id_pemilik'>Edit</a>";
                        }
                        
                        echo " | <a href='../mobil/Controler/hapuspemilik.php?id_pemilik=$id_pemilik' data-confirm=\"Yakin ingin menghapus data pemilik ini?\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' align='center'>Data tidak ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
    <?php endif; ?>

    <!-- ================= DATA RENTAL (Bisa dilihat Admin, Petugas, & User) ================= -->
    <h1>Data Rental</h1>
    <?php if ($role == 'admin'): ?>
        <a href="../mobil/aset/tambahrental.php">Tambah Data Rental</a><br>
    <?php endif; ?>
    <br>
    <?php
    if (isset($_GET['cari']) && $_GET['cari'] != '') {
        $cari = mysqli_real_escape_string($kon, $_GET['cari']);
        $data_rental = mysqli_query($kon, "SELECT * FROM tb_rental WHERE id_rental LIKE '%".$cari."%'");
    } else {
        $data_rental = mysqli_query($kon, "SELECT * FROM tb_rental");
    }
    ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>id rental</th>
                <th>plat mobil</th>
                <th>waktu pinjam</th>
                <th>waktu kembali</th>
                <th>harga rental</th>
                <th>total harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($data_rental) > 0) {
                while ($row = mysqli_fetch_array($data_rental)) {
                    $id_rental = $row['id_rental']; 
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id_rental']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['plat_mobil']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['waktu_pinjam']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['waktu_kembali']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['harga_rental']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['total_harga']) . "</td>";

                    echo "<td>
                            <a href='../mobil/view/viewrental.php?id_rental=$id_rental'>View</a>";
                    
                    if ($role == 'admin') {
                        echo " | <a href='../mobil/view/updaterental.php?id_rental=$id_rental'>Edit</a>";
                    }
                    
                    if ($role !== 'user') {
                        echo " | <a href='../mobil/Controler/hapusrental.php?id_rental=$id_rental' data-confirm=\"Yakin ingin menghapus data rental ini?\">Hapus</a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' align='center'>Data tidak ditemukan.</td></tr>";
            }
            ?>
        </tbody>
    </table><br>
<a href="../../logout.php">Logout</a> 
</body>
</html>