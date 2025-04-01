<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Carpeta donde se guardarán los PDFs firmados (relativa a la ubicación del script PHP)
$carpetaDestino = __DIR__ . "/contratos_firmados/";

// Crear la carpeta si no existe
if (!file_exists($carpetaDestino) && !mkdir($carpetaDestino, 0777, true)) {
    echo json_encode(["error" => "No se pudo crear la carpeta '$carpetaDestino'"]);
    exit;
}

// Verificar si se ha recibido el archivo correctamente
if (!isset($_FILES["archivo"]) || $_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "No se recibió ningún archivo o hubo un error al subirlo"]);
    exit;
}

// Obtener el nombre original del archivo
$nombreArchivo = $_FILES["archivo"]["name"];

// Validar que el archivo sea un PDF
$ext = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
if (strtolower($ext) !== 'pdf') {
    echo json_encode(["error" => "El archivo no es un PDF válido"]);
    exit;
}

// Obtener el nombre sin la extensión del archivo
$nombreArchivoSinExt = pathinfo($nombreArchivo, PATHINFO_FILENAME);

// Ruta del archivo a guardar
$nombreArchivoFirmado = $nombreArchivoSinExt . "." . $ext; 
$rutaArchivo = $carpetaDestino . $nombreArchivoFirmado;


$i = 1;
while (file_exists($rutaArchivo)) {
    
    $nombreArchivoFirmado = $nombreArchivoSinExt . "_$i." . $ext;
    $rutaArchivo = $carpetaDestino . $nombreArchivoFirmado;
    $i++;
}

// Intentar mover el archivo
if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $rutaArchivo)) {
    // Obtener el ID del contrato desde la solicitud
    $idContrato = isset($_POST['id_contrato']) ? $_POST['id_contrato'] : null; 

    if (!$idContrato) {
        echo json_encode(["error" => "No se proporcionó el ID del contrato en la solicitud"]);
        exit;
    }

    
    $rutaRelativa = "contratos_firmados/" . $nombreArchivoFirmado;

    // Conexión a la base de datos
    include('db.php');

    
    $sql = "UPDATE contratos SET ruta_pdf = ?, firmado = 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $rutaRelativa);
    $stmt->bindParam(2, $idContrato, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            "mensaje" => "Contrato firmado y actualizado con éxito",
            "ruta" => $rutaRelativa
        ]);
    } else {
        // Capturar el error si la consulta falla
        $errorInfo = $stmt->errorInfo();
        echo json_encode(["error" => "Error al actualizar el contrato en la base de datos", "detalle" => $errorInfo]);
    }
} else {
    echo json_encode(["error" => "Error al guardar el archivo en la carpeta '$carpetaDestino'"]);
}
?>
