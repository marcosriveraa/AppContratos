<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT id, nombre, ruta FROM plantillas");
    $plantillas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($plantillas);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener las plantillas: ' . $e->getMessage()]);
}
?>
