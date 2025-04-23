<?php
// Incluir el archivo de conexión a la base de datos
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("Formulario recibido.");

    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $denominacion = $_POST['denominacion'];
    $domicilio = $_POST['domicilio'];
    $identificacion = $_POST['identificacion'];
    $nombre = $_POST['nombre'];
    $lugarnot = $_POST['lugarnot'];
    $notario = $_POST['notario'];
    $numprotocolo = $_POST['numprotocolo'];

    error_log("Datos del formulario recibidos: " . json_encode($_POST));

    // Ruta del archivo .md de plantilla
    $md_file = 'plantillas_contratos/prestacion_servicios/plantilla01.md';
    if (!file_exists($md_file)) {
        error_log("El archivo plantilla .md no existe: $md_file");
        echo json_encode(["error" => "El archivo plantilla no existe"]);
        exit;
    }

    // Leer el contenido del archivo .md
    $contenido = file_get_contents($md_file);
    error_log("Contenido del archivo .md cargado.");

    // Reemplazar variables
    $contenido = str_replace('{{fecha}}', $fecha, $contenido);
    $contenido = str_replace('{{denominacion}}', $denominacion, $contenido);
    $contenido = str_replace('{{domicilio}}', $domicilio, $contenido);
    $contenido = str_replace('{{identificacion}}', $identificacion, $contenido);
    $contenido = str_replace('{{apoderado}}', $nombre, $contenido);
    $contenido = str_replace('{{lugarnot}}', $lugarnot, $contenido);
    $contenido = str_replace('{{notario}}', $notario, $contenido);
    $contenido = str_replace('{{numprotocolo}}', $numprotocolo, $contenido);
    error_log("Variables reemplazadas.");

    // Crear archivo temporal .md
    $working_dir = '/tmp';
    $temp_md_file = $working_dir . '/temp_contrato.md';
    if (!is_writable($working_dir)) {
        error_log("No se puede escribir en $working_dir");
        echo json_encode(["error" => "No se puede escribir en el directorio temporal"]);
        exit;
    }

    if (!file_put_contents($temp_md_file, $contenido)) {
        error_log("No se pudo crear el archivo .md temporal.");
        echo json_encode(["error" => "Error al crear el archivo .md"]);
        exit;
    }

    // Copiar imagen a /tmp
    $img_src = 'plantillas_contratos/prestacion_servicios/tabladatos.png';
    $img_dest = $working_dir . '/tabladatos.png';
    if (!copy($img_src, $img_dest)) {
        error_log("No se pudo copiar la imagen tabladatos.png a /tmp");
        echo json_encode(["error" => "No se pudo preparar la imagen para el PDF"]);
        exit;
    }

    // Generar nombre para el PDF
    $filename_pdf = 'contrato_' . strtolower(str_replace(' ', '_', $denominacion)) . '_' . date('YmdHis') . '.pdf';
    $intermediate_pdf = $working_dir . '/' . $filename_pdf;
    $final_pdf = 'pendientes_firma/' . $filename_pdf;

    // Verificar que el directorio final es escribible
    if (!is_writable('pendientes_firma')) {
        error_log("El directorio pendientes_firma no tiene permisos de escritura.");
        echo json_encode(["error" => "El directorio pendientes_firma no es escribible"]);
        exit;
    }

    // Ejecutar Pandoc
    $template_path = __DIR__ . '/plantillas_contratos/prestacion_servicios/pandoc_header.tex';
    $cmd = "cd $working_dir && pandoc temp_contrato.md -o " . basename($intermediate_pdf) . 
           " --from markdown --template=$template_path --pdf-engine=xelatex";

    exec($cmd . " 2>&1", $output, $status);
    error_log("Comando ejecutado: $cmd");
    error_log("Salida Pandoc: " . implode("\n", $output));
    error_log("Estado Pandoc: $status");

    if ($status !== 0 || !file_exists($intermediate_pdf)) {
        error_log("Error al generar el PDF con Pandoc.");
        echo json_encode(["error" => "No se pudo generar el PDF"]);
        exit;
    }

    // Mover PDF generado a carpeta final
    if (!rename($intermediate_pdf, $final_pdf)) {
        error_log("No se pudo mover el PDF a pendientes_firma.");
        echo json_encode(["error" => "No se pudo mover el archivo generado"]);
        exit;
    }

    // Eliminar temporal .md
    unlink($temp_md_file);

    // Guardar en la base de datos
    try {
        $sql = "INSERT INTO contratos (nombre, ruta_pdf, firmado) VALUES (:nombre, :ruta_pdf, :firmado)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $denominacion);
        $stmt->bindParam(':ruta_pdf', $final_pdf);
        $stmt->bindValue(':firmado', 0, PDO::PARAM_INT);
        $stmt->execute();
        error_log("Contrato guardado en la base de datos.");
        $lastInsertId = $pdo->lastInsertId();
        
        // Concatenar el nombre del archivo PDF y el ID del contrato
        $concat_str = $filename_pdf . $lastInsertId;

        
        $file_hash = md5($concat_str);
        header("Location: previsualizar_contrato.html?pdf_path=" . urlencode($final_pdf) . "&id_contrato=" . $lastInsertId . "&file_hash=" . $file_hash);
        exit;
    } catch (PDOException $e) {
        error_log("Error al guardar en la base de datos: " . $e->getMessage());
        echo json_encode(["error" => "Error al guardar el contrato en la base de datos"]);
        exit;
    }
}
?>
