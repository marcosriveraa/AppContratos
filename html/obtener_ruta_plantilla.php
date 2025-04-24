<?php
include 'db.php';

// Obtener el ID de la plantilla desde el POST
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["error" => "ID no proporcionado"]);
    exit;
}

// Consulta a la base de datos para obtener la ruta del archivo
$query = $pdo->prepare("SELECT ruta FROM plantillas WHERE id = :id");
$query->execute(['id' => $id]);
$plantilla = $query->fetch(PDO::FETCH_ASSOC);

if (!$plantilla) {
    echo json_encode(["error" => "Plantilla no encontrada"]);
    exit;
}

// Reemplazar las barras invertidas por barras normales (útil si la ruta es de Windows)
$ruta = str_replace('\\', '/', $plantilla['ruta']);

// Intentar obtener la ruta absoluta
$ruta_absoluta = realpath($ruta);

if (!$ruta_absoluta || !file_exists($ruta_absoluta)) {
    echo json_encode(["error" => "Archivo no encontrado: " . $ruta_absoluta]);
    exit;
}

echo json_encode([
    'ruta' => $ruta_absoluta
]);
?>
