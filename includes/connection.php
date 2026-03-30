<?php
$db = getenv('DB_NAME') ?: 'opti_info';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$socket = getenv('DB_SOCKET') ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';

if ($host !== '') {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
} else {
    // Fallback local macOS XAMPP via socket Unix
    $dsn = "mysql:unix_socket=$socket;dbname=$db;charset=$charset";
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Transforme les erreurs en exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les données sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES => false,                  // Utilise les vraies requêtes préparées (plus sûr)
];

$pdo = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}