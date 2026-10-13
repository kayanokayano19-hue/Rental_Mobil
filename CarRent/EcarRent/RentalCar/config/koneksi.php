<?php
class database {
    private $host;
    private $database;
    private $username;
    private $password;
    private $connection;

    public function __construct($host, $database, $username, $password) {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->connection();
    }

    private function connection() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function getConnection() {
        return $this->connection;
    } 
}

$db = new database("localhost", "db", "root", "");
$kon = $db->getConnection();
 
function flash_go($pesan, $url, $app_js = '../assets/app.js?v=2040') {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Memproses...</title></head>'
       . '<body data-flash="' . htmlspecialchars((string)$pesan, ENT_QUOTES, 'UTF-8') . '" data-redirect="' . htmlspecialchars((string)$url, ENT_QUOTES, 'UTF-8') . '">'
       . '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars((string)$url, ENT_QUOTES, 'UTF-8') . '"></noscript>'
       . '<script src="' . htmlspecialchars((string)$app_js, ENT_QUOTES, 'UTF-8') . '"></script></body></html>';
}
