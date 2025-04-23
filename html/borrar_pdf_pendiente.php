<?php
require_once 'db.php'; // Asegúrate de que la ruta es correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idContrato = $_POST['id_contrato'] ?? null; // Se espera el ID del contrato
    $ruta = $_POST['ruta'] ?? null; // Se espera la ruta del archivo

    if ($idContrato && $ruta) {
        // Primero obtenemos la ruta del archivo desde la base de datos (aunque ya la tenemos de la URL)
        $stmt = $pdo->prepare("SELECT ruta_pdf FROM contratos WHERE id = :id");
        $stmt->bindParam(':id', $idContrato, PDO::PARAM_INT);
        $stmt->execute();

        $documento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($documento) {
            $ruta = $documento['ruta_pdf']; // Se asegura de tener la ruta correcta desde la base de datos

            // Verifica si el archivo existe y se puede eliminar
            if (file_exists($ruta) && unlink($ruta)) {
                // Eliminar el registro en la base de datos
                $stmt = $pdo->prepare("DELETE FROM contratos WHERE id = :id");
                $stmt->bindParam(':id', $idContrato, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "ok", "mensaje" => "Archivo y registro eliminados correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Archivo eliminado, pero no se pudo eliminar el registro de la base de datos."]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "mensaje" => "No se pudo eliminar el archivo físico."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "mensaje" => "Documento no encontrado en la base de datos."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "mensaje" => "Faltan parámetros."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido."]);
}
?>
