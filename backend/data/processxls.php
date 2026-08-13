<?php
// Asegúrate de que no haya espacios en blanco al principio de este archivo.

// Dependencia de PhpSpreadsheet
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// 1. Manejo de la subida del archivo Excel
if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    die('Error en la subida del archivo: ' . (isset($_FILES['excelFile']['error']) ? $_FILES['excelFile']['error'] : 'Archivo no proporcionado.'));
}

$fileTmpPath = $_FILES['excelFile']['tmp_name'];
$uploadedFileName = $_FILES['excelFile']['name'];
$fileExtension = strtolower(pathinfo($uploadedFileName, PATHINFO_EXTENSION));

$allowedExtensions = ['xls', 'xlsx', 'xlsm'];
if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    die('Tipo de archivo no permitido. Solo se aceptan .xls, .xlsx, .xlsm.');
}

try {
    // 2. Cargar el archivo Excel
    $spreadsheet = IOFactory::load($fileTmpPath);
    $activeSheet = $spreadsheet->getActiveSheet();
    
    // 3. Generar el nombre de archivo para la descarga
    $fileName = pathinfo($uploadedFileName, PATHINFO_FILENAME) . '.csv';

    // 4. Configurar las cabeceras para forzar la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    // 5. Limpiar el búfer de salida antes de enviar el archivo
    ob_clean();
    flush();

    // 6. Crear un objeto writer para CSV y guardar directamente en la salida
    $writer = new Csv($spreadsheet);
    $writer->save('php://output');

    // 7. Salir del script
    exit;

} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    http_response_code(500);
    die('Error al leer el archivo Excel: ' . $e->getMessage());
} catch (\Exception $e) {
    http_response_code(500);
    die('Error inesperado durante el procesamiento: ' . $e->getMessage());
}
?>