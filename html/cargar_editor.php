<?php
session_start();
include 'db.php';

// Obtener la ruta del archivo desde la solicitud POST
$data = json_decode(file_get_contents("php://input"), true);
$ruta = $data['ruta'] ?? null;

if (!$ruta) {
    echo json_encode(["error" => "Ruta no proporcionada"]);
    exit;
}

// Verifica si el archivo existe
if (!file_exists($ruta)) {
    echo json_encode(["error" => "Archivo no encontrado"]);
    exit;
}

// Guardar la ruta de forma segura en la sesión o algún otro mecanismo
$_SESSION['ruta_plantilla'] = $ruta;

echo json_encode(["success" => true]);
?>
