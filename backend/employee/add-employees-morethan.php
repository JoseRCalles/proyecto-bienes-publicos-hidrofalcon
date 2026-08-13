<?php

require('../../connect/connect.php');
require '../../vendor/autoload.php';  // Autoload de Composer para PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
    <input type="file" name="excel_file" accept=".xls,.xlsx">
    <input type="submit" value="Subir y Procesar Excel">
</form>
</body>
</html>

<?php
header('Content-Type: text/html; charset=utf-8');

if (!isset($conexion) || $conexion->connect_error) {
    die("<p>Conexión fallida a la base de datos.</p>");
}

if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExtension === 'xls' || $fileExtension === 'xlsx') {
        try {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $worksheet = $spreadsheet->getActiveSheet();

            $columnNameToFind = 'UBICACIÓN FISICA';
            $targetColumnIndex = null;
            $startDataRow = null;

            // Buscar la fila del encabezado
            foreach ($worksheet->getRowIterator(1, 10) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    if (trim($cell->getValue() ?? '') === trim($columnNameToFind)) { // Búsqueda sensible a mayúsculas
                        $targetColumnIndex = Coordinate::columnIndexFromString($cell->getColumn()) - 1;
                        $startDataRow = $row->getRowIndex() + 1;
                        break 2;
                    }
                }
            }

            if ($targetColumnIndex !== null) {
                $processedData = [];

                // // Obtener todas las sedes de la base de datos para la comparación
                // $sqlSedes = "SELECT sede FROM sede_adm";
                // $resultSedes = $conexion->query($sqlSedes);
                // $sedesBD = [];
                // if ($resultSedes->num_rows > 0) {
                //     while ($rowSede = $resultSedes->fetch_assoc()) {
                //         $sedesBD[] = trim($rowSede['sede']); // Almacenar las sedes tal cual (sensible)
                //     }
                // }
                // $resultSedes->free();

                // Obtener los datos de la columna y filtrar por sede
                for ($row = $startDataRow; $row <= $worksheet->getHighestRow(); $row++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($targetColumnIndex + 1);
                    $cell = $worksheet->getCell($columnLetter . $row);
                    $cargoCompleto = trim($cell->getValue() ?? '');

                    if (!empty($cargoCompleto)) {
                        $cargoFiltrado = $cargoCompleto;

                        // // Eliminar cualquier coincidencia exacta (sensible) con las sedes de la BD
                        // foreach ($sedesBD as $sede) {
                        //     $cargoFiltrado = str_replace($sede, '', $cargoFiltrado);
                        // }

                        $processedData[] = trim($cargoFiltrado);
                    }
                }

                echo "<h2>Cargos Procesados (Sedes Eliminadas):</h2>";
                echo "<pre>";
                print_r(array_values(array_unique($processedData))); // Mostrar array puro de cargos únicos
                echo "</pre>";

                if (isset($conexion)) {
                    foreach (array_unique($processedData) as $cargo) {
                        $sql = "INSERT INTO sede_adm (sede) VALUES (?)";
                        $stmt = $conexion->prepare($sql);
                        $stmt->bind_param("s", $cargo);
                        $stmt->execute();
                    }
                    // echo "<p>Datos de cargos cargados correctamente a la base de datos.</p>";
                } else {
                    // echo "<p>¡Advertencia! No se ha establecido la conexión a la base de datos.</p>";
                }

            } else {
                echo "<p>No se encontró la columna con el encabezado '$columnNameToFind' en las primeras filas (búsqueda sensible).</p>";
            }

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('<p>Error al leer el archivo Excel: ' . htmlspecialchars($e->getMessage()) . '</p>');
        }
    } else {
        echo '<p>Por favor, sube un archivo Excel válido (.xls o .xlsx).</p>';
    }
} else {
    echo '<p>Por favor, selecciona un archivo Excel para subir.</p>';
}

?>