<?php
$host = "localhost";
$port = "5433";
$dbname = "mivivienda_db";
$user = "postgres";
$password = "123456";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>
