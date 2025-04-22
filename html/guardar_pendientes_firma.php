<?php
ob_start();  // Inicia el buffer de salida
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Incluir archivo de conexión a la base de datos
include('db.php');

// Carpeta donde se guardarán los PDFs
$carpetaDestino = "pendientes_firma/";

// Crear la carpeta si no existe
if (!file_exists($carpetaDestino) && !mkdir($carpetaDestino, 0777, true)) {
    echo json_encode(["error" => "No se pudo crear la carpeta $carpetaDestino"]);
    exit;
}

// Verificar si se ha recibido el archivo correctamente
if (!isset($_FILES["archivo"]) || $_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "No se recibió ningún archivo o hubo un error al subirlo"]);
    exit;
}

// Validar que el archivo sea un PDF
$tipoArchivo = mime_content_type($_FILES["archivo"]["tmp_name"]);
if ($tipoArchivo !== "application/pdf") {
    echo json_encode(["error" => "El archivo no es un PDF válido"]);
    exit;
}

// Obtener el valor de la denominación social desde el formulario
$denominacion = isset($_POST['denominacion']) ? $_POST['denominacion'] : 'Contrato_sin_nombre';

// Crear un nombre base para el archivo (convertir a minúsculas y reemplazar espacios por guiones bajos)
$nombreBase = strtolower(str_replace(" ", "_", $denominacion)) . "_contrato";

// Definir la ruta base del archivo
$rutaArchivo = $carpetaDestino . $nombreBase . ".pdf";

// Si el archivo ya existe, agregar un número al final para evitar sobrescribirlo
$i = 1;
while (file_exists($rutaArchivo)) {
    $rutaArchivo = $carpetaDestino . $nombreBase . "_$i.pdf";
    $i++;
}

// Mover el archivo subido a la carpeta de destino
if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $rutaArchivo)) {
    // Insertar el contrato en la base de datos (marcado como no firmado)
    $sql = "INSERT INTO contratos (nombre, ruta_pdf, firmado) VALUES (?, ?, ?)";

    // Preparamos la sentencia SQL para evitar inyecciones SQL
    $stmt = $pdo->prepare($sql);
    if ($stmt === false) {
        echo json_encode(["error" => "Error en la preparación de la consulta: " . $pdo->errorInfo()]);
        exit;
    }

    // Enlazamos los parámetros y ejecutamos la consulta
    $firmado = 0; // Contrato no firmado
    $stmt->bindParam(1, $denominacion);
    $stmt->bindParam(2, $rutaArchivo);
    $stmt->bindParam(3, $firmado, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Obtener el ID del contrato insertado
        $idContrato = $pdo->lastInsertId();

        // Crear la URL de redirección con el ID del contrato y la ruta del archivo PDF
        $redirectURL = "previsualizar_contrato.html?pdf=" . urlencode($rutaArchivo) . "&id=" . urlencode($idContrato);

        // Devolver la respuesta con la URL de redirección
        echo json_encode([
            "mensaje" => "Contrato guardado con éxito",
            "redirect" => $redirectURL
        ]);
        exit;
    } else {
        echo json_encode(["error" => "Error al registrar el contrato en la base de datos"]);
    }

    // Cerramos la sentencia
    $stmt = null;
} else {
    echo json_encode(["error" => "Error al guardar el archivo en la carpeta $carpetaDestino"]);
}

ob_end_flush();
?>
