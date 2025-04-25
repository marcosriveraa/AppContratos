<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

$id_plantilla = isset($_GET['id_plantilla']) ? (int)$_GET['id_plantilla'] : 0;

if ($id_plantilla > 0) {
    $query = "SELECT c.id, c.nombre, pc.orden FROM campos c 
              JOIN campos_plantillas pc ON c.id = pc.id_campo 
              WHERE pc.id_plantilla = :id_plantilla 
              ORDER BY pc.orden ASC";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_plantilla', $id_plantilla, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result && count($result) > 0) {
            echo json_encode($result);
        } else {
            echo json_encode(['mostrar_todo' => true]);
        }
    } catch (PDOException $e) {
        echo json_encode(['mostrar_todo' => true]);
    }
} else {
    echo json_encode(['mostrar_todo' => true]);
}

$pdo = null;
?>
