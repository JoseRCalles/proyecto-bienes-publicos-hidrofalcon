<?php
session_start();
header('Content-Type: application/json');

// --- VALIDACIÓN DE SESIÓN (solo si action=check) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'check') {
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        header('Location: /systemaHidrofalcon/frontend/pages/no_autorizado.php');
        exit();
    } else {
        echo json_encode([
            'success' => true,
            'usuario_id' => $_SESSION['usuario_id'],
            'nombre_usuario' => $_SESSION['nombre_usuario'] ?? null,
            'rango_id' => $_SESSION['rango_id'] ?? null
        ]);
        exit();
    }
}

require_once '../connect/connect.php';

$response = array('success' => false, 'errors' => array());
$max_failed_attempts = 3;

// --- LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $nombre_usuario = trim($_POST['usuario'] ?? '');
    $contrasena_ingresada = $_POST['contrasena'] ?? '';

    if (empty($nombre_usuario)) {
        $response['errors']['usuario'] = "El nombre de usuario es requerido.";
    }
    if (empty($contrasena_ingresada)) {
        $response['errors']['contrasena'] = "La contraseña es requerida.";
    }

    if (!empty($response['errors'])) {
        echo json_encode($response);
        exit();
    }

    try {
        $sql = "SELECT id, contrasena, rango, intentos, estado FROM usuarios WHERE usuario = :usuario";
        $stmt = $pdo->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta de login.");
        }

        $stmt->bindParam(':usuario', $nombre_usuario, PDO::PARAM_STR);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila) {
            $current_attempts = $fila['intentos'];
            $account_status = $fila['estado'];

            if ($account_status === 'bloqueado') {
                $response['errors']['usuario'] = "Tu cuenta está bloqueada. Por favor, contacta al administrador.";
                echo json_encode($response);
                exit();
            }

            if ($account_status === 'restringido') {
                $response['errors']['usuario'] = "Tu cuenta está restringida. Por favor, contacta al administrador.";
                echo json_encode($response);
                exit();
            }

            $hashed_password_from_db = $fila['contrasena'];

            if (password_verify($contrasena_ingresada, $hashed_password_from_db)) {
                $_SESSION['usuario_id'] = $fila['id'];
                $_SESSION['nombre_usuario'] = $nombre_usuario;
                $_SESSION['rango_id'] = $fila['rango'];

                $update_sql = "UPDATE usuarios SET intentos = 0, estado = 'activo' WHERE id = :id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(':id', $fila['id'], PDO::PARAM_INT);
                $update_stmt->execute();

                $response['success'] = true;
            } else {
                $response['errors']['contrasena'] = "Usuario o contraseña incorrectos.";
                $response['success'] = false;

                $new_attempts = $current_attempts + 1;
                $new_status = $account_status;

                if ($new_attempts >= $max_failed_attempts) {
                    $new_status = 'bloqueado';
                    $response['errors']['contrasena'] = "Tu cuenta ha sido bloqueada debido a demasiados intentos fallidos.";
                }

                $update_sql = "UPDATE usuarios SET intentos = :attempts, estado = :status WHERE id = :id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(':attempts', $new_attempts, PDO::PARAM_INT);
                $update_stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
                $update_stmt->bindParam(':id', $fila['id'], PDO::PARAM_INT);
                $update_stmt->execute();
            }
        } else {
            $response['errors']['usuario'] = "Usuario o contraseña incorrectos.";
            $response['success'] = false;
        }

    } catch (PDOException $e) {
        error_log("Error al procesar el login (PDO): " . $e->getMessage());
        $response['errors']['general'] = 'Error de base de datos. Por favor, inténtelo de nuevo más tarde.';
        $response['success'] = false;
    } catch (Exception $e) {
        error_log("Error al procesar el login (General): " . $e->getMessage());
        $response['errors']['general'] = 'Error interno del servidor al procesar la solicitud.';
        $response['success'] = false;
    }

    echo json_encode($response);
    exit();
}

// --- LOGOUT ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'logout') {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // Redirección a login (puedes cambiar la ruta si lo necesitas)
    header('Location: /systemaHidrofalcon/frontend/pages/login.php?sesion_cerrada=true');
    exit();
}

// --- Si no es login, logout ni check ---
$response['errors']['general'] = "Método o acción no válida.";
$response['success'] = false;
echo json_encode($response);
exit();
?>