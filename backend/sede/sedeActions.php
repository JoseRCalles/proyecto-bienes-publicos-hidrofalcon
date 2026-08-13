<?php
// systemahidrofalcon/backend/getdata/get-sedes.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once '../../backend/connect/connect.php';

// Iniciar sesión y verificar autenticación
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

/**
 * Función para obtener los datos de una sede por ID
 */
function getSedeData(PDO $pdo, $id_sede) {
    if (empty($id_sede)) {
        return null;
    }
    try {
        $sql = "SELECT sede, municipio FROM sede_adm WHERE id = :id_sede";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_sede', $id_sede, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener datos de sede: " . $e->getMessage());
        return null;
    }
}

/**
 * Función para registrar un evento en la bitácora.
 */
function logBitacora($id_usuario, $mensaje) {
    global $pdo;
    try {
        $sql = "INSERT INTO bitacora (id_usuario, mensaje) VALUES (:id_usuario, :mensaje)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':mensaje' => $mensaje
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error de bitácora para usuario ID $id_usuario: " . $e->getMessage());
        return false;
    }
}

// Manejar preflight request de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        if ($action === 'updateSede') {
            // Lógica para actualizar sede
            if (isset($data['id']) && isset($data['sede']) && isset($data['municipio'])) {
                $id = $data['id'];
                $nombre_sede = $data['sede'];
                $municipio = $data['municipio'];

                $sede_actual = getSedeData($pdo, $id);
                if (!$sede_actual) {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la sede con el ID proporcionado.']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE sede_adm SET sede = ?, municipio = ? WHERE id = ?");
                $success = $stmt->execute([$nombre_sede, $municipio, $id]);
                if ($success && $stmt->rowCount() > 0) {
                    $usuario_id = $_SESSION['usuario_id'];
                    $mensaje = "Actualización de sede (ID: $id) - ";
                    if ($sede_actual['sede'] != $nombre_sede) {
                        $mensaje .= "Nombre: de '{$sede_actual['sede']}' a '$nombre_sede'. ";
                    }
                    if ($sede_actual['municipio'] != $municipio) {
                        $mensaje .= "Municipio: de '{$sede_actual['municipio']}' a '$municipio'.";
                    }
                    if ($sede_actual['sede'] == $nombre_sede && $sede_actual['municipio'] == $municipio) {
                        $mensaje .= "Sin cambios detectados (valores idénticos).";
                    }
                    logBitacora($usuario_id, $mensaje);
                    echo json_encode(['success' => true, 'message' => 'Sede actualizada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la sede. ID no encontrado o datos idénticos.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar la sede.']);
            }

        } elseif ($action === 'addSede') {
            // Lógica para agregar sede
            if (isset($data['nombre_sede']) && isset($data['direccion_sede'])) {
                $nombre_sede = $data['nombre_sede'];
                $direccion_sede = $data['direccion_sede'];

                $sql_check = "SELECT COUNT(*) FROM sede_adm WHERE sede = :nombre_sede";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->bindParam(':nombre_sede', $nombre_sede);
                $stmt_check->execute();
                $count = $stmt_check->fetchColumn();
                if ($count > 0) {
                    echo json_encode(["success" => false, "message" => "Ya existe una sede con este nombre."]);
                    exit();
                }

                $sql_insert = "INSERT INTO sede_adm (sede, municipio) VALUES (:nombre_sede, :direccion_sede)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':nombre_sede', $nombre_sede);
                $stmt_insert->bindParam(':direccion_sede', $direccion_sede);
                $stmt_insert->execute();
                $nuevo_id = $pdo->lastInsertId();

                $usuario_id = $_SESSION['usuario_id'];
                $mensaje = "Nueva sede agregada (ID: $nuevo_id) - Nombre: '$nombre_sede', Municipio: '$direccion_sede'";
                logBitacora($usuario_id, $mensaje);
                echo json_encode(["success" => true, "message" => "Sede agregada correctamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Datos inválidos."]);
            }

        } elseif ($action === 'deleteSede') {
            // Lógica para eliminar sede
            if (isset($data['id'])) {
                $id = $data['id'];
                $sede_data = getSedeData($pdo, $id);
                if (!$sede_data) {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la sede con el ID proporcionado.']);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM sede_adm WHERE id = ?");
                $success = $stmt->execute([$id]);
                if ($success && $stmt->rowCount() > 0) {
                    $usuario_id = $_SESSION['usuario_id'];
                    $nombre_sede = $sede_data['sede'];
                    $municipio = $sede_data['municipio'];
                    $mensaje = "Eliminación de sede (ID: $id) - Nombre: '$nombre_sede', Municipio: '$municipio'";
                    logBitacora($usuario_id, $mensaje);
                    echo json_encode(['success' => true, 'message' => 'Sede eliminada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la sede. ID no encontrado.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de sede no proporcionado para eliminar.']);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'Acción POST no reconocida.']);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'getSedesData';
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

        if ($action === 'getSedesData') {
            // Lógica para obtener sedes
            $query = "SELECT id, sede, municipio FROM sede_adm";
            if (!empty($searchTerm)) {
                $query .= " WHERE sede LIKE :searchTerm OR municipio LIKE :searchTerm";
            }
            $query .= " ORDER BY sede ASC";
            $stmt = $pdo->prepare($query);
            if (!empty($searchTerm)) {
                $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
            }
            $stmt->execute();
            $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'sedes' => $sedes,
                'message' => 'Sedes obtenidas correctamente.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción GET no reconocida.']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
    }
} catch (PDOException $e) {
    error_log("Error de BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos.',
        'error_details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado.',
        'error_details' => $e->getMessage()
    ]);
}
?>
