<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("📨 Formulario recibido");

    // Recoge los campos enviados dinámicamente
    $campos_permitidos = ['fecha', 'denominacion', 'domicilio', 'identificacion', 'nombre', 'lugarnot', 'notario', 'numprotocolo', 'plantilla'];
    $datos = [];

    foreach ($campos_permitidos as $campo) {
        if (isset($_POST[$campo]) && trim($_POST[$campo]) !== '') {
            $datos[$campo] = trim($_POST[$campo]);
        }
    }

    // Verifica que al menos se haya seleccionado la plantilla
    if (!isset($datos['plantilla'])) {
        error_log("❌ Plantilla no seleccionada");
        echo json_encode(["error" => "No se ha seleccionado una plantilla"]);
        exit;
    }

    $id_plantilla = $datos['plantilla'];

    // Obtener la ruta real de la plantilla desde la base de datos
    try {
        $stmt = $pdo->prepare("SELECT ruta FROM plantillas WHERE id = :id");
        $stmt->execute([':id' => $id_plantilla]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            error_log("❌ Plantilla no encontrada en BD");
            echo json_encode(["error" => "La plantilla no existe"]);
            exit;
        }

        $ruta_plantilla = $row['ruta'];
    } catch (PDOException $e) {
        error_log("❌ DB Error al obtener plantilla: " . $e->getMessage());
        echo json_encode(["error" => "Error al obtener la plantilla"]);
        exit;
    }

    error_log("📝 Ruta de plantilla: $ruta_plantilla");

    if (!file_exists($ruta_plantilla)) {
        error_log("❌ Plantilla no encontrada: $ruta_plantilla");
        echo json_encode(["error" => "La plantilla no existe"]);
        exit;
    }

    $contenido = file_get_contents($ruta_plantilla);

    // Construir reemplazos dinámicamente solo con los campos recibidos
    $mapa_reemplazos = [
        'fecha'         => '{{fecha}}',
        'denominacion'  => '{{denominacion}}',
        'domicilio'     => '{{domicilio}}',
        'identificacion'=> '{{identificacion}}',
        'nombre'        => '{{apoderado}}',
        'lugarnot'      => '{{lugarnot}}',
        'notario'       => '{{notario}}',
        'numprotocolo'  => '{{numprotocolo}}',
    ];

    $reemplazos = [];
    foreach ($mapa_reemplazos as $clave => $placeholder) {
        if (isset($datos[$clave])) {
            $reemplazos[$placeholder] = $datos[$clave];
        }
    }

    $contenido = strtr($contenido, $reemplazos);

    // Firma
    $firma_path = __DIR__ . '/firma.md';
    if (!file_exists($firma_path)) {
        error_log("❌ Plantilla de firma no encontrada");
        echo json_encode(["error" => "No se encontró la plantilla de firma"]);
        exit;
    }

    $firma_md = strtr(file_get_contents($firma_path), $reemplazos);
    $contenido_final = $contenido . "\n\n\\newpage\n\n" . $firma_md;

    $tmp_dir = sys_get_temp_dir();
    $temp_md = $tmp_dir . '/contrato_' . uniqid() . '.md';
    if (!file_put_contents($temp_md, $contenido_final)) {
        error_log("❌ No se pudo escribir el archivo temporal");
        echo json_encode(["error" => "No se pudo generar el archivo temporal"]);
        exit;
    }

    @copy('plantillas_contratos/prestacion_servicios/tabladatos.png', $tmp_dir . '/tabladatos.png');

    $filename_pdf = 'contrato_' . strtolower(preg_replace('/\s+/', '_', $datos['denominacion'] ?? 'anonimo')) . '_' . date('YmdHis') . '.pdf';
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

    if (!rename($ruta_pdf_temp, $ruta_pdf_final)) {
        error_log("❌ No se pudo mover el PDF final");
        echo json_encode(["error" => "Error al mover el PDF generado"]);
        exit;
    }

    unlink($temp_md);

    // Guardar en base de datos
    try {
        $sql = "INSERT INTO contratos (nombre, ruta_pdf, firmado) VALUES (:nombre, :ruta_pdf, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'   => $datos['denominacion'] ?? 'Sin Nombre',
            ':ruta_pdf' => $ruta_pdf_final
        ]);
        $id_contrato = $pdo->lastInsertId();
        $hash = md5($filename_pdf . $id_contrato);

        header("Location: previsualizar_contrato.html?pdf_path=" . urlencode($ruta_pdf_final) . "&id_contrato=$id_contrato&file_hash=$hash");
        exit;
    } catch (PDOException $e) {
        error_log("❌ DB Error: " . $e->getMessage());
        echo json_encode(["error" => "Error al guardar el contrato en la base de datos"]);
        exit;
    }
}
?>
