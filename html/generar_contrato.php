<?php
// Incluir la conexión a la base de datos
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("📨 Formulario recibido");

    // Validar y recoger datos del formulario
    $campos = ['fecha', 'denominacion', 'domicilio', 'identificacion', 'nombre', 'lugarnot', 'notario', 'numprotocolo', 'plantilla'];
    foreach ($campos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            error_log("❌ Campo vacío: $campo");
            echo json_encode(["error" => "Campo requerido no proporcionado: $campo"]);
            exit;
        }
    }

    $fecha         = $_POST['fecha'];
    $denominacion  = $_POST['denominacion'];
    $domicilio     = $_POST['domicilio'];
    $identificacion= $_POST['identificacion'];
    $nombre        = $_POST['nombre'];
    $lugarnot      = $_POST['lugarnot'];
    $notario       = $_POST['notario'];
    $numprotocolo  = $_POST['numprotocolo'];
    $ruta_plantilla= $_POST['plantilla'];

    error_log("📝 Datos recibidos: " . json_encode($_POST));

    // Verificar si el archivo de plantilla existe
    if (!file_exists($ruta_plantilla)) {
        error_log("❌ Plantilla no encontrada: $ruta_plantilla");
        echo json_encode(["error" => "La plantilla no existe"]);
        exit;
    }

    // Leer la plantilla principal
    $contenido = file_get_contents($ruta_plantilla);

    // Reemplazar los placeholders
    $reemplazos = [
        '{{fecha}}'         => $fecha,
        '{{denominacion}}'  => $denominacion,
        '{{domicilio}}'     => $domicilio,
        '{{identificacion}}'=> $identificacion,
        '{{apoderado}}'     => $nombre,
        '{{lugarnot}}'      => $lugarnot,
        '{{notario}}'       => $notario,
        '{{numprotocolo}}'  => $numprotocolo
    ];
    $contenido = strtr($contenido, $reemplazos);

    // Leer plantilla de firma y aplicar los mismos reemplazos
    $firma_path = __DIR__ . '/firma.md';
    if (!file_exists($firma_path)) {
        error_log("❌ Plantilla de firma no encontrada");
        echo json_encode(["error" => "No se encontró la plantilla de firma"]);
        exit;
    }
    $firma_md = file_get_contents($firma_path);
    $firma_md = strtr($firma_md, $reemplazos);

    // Añadir firma en nueva página
    $contenido_final = $contenido . "\n\n\\newpage\n\n" . $firma_md;

    // Crear archivo temporal
    $tmp_dir = sys_get_temp_dir();
    $temp_md = $tmp_dir . '/contrato_' . uniqid() . '.md';
    if (!file_put_contents($temp_md, $contenido_final)) {
        error_log("❌ No se pudo escribir el archivo temporal");
        echo json_encode(["error" => "No se pudo generar el archivo temporal"]);
        exit;
    }

    // Copiar imagen (si aplica)
    $img_src = 'plantillas_contratos/prestacion_servicios/tabladatos.png';
    $img_dest = $tmp_dir . '/tabladatos.png';
    @copy($img_src, $img_dest);

    // Generar PDF
    $filename_pdf = 'contrato_' . strtolower(preg_replace('/\s+/', '_', $denominacion)) . '_' . date('YmdHis') . '.pdf';
    $ruta_pdf_temp = $tmp_dir . '/' . $filename_pdf;
    $ruta_pdf_final = 'pendientes_firma/' . $filename_pdf;

    $template_tex = __DIR__ . '/plantillas_contratos/prestacion_servicios/pandoc_header.tex';
    $cmd = "cd $tmp_dir && pandoc " . escapeshellarg($temp_md) . " -o " . escapeshellarg($ruta_pdf_temp) . " --from markdown --template=" . escapeshellarg($template_tex) . " --pdf-engine=xelatex";

    exec($cmd . " 2>&1", $salida, $estado);
    error_log("📦 Comando ejecutado: $cmd");
    error_log("📄 Pandoc salida: " . implode("\n", $salida));
    error_log("📌 Estado: $estado");

    if ($estado !== 0 || !file_exists($ruta_pdf_temp)) {
        error_log("❌ Error al generar PDF con Pandoc");
        echo json_encode(["error" => "Error al generar el PDF"]);
        exit;
    }

    // Mover PDF a la carpeta final
    if (!rename($ruta_pdf_temp, $ruta_pdf_final)) {
        error_log("❌ No se pudo mover el PDF final");
        echo json_encode(["error" => "Error al mover el PDF generado"]);
        exit;
    }

    // Eliminar temporal
    unlink($temp_md);

    // Guardar en base de datos
    try {
        $sql = "INSERT INTO contratos (nombre, ruta_pdf, firmado) VALUES (:nombre, :ruta_pdf, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'   => $denominacion,
            ':ruta_pdf' => $ruta_pdf_final
        ]);
        $id_contrato = $pdo->lastInsertId();
        $hash = md5($filename_pdf . $id_contrato);

        // Redirigir a la previsualización
        header("Location: previsualizar_contrato.html?pdf_path=" . urlencode($ruta_pdf_final) . "&id_contrato=$id_contrato&file_hash=$hash");
        exit;
    } catch (PDOException $e) {
        error_log("❌ DB Error: " . $e->getMessage());
        echo json_encode(["error" => "Error al guardar el contrato en la base de datos"]);
        exit;
    }
}
?>
