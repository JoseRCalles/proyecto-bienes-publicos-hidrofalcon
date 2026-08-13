<?php
// Configuración inicial
ini_set('display_errors', 1); // Desactivar en producción
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y verificar autenticación
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación (excepto para acciones públicas)
$public_actions = [
    'checkAssetExists', 'getEstatusData', 'getEstatusAdm', 'getRegisterData',
    'getPropertiesData', 'getFilteredProperties', 'getTables', 'exportAssetsToExcel'
];
$action = $_REQUEST['action'] ?? '';
if (!in_array($action, $public_actions) && (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id']))) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

require_once __DIR__ . '/../connect/connect.php';
require_once __DIR__ . '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// --- FUNCIONES COMUNES ---
/**
 * Obtiene el ID de una fila en una tabla por el nombre de una columna.
 */
function getIdByName(PDO $pdo, $tableName, $nameColumn, $nameValue) {
    if (empty($nameValue)) return null;
    $nameValueClean = mb_strtoupper(trim(preg_replace('/\s+/', ' ', $nameValue)), 'UTF-8');
    $sql = "SELECT id FROM `$tableName` WHERE UPPER(TRIM(`$nameColumn`)) = :nameValue";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nameValue', $nameValueClean, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
}

/**
 * Obtiene el ID de una sede, aplicando lógica de abreviaturas.
 */
function getSedeIdByName(PDO $pdo, $sedeName) {
    if (empty($sedeName)) return null;
    $sedeNameCleaned = trim(preg_replace('/\s+/', ' ', $sedeName));
    $sedeNameUpper = mb_strtoupper($sedeNameCleaned, 'UTF-8');
    $prefix_full = 'ESTACION DE BOMBEO';
    $prefix_abbr = 'E/B';
    if (strpos($sedeNameUpper, $prefix_full) === 0) {
        $restOfName = trim(substr($sedeNameUpper, strlen($prefix_full)));
        $searchName = $restOfName ? $prefix_abbr . ' ' . $restOfName : $prefix_abbr;
    } else {
        $searchName = $sedeNameUpper;
    }
    return getIdByName($pdo, 'sede_adm', 'sede', $searchName);
}

/**
 * Obtiene el ID del estatus físico.
 */
function getEstatusFisicoIdByName(PDO $pdo, $estatusName) {
    return getIdByName($pdo, 'estatus_fisico', 'nombre_estatus', $estatusName);
}

/**
 * Obtiene el ID del custodio (por ID o nombre).
 */
function getCustodioId(PDO $pdo, $input) {
    $input = trim($input ?? '');
    if (empty($input)) return null;
    if (ctype_digit($input)) {
        $sql = "SELECT id FROM trabajador WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $input, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
    }
    $cleanName = preg_replace('/\bci\s*\d+\.?/i', '', $input);
    $cleanName = trim($cleanName);
    if (empty($cleanName)) return null;
    $sql = "SELECT id FROM `trabajador` WHERE TRIM(CONCAT(nombres, ' ', apellidos)) = :cleanName";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cleanName', $cleanName);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
}

/**
 * Obtiene el nombre completo de un custodio por su ID.
 */
function getCustodioNombre(PDO $pdo, $custodio_id) {
    if (empty($custodio_id)) return 'sin asignar';
    try {
        $sql = "SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM trabajador WHERE id = :custodio_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':custodio_id', $custodio_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['nombre_completo'] ?? 'sin asignar';
    } catch (PDOException $e) {
        error_log("Error al obtener nombre de custodio: " . $e->getMessage());
        return 'sin asignar';
    }
}

/**
 * Obtiene el código de activo fijo por ID.
 */
function getCodigoActivoFijo(PDO $pdo, $asset_id) {
    if (empty($asset_id)) return 'desconocido';
    try {
        $sql = "SELECT codigo_activo_fijo FROM activo WHERE id = :asset_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['codigo_activo_fijo'] ?? 'desconocido';
    } catch (PDOException $e) {
        error_log("Error al obtener código de activo: " . $e->getMessage());
        return 'desconocido';
    }
}

/**
 * Registra un evento en la bitácora.
 */
function logBitacora($pdo, $id_usuario, $mensaje) {
    try {
        $sql = "INSERT INTO bitacora (id_usuario, mensaje) VALUES (:id_usuario, :mensaje)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id_usuario' => $id_usuario, ':mensaje' => $mensaje]);
    } catch (PDOException $e) {
        error_log("Error de bitácora para usuario ID $id_usuario: " . $e->getMessage());
        return false;
    }
}

// --- ACCIONES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'addAsset') {
        // =============================================
        // Acción: Registrar un activo
        // =============================================
        $response = ['success' => false, 'message' => '', 'debug' => []];
        $errors = [];
        // Validar y limpiar datos
        $cod_act_f = trim($_POST['cod_act_f'] ?? '');
        if (empty($cod_act_f) || !ctype_digit($cod_act_f)) $errors['cod_act_f'] = 'Código de Activo Fijo inválido.';
        $descripcion = trim($_POST['descripcion'] ?? '');
        if (empty($descripcion)) $errors['descripcion'] = 'Descripción obligatoria.';
        $color = trim($_POST['color'] ?? '');
        if (empty($color)) $errors['color'] = 'Color obligatorio.';
        $marca = trim($_POST['marca'] ?? '');
        if (empty($marca)) $errors['marca'] = 'Marca obligatoria.';
        $modelo_for_db = (trim($_POST['modelo'] ?? '') !== 's/i') ? trim($_POST['modelo']) : null;
        $serial_for_db = (trim($_POST['serial'] ?? '') !== 's/i') ? trim($_POST['serial']) : null;
        $estatus_fisico_input = trim($_POST['estatus'] ?? '');
        $estatus_fisico_id = ctype_digit($estatus_fisico_input) ? (int)$estatus_fisico_input : getEstatusFisicoIdByName($pdo, $estatus_fisico_input);
        if (empty($estatus_fisico_id)) $errors['estatus'] = 'Estatus inválido.';
        $codigo_u_u = trim($_POST['codigo_u_u'] ?? '');
        if (empty($codigo_u_u)) $errors['codigo_u_u'] = 'Código de Unidad obligatorio.';
        $custodio_id_for_db = getCustodioId($pdo, trim($_POST['custodio_id'] ?? ''));
        $observacion_for_db = (trim($_POST['observacion'] ?? '') !== 'n/a') ? trim($_POST['observacion']) : null;
        $doc_for_db = (trim($_POST['doc'] ?? '') !== 's/i' && trim($_POST['doc'] ?? '') !== '0') ? trim($_POST['doc']) : null;
        $fecha_for_db = null;
        $fecha = trim($_POST['fecha'] ?? '');
        if (!empty($fecha) && mb_strtolower($fecha, 'UTF-8') !== 's/i') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $dateObject = DateTime::createFromFormat('Y-m-d', $fecha);
                $fecha_for_db = $dateObject ? $fecha : null;
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $fecha)) {
                $parts = explode('/', $fecha);
                $year = ($parts[2] < 70) ? '20' . $parts[2] : '19' . $parts[2];
                $dateObject = DateTime::createFromFormat('d/m/Y', "$parts[0]/$parts[1]/$year");
                $fecha_for_db = $dateObject ? $dateObject->format('Y-m-d') : null;
            }
            if (!$fecha_for_db) $errors['fecha'] = 'Fecha inválida.';
        }
        $monto_for_db = null;
        $monto_str = trim($_POST['monto'] ?? '');
        if (!empty($monto_str) && $monto_str !== '-' && mb_strtolower($monto_str, 'UTF-8') !== 's/i') {
            $monto_str = str_replace(',', '.', $monto_str);
            if (!is_numeric($monto_str) || (float)$monto_str < 0) $errors['monto'] = 'Monto inválido.';
            else $monto_for_db = (float)$monto_str;
        }
        $sede_adm_id = ctype_digit(trim($_POST['sede_adm'] ?? '')) ? (int)trim($_POST['sede_adm']) : getSedeIdByName($pdo, trim($_POST['sede_adm'] ?? ''));
        if (empty($sede_adm_id)) $errors['sede_adm'] = 'Sede Administrativa inválida.';
        $operation_type = trim($_POST['operation_type'] ?? '');
        $estatus_administrativo_id = ['Incorporacion' => 1, 'Desincorporacion' => 2, 'Sin Asignar' => 3][$operation_type] ?? null;
        if (empty($estatus_administrativo_id)) $errors['operation_type'] = 'Tipo de operación inválido.';
        if (!empty($errors)) {
            $response['message'] = 'Errores de validación.';
            $response['errors'] = $errors;
            echo json_encode($response);
            exit;
        }
        try {
            $sql = "INSERT INTO activo (
                codigo_activo_fijo, descripcion, color, marca, modelo, serial, estatus_fisico,
                unidad, custodio, observacion, documento, fecha, monto, estatus_administrativo, sede_adm
            ) VALUES (
                :codigo_activo_fijo, :descripcion, :color, :marca, :modelo, :serial, :estatus_fisico,
                :codigo_unidad_unidad, :custodio, :observacion, :documento, :fecha, :monto, :estatus_administrativo, :sede_adm
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':codigo_activo_fijo', $cod_act_f, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':color', $color, PDO::PARAM_STR);
            $stmt->bindParam(':marca', $marca, PDO::PARAM_STR);
            $stmt->bindValue(':modelo', $modelo_for_db, $modelo_for_db ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':serial', $serial_for_db, $serial_for_db ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':estatus_fisico', $estatus_fisico_id, PDO::PARAM_INT);
            $stmt->bindParam(':codigo_unidad_unidad', $codigo_u_u, PDO::PARAM_STR);
            $stmt->bindValue(':custodio', $custodio_id_for_db, $custodio_id_for_db ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':observacion', $observacion_for_db, $observacion_for_db ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':documento', $doc_for_db, $doc_for_db ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':fecha', $fecha_for_db, $fecha_for_db ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':monto', $monto_for_db, $monto_for_db ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':estatus_administrativo', $estatus_administrativo_id, PDO::PARAM_INT);
            $stmt->bindParam(':sede_adm', $sede_adm_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Activo registrado exitosamente.';
                $custodio_nombre = getCustodioNombre($pdo, $custodio_id_for_db);
                logBitacora($pdo, $_SESSION['usuario_id'], "Incorporación de nuevo activo (Código: $cod_act_f) a cargo de {$custodio_nombre}.");
            } else {
                throw new Exception("Error al ejecutar la consulta.");
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log("Error en registerAsset: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'updateAssetCustodian') {
        // =============================================
        // Acción: Actualizar custodio de un activo
        // =============================================
        $response = ['success' => false, 'message' => ''];
        $asset_id = filter_input(INPUT_POST, 'asset_id', FILTER_VALIDATE_INT);
        $new_custodio_id = filter_input(INPUT_POST, 'new_custodio_id', FILTER_VALIDATE_INT);
        if ($asset_id === null || $asset_id === false) {
            $response['message'] = 'ID de activo no válido.';
            echo json_encode($response);
            exit;
        }
        if (isset($_POST['new_custodio_id']) && strtolower(trim($_POST['new_custodio_id'])) === 'null') {
            $new_custodio_id = null;
        } elseif ($new_custodio_id === 0) {
            $new_custodio_id = null;
        } elseif ($new_custodio_id === false && !empty($_POST['new_custodio_id'])) {
            $response['message'] = 'ID de custodio no válido.';
            echo json_encode($response);
            exit;
        }
        $operation_type = trim($_POST['operation_type'] ?? '');
        $estatus_administrativo_id = ['Incorporacion' => 1, 'Desincorporacion' => 2, 'Sin Asignar' => 3][$operation_type] ?? null;
        if (empty($estatus_administrativo_id)) {
            $response['message'] = 'Tipo de operación no válido.';
            echo json_encode($response);
            exit;
        }
        try {
            $query = "UPDATE activo SET custodio = :new_custodio_id, estatus_administrativo = :estatus_administrativo_id WHERE id = :asset_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':asset_id' => $asset_id,
                ':new_custodio_id' => $new_custodio_id,
                ':estatus_administrativo_id' => $estatus_administrativo_id
            ]);
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Activo actualizado exitosamente.';
                $codigo_activo = getCodigoActivoFijo($pdo, $asset_id);
                $nuevo_custodio_nombre = getCustodioNombre($pdo, $new_custodio_id);
                logBitacora($pdo, $_SESSION['usuario_id'], "Incorporación de activo (Código: $codigo_activo) - a nombre de: '$nuevo_custodio_nombre'.");
            } else {
                $response['message'] = 'No se encontró el activo o no se realizaron cambios.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log("Error en updateAssetCustodian: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'desincorporateAsset') {
        // =============================================
        // Acción: Desincorporar un activo
        // =============================================
        $response = ['success' => false, 'message' => ''];
        $assetId = filter_input(INPUT_POST, 'assetId', FILTER_VALIDATE_INT);
        if ($assetId === null || $assetId === false) {
            $response['message'] = 'ID de activo no válido.';
            echo json_encode($response);
            exit;
        }
        try {
            $estatus_desincorporado_id = 2;
            $query = 'UPDATE activo SET estatus_administrativo = :estatus_id WHERE id = :id';
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':estatus_id', $estatus_desincorporado_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $assetId, PDO::PARAM_INT);
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Activo desincorporado exitosamente.';
                $codigo_activo = getCodigoActivoFijo($pdo, $assetId);
                logBitacora($pdo, $_SESSION['usuario_id'], "Desincorporación de activo (Código: $codigo_activo)");
            } else {
                $response['message'] = 'El activo no fue encontrado o ya estaba desincorporado.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log("Error en desincorporateAsset: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'checkUserOrCedula') {
        // =============================================
        // Acción: Verificar existencia de usuario o cédula
        // =============================================
        function verificarExistencia(PDO $pdo, string $tabla, string $campo, string $valor): bool {
            $sql = "SELECT $campo FROM $tabla WHERE $campo = :valor";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':valor', $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch() !== false;
        }
        if (isset($_POST['usuario'])) {
            $usuario = $_POST['usuario'];
            $existeUsuario = verificarExistencia($pdo, 'usuarios', 'usuario', $usuario);
            echo json_encode(['exists' => $existeUsuario]);
        } elseif (isset($_POST['cedula'])) {
            $cedula = $_POST['cedula'];
            $existeCedula = verificarExistencia($pdo, 'usuarios', 'cedula', $cedula);
            echo json_encode(['exists' => $existeCedula]);
        } else {
            echo json_encode(['exists' => false, 'error' => 'No se proporcionó usuario ni cédula.']);
        }
    }
    elseif ($action === 'checkAssetExists') {
        // =============================================
        // Acción: Verificar existencia de activo por código o serial
        // =============================================
        $response = ['exists' => false, 'field' => null];
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $codActivoFijo = $data['codigo_activo_fijo'] ?? null;
        $serial = $data['serial'] ?? null;
        try {
            if ($codActivoFijo) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM activo WHERE codigo_activo_fijo = :codigo_activo_fijo");
                $stmt->bindParam(':codigo_activo_fijo', $codActivoFijo, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->fetchColumn() > 0) {
                    $response['exists'] = true;
                    $response['field'] = 'codigo_activo_fijo';
                    echo json_encode($response);
                    exit;
                }
            }
            if ($serial) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM activo WHERE serial = :serial");
                $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->fetchColumn() > 0) {
                    $response['exists'] = true;
                    $response['field'] = 'serial';
                    echo json_encode($response);
                    exit;
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            error_log("Error en checkAssetExists: " . $e->getMessage());
            $response['message'] = 'Error interno.';
            echo json_encode($response);
        }
    }
    elseif ($action === 'assignAssetToEmployee') {
        // =============================================
        // Acción: Asignar/Transferir activo a empleado
        // =============================================
        $response = ['success' => false, 'message' => ''];
        $employeeId = isset($_POST['employeeId']) ? intval($_POST['employeeId']) : 0;
        $assetId = isset($_POST['assetId']) ? intval($_POST['assetId']) : 0;
        if ($employeeId <= 0 || $assetId <= 0) {
            $response['message'] = 'ID de Trabajador o ID de Activo inválido(s) proporcionado(s).';
            echo json_encode($response);
            exit();
        }
        try {
            // Obtener el custodio actual del activo (si existe)
            $currentCustodianQuery = $pdo->prepare("SELECT custodio FROM activo WHERE id = :assetId");
            $currentCustodianQuery->bindParam(':assetId', $assetId, PDO::PARAM_INT);
            $currentCustodianQuery->execute();
            $currentCustodian = $currentCustodianQuery->fetch(PDO::FETCH_ASSOC);
            if (!$currentCustodian) {
                $response['message'] = 'Activo no encontrado.';
                echo json_encode($response);
                exit();
            }
            $previousCustodianId = $currentCustodian['custodio'];
            $previousCustodianName = getCustodioNombre($pdo, $previousCustodianId);
            // Actualizar el custodio del activo
            $stmt = $pdo->prepare("UPDATE activo SET custodio = :employeeId WHERE id = :assetId");
            $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(':assetId', $assetId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Activo asignado/transferido correctamente.';
                $codigo_activo = getCodigoActivoFijo($pdo, $assetId);
                $nuevo_custodio_nombre = getCustodioNombre($pdo, $employeeId);
                // Registrar en bitácora según el tipo de operación
                if ($previousCustodianId === null) {
                    // Asignación inicial
                    logBitacora($pdo, $_SESSION['usuario_id'], "Asignación inicial de activo (Código: $codigo_activo) a nombre de: '$nuevo_custodio_nombre'.");
                } else {
                    // Transferencia entre custodios
                    logBitacora($pdo, $_SESSION['usuario_id'], "Transferencia de activo (Código: $codigo_activo) de '$previousCustodianName' a '$nuevo_custodio_nombre'.");
                }
            } else {
                $response['message'] = 'Activo no encontrado o el custodio ya estaba asignado.';
            }
        } catch (PDOException $e) {
            error_log("Error al asignar/transferir activo: " . $e->getMessage());
            $response['message'] = 'Error al procesar la solicitud. Por favor, inténtelo de nuevo.';
        }
        echo json_encode($response);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'getEstatusData') {
        // =============================================
        // Acción: Obtener estatus físicos
        // =============================================
        $response = ['success' => false, 'data' => [], 'message' => ''];
        try {
            $sql = "SELECT id, estatus AS nombre FROM estatus_fisico ORDER BY estatus ASC";
            $stmt = $pdo->query($sql);
            $estatus_list = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estatus_list[] = $row;
            }
            $response['success'] = true;
            $response['data'] = $estatus_list;
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            error_log("Error en getEstatusData: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'getEstatusAdm') {
        // =============================================
        // Acción: Obtener estatus administrativos
        // =============================================
        $response = ['success' => false, 'data' => [], 'message' => ''];
        try {
            $query = "SELECT id, estatus FROM estatus_administrativo ORDER BY estatus ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $response['success'] = true;
            $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            error_log("Error en getEstatusAdm: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'getRegisterData') {
        // =============================================
        // Acción: Obtener datos para registro (gerencias, cargos, preguntas)
        // =============================================
        try {
            $stmt_gerencias = $pdo->query("SELECT id, gerencia AS nombre_gerencia FROM gerencia_adm ORDER BY gerencia ASC");
            $gerencias = $stmt_gerencias->fetchAll(PDO::FETCH_ASSOC);
            $stmt_cargos = $pdo->query("SELECT id, cargo AS nombre_cargo FROM cargo ORDER BY cargo ASC");
            $cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_preguntas = $pdo->query("SELECT id, pregunta FROM preg_seguridad ORDER BY id ASC");
            $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'gerencias' => $gerencias,
                'cargos' => $cargos,
                'preguntas' => $preguntas
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }
    elseif ($action === 'getPropertiesData') {
        // =============================================
        // Acción: Obtener datos de activos (búsqueda)
        // =============================================
        $response = ['success' => false, 'message' => '', 'headers' => [], 'data' => [], 'total_count' => 0];
        try {
            $search_term = $_GET['search'] ?? '';
            $search_param = '%' . $search_term . '%';
            $sql = "
                SELECT
                    a.id, a.codigo_activo_fijo, a.descripcion, a.color, a.marca, a.modelo, a.serial,
                    e.estatus AS nombre_estatus, a.codigo_u_u,
                    CONCAT(t.nombres, ' ', t.apellidos) AS custodio_nombre,
                    t.cedula AS custodio_cedula, ca.cargo AS custodio_cargo, ga.gerencia AS custodio_gerencia,
                    a.observacion, a.doc, a.fecha, a.monto, a.fecha_mov
                FROM activo AS a
                LEFT JOIN estatus AS e ON a.estatus = e.id
                LEFT JOIN trabajador AS t ON a.custodio = t.id
                LEFT JOIN cargo AS ca ON t.cargo = ca.id
                LEFT JOIN gerencia_adm AS ga ON t.gerencia = ga.id
                WHERE a.codigo_activo_fijo LIKE :search_param OR a.descripcion LIKE :search_param
                ORDER BY a.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':search_param', $search_param, PDO::PARAM_STR);
            $stmt->execute();
            $headers_map = [
                'id' => 'ID', 'codigo_activo_fijo' => 'Cód. Act. Fijo', 'descripcion' => 'Descripción',
                'color' => 'Color', 'marca' => 'Marca', 'modelo' => 'Modelo', 'serial' => 'Serial',
                'nombre_estatus' => 'Estatus', 'codigo_u_u' => 'Cód. Unidad',
                'custodio_nombre' => 'Custodio', 'custodio_cedula' => 'Cédula', 'custodio_cargo' => 'Cargo',
                'custodio_gerencia' => 'Gerencia', 'observacion' => 'Observación',
                'doc' => 'Doc', 'fecha' => 'Fecha', 'monto' => 'Monto', 'fecha_mov' => 'Fecha Mov.'
            ];
            $fetched_headers = [];
            if ($stmt->columnCount() > 0) {
                for ($i = 0; $i < $stmt->columnCount(); $i++) {
                    $col_meta = $stmt->getColumnMeta($i);
                    $field_name = $col_meta['name'];
                    $fetched_headers[$field_name] = $headers_map[$field_name] ?? ucfirst(str_replace('_', ' ', $field_name));
                }
            }
            $response['headers'] = array_values($fetched_headers);
            $assets_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            foreach ($assets_raw as $row) {
                $formatted_row = [];
                foreach ($fetched_headers as $key => $display_name) {
                    $formatted_row[] = htmlspecialchars($row[$key] ?? '');
                }
                $data[] = $formatted_row;
            }
            $response['success'] = true;
            $response['data'] = $data;
            $response['total_count'] = count($data);
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            error_log("Error en getPropertiesData: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'getFilteredAssets') {
        // =============================================
        // Acción: Obtener activos filtrados
        // =============================================
        $response = ['success' => false, 'message' => '', 'assets' => [], 'total_assets' => 0];
        try {
            $search = $_GET['search'] ?? '';
            $estatus_fisico_id = $_GET['estatus_fisico_id'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;
            $where_clauses = ["a.estatus_administrativo != 2"];
            $params = [];
            if (!empty($search)) {
                $where_clauses[] = "(a.codigo_activo_fijo LIKE :search1 OR a.descripcion LIKE :search2 OR a.serial LIKE :search3 OR a.marca LIKE :search4 OR a.modelo LIKE :search5)";
                $params[':search1'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
                $params[':search3'] = '%' . $search . '%';
                $params[':search4'] = '%' . $search . '%';
                $params[':search5'] = '%' . $search . '%';
            }
            if (!empty($estatus_fisico_id)) {
                $where_clauses[] = "a.estatus_fisico = :estatus_fisico_id";
                $params[':estatus_fisico_id'] = $estatus_fisico_id;
            }
            $where_sql = count($where_clauses) > 0 ? ' WHERE ' . implode(' AND ', $where_clauses) : '';
            $count_query = "SELECT COUNT(a.id) AS total_count FROM activo a LEFT JOIN estatus_fisico esf ON a.estatus_fisico = esf.id LEFT JOIN estatus_administrativo esa ON a.estatus_administrativo = esa.id" . $where_sql;
            $stmt_count = $pdo->prepare($count_query);
            foreach ($params as $key => $value) $stmt_count->bindValue($key, $value);
            $stmt_count->execute();
            $total_assets = $stmt_count->fetchColumn();
            $main_query = "
                SELECT a.id, a.codigo_activo_fijo, a.descripcion, a.serial, a.marca, a.modelo,
                esf.id AS estatus_fisico_id, esf.estatus AS estatus_fisico_name, esa.estatus AS estatus_admin_name
                FROM activo a
                LEFT JOIN estatus_fisico esf ON a.estatus_fisico = esf.id
                LEFT JOIN estatus_administrativo esa ON a.estatus_administrativo = esa.id
                LEFT JOIN trabajador t ON a.custodio = t.id
                LEFT JOIN cargo c ON t.cargo = c.id
                LEFT JOIN gerencia_adm ga ON t.gerencia = ga.id
                " . $where_sql . " LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($main_query);
            foreach ($params as $key => $value) $stmt->bindValue($key, $value);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $response['success'] = true;
            $response['assets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['total_assets'] = $total_assets;
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            error_log("Error en getFilteredProperties: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'getTables') {
        // =============================================
        // Acción: Obtener tabla de activos (paginada y filtrada)
        // =============================================
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $getAll = isset($_GET['all']) && $_GET['all'] === 'true';
        $page = max(1, $page);
        $limit = max(1, $limit);
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $estatus_fisico_id = isset($_GET['estatus_fisico_id']) ? $_GET['estatus_fisico_id'] : '';
        $estatus_administrativo_id = isset($_GET['estatus_administrativo_id']) ? $_GET['estatus_administrativo_id'] : '';
        $gerencia_id = isset($_GET['gerencia_id']) ? $_GET['gerencia_id'] : '';
        $fecha_adquisicion_start = isset($_GET['fecha_adquisicion_start']) ? $_GET['fecha_adquisicion_start'] : '';
        $fecha_adquisicion_end = isset($_GET['fecha_adquisicion_end']) ? $_GET['fecha_adquisicion_end'] : '';
        try {
            $where_conditions = "WHERE 1=1";
            $params = [];
            if (!empty($search)) {
                $where_conditions .= " AND (a.descripcion LIKE :search_desc OR a.serial LIKE :search_serial OR CONCAT(t.nombres, ' ', t.apellidos) LIKE :search_custodio_name OR a.codigo_activo_fijo LIKE :search_codigo_activo_fijo)";
                $params[':search_desc'] = '%' . $search . '%';
                $params[':search_serial'] = '%' . $search . '%';
                $params[':search_custodio_name'] = '%' . $search . '%';
                $params[':search_codigo_activo_fijo'] = '%' . $search . '%';
            }
            if (!empty($estatus_fisico_id)) {
                $where_conditions .= " AND a.estatus_fisico = :estatus_fisico_id_param";
                $params[':estatus_fisico_id_param'] = (int)$estatus_fisico_id;
            }
            if (!empty($estatus_administrativo_id)) {
                $where_conditions .= " AND a.estatus_administrativo = :estatus_administrativo_id_param";
                $params[':estatus_administrativo_id_param'] = (int)$estatus_administrativo_id;
            }
            if (!empty($gerencia_id)) {
                $where_conditions .= " AND t.gerencia = :gerencia_id";
                $params[':gerencia_id'] = (int)$gerencia_id;
            }
            if (!empty($fecha_adquisicion_start)) {
                $where_conditions .= " AND a.fecha >= :fecha_adq_start";
                $params[':fecha_adq_start'] = $fecha_adquisicion_start;
            }
            if (!empty($fecha_adquisicion_end)) {
                $where_conditions .= " AND a.fecha <= :fecha_adq_end";
                $params[':fecha_adq_end'] = $fecha_adquisicion_end;
            }
            $countQuery = "SELECT COUNT(a.id) FROM activo a LEFT JOIN trabajador t ON a.custodio = t.id LEFT JOIN estatus_administrativo esa ON a.estatus_administrativo = esa.id LEFT JOIN gerencia_adm ga ON t.gerencia = ga.id LEFT JOIN sede_adm sa ON a.sede_adm = sa.id " . $where_conditions;
            $stmtCount = $pdo->prepare($countQuery);
            foreach ($params as $key => $value) {
                $param_type = (strpos($key, 'id_param') !== false || strpos($key, 'gerencia_id') !== false) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmtCount->bindValue($key, $value, $param_type);
            }
            $stmtCount->execute();
            $totalProperties = $stmtCount->fetchColumn();
            $limit_clause = '';
            $offset_clause = '';
            if (!$getAll) {
                $offset = ($page - 1) * $limit;
                $limit_clause = "LIMIT :limit OFFSET :offset";
            }
            $dataQuery = "
                SELECT a.id, a.codigo_activo_fijo, a.descripcion, a.marca, a.modelo, a.serial,
                esf.estatus AS estado_fisico_name, esa.estatus AS estatus_administrativo_name,
                a.unidad, CONCAT(t.nombres, ' ', t.apellidos) AS custodio_name, t.cedula AS custodio_cedula,
                c.cargo AS cargo_custodio, ga.gerencia AS gerencia_custodio, sa.sede AS sede_adm,
                a.observacion, a.documento, a.fecha, a.monto
                FROM activo a
                LEFT JOIN estatus_fisico esf ON a.estatus_fisico = esf.id
                LEFT JOIN estatus_administrativo esa ON a.estatus_administrativo = esa.id
                LEFT JOIN trabajador t ON a.custodio = t.id
                LEFT JOIN cargo c ON t.cargo = c.id
                LEFT JOIN gerencia_adm ga ON t.gerencia = ga.id
                LEFT JOIN sede_adm sa ON a.sede_adm = sa.id
                " . $where_conditions . " ORDER BY a.id ASC " . $limit_clause;
            $stmtData = $pdo->prepare($dataQuery);
            foreach ($params as $key => $value) {
                $param_type = (strpos($key, 'id_param') !== false || strpos($key, 'gerencia_id') !== false) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmtData->bindValue($key, $value, $param_type);
            }
            if (!$getAll) {
                $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmtData->execute();
            $properties = $stmtData->fetchAll(PDO::FETCH_ASSOC);
            foreach ($properties as &$row) {
                $monto_from_db_str = $row['monto'] ?? '';
                if (is_numeric($monto_from_db_str)) {
                    $row['monto'] = "VEF " . number_format((float)$monto_from_db_str, 2, ',', '.');
                } else {
                    $row['monto'] = "VEF 0,00";
                }
            }
            $headers = [
                'ID', 'Sede Adm', 'Cód. Act. Fijo', 'Descripción', 'Marca', 'Modelo', 'Serial',
                'Estado Físico', 'Estatus Admin.', 'Unidad', 'Custodio', 'Cédula Custodio',
                'Cargo Custodio', 'Gerencia Custodio', 'Observación', 'Documento', 'Fecha', 'Monto'
            ];
            echo json_encode([
                'success' => true,
                'properties' => $properties,
                'headers' => $headers,
                'totalProperties' => $totalProperties,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            error_log("Error en getTables: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    elseif ($action === 'getAssetCounts') {
        // =============================================
        // Acción: Obtener conteo de activos por estatus
        // =============================================
        $response = [
            'success' => false,
            'message' => '',
            'counts' => [
                'incorporado' => 0,
                'desincorporado' => 0,
                'sin_asignar' => 0
            ],
            'errors' => []
        ];
        try {
            $id_incorporado = 1;
            $id_desincorporado = 2;
            $id_sin_asignar = 3;
            $stmt_incorporado = $pdo->prepare("SELECT COUNT(*) FROM activo WHERE estatus_administrativo = :id_incorporado");
            $stmt_incorporado->bindParam(':id_incorporado', $id_incorporado, PDO::PARAM_INT);
            $stmt_incorporado->execute();
            $response['counts']['incorporado'] = (int) $stmt_incorporado->fetchColumn();
            $stmt_desincorporado = $pdo->prepare("SELECT COUNT(*) FROM activo WHERE estatus_administrativo = :id_desincorporado");
            $stmt_desincorporado->bindParam(':id_desincorporado', $id_desincorporado, PDO::PARAM_INT);
            $stmt_desincorporado->execute();
            $response['counts']['desincorporado'] = (int) $stmt_desincorporado->fetchColumn();
            $stmt_sin_asignar = $pdo->prepare("SELECT COUNT(*) FROM activo WHERE estatus_administrativo = :id_sin_asignar");
            $stmt_sin_asignar->bindParam(':id_sin_asignar', $id_sin_asignar, PDO::PARAM_INT);
            $stmt_sin_asignar->execute();
            $response['counts']['sin_asignar'] = (int) $stmt_sin_asignar->fetchColumn();
            $response['success'] = true;
            $response['message'] = 'Conteos obtenidos correctamente.';
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            $response['errors']['db'] = $e->getMessage();
            error_log("Error en getAssetCounts: " . $e->getMessage());
        }
        echo json_encode($response);
    }
    elseif ($action === 'exportAssetsToExcel') {
        // =============================================
        // Acción: Exportar activos a Excel
        // =============================================
        $search = $_GET['search'] ?? '';
        $estatus_fisico_id = $_GET['estatus_fisico_id'] ?? '';
        $estatus_administrativo_id = $_GET['estatus_administrativo_id'] ?? '';
        $gerencia_id = $_GET['gerencia_id'] ?? '';
        $fecha_adquisicion_start = $_GET['fecha_adquisicion_start'] ?? '';
        $fecha_adquisicion_end = $_GET['fecha_adquisicion_end'] ?? '';

        function getAssetsFromDatabase($search, $estatus_fisico_id, $estatus_administrativo_id, $gerencia_id, $fecha_adquisicion_start, $fecha_adquisicion_end, $pdo) {
            $sql = "SELECT
                        a.id,
                        a.codigo_activo_fijo,
                        a.descripcion,
                        a.marca,
                        a.modelo,
                        a.serial,
                        ef.estatus as estado_fisico_name,
                        ea.estatus as estatus_administrativo_name,
                        CONCAT(c.nombres, ' ', c.apellidos) as custodio_name,
                        c.cedula as custodio_cedula,
                        ca.cargo as cargo_custodio,
                        g.gerencia as gerencia_custodio,
                        a.observacion,
                        a.documento,
                        a.fecha,
                        a.monto
                    FROM activo a
                    LEFT JOIN estatus_fisico ef ON a.estatus_fisico = ef.id
                    LEFT JOIN estatus_administrativo ea ON a.estatus_administrativo = ea.id
                    LEFT JOIN trabajador c ON a.custodio = c.id
                    LEFT JOIN cargo ca ON c.cargo = ca.id
                    LEFT JOIN gerencia_adm g ON c.gerencia = g.id
                    WHERE 1=1";
            $params = [];
            if (!empty($search)) {
                $sql .= " AND (a.codigo_activo_fijo LIKE ? OR a.descripcion LIKE ? OR a.serial LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ? OR c.cedula LIKE ?)";
                $searchParam = "%$search%";
                $params = array_merge($params, array_fill(0, 6, $searchParam));
            }
            if (!empty($estatus_fisico_id)) {
                $sql .= " AND a.estatus_fisico = ?";
                $params[] = $estatus_fisico_id;
            }
            if (!empty($estatus_administrativo_id)) {
                $sql .= " AND a.estatus_administrativo = ?";
                $params[] = $estatus_administrativo_id;
            }
            if (!empty($gerencia_id)) {
                $sql .= " AND c.gerencia = ?";
                $params[] = $gerencia_id;
            }
            if (!empty($fecha_adquisicion_start)) {
                $sql .= " AND a.fecha >= ?";
                $params[] = $fecha_adquisicion_start;
            }
            if (!empty($fecha_adquisicion_end)) {
                $sql .= " AND a.fecha <= ?";
                $params[] = $fecha_adquisicion_end;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $assets = getAssetsFromDatabase($search, $estatus_fisico_id, $estatus_administrativo_id, $gerencia_id, $fecha_adquisicion_start, $fecha_adquisicion_end, $pdo);

        $headers = [
            'ID', 'Cód. Act. Fijo', 'Descripción', 'Marca', 'Modelo', 'Serial',
            'Estado Físico', 'Estatus Admin.', 'Custodio',
            'Cédula Custodio', 'Cargo Custodio', 'Gerencia Custodio', 'Asignación',
            'Observación', 'Adquisición', 'Documento', 'Fecha', 'Monto'
        ];

        $headerToDbColumnMap = [
            'ID' => 'id',
            'Cód. Act. Fijo' => 'codigo_activo_fijo',
            'Descripción' => 'descripcion',
            'Marca' => 'marca',
            'Modelo' => 'modelo',
            'Serial' => 'serial',
            'Estado Físico' => 'estado_fisico_name',
            'Estatus Admin.' => 'estatus_administrativo_name',
            'Custodio' => 'custodio_name',
            'Cédula Custodio' => 'custodio_cedula',
            'Cargo Custodio' => 'cargo_custodio',
            'Gerencia Custodio' => 'gerencia_custodio',
            'Asignación' => 'asignacion',
            'Observación' => 'observacion',
            'Adquisición' => 'adquisicion',
            'Documento' => 'documento',
            'Fecha' => 'fecha',
            'Monto' => 'monto'
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Activos Hidrofalcon');

        // Add headers
        $col = 1;
        foreach ($headers as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($columnLetter . '1', $header);
            $col++;
        }

        // Add data
        $row = 2;
        foreach ($assets as $asset) {
            $col = 1;
            foreach ($headers as $header) {
                $dbColumn = $headerToDbColumnMap[$header];
                $value = $asset[$dbColumn] ?? '';
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($columnLetter . $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', Coordinate::stringFromColumnIndex(count($headers))) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Prepare file for download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Activos_Hidrofalcon_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }
    else {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    }
}
else {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no válido.']);
}
?>
