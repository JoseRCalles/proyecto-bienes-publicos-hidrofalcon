<?php
$servername = "localhost";
$username = "root";
$password = ""; // Tu contraseña de base de datos
$dbname = "bienes_publicos";

try {
    // Cambio clave: usa 'utf8mb4' para soporte completo de caracteres
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Set default fetch mode to associative arrays
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log the error for debugging, and return a generic error to the frontend
    error_log("Database connection failed: " . $e->getMessage());
    // It's better not to expose internal error messages to the client
    // For production, you might want a more generic message here.
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos. Por favor, inténtelo de nuevo más tarde.']));
}
?>