<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../connect/connect.php';

// Iniciar sesión y verificar autenticación
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Acciones públicas que no requieren autenticación
$public_actions = ['get_cargos', 'get_employees'];

$action = $_REQUEST['action'] ?? '';
if (!in_array($action, $public_actions) && (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

/**
 * Función para obtener los datos actuales de un empleado por ID
 */
function getEmpleadoActual(PDO $pdo, $id_empleado) {
    if (empty($id_empleado)) {
        return null;
    }
    try {
        $sql = "SELECT cedula, nombres, apellidos, telefono FROM trabajador WHERE id = :id_empleado";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener datos de empleado: " . $e->getMessage());
        return null;
    }
}

/**
 * Función para registrar un evento en la bitácora.
 */
function logBitacora($pdo, $id_usuario, $mensaje) {
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

// =================================================================
// LÓGICA PARA OBTENER CARGOS (GET)
// =================================================================
if ($action === 'getCargos') {
    try {
        $stmt = $pdo->query("SELECT id, cargo AS nombre_cargo FROM cargo ORDER BY cargo ASC");
        $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $cargos]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error de base de datos: " . $e->getMessage()]);
    }
    exit;
}

// =================================================================
// LÓGICA PARA OBTENER EMPLEADOS (READ)
// =================================================================
elseif ($action === 'getEmployees') {
    $searchTerm = $_GET['search'] ?? '';
    $gerenciaId = $_GET['gerencia'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = (int) ($_GET['limit'] ?? 5);
    $showId = isset($_GET['show_id']) && $_GET['show_id'] === 'true';
    $offset = ($page - 1) * $limit;
    try {
        $countSql = "SELECT COUNT(t.id) AS total_count FROM trabajador t LEFT JOIN cargo c ON t.cargo = c.id LEFT JOIN gerencia_adm ga ON t.gerencia = ga.id WHERE 1=1";
        $countParams = [];
        if (!empty($searchTerm)) {
            $countSql .= " AND (t.cedula LIKE :searchTerm OR t.nombres LIKE :searchTerm OR t.apellidos LIKE :searchTerm)";
            $countParams[':searchTerm'] = '%' . $searchTerm . '%';
        }
        if (!empty($gerenciaId)) {
            $countSql .= " AND ga.id = :gerenciaId";
            $countParams[':gerenciaId'] = (int) $gerenciaId;
        }
        $stmtCount = $pdo->prepare($countSql);
        foreach ($countParams as $key => $value) {
            if ($key === ':gerenciaId') {
                $stmtCount->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmtCount->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmtCount->execute();
        $totalCount = $stmtCount->fetchColumn();
        $selectFields = $showId ? "t.id," : "";
        $dataSql = "SELECT {$selectFields} t.cedula, t.nombres, t.apellidos, t.telefono, c.cargo AS nombre_cargo, ga.gerencia AS nombre_gerencia FROM trabajador t LEFT JOIN cargo c ON t.cargo = c.id LEFT JOIN gerencia_adm ga ON t.gerencia = ga.id WHERE 1=1";
        $dataParams = [];
        if (!empty($searchTerm)) {
            $dataSql .= " AND (t.cedula LIKE :searchTerm OR t.nombres LIKE :searchTerm OR t.apellidos LIKE :searchTerm)";
            $dataParams[':searchTerm'] = '%' . $searchTerm . '%';
        }
        if (!empty($gerenciaId)) {
            $dataSql .= " AND ga.id = :gerenciaId";
            $dataParams[':gerenciaId'] = (int) $gerenciaId;
        }
        $dataSql .= " ORDER BY t.nombres ASC LIMIT :limit OFFSET :offset";

        $stmtData = $pdo->prepare($dataSql);
        foreach ($dataParams as $key => $value) {
            if ($key === ':gerenciaId') {
                $stmtData->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmtData->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmtData->execute();
        $employees = $stmtData->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'employees' => $employees, 'total_employees' => $totalCount]);
    } catch (PDOException $e) {
        error_log("Error buscando empleados (PDO): " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo realizar la búsqueda de trabajadores. Error de DB.']);
    } catch (Exception $e) {
        error_log("Error buscando empleados (General): " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo realizar la búsqueda de trabajadores. Error inesperado.']);
    }
    exit;
}

// =================================================================
// LÓGICA PARA AGREGAR UN EMPLEADO (ADD)
// =================================================================
elseif ($action === 'addEmployee') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos o incompletos.']);
        exit;
    }
    $cedula = $input['cedula'] ?? null;
    $nombres = $input['nombres'] ?? null;
    $apellidos = $input['apellidos'] ?? null;
    $telefono = $input['telefono'] ?? null;
    $gerencia_id = $input['gerencia_id'] ?? null;
    $cargo_id = $input['cargo_id'] ?? null;
    if (!$cedula || !$nombres || !$apellidos || !$telefono || !$gerencia_id || !$cargo_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios para agregar el empleado.']);
        exit;
    }
    try {
        // 1. Verificar si ya existe un empleado con la misma cédula
        $sql_check_cedula = "SELECT COUNT(*) FROM trabajador WHERE cedula = :cedula";
        $stmt_check_cedula = $pdo->prepare($sql_check_cedula);
        $stmt_check_cedula->bindParam(':cedula', $cedula);
        $stmt_check_cedula->execute();
        $count_cedula = $stmt_check_cedula->fetchColumn();
        if ($count_cedula > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe un empleado con esta cédula."]);
            exit();
        }
        // 2. Verificar si ya existe un empleado con el mismo nombre y apellido
        $sql_check_nombre = "SELECT COUNT(*) FROM trabajador WHERE nombres = :nombres AND apellidos = :apellidos";
        $stmt_check_nombre = $pdo->prepare($sql_check_nombre);
        $stmt_check_nombre->bindParam(':nombres', $nombres);
        $stmt_check_nombre->bindParam(':apellidos', $apellidos);
        $stmt_check_nombre->execute();
        $count_nombre = $stmt_check_nombre->fetchColumn();
        if ($count_nombre > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe un empleado con este nombre y apellido."]);
            exit();
        }
        // 3. Insertar el nuevo empleado
        $sql_insert = "INSERT INTO trabajador (cedula, nombres, apellidos, telefono, gerencia, cargo)
                      VALUES (:cedula, :nombres, :apellidos, :telefono, :gerencia_id, :cargo_id)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->bindParam(':cedula', $cedula);
        $stmt_insert->bindParam(':nombres', $nombres);
        $stmt_insert->bindParam(':apellidos', $apellidos);
        $stmt_insert->bindParam(':telefono', $telefono);
        $stmt_insert->bindParam(':gerencia_id', $gerencia_id, PDO::PARAM_INT);
        $stmt_insert->bindParam(':cargo_id', $cargo_id, PDO::PARAM_INT);
        if ($stmt_insert->execute()) {
            $nuevo_id = $pdo->lastInsertId();
            logBitacora($pdo, $_SESSION['usuario_id'], "Nuevo empleado agregado (ID: $nuevo_id) - Cédula: $cedula, Nombre: $nombres $apellidos, Teléfono: $telefono, Gerencia ID: $gerencia_id, Cargo ID: $cargo_id");
            echo json_encode(["success" => true, "message" => "Empleado agregado correctamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error al insertar el empleado."]);
        }
    } catch(PDOException $e) {
        error_log("Error al insertar empleado: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error al insertar el empleado: " . $e->getMessage()]);
    }
    exit;
}

// =================================================================
// LÓGICA PARA ACTUALIZAR UN EMPLEADO (UPDATE)
// =================================================================
elseif ($action === 'updateEmployee') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Faltan datos para la actualización.']);
        exit;
    }
    $id = $input['id'] ?? null;
    $cedula = $input['cedula'] ?? null;
    $nombres = $input['nombres'] ?? null;
    $apellidos = $input['apellidos'] ?? null;
    $telefono = $input['telefono'] ?? null;
    if (!$id || !$cedula || !$nombres || !$apellidos || !$telefono) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Faltan datos para la actualización.']);
        exit;
    }
    try {
        $empleado_actual = getEmpleadoActual($pdo, $id);
        if (!$empleado_actual) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No se encontró el empleado con el ID proporcionado.']);
            exit;
        }
        $sql = "UPDATE trabajador SET cedula = :cedula, nombres = :nombres, apellidos = :apellidos, telefono = :telefono WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
        $stmt->bindParam(':nombres', $nombres, PDO::PARAM_STR);
        $stmt->bindParam(':apellidos', $apellidos, PDO::PARAM_STR);
        $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $usuario_id = $_SESSION['usuario_id'];
            $mensaje = "Actualización de empleado (ID: $id) - ";
            $cambios = [];
            if ($empleado_actual['cedula'] != $cedula) $cambios[] = "Cédula: de '{$empleado_actual['cedula']}' a '$cedula'";
            if ($empleado_actual['nombres'] != $nombres) $cambios[] = "Nombres: de '{$empleado_actual['nombres']}' a '$nombres'";
            if ($empleado_actual['apellidos'] != $apellidos) $cambios[] = "Apellidos: de '{$empleado_actual['apellidos']}' a '$apellidos'";
            if ($empleado_actual['telefono'] != $telefono) $cambios[] = "Teléfono: de '{$empleado_actual['telefono']}' a '$telefono'";
            $mensaje .= !empty($cambios) ? implode(', ', $cambios) : "Sin cambios detectados (valores idénticos)";
            logBitacora($pdo, $usuario_id, $mensaje);
            echo json_encode(['success' => true, 'message' => 'Trabajador actualizado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo ejecutar la actualización.']);
        }
    } catch (PDOException $e) {
        error_log("Error al actualizar trabajador: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error de base de datos al actualizar el trabajador.']);
    }
    exit;
}

// =================================================================
// LÓGICA PARA ELIMINAR UN EMPLEADO (DELETE)
// =================================================================
elseif ($action === 'deleteEmployee') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Faltan datos para la eliminación.']);
        exit;
    }
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Falta el ID del empleado a eliminar.']);
        exit;
    }
    try {
        $empleado = getEmpleadoActual($pdo, $id);
        if (!$empleado) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No se encontró el empleado con el ID proporcionado.']);
            exit;
        }
        $sql = "DELETE FROM trabajador WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            logBitacora($pdo, $_SESSION['usuario_id'], "Eliminación de empleado (ID: $id) - Cédula: {$empleado['cedula']}, Nombre: {$empleado['nombres']} {$empleado['apellidos']}");
            echo json_encode(['success' => true, 'message' => 'Trabajador eliminado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo ejecutar la eliminación.']);
        }
    } catch (PDOException $e) {
        error_log("Error al eliminar trabajador: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar el empleado porque tiene registros relacionados en otras tablas.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error de base de datos al eliminar el trabajador.']);
        }
    }
    exit;
}

// =================================================================
// Manejar acción no reconocida
// =================================================================
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Acción no reconocida.']);
}
?>
