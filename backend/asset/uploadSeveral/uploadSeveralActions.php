<?php
// Inicia el búfer de salida para capturar cualquier impresión no deseada
ob_start();
header('Content-Type: application/json');
require_once '../../vendor/autoload.php';
require_once '../../connect/connect.php';

// Asegúrate de iniciar la sesión si no lo has hecho ya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadDir = __DIR__ . '/temp_uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function sendJsonResponse($success, $message, $data = []) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function cleanHeaderName($header) {
    $header = (string)($header ?? '');
    $header = trim($header);
    $header = mb_strtolower($header, 'UTF-8');
    $header = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ', ' '],
        ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'u', 'n', '_'],
        $header
    );
    $header = preg_replace('/[^a-z0-9_]/', '', $header);
    $header = preg_replace('/_+/', '_', $header);
    return trim($header, '_');
}

function getIdByName(PDO $pdo, $tableName, $nameColumn, $nameValue) {
    if (empty($nameValue)) {
        return null;
    }
    $nameValueClean = mb_strtoupper(trim(preg_replace('/\s+/', ' ', $nameValue)), 'UTF-8');
    $sql = "SELECT id FROM `$tableName` WHERE UPPER(TRIM(`$nameColumn`)) = :nameValue";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nameValue', $nameValueClean, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['id'] ?? null;
}

function getSedeIdByName(PDO $pdo, $sedeName) {
    if (empty($sedeName)) {
        return null;
    }
    $sedeNameCleaned = trim(preg_replace('/\s+/', ' ', $sedeName));
    $sedeNameUpper = mb_strtoupper($sedeNameCleaned, 'UTF-8');
    $searchName = $sedeNameUpper;
    $prefix_full = 'ESTACION DE BOMBEO';
    $prefix_abbr = 'E/B';
    if (strpos($searchName, $prefix_full) === 0) {
        $restOfName = trim(substr($searchName, strlen($prefix_full)));
        if (!empty($restOfName)) {
            $searchName = $prefix_abbr . ' ' . $restOfName;
        } else {
            $searchName = $prefix_abbr;
        }
    }
    return getIdByName($pdo, 'sede_adm', 'sede', $searchName);
}

function getEstatusFisicoIdByName(PDO $pdo, $estatusName) {
    return getIdByName($pdo, 'estatus_fisico', 'estatus', $estatusName);
}

function getIdByFullname(PDO $pdo, $fullName) {
    $fullName = (string)($fullName ?? '');
    $fullName = trim($fullName);
    if (empty($fullName)) {
        return null;
    }
    $cleanName = preg_replace('/\bci\s*\d+\.?/i', '', $fullName);
    $cleanName = trim($cleanName);
    if (empty($cleanName)) {
        return null;
    }
    $sql = "SELECT id FROM `trabajador` WHERE TRIM(CONCAT(nombres, ' ', apellidos)) = :cleanName";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cleanName', $cleanName);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result['id'];
    }
    $nameParts = explode(' ', $cleanName);
    $count = count($nameParts);
    if ($count >= 3) {
        $nombres = implode(' ', array_slice($nameParts, 0, $count - 2));
        $apellidos = implode(' ', array_slice($nameParts, $count - 2));
        $sql = "SELECT id FROM `trabajador` WHERE TRIM(nombres) = :nombres AND TRIM(apellidos) = :apellidos";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombres', $nombres);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['id'];
        }
    }
    if ($count >= 2) {
        $nombres = implode(' ', array_slice($nameParts, 0, $count - 1));
        $apellidos = $nameParts[$count - 1];
        $sql = "SELECT id FROM `trabajador` WHERE TRIM(nombres) = :nombres AND TRIM(apellidos) = :apellidos";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombres', $nombres);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['id'];
        }
    }
    return null;
}

function checkExistence(PDO $pdo, $tableName, $idColumn, $idValue) {
    if (empty($idValue)) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$tableName` WHERE `$idColumn` = :idValue");
    $stmt->bindParam(':idValue', $idValue);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function activoExists(PDO $pdo, $codigo_activo_fijo, $serial) {
    if (empty($codigo_activo_fijo) && empty($serial)) {
        return false;
    }
    $sql = "SELECT id FROM `activo` WHERE `codigo_activo_fijo` = :codigo OR `serial` = :serial LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':codigo', $codigo_activo_fijo, PDO::PARAM_STR);
    $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function logBitacora($id_usuario, $mensaje) {
    global $pdo;
    try {
        $sql = "INSERT INTO bitacora (id_usuario, mensaje, fecha_registro) VALUES (:id_usuario, :mensaje, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':mensaje' => $mensaje
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error de bitácora: " . $e->getMessage());
        return false;
    }
}

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    sendJsonResponse(false, 'Usuario no autenticado');
}

// Verificar acción
$action = $_POST['action'] ?? '';
if (empty($action)) {
    sendJsonResponse(false, 'Parámetro "action" no especificado.');
}

// Verificar archivo
if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    sendJsonResponse(false, 'Error en la subida del archivo: ' . (isset($_FILES['excelFile']['error']) ? $_FILES['excelFile']['error'] : 'Archivo no proporcionado.'));
}

// Obtener tipo de operación
$operationType = $_POST['operation_type'] ?? '';
$isIncorporacion = (strtolower($operationType) === 'incorporation');
$isRelacion = (strtolower($operationType) === 'relation');

if ($action === 'import_assets') {
    $fileTmpPath = $_FILES['excelFile']['tmp_name'];
    $uploadedFileName = $_FILES['excelFile']['name'];
    $fileExtension = strtolower(pathinfo($uploadedFileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['xls', 'xlsx', 'xlsm', 'csv'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        sendJsonResponse(false, 'Tipo de archivo no permitido. Solo se aceptan .xls, .xlsx, .xlsm, .csv.');
    }
    $newExcelFileName = uniqid('uploaded_excel_') . '.' . $fileExtension;
    $destExcelPath = $uploadDir . $newExcelFileName;
    if (!move_uploaded_file($fileTmpPath, $destExcelPath)) {
        sendJsonResponse(false, 'No se pudo mover el archivo subido al directorio temporal.');
    }
    $csvDownloadUrl = null;
    $errors = [];
    try {
        $spreadsheet = IOFactory::load($destExcelPath);
        $activeSheet = $spreadsheet->getActiveSheet();
        $excelData = $activeSheet->toArray(null, true, true, true);
        $headerMapping = [
            'sede_adm' => ['sede_adm', 'sede'],
            'codigo_activo_fijo' => ['codigo_activo_fijo', 'codigo', 'cod'],
            'descripcion' => ['descripcion'],
            'color' => ['color'],
            'marca' => ['marca'],
            'modelo' => ['modelo'],
            'serial' => ['serial'],
            'estatus_fisico' => ['estatus_fisico', 'estatus'],
            'unidad' => ['unidad'],
            'observacion' => ['observacion'],
            'documento' => ['doc', 'documento'],
            'fecha' => ['fecha'],
            'monto' => ['costo', 'monto'],
        ];
        if ($isIncorporacion) {
            $headerMapping['custodio'] = ['custodio'];
        }
        $requiredHeadersForDB = array_keys($headerMapping);
        $headerRowIndex = -1;
        $cleanedHeaders = [];
        $dataToProcess = [];
        foreach ($excelData as $rowIndex => $row) {
            if (!is_array($row) || empty(array_filter($row))) {
                continue;
            }
            $currentCleanedHeaders = array_map('cleanHeaderName', $row);
            $foundAllRequired = true;
            foreach ($requiredHeadersForDB as $requiredHeader) {
                $foundMatch = false;
                foreach ($headerMapping[$requiredHeader] as $possibleName) {
                    if (in_array(cleanHeaderName($possibleName), $currentCleanedHeaders)) {
                        $foundMatch = true;
                        break;
                    }
                }
                if (!$foundMatch) {
                    $foundAllRequired = false;
                    break;
                }
            }
            if ($foundAllRequired) {
                $headerRowIndex = $rowIndex;
                $cleanedHeaders = $currentCleanedHeaders;
                $dataToProcess = array_slice($excelData, $headerRowIndex + 1);
                break;
            }
        }
        if ($headerRowIndex === -1) {
            unlink($destExcelPath);
            sendJsonResponse(false, 'No se pudo encontrar una fila de encabezados válida en la hoja de cálculo.');
        }
        $csvDataOutput = [$requiredHeadersForDB];
        $dataRows = [];
        $sedeIndex = array_search(cleanHeaderName('sede_adm'), $cleanedHeaders);
        $codigoIndex = array_search(cleanHeaderName('codigo_activo_fijo'), $cleanedHeaders);
        $descripcionIndex = array_search(cleanHeaderName('descripcion'), $cleanedHeaders);
        $serialIndex = array_search(cleanHeaderName('serial'), $cleanedHeaders);
        $estatusFisicoIndex = array_search(cleanHeaderName('estatus_fisico'), $cleanedHeaders);
        $custodioIndex = $isIncorporacion ? array_search(cleanHeaderName('custodio'), $cleanedHeaders) : false;
        foreach ($dataToProcess as $rowIndex => $row) {
            if (!is_array($row) || empty(array_filter($row))) {
                continue;
            }
            $sedeValue = ($sedeIndex !== false && isset($row[$sedeIndex])) ? trim($row[$sedeIndex]) : '';
            $codigoValue = ($codigoIndex !== false && isset($row[$codigoIndex])) ? trim($row[$codigoIndex]) : '';
            $descripcionValue = ($descripcionIndex !== false && isset($row[$descripcionIndex])) ? trim($row[$descripcionIndex]) : '';
            $serialValue = ($serialIndex !== false && isset($row[$serialIndex])) ? trim($row[$serialIndex]) : '';
            $estatusFisicoValue = ($estatusFisicoIndex !== false && isset($row[$estatusFisicoIndex])) ? trim($row[$estatusFisicoIndex]) : '';
            if (empty($sedeValue) && empty($descripcionValue) && empty($codigoValue) && empty($serialValue) && empty($estatusFisicoValue)) {
                break;
            }
            $rowData = [];
            $csvRow = [];
            foreach ($requiredHeadersForDB as $dbColName) {
                $value = null;
                foreach ($headerMapping[$dbColName] as $possibleHeaderName) {
                    $cleanedPossibleHeader = cleanHeaderName($possibleHeaderName);
                    $originalColIndex = array_search($cleanedPossibleHeader, $cleanedHeaders);
                    if ($originalColIndex !== false && isset($row[$originalColIndex])) {
                        $value = $row[$originalColIndex];
                        break;
                    }
                }
                $rowData[$dbColName] = $value;
                $csvRow[] = $value;
            }
            $dataRows[] = $rowData;
            $csvDataOutput[] = $csvRow;
        }
        if (!empty($csvDataOutput)) {
            $newCsvFileName = 'processed_excel_' . uniqid() . '.csv';
            $destCsvPath = $uploadDir . $newCsvFileName;
            $csvSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $csvSpreadsheet->getActiveSheet()->fromArray($csvDataOutput, null, 'A1');
            $writer = new Csv($csvSpreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            try {
                $writer->save($destCsvPath);
                $csvDownloadUrl = dirname($_SERVER['PHP_SELF']) . '/temp_uploads/' . $newCsvFileName;
            } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                $errors[] = "Error al generar el archivo CSV: " . $e->getMessage();
            }
        }
        $successfulInserts = 0;
        $failedInserts = 0;
        $addActivoUrl = '/systemahidrofalcon/backend/asset/assetActions.php?action=addAsset';
        foreach ($dataRows as $rowIndex => $rowData) {
            $currentRowNumberInExcel = ($rowIndex + $headerRowIndex + 2);
            $codigo_activo_fijo = trim($rowData['codigo_activo_fijo'] ?? '');
            $serial = trim($rowData['serial'] ?? '');
            if (activoExists($pdo, $codigo_activo_fijo, $serial)) {
                $failedInserts++;
                $errors[] = "Fila " . $currentRowNumberInExcel . " - Error: El activo con código '" . htmlspecialchars($codigo_activo_fijo) . "' o serial '" . htmlspecialchars($serial) . "' ya existe en la base de datos.";
                continue;
            }
            $sedeId = getSedeIdByName($pdo, $rowData['sede_adm']);
            $estatusId = getEstatusFisicoIdByName($pdo, $rowData['estatus_fisico']);
            $custodioId = null;
            if ($isIncorporacion) {
                $custodioId = getIdByFullname($pdo, $rowData['custodio'] ?? '');
            }
            $operationTypeForInsert = $operationType;
            if (strtolower($operationType) === 'incorporation') {
                $operationTypeForInsert = 'Incorporacion';
            } else if (strtolower($operationType) === 'relation') {
                $operationTypeForInsert = 'Sin Asignar';
            }
            $postData = [
                'sede_adm' => $sedeId,
                'cod_act_f' => $codigo_activo_fijo,
                'color' => $rowData['color'] ?? '',
                'marca' => $rowData['marca'] ?? '',
                'modelo' => $rowData['modelo'] ?? '',
                'serial' => $serial,
                'estatus' => $estatusId,
                'custodio_id' => $custodioId,
                'descripcion' => $rowData['descripcion'] ?? '',
                'codigo_u_u' => $rowData['unidad'] ?? '',
                'observacion' => $rowData['observacion'] ?? '',
                'doc' => $rowData['documento'] ?? '',
                'fecha' => $rowData['fecha'] ?? '',
                'monto' => $rowData['monto'] ?? '',
                'operation_type' => $operationTypeForInsert,
            ];
            $rowValidationErrors = [];
            if ($sedeId === null) {
                $rowValidationErrors[] = "Sede '" . htmlspecialchars($rowData['sede_adm'] ?? '') . "' no se encontró en la base de datos.";
            }
            if ($estatusId === null) {
                $rowValidationErrors[] = "Estatus '" . htmlspecialchars($rowData['estatus_fisico'] ?? '') . "' no se encontró en la base de datos.";
            }
            if ($isIncorporacion && $custodioId === null && !empty($rowData['custodio'])) {
                $rowValidationErrors[] = "Custodio '" . htmlspecialchars($rowData['custodio'] ?? '') . "' no se encontró en la base de datos.";
            }
            $fecha = trim($rowData['fecha'] ?? '');
            if (empty($fecha) || strtolower($fecha) === 's/i' || $fecha === '#######') {
                $postData['fecha'] = null;
            } else {
                try {
                    if (is_numeric($fecha)) {
                        $date = Date::excelToDateTimeObject($fecha);
                        $postData['fecha'] = $date->format('Y-m-d');
                    } else {
                        $dateFormats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y', 'Y/m/d'];
                        $parsedDate = null;
                        foreach ($dateFormats as $format) {
                            $parsedDate = DateTime::createFromFormat($format, $fecha);
                            if ($parsedDate && $parsedDate->format($format) === $fecha) {
                                $postData['fecha'] = $parsedDate->format('Y-m-d');
                                break;
                            }
                        }
                        if (!$parsedDate) {
                            $rowValidationErrors[] = "Formato de fecha inválido '" . htmlspecialchars($fecha) . "'. Se ha omitido la fecha.";
                            $postData['fecha'] = null;
                        }
                    }
                } catch (\Exception $e) {
                    $rowValidationErrors[] = "Error al procesar la fecha '" . htmlspecialchars($fecha) . "': " . $e->getMessage() . ". Se ha omitido la fecha.";
                    $postData['fecha'] = null;
                }
            }
            if (!empty($rowValidationErrors)) {
                $failedInserts++;
                $errors[] = "Fila " . $currentRowNumberInExcel . " - Errores de validación: " . implode('; ', $rowValidationErrors);
                continue;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $addActivoUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            $session_cookie = session_name() . '=' . session_id();
            curl_setopt($ch, CURLOPT_COOKIE, $session_cookie);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                $failedInserts++;
                $errors[] = "Error de cURL para fila " . $currentRowNumberInExcel . ": " . curl_error($ch) . " - Datos enviados: " . json_encode($postData);
            } else {
                $responseData = json_decode($response, true);
                if ($responseData && $responseData['success']) {
                    $successfulInserts++;
                    $usuario_id = $_SESSION['user_id'] ?? 0;
                    $mensaje = "Registro de activo (Importación Excel): Cód. Fijo '{$codigo_activo_fijo}', Descripción '{$rowData['descripcion']}'";
                    logBitacora($usuario_id, $mensaje);
                } else {
                    $failedInserts++;
                    $errorMessage = $responseData['message'] ?? 'Error desconocido';
                    if (isset($responseData['errors'])) {
                        $errorMessage .= " - Errores de validación de 'add-property.php': " . json_encode($responseData['errors']);
                    }
                    $errors[] = "Error al insertar fila " . $currentRowNumberInExcel . " (HTTP $httpCode): " . $errorMessage . " - Datos enviados: " . json_encode($postData);
                }
            }
            curl_close($ch);
        }
        if (file_exists($destExcelPath)) {
            unlink($destExcelPath);
        }
        ob_end_clean();
        sendJsonResponse(true, 'Procesamiento de archivo Excel completado.', [
            'total_rows_processed' => count($dataRows),
            'successful_inserts' => $successfulInserts,
            'failed_inserts' => $failedInserts,
            'errors' => $errors,
            'csv_download_url' => $csvDownloadUrl
        ]);
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        if (file_exists($destExcelPath)) {
            unlink($destExcelPath);
        }
        ob_end_clean();
        sendJsonResponse(false, 'Error al leer el archivo Excel: ' . $e->getMessage());
    } catch (\Exception $e) {
        if (file_exists($destExcelPath)) {
            unlink($destExcelPath);
        }
        ob_end_clean();
        sendJsonResponse(false, 'Error inesperado durante el procesamiento: ' . $e->getMessage());
    }
} else {
    sendJsonResponse(false, 'Acción no reconocida.');
}
?>
