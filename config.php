<?php
// Define BASE_URL dinamicamente para tornar o projeto portátil
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
$sys_host = $_SERVER['HTTP_HOST'] ?? 'mysql11.serv00.com';
$dir = str_replace('\\', '/', __DIR__);
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$baseDir = str_replace($docRoot, '', $dir);
define('BASE_URL', rtrim($protocol . $sys_host . $baseDir, '/'));

// Credenciais lidas de variaveis de ambiente (Docker) com fallback
// para os valores locais usados no XAMPP. Localmente nada muda.
$host     = (getenv('DB_HOST')     !== false) ? getenv('DB_HOST')     : 'localhost';
$dbname   = (getenv('DB_NAME')     !== false) ? getenv('DB_NAME')     : 'guiartcc';
$username = (getenv('DB_USER')     !== false) ? getenv('DB_USER')     : 'root';
$password = (getenv('DB_PASSWORD') !== false) ? getenv('DB_PASSWORD') : '';

try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e){
    echo 'Erro na conexão: '.$e->getMessage();
}

?>