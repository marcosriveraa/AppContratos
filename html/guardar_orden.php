<?php
header('Content-Type: application/json');
require 'conexion.php'; // Asegúrate de tener conexión PDO en este archivo

// Validar datos recibidos
$idPlantilla = $_POST['id_plantilla'] ?? null;
$idsCampo = $_POST['id_campo'] ?? [];
$ordenes = $_POST['orden'] ?? [];

if (!$idPlantilla || !is_array($idsCampo) || !is_array($ordenes)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Eliminar entradas anteriores para esta plantilla
    $stmtDelete = $pdo->prepare("DELETE FROM campos_plantillas WHERE id_plantilla = ?");
    $stmtDelete->execute([$idPlantilla]);

    // Insertar nuevas órdenes
    $stmtInsert = $pdo->prepare("INSERT INTO campos_plantillas (id_plantilla, id_campo, orden) VALUES (?, ?, ?)");

    foreach ($idsCampo as $i => $idCampo) {
        $orden = trim($ordenes[$i]);

        if ($orden !== '') { // Solo si se especificó orden
            $stmtInsert->execute([$idPlantilla, $idCampo, (int)$orden]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar: ' . $e->getMessage()
    ]);
}
?>
