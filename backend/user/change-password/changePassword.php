<?php
session_start();
require '../../connect/connect.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no válido.']);
    exit();
}

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetro "action" no proporcionado.']);
    exit();
}

$action = $_POST['action'];

if ($action === 'changePassword') {
    // Cambiar contraseña (usando ID de sesión)
    if (!isset($_SESSION['restablecer_user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
        exit();
    }

    $user_id = $_SESSION['restablecer_user_id'];
    $new_password = $_POST['password'] ?? '';
    $confirm_new_password = $_POST['confirm-password'] ?? '';

    if (empty($new_password) || empty($confirm_new_password)) {
        echo json_encode(['success' => false, 'message' => 'Ambos campos de contraseña son obligatorios.']);
        exit();
    }

    if ($new_password !== $confirm_new_password) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
        exit();
    }

    try {
        $query_get_password = "SELECT contrasena FROM usuarios WHERE id = :user_id";
        $stmt_get_password = $pdo->prepare($query_get_password);
        $stmt_get_password->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_get_password->execute();
        $user = $stmt_get_password->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($new_password, $user['contrasena'])) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña no puede ser la misma que la actual.']);
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $pdo->beginTransaction();

        $query_update_password = "UPDATE usuarios SET contrasena = :hashed_password WHERE id = :user_id";
        $stmt_update_password = $pdo->prepare($query_update_password);
        $stmt_update_password->bindParam(':hashed_password', $hashed_password);
        $stmt_update_password->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_update_password->execute();

        $query_update_status = "UPDATE usuarios SET estado = 'activo', intentos = 0 WHERE id = :user_id";
        $stmt_update_status = $pdo->prepare($query_update_status);
        $stmt_update_status->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_update_status->execute();

        $pdo->commit();

        if ($stmt_update_password->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la contraseña.']);
        } else {
            echo json_encode(['success' => true, 'message' => "Contraseña actualizada exitosamente."]);
            session_unset();
            session_destroy();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al cambiar la contraseña: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error general: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error inesperado.']);
    }

} elseif ($action === 'verifySecurityAnswers') {
    // Verificar respuestas de seguridad (usando ID de sesión)
    if (!isset($_SESSION['recovery_user_id'])) {
        echo json_encode(['response' => false, 'error' => 'Sesión inválida.']);
        exit();
    }

    $idUsuario = $_SESSION['recovery_user_id'];
    $respuesta1Enviada = trim($_POST['respuesta1'] ?? '');
    $respuesta2Enviada = trim($_POST['respuesta2'] ?? '');
    $idPregunta1 = $_POST['id_pregunta1'] ?? '';
    $idPregunta2 = $_POST['id_pregunta2'] ?? '';

    if (empty($respuesta1Enviada) || empty($respuesta2Enviada) || empty($idPregunta1) || empty($idPregunta2)) {
        echo json_encode(['response' => false, 'error' => 'Faltan parámetros.']);
        exit();
    }

    try {
        $sql = "SELECT intentos, estado FROM usuarios WHERE id = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioDB['estado'] === 'restringido') {
            echo json_encode(['response' => false, 'error' => 'Cuenta restringida.']);
            exit();
        }

        $sqlRespuestas = "SELECT id_pregunta, respuesta FROM resp_seguridad WHERE id_usuario = :id_usuario AND (id_pregunta = :id_pregunta1 OR id_pregunta = :id_pregunta2)";
        $stmtRespuestas = $pdo->prepare($sqlRespuestas);
        $stmtRespuestas->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmtRespuestas->bindParam(':id_pregunta1', $idPregunta1, PDO::PARAM_INT);
        $stmtRespuestas->bindParam(':id_pregunta2', $idPregunta2, PDO::PARAM_INT);
        $stmtRespuestas->execute();
        $respuestasDB = $stmtRespuestas->fetchAll(PDO::FETCH_ASSOC);

        if (count($respuestasDB) < 2) {
            echo json_encode(['response' => false, 'error' => 'Preguntas de seguridad no encontradas.']);
            exit();
        }

        $respuestasCorrectas = 0;
        foreach ($respuestasDB as $row) {
            if ($row['id_pregunta'] == $idPregunta1 && password_verify($respuesta1Enviada, $row['respuesta'])) {
                $respuestasCorrectas++;
            }
            if ($row['id_pregunta'] == $idPregunta2 && password_verify($respuesta2Enviada, $row['respuesta'])) {
                $respuestasCorrectas++;
            }
        }

        if ($respuestasCorrectas === 2) {
            $update_sql = "UPDATE usuarios SET intentos = 0 WHERE id = :id_usuario";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $update_stmt->execute();

            $restablecer_token = bin2hex(random_bytes(32));
            $_SESSION['restablecer_token'] = $restablecer_token;
            $_SESSION['restablecer_user_id'] = $idUsuario;

            echo json_encode([
                'response' => true,
                'message' => 'Respuestas correctas.',
                'token_para_restablecer' => $restablecer_token
            ]);
        } else {
            $new_attempts = $usuarioDB['intentos'] + 1;
            $new_status = ($new_attempts >= 3) ? 'restringido' : 'activo';
            $error_message = ($new_attempts >= 3) ? 'Cuenta restringida por intentos fallidos.' : 'Respuestas incorrectas.';

            $update_sql = "UPDATE usuarios SET intentos = :intentos, estado = :estado WHERE id = :id_usuario";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':intentos', $new_attempts, PDO::PARAM_INT);
            $update_stmt->bindParam(':estado', $new_status, PDO::PARAM_STR);
            $update_stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $update_stmt->execute();

            echo json_encode(['response' => false, 'error' => $error_message]);
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(['response' => false, 'error' => 'Error interno.']);
    }

} elseif ($action === 'verifyUserCedula') {
    // Verificar usuario y guardar ID en sesión
    if (!isset($_POST['usuario'])) {
        echo json_encode(['response' => false, 'error' => 'Usuario no proporcionado.']);
        exit();
    }

    $usuario = $_POST['usuario'];

    try {
        $sql = "SELECT id, usuario, estado FROM usuarios WHERE usuario = :usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if ($row['estado'] === 'restringido') {
                echo json_encode(['response' => false, 'error' => 'Cuenta restringida.']);
                exit();
            }
            $_SESSION['recovery_user_id'] = $row['id'];
            echo json_encode(['response' => true]);
        } else {
            echo json_encode(['response' => false, 'error' => 'Usuario no encontrado.']);
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(['response' => false, 'error' => 'Error interno.']);
    }

} elseif ($action === 'getSecurityQuestions') {
    // Obtener preguntas de seguridad (usando ID de sesión)
    if (!isset($_SESSION['recovery_user_id'])) {
        echo json_encode(['error' => 'Sesión inválida.']);
        exit();
    }

    $id_usuario = $_SESSION['recovery_user_id'];

    try {
        $sql = "SELECT id_pregunta FROM resp_seguridad WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $id_preguntas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!empty($id_preguntas)) {
            $placeholders = [];
            foreach ($id_preguntas as $index => $id) {
                $placeholders[] = ":id" . $index;
            }
            $inClause = implode(',', $placeholders);
            $sqlPreguntas = "SELECT id, pregunta FROM preg_seguridad WHERE id IN ($inClause)";
            $stmtPreguntas = $pdo->prepare($sqlPreguntas);

            foreach ($id_preguntas as $index => $id) {
                $stmtPreguntas->bindValue(":id" . $index, $id, PDO::PARAM_INT);
            }

            $stmtPreguntas->execute();
            $response = $stmtPreguntas->fetchAll();
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Preguntas no encontradas.']);
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(['error' => 'Error interno.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
}
?>
