<?php

$host = 'localhost';
$user = 'root';
$password = 'sistemas';
$dbname = 'DB_ElevadoresMontacarga_php';
$port = 3306;

try {
    $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}