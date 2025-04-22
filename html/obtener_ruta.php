<?php
include('db.php'); // Conexión a la base de datos

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "ID no proporcionado"]);
    exit();
}

$id = intval($_GET['id']); // Convertir a número entero para seguridad

try {
    $stmt = $pdo->prepare("SELECT ruta_pdf FROM contratos WHERE id = ?");
    $stmt->execute([$id]);
    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contrato) {
        echo json_encode(["ruta_pdf" => $contrato['ruta_pdf']]);
    } else {
        echo json_encode(["error" => "Contrato no encontrado"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
}
?>
