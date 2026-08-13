<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../connect/connect.php';

// Iniciar sesión y verificar autenticación
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Acciones públicas que no requieren autenticación
$public_actions = ['gerencias_solo', 'gerencias_con_encargado'];

$action = $_REQUEST['action'] ?? '';
if (!in_array($action, $public_actions) && (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id']))) {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

// Verificar si la conexión a la base de datos se estableció correctamente
if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Error al conectar a la base de datos.']);
    exit();
}

/**
 * Función para obtener los datos de una gerencia por ID
 */
function getGerenciaData(PDO $pdo, $id_gerencia, $con_encargado = false) {
    if (empty($id_gerencia)) {
        return null;
    }
    try {
        $sql = "SELECT gerencia" . ($con_encargado ? ", encargado" : "") . " FROM gerencia_adm WHERE id = :id_gerencia";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_gerencia', $id_gerencia, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener datos de gerencia: " . $e->getMessage());
        return null;
    }
}

/**
 * Función para registrar un evento en la bitácora.
 */
function logBitacora(PDO $pdo, $id_usuario, $mensaje) {
    try {
        $sql = "INSERT INTO bitacora (id_usuario, mensaje) VALUES (:id_usuario, :mensaje)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':mensaje' => $mensaje
        ]);
    } catch (PDOException $e) {
        error_log("Error de bitácora para usuario ID $id_usuario: " . $e->getMessage());
        return false;
    }
}

// Obtener datos de la solicitud
$requestData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    if ($requestData === null) {
        echo json_encode(['success' => false, 'status' => 'error', 'message' => 'JSON inválido recibido.']);
        exit();
    }
}

// Unir los datos de GET y POST para un manejo más sencillo
$data = array_merge($_GET, $requestData);
$action = $data['action'] ?? 'gerencias_solo';

// =================================================================
// LÓGICA PARA AGREGAR UNA GERENCIA (ADD)
// =================================================================
if ($action === 'addGerencia') {
    if (empty($data['gerencia']) || empty($data['encargado'])) {
        echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Faltan datos para la inserción.']);
        exit;
    }
    try {
        // 1. Verificar si el nombre de la gerencia ya existe
        $sql_check = "SELECT COUNT(*) FROM gerencia_adm WHERE gerencia = :gerencia";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':gerencia', $data['gerencia']);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Ya existe una gerencia con este nombre.']);
            exit();
        }
        // 2. Insertar la nueva gerencia
        $sql = "INSERT INTO gerencia_adm (gerencia, encargado) VALUES (:gerencia, :encargado)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':gerencia', $data['gerencia']);
        $stmt->bindParam(':encargado', $data['encargado']);
        $stmt->execute();
        $nuevo_id = $pdo->lastInsertId();
        // Registrar en bitácora
        logBitacora($pdo, $_SESSION['usuario_id'], "Inserción de nueva gerencia (ID: $nuevo_id) - Nombre: '{$data['gerencia']}', Encargado: '{$data['encargado']}'");
        echo json_encode(['success' => true, 'status' => 'created', 'message' => 'Gerencia insertada con éxito.', 'new_id' => $nuevo_id]);
    } catch (PDOException $e) {
        error_log("Error de base de datos en add_gerencia: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'status' => 'db_error', 'message' => 'Error de base de datos.']);
    }
    exit;
}

// =================================================================
// LÓGICA PARA ACTUALIZAR UNA GERENCIA (UPDATE)
// =================================================================
elseif ($action === 'updateGerencia') {
    if (empty($data['id']) || empty($data['gerencia']) || empty($data['encargado'])) {
        echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Faltan datos para la actualización.']);
        exit;
    }
    try {
        // Obtener datos actuales de la gerencia para la bitácora
        $gerencia_actual = getGerenciaData($pdo, $data['id'], true);
        if (!$gerencia_actual) {
            echo json_encode(['success' => false, 'status' => 'error', 'message' => 'No se encontró la gerencia con el ID proporcionado.']);
            exit;
        }
        $sql = "UPDATE gerencia_adm SET gerencia = :gerencia, encargado = :encargado WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':gerencia', $data['gerencia']);
        $stmt->bindParam(':encargado', $data['encargado']);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $usuario_id = $_SESSION['usuario_id'];
            $mensaje = "Actualización de gerencia (ID: {$data['id']}) - ";
            if ($gerencia_actual['gerencia'] != $data['gerencia']) {
                $mensaje .= "Nombre: de '{$gerencia_actual['gerencia']}' a '{$data['gerencia']}'. ";
            }
            if ($gerencia_actual['encargado'] != $data['encargado']) {
                $mensaje .= "Encargado: de '{$gerencia_actual['encargado']}' a '{$data['encargado']}'.";
            }
            if ($gerencia_actual['gerencia'] == $data['gerencia'] && $gerencia_actual['encargado'] == $data['encargado']) {
                $mensaje .= "Sin cambios detectados (valores idénticos).";
            }
            logBitacora($pdo, $usuario_id, $mensaje);
            echo json_encode(['success' => true, 'status' => 'updated', 'message' => 'Gerencia actualizada con éxito.']);
        } else {
            echo json_encode(['success' => false, 'status' => 'info', 'message' => 'No se encontraron cambios o la gerencia no existe.']);
        }
    } catch (PDOException $e) {
        error_log("Error de base de datos en update_gerencia: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'status' => 'db_error', 'message' => 'Error de base de datos.']);
    }
    exit;
}

// =================================================================
// LÓGICA PARA ELIMINAR UNA GERENCIA (DELETE)
// =================================================================
elseif ($action === 'deleteGerencia') {
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Falta el ID de la gerencia a eliminar.']);
        exit;
    }
    try {
        $gerencia_data = getGerenciaData($pdo, $data['id'], true);
        if (!$gerencia_data) {
            echo json_encode(['success' => false, 'status' => 'error', 'message' => 'No se encontró la gerencia con el ID proporcionado.']);
            exit;
        }
        $sql = "DELETE FROM gerencia_adm WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $usuario_id = $_SESSION['usuario_id'];
            $mensaje = "Eliminación de gerencia (ID: {$data['id']}) - Nombre: '{$gerencia_data['gerencia']}', Encargado: '{$gerencia_data['encargado']}'";
            logBitacora($pdo, $usuario_id, $mensaje);
            echo json_encode(['success' => true, 'status' => 'deleted', 'message' => 'Gerencia eliminada con éxito.']);
        } else {
            echo json_encode(['success' => false, 'status' => 'not_found', 'message' => 'La gerencia no existe o ya fue eliminada.']);
        }
    } catch (PDOException $e) {
        error_log("Error de base de datos en delete_gerencia: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'status' => 'db_error', 'message' => 'Error de base de datos.']);
    }
    exit;
}

// =================================================================
// LÓGICA PARA OBTENER GERENCIAS CON ENCARGADO (READ)
// =================================================================
elseif ($action === 'gerenciasConEncargado') {
    try {
        $searchValue = $data['search'] ?? '';
        $sql = "SELECT id, gerencia AS nombre_gerencia, encargado FROM gerencia_adm";
        $params = [];
        if (!empty($searchValue)) {
            $sql .= " WHERE gerencia LIKE :search OR encargado LIKE :search";
            $params[':search'] = '%' . $searchValue . '%';
        }
        $sql .= " ORDER BY gerencia ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $gerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'status' => 'data_fetched',
            'message' => 'Datos de gerencias obtenidos.',
            'gerencias' => $gerencias
        ]);
    } catch (PDOException $e) {
        error_log("Error de base de datos en gerencias_con_encargado: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'status' => 'db_error', 'message' => 'Error de base de datos.']);
    }
    exit;
}

// =================================================================
// LÓGICA PARA OBTENER SOLO GERENCIAS (READ)
// =================================================================
elseif ($action === 'gerenciasSolo') {
    try {
        $searchValue = $data['search'] ?? '';
        $sql = "SELECT id, gerencia AS nombre_gerencia FROM gerencia_adm";
        $params = [];
        if (!empty($searchValue)) {
            $sql .= " WHERE gerencia LIKE :search";
            $params[':search'] = '%' . $searchValue . '%';
        }
        $sql .= " ORDER BY gerencia ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $gerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'status' => 'data_fetched',
            'message' => 'Datos de gerencias obtenidos.',
            'gerencias' => $gerencias
        ]);
    } catch (PDOException $e) {
        error_log("Error de base de datos en gerencias_solo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'status' => 'db_error', 'message' => 'Error de base de datos.']);
    }
    exit;
}

// =================================================================
// Manejar acción no reconocida
// =================================================================
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Acción no reconocida.']);
}
?>
