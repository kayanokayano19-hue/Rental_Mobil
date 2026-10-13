<?php
include_once(__DIR__ . '/../../../config/guard.php');
wajib_admin();
include_once("../../../config/koneksi.php");

class PemilikController {
    private $kon;

    public function __construct($connection) {
        $this->kon = $connection;
    }

    public function getPemilikData($id_pemilik) {
        $stmt = mysqli_prepare($this->kon, "SELECT * FROM tb_pemilik_mobil WHERE id_pemilik = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_pemilik);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = null;
            if ($result && mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_array($result);
            }
            mysqli_stmt_close($stmt);
            return $data;
        }
        return null;
    }
}


$controller = new PemilikController($kon);
$id_pemilik_param = $_GET['id_pemilik'] ?? '';
$pemilikData = $controller->getPemilikData($id_pemilik_param);

$nama = "";
$email = "";
$password = "";
$no_hp = "";
$alamat = "";

if ($pemilikData) {
    $nama     = $pemilikData['nama'];
    $email    = $pemilikData['email'];
    $password = $pemilikData['password'];
    $no_hp    = $pemilikData['no_hp'];
    $alamat   = $pemilikData['alamat'];
}
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Data Pemilik</title>
    <link rel="stylesheet" href="../../assets/app.css?v=2040">
<link rel="icon" type="image/svg+xml" href="../../assets/logo.svg">
    <script src="../../assets/app.js?v=2040" defer></script>
</head>
<body data-role="admin" data-pub="../../" data-user="<?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <a href="../../dashboard/dashboard.php">Home</a>
    <br/><br/>
    <table class="kv">
            <tr>
                <td>Nama</td>
                <td><?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo isset($email) ? htmlspecialchars($email) : ''; ?></td>
            </tr>
            <tr>
                <td>password</td>
                <td><?php echo isset($password) ? htmlspecialchars($password) : ''; ?></td>
            </tr>
            <tr>
                <td>no_hp</td>
                <td><?php echo isset($no_hp) ? htmlspecialchars($no_hp) : ''; ?></td>
            </tr>
            <tr>
                <td>alamat</td>
                <td><?php echo isset($alamat) ? htmlspecialchars($alamat) : ''; ?></td>
            </tr>
        </table>
</body>
</html>