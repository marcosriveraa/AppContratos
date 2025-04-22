<?php
require_once 'db.php'; // Incluir la conexión a la base de datos

header('Content-Type: application/json'); // Establecer que el contenido será JSON

try {
    // Preparar la consulta SQL para obtener el total de contratos, total firmados y pendientes de firma
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total, 
                                  SUM(firmado = 1) AS total_firmado, 
                                  SUM(firmado = 0) AS pendientes_firma 
                           FROM contratos");
    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver los resultados como JSON
    echo json_encode($result);

} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    echo json_encode(["error" => "Error al obtener los datos: " . $e->getMessage()]);
}
?>
