<?php
require_once 'db.php'; // Conexión PDO a la base de datos

// Verificamos si se ha pasado el parámetro 'data'
if (!isset($_GET['data'])) {
    echo "Error: falta el parámetro 'data'.";
    exit;
}

// Tomamos el parámetro directamente como ID
$contratoId = intval($_GET['data']); // Nos aseguramos de que sea un número entero

// Mostramos el ID en la consola del navegador
echo "<script>console.log('ID recibido: " . htmlspecialchars($contratoId) . "');</script>";

// Consultamos el contrato por ID
$stmt = $pdo->prepare("SELECT * FROM contratos WHERE id = :id");
$stmt->execute(['id' => $contratoId]);
$contrato = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contrato) {
    echo "Contrato no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato Firmado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="text-center mb-4">
            <i class="bi bi-file-earmark-check-fill text-success" style="font-size: 4rem;"></i>
            <h1 class="mt-3 text-success">Contrato Firmado</h1>
            <p class="lead">Este contrato ha sido firmado correctamente y está disponible para su descarga.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Contrato: <?php echo htmlspecialchars($contrato['nombre']); ?></h5>
                <p class="card-text"><strong>Estado:</strong> 
                    <?php echo $contrato['firmado'] == 1 ? '<span class="text-success">Firmado</span>' : '<span class="text-danger">No firmado</span>'; ?>
                </p>
                <p class="card-text"><strong>Fecha de firma:</strong> 
                    <?php echo htmlspecialchars($contrato['fecha_firma'] ?? 'No disponible'); ?>
                </p>
                <a href="<?php echo htmlspecialchars($contrato['ruta_pdf']); ?>" class="btn btn-success mt-3" download>📄 Descargar PDF</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
