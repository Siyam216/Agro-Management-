<?php
// config/db.php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'agro_mgmt';
$DB_USER = 'root';   // XAMPP default
$DB_PASS = '';       // XAMPP default
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}
