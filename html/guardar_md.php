<?php
session_start();

// Obtener los datos del contenido a guardar
$data = json_decode(file_get_contents("php://input"), true);

// Obtener la ruta y el contenido del archivo desde el cuerpo de la solicitud
$ruta = $data['ruta'] ?? '';
$contenido = $data['contenido'] ?? '';

if (!$ruta || !$contenido) {
    echo json_encode(["error" => "Ruta o contenido no proporcionados"]);
    exit;
}

// Verificar que la ruta es válida y que el archivo existe
if (!file_exists($ruta)) {
    echo json_encode(["error" => "Archivo no encontrado"]);
    exit;
}

// Sobrescribir el archivo con el contenido nuevo
if (file_put_contents($ruta, $contenido)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "No se pudo guardar el archivo"]);
}
?>
