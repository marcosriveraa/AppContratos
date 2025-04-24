<?php
// Asegura que se envíe como JSON
header('Content-Type: application/json');

// Conexión a la base de datos
include 'conexion.php';  // Esto carga la conexión PDO

// Leer el cuerpo de la solicitud como JSON
$datos = json_decode(file_get_contents('php://input'), true);

// Depuración: verificar los datos recibidos
error_log("Datos recibidos: " . print_r($datos, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($datos['nombre']);

    if (empty($nombre)) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre de la plantilla no puede estar vacío.']);
        exit;
    }

    // Sanitizar nombre para carpeta y archivo
    $nombre_sanitizado = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($nombre));
    $carpeta = "plantillas_contratos/$nombre_sanitizado";
    $archivo_md = "$carpeta/$nombre_sanitizado.md";

    // Verificar si ya existe
    if (is_dir($carpeta)) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe una plantilla con ese nombre.']);
        exit;
    }

    // Crear carpeta
    if (!mkdir($carpeta, 0777, true)) {
        http_response_code(500);
        error_log("Error al crear la carpeta: " . $carpeta);  // Depuración
        echo json_encode(['error' => 'No se pudo crear la carpeta.']);
        exit;
    }

    // Contenido a agregar al principio del archivo .md
    $cabecera_md = <<<EOD
---
geometry: "a4paper, left=2.5cm, right=2.5cm, top=2.5cm, bottom=2.5cm"
header-includes:
  - \usepackage{amsmath} 
---

EOD;

    // Crear archivo .md con la cabecera añadida
    $contenido_md = $cabecera_md . "#Contenido del contrato...";

    if (file_put_contents($archivo_md, $contenido_md) === false) {
        http_response_code(500);
        error_log("Error al crear el archivo .md: " . $archivo_md);  // Depuración
        echo json_encode(['error' => 'No se pudo crear el archivo .md.']);
        exit;
    }

    // Copiar archivos adicionales: pandoc_header.text y atalaya.png
    $archivo_pandoc_header = 'plantillas_contratos/pandoc_header.tex';  // Actualiza con la ruta real
    $archivo_atalaya_png = 'plantillas_contratos/atalaya.png';  // Actualiza con la ruta real

    if (!copy($archivo_pandoc_header, "$carpeta/pandoc_header.text")) {
        http_response_code(500);
        error_log("Error al copiar pandoc_header.text a: " . "$carpeta/pandoc_header.text");
        echo json_encode(['error' => 'No se pudo copiar el archivo pandoc_header.text.']);
        exit;
    }

    if (!copy($archivo_atalaya_png, "$carpeta/atalaya.png")) {
        http_response_code(500);
        error_log("Error al copiar atalaya.png a: " . "$carpeta/atalaya.png");
        echo json_encode(['error' => 'No se pudo copiar el archivo atalaya.png.']);
        exit;
    }

    // Insertar en la base de datos
    $stmt = $pdo->prepare("INSERT INTO plantillas (nombre, ruta) VALUES (?, ?)"); // Cambié $conn a $pdo
    if (!$stmt) {
        http_response_code(500);
        error_log("Error en la preparación de la consulta SQL: " . $pdo->errorInfo());  // Depuración
        echo json_encode(['error' => 'Error en la preparación de la consulta SQL.']);
        exit;
    }

    $stmt->bindParam(1, $nombre);
    $stmt->bindParam(2, $archivo_md);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'ruta' => $archivo_md, 'nombre' => $nombre]);
    } else {
        http_response_code(500);
        error_log("Error al ejecutar la consulta SQL: " . implode(", ", $stmt->errorInfo()));  // Depuración
        echo json_encode(['error' => 'Error al insertar en la base de datos.']);
    }

    $stmt->closeCursor(); // Cierra el cursor de la consulta PDO
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}

?>
