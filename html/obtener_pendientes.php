<?php
include('db.php'); // Conexión a la base de datos

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, nombre, ruta_pdf, firmado, fecha_firma FROM contratos WHERE firmado = 0");
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($contratos);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener contratos: " . $e->getMessage()]);
}
?>
