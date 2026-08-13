<?php
session_start();
header('Content-Type: application/json');
require_once '../connect/connect.php';
// Inicia un array para la respuesta
$response = ['success' => false, 'message' => ''];
try {
    // Obtén el parámetro 'action' de la solicitud
    $action = $_REQUEST['action'] ?? '';

    // Eliminar usuario
    if ($action === 'deleteUser') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['id'])) {
            $response['message'] = 'ID de usuario no proporcionado.';
        } else {
            $userId = $input['id'];
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bindParam(1, $userId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Usuario eliminado exitosamente.';
                } else {
                    $response['message'] = 'No se encontró el usuario con el ID proporcionado.';
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                $response['message'] = 'Error al ejecutar la consulta: ' . $errorInfo[2];
            }
        }
    }
    // Obtener rangos disponibles
    else if ($action === 'getAvailableRanks') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['message'] = 'Método no permitido.';
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $currentUserRank = $input['current_user_rank'] ?? null;
            if ($currentUserRank === null) {
                $response['message'] = 'Rango de usuario no proporcionado.';
            } else {
                $ranks = [];
                if ($currentUserRank == 0) { // Administrador
                    $ranks = [
                        ['id' => 1, 'rango' => 'Asociado'],
                        ['id' => 2, 'rango' => 'Usuario'],
                        ['id' => 3, 'rango' => 'Administrador']
                    ];
                } elseif ($currentUserRank == 1) { // Usuario
                    $ranks = [
                        ['id' => 1, 'rango' => 'Asociado']
                    ];
                } else {
                    $ranks = [
                        ['id' => 1, 'rango' => 'Asociado']
                    ];
                }
                $response['success'] = true;
                $response['ranks'] = $ranks;
            }
        }
    }
    // Obtener rango de usuario
    else if ($action === 'getUserRank') {
        if (!isset($_GET['user_id'])) {
            $response['message'] = 'ID de usuario no proporcionado.';
        } else {
            $userId = $_GET['user_id'];
            $stmt = $pdo->prepare("SELECT rango FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if ($user) {
                $response['success'] = true;
                $response['rank'] = $user['rango'];
            } else {
                $response['message'] = 'Usuario no encontrado.';
            }
        }
    }
    // Obtener usuarios
    else if ($action === 'getUsers') {
        if (!isset($_SESSION['usuario_id'])) {
            $response['message'] = 'Acceso no autorizado.';
            http_response_code(401);
        } else {
            $searchTerm = $_GET['search'] ?? '';
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $currentUserId = $_SESSION['usuario_id'];
            $page = max(1, intval($page));
            $limit = max(1, min(100, intval($limit)));
            $offset = ($page - 1) * $limit;
            $baseQuery = "FROM usuarios";
            $params = [];
            $whereClause = " WHERE id != :currentUserId";
            if (!empty($searchTerm)) {
                $whereClause .= " AND (usuario LIKE :search OR nombres LIKE :search OR apellidos LIKE :search OR cedula LIKE :search)";
                $params[':search'] = '%' . $searchTerm . '%';
            }
            $countQuery = "SELECT COUNT(*) " . $baseQuery . $whereClause;
            $stmtCount = $pdo->prepare($countQuery);
            $params[':currentUserId'] = $currentUserId;
            $stmtCount->execute($params);
            $totalUsers = $stmtCount->fetchColumn();
            $dataQuery = "SELECT id, nombres, apellidos, cedula, cargo, gerencia, usuario, rango, estado " . $baseQuery . $whereClause;
            $dataQuery .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
            $stmtData = $pdo->prepare($dataQuery);
            $stmtData->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
            if (!empty($searchTerm)) {
                $stmtData->bindParam(':search', $params[':search'], PDO::PARAM_STR);
            }
            $stmtData->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmtData->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmtData->execute();
            $users = $stmtData->fetchAll(PDO::FETCH_ASSOC);
            $headers = ['ID', 'Nombres', 'Apellidos', 'Cédula', 'Cargo', 'Gerencia', 'Usuario', 'Rango', 'Estado'];
            $response['success'] = true;
            $response['users'] = $users;
            $response['totalUsers'] = $totalUsers;
            $response['headers'] = $headers;
            $response['message'] = 'Usuarios obtenidos exitosamente.';
        }
    }
    // Obtener bitácora
    else if ($action === 'getBitacora') {
        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;
        $offset = ($page - 1) * $limit;
        $countQuery = "SELECT COUNT(*) FROM bitacora WHERE mensaje LIKE :search";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute([':search' => '%' . $search . '%']);
        $totalLogs = $countStmt->fetchColumn();
        $dataQuery = "SELECT id, id_usuario, fecha, mensaje FROM bitacora WHERE mensaje LIKE :search ORDER BY fecha DESC LIMIT :limit OFFSET :offset";
        $dataStmt = $pdo->prepare($dataQuery);
        $dataStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();
        $logs = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
        $response['logs'] = $logs;
        $response['totalLogs'] = $totalLogs;
        $response['message'] = 'Bitácora cargada exitosamente.';
    }
    // Actualizar estado de usuario
    else if ($action === 'updateUserStatus') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['message'] = 'Método de solicitud no permitido.';
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!isset($input['id']) || !isset($input['status'])) {
                $response['message'] = 'Datos incompletos. Se requiere ID y status.';
            } else {
                $userId = $input['id'];
                $newStatus = $input['status'];
                $sql = "UPDATE usuarios SET estado = :status WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':status', $newStatus, PDO::PARAM_STR);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Estado del usuario actualizado.';
                } else {
                    $response['message'] = 'No se encontró el usuario o el estado no cambió.';
                }
            }
        }
    }
    // Actualizar rango de usuario
    else if ($action === 'updateUserRank') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['message'] = 'Método no permitido.';
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['user_id'] ?? null;
            $newRank = $input['new_rank'] ?? null;
            if (!$userId || !$newRank) {
                $response['message'] = 'Datos incompletos.';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET rango = ? WHERE id = ?");
                $stmt->execute([$newRank, $userId]);
                $response['success'] = true;
                $response['message'] = 'Rango actualizado correctamente.';
            }
        }
    }
    // Acción no reconocida
    else {
        $response['message'] = 'Acción no reconocida.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}
echo json_encode($response);
?>
