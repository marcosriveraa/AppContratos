<?php
// Ruta al archivo .env (fuera del directorio actual)
$envFilePath = './.env'; // Cambia esta ruta a la ubicación real de tu archivo .env

// Verifica si el archivo .env existe
if (file_exists($envFilePath)) {
    // Carga las variables de entorno desde el archivo .env
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos($line, '#') === 0) continue;

        // Establecer cada variable de entorno
        list($name, $value) = explode('=', $line, 2);
        putenv("$name=$value");
    }
} else {
    die("El archivo .env no se encuentra en la ruta especificada.");
}

// Obtener las variables desde el .env
$host = getenv('DB_HOST') ?: 'db';  // Usa 'db' como valor por defecto si no está en el .env
$dbname = getenv('MYSQL_DATABASE') ?: 'contratos';
$user = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

try {
    // Conexión a la base de datos usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura el manejo de errores
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?>
