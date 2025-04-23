<?php
require_once('fpdf/fpdf.php');
require_once('fpdi/src/autoload.php');  // Si usas FPDI para manejar PDFs existentes

// Obtener los datos enviados por POST
$firmaBase64 = $_POST['firma'];  // Firma en formato Base64
$pdfUrl = $_POST['pdf_path'];    // Ruta al archivo PDF original

// Decodificar la firma Base64
list($type, $data) = explode(';', $firmaBase64);
list(, $data)      = explode(',', $data);
$firmaData = base64_decode($data);

// Guardar la firma en un archivo temporal
$tempFirmaFile = tempnam(sys_get_temp_dir(), 'firma');
file_put_contents($tempFirmaFile, $firmaData);

// Crear un objeto FPDI para trabajar con el PDF original
$pdf = new \setasign\Fpdi\Fpdi();
$pageCount = $pdf->setSourceFile($pdfUrl);  // Cargar el PDF original

// Importar la primera página del PDF
$templateId = $pdf->importPage(1);
$pdf->addPage();
$pdf->useTemplate($templateId);

// Agregar la firma sobre el PDF
$pdf->Image($tempFirmaFile, 50, 200, 100);  // Posicionar la firma en (50, 200) y tamaño 100mm de ancho

// Enviar el PDF firmado al cliente
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="contrato_firmado.pdf"');
$pdf->Output('I');  // Enviar el PDF al navegador para descargarlo
?>
