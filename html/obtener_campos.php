<?php
header('Content-Type: application/json');
include 'conexion.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$idPlantilla = $_GET['idPlantilla'] ?? null;

if (!$idPlantilla) {
    echo json_encode(['error' => 'ID de plantilla no proporcionado']);
    exit;
}

// Obtener todos los campos, e incluir orden si existe en campos_plantillas
$sql = "SELECT 
            c.id AS id_campo, 
            c.nombre, 
            cp.orden 
        FROM campos c
        LEFT JOIN campos_plantillas cp 
            ON c.id = cp.id_campo AND cp.id_plantilla = ?
        ORDER BY cp.orden IS NULL, cp.orden ASC, c.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute([$idPlantilla]);
$campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($campos);
