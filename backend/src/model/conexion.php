<?php  // Inicia el bloque de código PHP

// --- Datos de conexión ---
$host = 'localhost';  // Dirección del servidor de la base de datos (aquí mismo)
$user = 'root';       // Usuario de la base de datos
$password = 'sistemas';  // Contraseña del usuario
$dbname = 'DB_ElevadoresMontacarga_php'; // Nombre de la base de datos
$port = 3306;         // Puerto de conexión (por defecto MySQL usa 3306)

try {
    // Se construye el DSN (Data Source Name) con todos los datos de conexión
    $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";

    // Se crea un objeto PDO (la conexión a la BD)
    $pdo = new PDO(
        $dsn,       // Cadena de conexión (DSN)
        $user,      // Usuario
        $password,  // Contraseña
        [           // Opciones de configuración
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,   // Lanza excepciones si hay errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Los resultados se devuelven como arrays asociativos
            PDO::ATTR_PERSISTENT => false, // No usa conexiones persistentes
        ]
    );

} catch (PDOException $e) { // Captura errores de conexión (excepciones de tipo PDOException)
    // Si hay error, se muestra un mensaje y se detiene la ejecución
    die('Error de conexión: ' . $e->getMessage());
}