<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'projeto_infraestrutura');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações da aplicação
define('SITE_NAME', 'TechStore Pro');
define('SITE_URL', 'http://localhost/projeto-infraestrutura');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();

// Classe para conexão com banco de dados
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn = null;

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
        return $this->conn;
    }
}

function verificarLogin() {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_login']);
}

function verificarMaster() {
    return verificarLogin() && $_SESSION['usuario_perfil'] === 'master';
}
?>