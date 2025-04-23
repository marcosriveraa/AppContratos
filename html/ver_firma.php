<?php
if (isset($_GET['firma']) && isset($_GET['fechaHora'])) {
    $firma = $_GET['firma'];
    $fechaHora = $_GET['fechaHora'];
    $pdfUrl = isset($_GET['pdfUrl']) ? $_GET['pdfUrl'] : '';

    // Decodificar la firma (que está en base64)
    $firmaImageData = base64_decode($firma);
} else {
    echo "Falta la firma o la fecha en los parámetros.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de la Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalles de la Firma Digital</h1>

        <div class="mb-3">
            <strong>Fecha y Hora de la Firma:</strong>
            <p><?php echo htmlspecialchars($fechaHora); ?></p>
        </div>

        <div class="mb-3">
            <strong>Firma Digitalizada:</strong>
            <img src="data:image/png;base64,<?php echo base64_encode($firmaImageData); ?>" alt="Firma Digital" style="max-width: 300px;">
        </div>

        <div class="mb-3">
            <strong>Documento PDF:</strong>
            <?php if ($pdfUrl) { ?>
                <a href="<?php echo htmlspecialchars($pdfUrl); ?>" target="_blank">Ver Documento PDF</a>
            <?php } else { ?>
                <p>No hay URL del PDF disponible.</p>
            <?php } ?>
        </div>

        <a href="dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
