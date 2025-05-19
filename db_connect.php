<?php
$host = 'mysql.sqlpub.com';
$db   = 'mysql_app';
$user = 'sujiangxi';
$pass = 'U4JcgUOkcHMI1suU';
$port = 3306;

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>