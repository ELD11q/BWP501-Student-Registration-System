<?php
/*
 * Database connection (PDO).
 * Settings are read from config.local.php, or from environment variables
 * (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_SSL_CA), with XAMPP defaults.
 */

$localConfig = __DIR__ . '/../config.local.php';
$cfg = file_exists($localConfig) ? require $localConfig : [];

$dbHost  = $cfg['db_host']   ?? (getenv('DB_HOST')   ?: 'localhost');
$dbPort  = $cfg['db_port']   ?? (getenv('DB_PORT')   ?: 3306);
$dbName  = $cfg['db_name']   ?? (getenv('DB_NAME')   ?: 'student_registration_system');
$dbUser  = $cfg['db_user']   ?? (getenv('DB_USER')   ?: 'root');
$dbPass  = $cfg['db_pass']   ?? (getenv('DB_PASS')   ?: '');
$dbSslCa = $cfg['db_ssl_ca'] ?? (getenv('DB_SSL_CA') ?: '');

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements (SQL injection prevention)
];
if ($dbSslCa !== '') {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $dbSslCa;
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('DB connection failed: ' . $e->getMessage());
    die('Database connection failed. Please check the database settings (see README).');
}
